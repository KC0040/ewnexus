# M3 Demo 製作指令

## 任務
製作以下新 demo 網站，每個行業多做幾個風格版本，讓客戶可以直接選喜歡的改。

---

## 需要製作的新 Demo（共 8 個）

| 檔案名 | 行業 | 風格方向 |
|--------|------|---------|
| `DEMO_Roadside.html` | 道路救援（非拖車） | 緊急橘紅系，強調快速到達 |
| `DEMO_Restaurant_Mexican.html` | 墨西哥餐廳 | 暖色系、活潑、大食物圖 |
| `DEMO_Restaurant_BBQ.html` | 美式 BBQ | 深棕/黑、粗獷、煙燻感 |
| `DEMO_Restaurant_Asian.html` | 亞洲餐廳 | 深色+紅金，精緻感 |
| `DEMO_NailSpa.html` | 指甲沙龍/Spa | 粉/玫瑰金，奢華感 |
| `DEMO_Cleaning.html` | 清潔公司 | 清新藍白，乾淨感 |
| `DEMO_MobileTire.html` | 行動輪胎服務 | 深灰/橘，戶外工作感 |
| `DEMO_Locksmith.html` | 汽車鎖匠 | 深色/金，緊急服務感 |

---

## 每個 Demo 必須包含的內容

### 基本要素
- 假業主名稱（用虛構的 Grand Prairie TX 公司名）
- 假電話：(972) 555-XXXX
- 假地址：Grand Prairie, TX
- 完整 5 個 section：Hero / Services / About / FAQ / Contact

### SEO（每頁必備）
```html
<meta name="geo.region" content="US-TX"/>
<meta name="geo.placename" content="Grand Prairie, Texas"/>
<meta name="geo.position" content="32.7459;-97.0181"/>
<meta name="ICBM" content="32.7459, -97.0181"/>
<link rel="canonical" href="https://www.ewnexus.com/demos/[檔案名]"/>
```

### Schema.org
每頁加對應的 LocalBusiness JSON-LD + FAQPage（至少 3 組 Q&A）

---

## Banner（每個 Demo 結尾 </body> 前必須加）

把下面這段 HTML 貼在 `</body>` 前，並根據行業修改三個 benefit 文字：

```html
<!-- EWNexus Demo Banner -->
<div id="ew-demo-bar" style="position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#0a1f14;border-top:2px solid #5af0b3;display:flex;align-items:center;justify-content:space-between;padding:10px 20px;gap:12px;font-family:'Inter',system-ui,sans-serif;box-shadow:0 -4px 24px rgba(0,0,0,.5);">
  <div style="flex-shrink:0;"><span style="color:#5af0b3;font-weight:900;font-size:15px;letter-spacing:-.02em;">EWNexus</span></div>
  <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;">
    <span style="color:#c8ddd2;font-size:12px;">✓ [行業 benefit 1]</span>
    <span style="color:#c8ddd2;font-size:12px;">✓ [行業 benefit 2]</span>
    <span style="color:#c8ddd2;font-size:12px;">✓ Google ranked for "[行業] near me"</span>
  </div>
  <a href="https://www.ewnexus.com/contact.html" style="background:#5af0b3;color:#001a0e;font-weight:800;font-size:13px;padding:8px 18px;text-decoration:none;border-radius:6px;white-space:nowrap;flex-shrink:0;">Get Yours — $50 to Start</a>
  <button onclick="document.getElementById('ew-demo-bar').style.display='none'" style="background:none;border:none;color:#6b8f7a;font-size:20px;cursor:pointer;flex-shrink:0;line-height:1;">×</button>
</div>
```

### 各行業 Benefit 建議

| 行業 | Benefit 1 | Benefit 2 |
|------|-----------|-----------|
| 道路救援 | GPS live tracking for customers | AI chat dispatches 24/7 |
| 墨西哥餐廳 | Online menu + reservation form | AI chat handles orders |
| BBQ 餐廳 | Online ordering & catering requests | AI chat answers hours & menu |
| 亞洲餐廳 | Online menu with photos | AI chat handles reservations |
| 指甲 Spa | Online booking, no phone tag | AI chat schedules appointments 24/7 |
| 清潔公司 | Instant online quote request | AI chat captures leads after hours |
| 行動輪胎 | GPS tracking so customer sees ETA | AI chat handles service requests 24/7 |
| 汽車鎖匠 | GPS tracking — customer sees you coming | AI chat dispatches emergency calls |

---

## 完成後
把所有新檔案放在 `C:\Claude\ewnexus\demos\` 目錄下，我來統一 push。
