"""
EWNexus 後端伺服器
功能：
  1. EVA AI 聊天（/api/chat）— 使用 Anthropic API
  2. 聯絡表單接收（/api/contact）— 自動回覆客戶 + Telegram 通知老闆
  3. Telegram Webhook（/api/telegram）— 老闆可直接在 Telegram 回覆客戶
  4. 靜態文件服務（HTML 頁面）

環境變數（.env）：
  ANTHROPIC_API_KEY=sk-ant-...
  TELEGRAM_BOT_TOKEN=123456:ABC-...
  TELEGRAM_CHAT_ID=-100xxxxxxxxxxxx  (老闆的 Telegram 頻道/群組)
  PORT=8769
"""

import os
import json
import time
import hashlib
import requests
from datetime import datetime
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs
import threading

# 載入 .env（如有）
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass

# ── 設定 ──────────────────────────────────────────────
ANTHROPIC_API_KEY  = os.getenv("ANTHROPIC_API_KEY", "")
TELEGRAM_BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN", "")
TELEGRAM_CHAT_ID   = os.getenv("TELEGRAM_CHAT_ID", "")
PORT               = int(os.getenv("PORT", 8769))
BASE_DIR           = os.path.dirname(os.path.abspath(__file__))

# EVA 人設 System Prompt
EVA_SYSTEM = """You are EVA, the friendly AI assistant for EWNexus — a Texas-based digital services company that builds websites and business tools for small businesses.

Key facts about EWNexus:
- Price: $500 all-in for Year 1 (website + hosting + business email + SEO + contact form)
- Renewal: $12/year from Year 2 (hosting + maintenance)
- Domain: billed at actual cost ($10–20/year)
- Launch time: 3 business days (same-day possible for simpler sites)
- Location: Texas-based, serving all 50 US states (remote-first)
- Contact: info@ewnexus.com
- Industries served: restaurants, retail, salons, plumbers, HVAC, auto repair, bakeries, law offices, gyms, and more

Services included in $500:
- 5-page professional website
- Mobile-responsive design
- Business email (yourname@yourdomain.com)
- SEO setup (meta tags, Google indexing)
- Contact form
- Hosting for Year 1
- Same-day revision turnaround

Business tools (add-on pricing, varies):
- Online ordering systems
- Booking / appointment forms
- Quote generators
- Inventory tools
- No monthly commissions or subscriptions

Your personality:
- Warm, direct, and helpful — like talking to a knowledgeable friend
- NOT corporate or salesy
- Speak plainly — the audience is restaurant owners, salon owners, contractors, not developers
- Always emphasize the value: $500 is less than one month of most competitors
- If asked about competitors (Wix, Squarespace, GoDaddy, Shopify): explain that those require ongoing monthly fees ($16–$49/month) and the owner does all the work. EWNexus does everything for a flat fee.
- If someone is ready to start: direct them to fill out the contact form or email info@ewnexus.com

Keep responses concise (2–4 sentences usually). Use bullet points only when listing multiple items. Do not use excessive markdown."""

# 暫存客戶對話（記憶 context，存記憶體，重啟清空）
_sessions: dict[str, list] = {}


# ── Telegram 工具函式 ──────────────────────────────────
def tg_send(text: str, parse_mode: str = "HTML") -> dict:
    """傳送訊息到老闆的 Telegram 頻道/群組"""
    if not TELEGRAM_BOT_TOKEN or not TELEGRAM_CHAT_ID:
        print("[TG] Token 或 Chat ID 未設定，略過")
        return {}
    url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
    payload = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text":    text,
        "parse_mode": parse_mode,
    }
    try:
        r = requests.post(url, json=payload, timeout=8)
        return r.json()
    except Exception as e:
        print(f"[TG] 傳送失敗: {e}")
        return {}


def tg_notify_contact(data: dict) -> None:
    """客戶填表 → 通知老闆"""
    name     = data.get("name", "N/A")
    email    = data.get("email", "N/A")
    phone    = data.get("phone", "N/A")
    business = data.get("business", "N/A")
    industry = data.get("industry", "N/A")
    state    = data.get("state", "N/A")
    message  = data.get("message", "N/A")
    ts       = datetime.now().strftime("%Y-%m-%d %H:%M")

    text = (
        f"🔔 <b>新詢價！EWNexus</b>\n"
        f"━━━━━━━━━━━━━━━\n"
        f"👤 <b>{name}</b>\n"
        f"🏪 {business} · {industry}\n"
        f"📍 {state}\n"
        f"📞 {phone}\n"
        f"✉️ {email}\n"
        f"━━━━━━━━━━━━━━━\n"
        f"💬 {message}\n"
        f"━━━━━━━━━━━━━━━\n"
        f"🕐 {ts}\n"
        f"➡️ 回覆此訊息直接回 {name}"
    )
    tg_send(text)


# ── Anthropic Chat ─────────────────────────────────────
def call_claude(session_id: str, user_msg: str) -> str:
    """呼叫 Claude API，回傳 EVA 回覆文字"""
    if not ANTHROPIC_API_KEY:
        return "EVA 目前無法連線（API Key 未設定）。請直接寄信至 info@ewnexus.com，我們會盡快回覆！"

    # 維護對話歷史（最多 20 輪）
    history = _sessions.setdefault(session_id, [])
    history.append({"role": "user", "content": user_msg})
    if len(history) > 40:
        history[:] = history[-40:]

    headers = {
        "x-api-key":         ANTHROPIC_API_KEY,
        "anthropic-version": "2023-06-01",
        "content-type":      "application/json",
    }
    payload = {
        "model":      "claude-haiku-4-5-20251001",
        "max_tokens": 400,
        "system":     EVA_SYSTEM,
        "messages":   history,
    }
    try:
        r = requests.post(
            "https://api.anthropic.com/v1/messages",
            headers=headers,
            json=payload,
            timeout=20,
        )
        data = r.json()
        if r.status_code != 200:
            print(f"[Claude] Error {r.status_code}: {data}")
            return "Sorry, I'm having trouble connecting right now. Please email info@ewnexus.com and we'll get back to you shortly!"
        reply = data["content"][0]["text"]
        history.append({"role": "assistant", "content": reply})
        return reply
    except Exception as e:
        print(f"[Claude] Exception: {e}")
        return "I'm having a connection issue. Please email info@ewnexus.com and we'll get back to you within a few hours!"


# ── HTTP Handler ───────────────────────────────────────
class EWNexusHandler(BaseHTTPRequestHandler):

    def log_message(self, fmt, *args):
        ts = datetime.now().strftime("%H:%M:%S")
        print(f"[{ts}] {fmt % args}")

    # CORS headers
    def _cors(self):
        self.send_header("Access-Control-Allow-Origin",  "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")

    def do_OPTIONS(self):
        self.send_response(200)
        self._cors()
        self.end_headers()

    def do_GET(self):
        parsed = urlparse(self.path)
        path   = parsed.path.rstrip("/") or "/index.html"

        # API: health check
        if path == "/api/health":
            self._json(200, {"status": "ok", "time": datetime.now().isoformat()})
            return

        # 靜態文件
        if path == "/":
            path = "/index.html"
        file_path = os.path.join(BASE_DIR, path.lstrip("/"))

        # 防目錄穿越
        if not os.path.abspath(file_path).startswith(BASE_DIR):
            self._json(403, {"error": "forbidden"})
            return

        if os.path.isfile(file_path):
            self._serve_file(file_path)
        else:
            self._json(404, {"error": "not found", "path": path})

    def do_POST(self):
        parsed   = urlparse(self.path)
        path     = parsed.path
        length   = int(self.headers.get("Content-Length", 0))
        raw_body = self.rfile.read(length) if length else b""

        try:
            body = json.loads(raw_body) if raw_body else {}
        except json.JSONDecodeError:
            body = {}

        # ── /api/chat — EVA 聊天 ──
        if path == "/api/chat":
            user_msg   = body.get("message", "").strip()
            session_id = body.get("session_id", "anon")

            if not user_msg:
                self._json(400, {"error": "message required"})
                return

            reply = call_claude(session_id, user_msg)
            self._json(200, {"reply": reply})
            return

        # ── /api/contact — 聯絡表單 ──
        if path == "/api/contact":
            required = ["name", "email"]
            missing  = [f for f in required if not body.get(f, "").strip()]
            if missing:
                self._json(400, {"error": f"Missing: {', '.join(missing)}"})
                return

            # 非同步通知 Telegram（不阻塞回應）
            threading.Thread(target=tg_notify_contact, args=(body,), daemon=True).start()

            self._json(200, {
                "success": True,
                "message": "Got it! We'll be in touch within 24 hours.",
            })
            return

        # ── /api/telegram — Telegram Webhook（老闆回覆客戶）──
        if path == "/api/telegram":
            # 收到 Telegram update，目前只記錄（可擴充為轉發回 email）
            print(f"[TG Webhook] {json.dumps(body, ensure_ascii=False)[:200]}")
            self._json(200, {"ok": True})
            return

        self._json(404, {"error": "route not found"})

    # ── 工具方法 ──────────────────────────────────────
    def _json(self, code: int, data: dict):
        body = json.dumps(data, ensure_ascii=False).encode()
        self.send_response(code)
        self.send_header("Content-Type",   "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self._cors()
        self.end_headers()
        self.wfile.write(body)

    def _serve_file(self, file_path: str):
        ext_map = {
            ".html": "text/html; charset=utf-8",
            ".css":  "text/css",
            ".js":   "application/javascript",
            ".json": "application/json",
            ".png":  "image/png",
            ".jpg":  "image/jpeg",
            ".svg":  "image/svg+xml",
            ".ico":  "image/x-icon",
            ".woff2":"font/woff2",
        }
        ext  = os.path.splitext(file_path)[1].lower()
        mime = ext_map.get(ext, "application/octet-stream")
        with open(file_path, "rb") as f:
            content = f.read()
        self.send_response(200)
        self.send_header("Content-Type",   mime)
        self.send_header("Content-Length", str(len(content)))
        self._cors()
        self.end_headers()
        self.wfile.write(content)


# ── 入口 ───────────────────────────────────────────────
if __name__ == "__main__":
    print(f"""
╔══════════════════════════════════════╗
║  EWNexus Server  →  http://localhost:{PORT}
║  API  /api/chat      EVA 聊天
║  API  /api/contact   聯絡表單 + Telegram
║  API  /api/health    健康檢查
╚══════════════════════════════════════╝

設定狀態：
  ANTHROPIC_API_KEY : {"✓ 已設定" if ANTHROPIC_API_KEY else "✗ 未設定（EVA fallback 模式）"}
  TELEGRAM_BOT_TOKEN: {"✓ 已設定" if TELEGRAM_BOT_TOKEN else "✗ 未設定（不會傳 Telegram）"}
  TELEGRAM_CHAT_ID  : {"✓ 已設定" if TELEGRAM_CHAT_ID else "✗ 未設定"}
""")
    server = HTTPServer(("0.0.0.0", PORT), EWNexusHandler)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n[停止] Server 已關閉")
