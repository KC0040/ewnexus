# EWNexus — 部署與管理手冊

## 網站資訊
- **網址**: https://www.ewnexus.com
- **性質**: 靜態 HTML 網站（無框架、無 build step）
- **本地路徑**: `C:\Claude\ewnexus`
- **GitHub**: https://github.com/KC0040/ewnexus（branch: `main`）

## 主機設定
| 項目 | 值 |
|------|-----|
| 主機平台 | EasyPanel VPS 72.60.116.200 |
| Project | `new2` |
| Service | `ewnexus` |
| PORT | 8000 |
| autoDeploy | **true**（GitHub push 自動上線） |

## 部署流程（日常更新）

```powershell
# 1. 編輯 HTML 檔案
# 2. 推上 GitHub
cd C:\Claude\ewnexus
git add <檔案名>
git commit -m "fix: 說明改了什麼"
git push origin main
# 3. 等約 1-2 分鐘，EasyPanel 自動 pull + 重啟
```

**注意**：因為 autoDeploy=true，push 完不需要手動觸發 EasyPanel。

## 手動強制部署（若 auto 失效）

```powershell
$TOKEN = "<EasyPanel API Token — 見本地 memory reference_easypanel_api.md>"
$body = '{"json":{"projectName":"new2","serviceName":"ewnexus"}}'
Invoke-RestMethod -Uri "http://72.60.116.200:3000/api/trpc/services.app.deployService" `
    -Method POST `
    -Headers @{ "Authorization" = "Bearer $TOKEN"; "Content-Type" = "application/json" } `
    -Body $body
```

## 檔案結構

```
ewnexus/
├── index.html          # 首頁
├── pricing.html        # 定價頁（基礎$600 + 客製方案）
├── contact.html        # 聯絡表單（→ n8n webhook）
├── how-it-works.html   # 服務流程說明
├── about.html          # 關於頁
├── cases.html          # 客戶案例
├── contract.html       # 合約頁
├── privacy.html        # 隱私政策
├── demos/              # 客戶 demo 網站（DEMO_*.html）
├── templates/          # 建站用模板與 JSON schema
├── clients/            # 客戶資料 JSON（site-config）
├── assets/             # CSS、圖片、logo
└── blog/               # 部落格文章
```

## 定價規則（必須遵守）
- 建站費：**$600**（基礎 5 頁）
- 年度維護：$150/年（含 2 次內容更新）
- 主機續費（無更新）：$40/年
- 單次更新：$150/次
- 加頁面：$100/頁
- AI 客服 Starter：$20/月（150次對話）
- AI 客服 Growth：$35/月（500次對話）
- E-Commerce：$1,200+ 起
- Booking System：$800+ 起
- ❌ 禁止出現舊數字：$500、$10/月、$12/年

## 聯絡表單設定
- 表單 POST → n8n webhook（URL 在 contact.html 的 `<form action="...">`）
- n8n 收到後 → Telegram 通知老闆

## GA4 追蹤碼
- ID：`G-5067Y7JEBP`（已在所有主頁面 `<head>` 安裝）

## 已知問題
- `chatbot/` 子資料夾是 git submodule，有 untracked content，不影響主站，忽略即可
