# EWNexus — System Handover Document
**Date:** 2026-05-22  
**Prepared for:** New machine / new operator

---

## 1. System Architecture

```
Visitor lands on ewnexus.com (Hostinger static hosting)
    ↓  clicks "Try Live Demo" → chatbot widget pops up
Chatbot Widget (site-render.js)
    ↓  POST /api/chat  →  Chatbot Server (Docker on VPS / EasyPanel)
    ↓  calls DeepSeek API (openai-compatible)
AI replies in character as the business bot (Mike / Alex / Sarah…)
    ↓  visitor interested → fills Contact Form
Owner receives notification → collects $500 → builds site
```

---

## 2. What's in This Package

```
ewnexus_handover/
├── HANDOVER.md              ← this file
├── EWNEXUS_KB_v1.md         ← full knowledge base (pricing, scripts, FAQs)
├── KEY.txt                  ← API keys (keep secure — do NOT share via email)
└── ewnexus/
    ├── HANDOVER.md          ← same file (also lives in repo root)
    ├── CLAUDE.md            ← AI site-building instruction set
    ├── docker-compose.yml   ← one-command chatbot deployment
    │
    ├── index.html           ← EWNexus homepage
    ├── about.html
    ├── how-it-works.html
    ├── pricing.html
    ├── contact.html
    ├── contract.html        ← digital contract / e-signature page
    ├── cases.html           ← demo showcase (7 industry cards)
    ├── privacy.html
    │
    ├── assets/
    │   ├── base.css         ← global CSS variables (colors, fonts)
    │   ├── site-layout.css  ← shared layout CSS for all pages
    │   └── site-render.js   ← rendering engine + AI chat widget
    │
    ├── demos/
    │   ├── DEMO_TireShop.html    ← AI: "Mike"
    │   ├── DEMO_AutoRepair.html  ← AI: "Alex"
    │   ├── DEMO_HVAC.html        ← AI: "Sarah"
    │   ├── DEMO_Bakery.html      ← AI: "Maria"
    │   ├── DEMO_LawnCare.html    ← AI: "Jake"
    │   ├── restaurant.html
    │   ├── plumber.html
    │   └── salon.html
    │
    ├── templates/
    │   ├── BASE_TEMPLATE.html    ← master template (driven by SITE config ~80 lines)
    │   ├── DEMO_TireShop.json    ← example client config
    │   └── site-config.schema.json  ← JSON schema for client configs
    │
    ├── clients/
    │   └── _TEMPLATE.json        ← new client credential template
    │
    └── chatbot/
        ├── server.py             ← Python chatbot backend
        ├── Dockerfile            ← python:3.11-slim, port 8000
        ├── requirements.txt      ← openai>=1.0.0, python-dotenv>=1.0.0
        ├── .env.example          ← environment variable template
        ├── DEPLOY.md             ← EasyPanel deploy guide
        └── clients/
            ├── ewnexus.json
            ├── DEMO_TireShop.json
            ├── DEMO_AutoRepair.json
            ├── DEMO_HVAC.json
            ├── DEMO_Bakery.json
            └── DEMO_LawnCare.json
```

---

## 3. Quick Start — Chatbot Server (Linux / Docker)

```bash
# 1. Unzip and enter the directory
unzip ewnexus_handover_20260522.zip
cd ewnexus_handover/ewnexus

# 2. Set up environment variables
cp chatbot/.env.example chatbot/.env
nano chatbot/.env          # fill in your API key (see KEY.txt)

# 3. Start the chatbot server
docker compose up -d

# 4. Verify it's running
curl http://localhost:8000/api/health
# Expected: {"status": "ok", "clients": ["ewnexus", "DEMO_TireShop", ...]}
```

> **Requirements:** Docker + Docker Compose installed on the Linux machine.  
> Install Docker: `curl -fsSL https://get.docker.com | sh`

---

## 4. Environment Variables

Edit `chatbot/.env` with values from `KEY.txt`:

| Variable | Required | Description |
|----------|----------|-------------|
| `DEEPSEEK_API_KEY` | ✅ Yes (if using DeepSeek) | Main AI API key — get from platform.deepseek.com |
| `ANTHROPIC_API_KEY` | ✅ Yes (if using Claude) | Alternative AI — get from console.anthropic.com |
| `PORT` | Optional | Chatbot server port (default: 8000) |

> **Note:** The server uses DeepSeek by default (OpenAI-compatible endpoint).  
> Check `chatbot/server.py` top section to see which key it reads.

---

## 5. Chatbot Server — API Reference

Base URL: `http://localhost:8000` (or your public domain)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/health` | Health check — lists all loaded clients |
| `GET` | `/api/clients` | Returns slug → business name map |
| `POST` | `/api/chat` | Main chat endpoint |
| `POST` | `/api/reload` | Hot-reload clients/ folder (no restart needed) |

### Chat Request Example
```bash
curl -X POST http://localhost:8000/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "DEMO_TireShop",
    "message": "What tires do you have for a 2019 Camry?",
    "session_id": "user-abc-123"
  }'
```

### Chat Response
```json
{
  "reply": "Great question! For a 2019 Camry we carry Michelin, Bridgestone...",
  "session_id": "user-abc-123"
}
```

---

## 6. Website Deployment — Hostinger

The main website is **static HTML** — no backend needed on Hostinger.

1. Log in to [hpanel.hostinger.com](https://hpanel.hostinger.com)
2. Go to **File Manager → public_html/**
3. Upload and extract `ewnexus_hostinger_upload.zip`  
   *(or manually upload the contents of the `ewnexus/` folder, excluding `chatbot/`)*
4. Verify the file structure:
   ```
   public_html/
   ├── index.html
   ├── cases.html
   ├── contact.html
   ├── assets/
   └── demos/
   ```
5. Visit `https://ewnexus.com` to confirm it loads.

> The chat widget in each demo page calls the **VPS chatbot URL** (see Section 8).  
> If you change the VPS domain, update `endpoint` in each demo's `SITE.chatbot` config.

---

## 7. Adding a New Client

### Step 1 — Collect Info
Ask the client for:
- Business name, phone, email, address
- 3–6 main services (with price ranges)
- Business hours
- Service area (cities)
- Color preference / logo (optional)
- Need AI chatbot? (+$10/month)

### Step 2 — Build the Website
1. Copy `demos/DEMO_AutoRepair.html` as a starting template
2. Edit the `SITE` config block at the top (~80 lines) — change name, services, theme color, FAQ
3. Save as `demos/{client-slug}.html`
4. Upload to Hostinger `public_html/demos/`

### Step 3 — Create the Chatbot
1. Copy `chatbot/clients/DEMO_AutoRepair.json`
2. Fill in client's business info, services, FAQ answers
3. Save as `chatbot/clients/{client-slug}.json`
4. Hot-reload without restart:
   ```bash
   curl -X POST http://localhost:8000/api/reload
   ```
   Or redeploy via EasyPanel if using VPS hosting.

### Step 4 — Update the Demo Widget
In the client's HTML, update the chatbot config:
```js
chatbot: {
  enabled: true,
  bot_name: "Mike",                          // bot's first name
  endpoint: "https://your-chatbot-domain",   // your VPS chatbot URL
  slug: "DEMO_TireShop",                     // matches the .json filename
  welcome: "Hi! I'm Mike, how can I help?"
}
```

### Step 5 — Collect Payment
| Item | Price |
|------|-------|
| Website setup | **$500** (one-time) |
| AI chatbot | **$10/month** (optional) |
| Annual maintenance | **$150/year** |
| Extra page | **$100/page** |
| One-time update | **$150** |

> ⚠️ **Never quote** $12/year, $548, or $9.13/month — those are old incorrect numbers.

---

## 8. VPS / EasyPanel Info

| Item | Value |
|------|-------|
| Panel URL | http://72.60.116.200:3000 |
| Panel credentials | See KEY.txt |
| Subdomain base | pkxdtf.easypanel.host |
| Chatbot service URL | https://new2-chatbotservice.pkxdtf.easypanel.host |
| GitHub repo (chatbot) | https://github.com/KC0040/ewnexus-chatbot |

### Other Services on the Same VPS
| Service | URL | Status |
|---------|-----|--------|
| n8n (automation) | ach-n8n.pkxdtf.easypanel.host | Inactive |
| Dify (AI platform) | test-dify.pkxdtf.easypanel.host | Inactive |
| word-roots app | new2-word-roots.pkxdtf.easypanel.host | Active |

### Deploying to EasyPanel (alternative to docker compose)
1. Log in to the panel → Project: **new2** → App: **chatbotservice**
2. Go to **Environment** tab → set `DEEPSEEK_API_KEY` and `PORT=80`
3. Go to **Deployments** → click **Deploy**
4. Set domain: `new2-chatbotservice.pkxdtf.easypanel.host`, port: 8000

---

## 9. Pricing Table (Official — Do Not Change)

| Item | Price | Notes |
|------|-------|-------|
| Website Setup | $500 | One-time. Includes: 5 pages, Year 1 hosting, email, SEO, contact form, 3 revisions |
| Annual Maintenance | $150/year | Hosting renewal + 2 content updates |
| One-time Update | $150 | Without annual plan |
| Extra Page | $100/page | Beyond the initial 5 |
| AI Chatbot | $10/month | Fully managed by EWNexus |
| Domain | Cost pass-through | ~$10–20/year, client owns it |
| **5-year total** | **$1,100** | ≈ $18.33/month |

---

## 10. Key Files Quick Reference

| File | Purpose |
|------|---------|
| `ewnexus/CLAUDE.md` | AI instruction set for automated site building |
| `ewnexus/assets/site-render.js` | Rendering engine + chat widget (the brain of every page) |
| `ewnexus/assets/base.css` | Global CSS variables — edit to change brand colors |
| `ewnexus/templates/BASE_TEMPLATE.html` | Master template for generating new client sites |
| `ewnexus/templates/site-config.schema.json` | JSON schema — describes all client config fields |
| `ewnexus/chatbot/server.py` | Chatbot backend — handles all clients via slug routing |
| `ewnexus/chatbot/clients/*.json` | Per-client bot personality, services, FAQ |
| `EWNEXUS_KB_v1.md` | Full knowledge base — pricing scripts, objection handling, FAQs |
| `KEY.txt` | All API keys — **keep secure** |

---

## 11. Security Notes

- `KEY.txt` contains live API keys. **Do not commit to Git. Do not send via email.**
- Transfer this ZIP via encrypted method (SFTP, Signal, encrypted USB).
- After setup, store keys in environment variables only — never hardcode.
- The `chatbot/clients/` folder does NOT contain API keys — safe to push to GitHub.

---

## 12. Support & Contacts

- DeepSeek API: https://platform.deepseek.com
- Anthropic API: https://console.anthropic.com
- Hostinger Panel: https://hpanel.hostinger.com
- EasyPanel Docs: https://easypanel.io/docs
