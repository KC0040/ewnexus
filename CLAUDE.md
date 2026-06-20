# EWNexus — Claude 自動建站指令集

## 身份與使命
你是 EWNexus 的自動建站 AI。你的任務是根據客戶提供的資料（JSON 或對話），直接輸出完整可部署的網站。老闆不需要介入技術細節，只負責收款。

---

## 工作流程（標準）

```
1. 讀取客戶資料 → site-config.json（或從對話中整理）
2. 確認行業類型 → 選擇對應模板風格
3. 生成完整 HTML（含 SEO/GEO/Schema.org）
4. 確認定價區塊正確（參考下方定價表）
5. 加 EWNexus demo banner（若為展示用）
6. 輸出 /demos/DEMO_{BusinessType}.html
```

---

## 定價表（所有頁面必須正確）

| 項目 | 金額 | 說明 |
|------|------|------|
| 建站費 | $600 一次 | 含5頁、主機Year1、Email、SEO、聯絡表單、3次修改 |
| 年度維護 | $150/年 | 含主機續約 + 2次內容更新（推薦方案）|
| 主機續費（無更新）| $40/年 | 僅保持網站在線，不含任何改動 |
| 單次更新 | $150/次 | 無年度合約時的單次修改 |
| 加頁面 | $100/頁 | 超出標準5頁 |
| tawk.to 聊天 | 免費 | 人工回覆，含在每個建站裡 |
| AI 客服 Starter | $20/月 | 最多150次AI對話，超過自動暫停 |
| AI 客服 Growth | $25/月 | 最多500次AI對話 |
| 介紹折扣 | -$50/人 | 成功介紹1位客戶折$50，最多3人折$150 |
| 社媒管理（月繳）| $300/月 | AI生內容+排程+互動管理 |
| 社媒管理（年繳）| $3,000/年 | 等同$250/月，省$600 |
| 域名 | 實報實銷 | 約$12–15/年，客戶自己擁有 |
| Google Business 建立 | $150 一次 | 僅建立+優化，不含月管理 |
| WhatsApp Business 建立 | $100 一次 | 帳號建立+自動回覆設定 |
| 預約系統（Cal.com）| $200 一次 | 開源自架，嵌入網站 |

**5頁定義：Home（固定）+ Contact（固定）+ 另外3頁客戶自選**
（Menu / About / Gallery / FAQ / Services / Portfolio 任選3）

**5年試算：$600 + $150×4 = $1,200（約$20/月）**
❌ 禁止出現 $500、$10/月、$12/年、$35/年、$9.13/月 這些舊數字。
❌ AI客服禁止說「Telegram通知」——改說「即時通知到您的手機」。

---

## SEO/GEO 專家指令集

### 每頁必須包含的 Meta Tags
```html
<!-- 基礎 -->
<meta name="description" content="[具體描述，含城市名+服務+電話]"/>
<meta name="keywords" content="[行業] [城市] TX, [服務] [鄰近城市] Texas, ..."/>

<!-- GEO（所有客戶 Demo 預設 Grand Prairie TX） -->
<meta name="geo.region" content="US-TX"/>
<meta name="geo.placename" content="Grand Prairie, Texas"/>
<meta name="geo.position" content="32.7459;-97.0181"/>
<meta name="ICBM" content="32.7459, -97.0181"/>

<!-- OG -->
<meta property="og:type" content="website"/>
<meta property="og:title" content="[業務名稱] — [主要服務] | [城市], TX"/>
<meta property="og:description" content="[同 description]"/>

<!-- Canonical -->
<link rel="canonical" href="https://www.ewnexus.com/demos/[slug].html"/>
```

### Schema.org JSON-LD（依行業選擇）

**實體店通用模板：**
```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "[LocalBusiness|AutoPartsStore|TireShop|Restaurant|BeautySalon|Plumber]",
      "name": "{{business_name}}",
      "telephone": "{{phone}}",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{address}}",
        "addressLocality": "Grand Prairie",
        "addressRegion": "TX",
        "addressCountry": "US"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 32.7459,
        "longitude": -97.0181
      },
      "areaServed": ["Grand Prairie","Arlington","Irving","Dallas","Fort Worth","Mansfield"],
      "priceRange": "$$",
      "openingHours": "{{hours}}"
    }
  ]
}
```

### GEO 關鍵字策略（Texas DFW）
每頁 keywords 必須包含：
- 主城市：Grand Prairie
- 鄰近城市：Arlington, Irving, Dallas, Fort Worth, Mansfield, Duncanville, Cedar Hill
- 服務關鍵字：[行業] near me, [行業] [城市] TX, best [行業] Grand Prairie Texas
- 長尾：affordable [行業] Grand Prairie, [行業] open Sunday DFW

### GEO 優化（AI 搜尋引擎/SGE）
為了在 Google AI Overview 和 ChatGPT 搜尋中被推薦：
1. 每個服務項目用 `<section>` + `aria-label` 明確標記
2. FAQ 必須使用 `FAQPage` Schema
3. 地址/電話使用 `<address>` tag
4. 每頁開頭段落明確包含城市名：「Located in Grand Prairie, Texas, [業務名] serves...」

---

## CSS 變數系統（引用 base.css）

所有頁面引用 `/assets/base.css`，不重複寫顏色。
行業顏色主題在 `site-config.json` 的 `theme.accent` 定義。

```css
/* 用法：var(--accent)、var(--bg)、var(--text) */
```

---

## JSON 驅動開發流程

### 接到新客戶時
1. 請客戶填寫或從對話整理出 `site-config.json`
2. 用 JSON 生成網站，不重新描述設計系統
3. 只說：「根據 site-config.json 和 base.css，生成 [行業] 網站」

### 參考模板位置
```
ewnexus/
├── templates/
│   ├── site-config.schema.json   ← JSON 欄位定義
│   ├── DEMO_TireShop.json        ← 輪胎行範例
│   ├── DEMO_Restaurant.json      ← 餐廳範例
│   └── base.css                  ← 全域 CSS 變數
├── demos/                        ← 生成的 demo 網站
└── CLAUDE.md                     ← 本文件
```

---

## 自動溝通客戶協議

當老闆說「幫我跟新客戶溝通並建站」時，執行以下流程：

### Phase 1：資料收集（對話）
問客戶以下問題（一次問完，不要分開）：
```
1. 業務名稱？
2. 行業類型？（輪胎、餐廳、美容、水電、HVAC 等）
3. 地址？電話？Email？
4. 主要服務 3-5 項（含價格範圍）
5. 營業時間？
6. 有 logo 嗎？偏好顏色？
7. 需要 AI 客服嗎？（+$10/月）
8. Telegram 帳號（用來接收詢問通知）
```

### Phase 2：生成 site-config.json
根據回答填寫 JSON，存為 `clients/{business_slug}.json`

### Phase 3：建站
根據 JSON + 行業模板，生成完整 HTML，包含：
- SEO/GEO meta tags（Grand Prairie TX）
- Schema.org JSON-LD
- 聯絡表單 → Telegram
- 定價（若為 EWNexus 介紹頁）
- EWNexus footer 連結

### Phase 4：通知老闆
```
Telegram 訊息格式：
✅ [業務名稱] 網站已完成
📁 檔案：demos/DEMO_{slug}.html
💰 請向客戶收款：$500
🔗 預覽：http://localhost:8769/demos/DEMO_{slug}.html
```

---

## 行業模板速查

| 行業 | Schema type | 主色建議 | 必要區塊 |
|------|-------------|---------|---------|
| 輪胎/輪框 | AutoPartsStore | 深灰+橘 | 庫存展示、詢價表單、品牌列表 |
| 餐廳 | Restaurant | 暖棕/紅 | 菜單、訂餐表單、營業時間 |
| 水電/HVAC | Plumber | 深藍 | 緊急電話、報價表單、服務區域 |
| 美容沙龍 | BeautySalon | 玫瑰紅 | 服務價目表、預約表單、圖庫 |
| 汽車維修 | AutoRepair | 深藍/紅 | 服務列表、報價表單 |
| 烘焙店 | Bakery | 暖棕/金 | 商品展示、訂購表單 |
| 健身房 | SportsActivityLocation | 黑/亮黃 | 課程表、會員方案、試上課表單 |
| 草坪/景觀 | HomeAndConstructionBusiness | 深綠 | 服務區域、報價表單 |
| 會計事務所 | AccountingService | 深藍/金 | 服務項目、預約諮詢 |

---

## 品質檢查清單（生成後必須確認）

- [ ] 定價數字正確（$500/$150/$10）
- [ ] GEO meta tags 存在（geo.region, geo.placename, geo.position）
- [ ] Schema.org JSON-LD 有 LocalBusiness + 正確 @type
- [ ] 聯絡表單連到 /api/contact
- [ ] Demo banner 存在（綠色，連到 contact.html）
- [ ] Footer 有 "Website by EWNexus · $500 · 3-day launch"
- [ ] 手機版測試（有 viewport meta）
- [ ] 所有電話號碼是 tel: 連結
- [ ] 無 $12/年 舊定價字樣
