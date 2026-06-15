<?php
/**
 * EWNexus UUID Preview System
 *
 * 用法：
 *   /preview.php?id=a8f3b2c1d4e5f6g7   → 顯示客戶 demo（含浮水印）
 *
 * 每個 preview 對應 previews/{uuid}.json：
 * {
 *   "uuid": "a8f3b2c1d4e5f6g7",
 *   "business": "Smith's Tire Shop",
 *   "file": "demos/DEMO_TireShop.html",
 *   "created": "2025-06-13T10:00:00+00:00",
 *   "expires": "2025-07-13T10:00:00+00:00",
 *   "active": true,
 *   "client_email": "smith@example.com",
 *   "paid": false
 * }
 */

$uuid = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id'] ?? '');

if (!$uuid) {
    http_response_code(400);
    die(previewError('Missing preview ID', 'Please check the link you were sent.'));
}

$configFile = __DIR__ . "/previews/{$uuid}.json";

if (!file_exists($configFile)) {
    http_response_code(404);
    die(previewError('Preview Not Found', 'This link may have expired or never existed. Contact info@ewnexus.com for a new link.'));
}

$config = json_decode(file_get_contents($configFile), true);

if (!$config || empty($config['active'])) {
    http_response_code(403);
    die(previewError('Preview Unavailable', 'This demo has been disabled. Contact info@ewnexus.com if you believe this is an error.'));
}

/* 檢查是否過期 */
if (!empty($config['expires']) && strtotime($config['expires']) < time()) {
    http_response_code(410);
    die(previewError('Preview Expired', 'This demo link has expired. Please contact info@ewnexus.com to request an updated preview.'));
}

/* 讀取 demo HTML */
$demoFile = __DIR__ . '/' . ltrim($config['file'], '/');
if (!file_exists($demoFile)) {
    http_response_code(500);
    die(previewError('Demo File Missing', 'We\'re having a technical issue. Please contact info@ewnexus.com.'));
}

$html = file_get_contents($demoFile);
$business = htmlspecialchars($config['business'] ?? 'Your Business');
$daysLeft = '';
if (!empty($config['expires'])) {
    $diff = ceil((strtotime($config['expires']) - time()) / 86400);
    $daysLeft = $diff > 0 ? " · Expires in {$diff} day" . ($diff !== 1 ? 's' : '') : '';
}

/* 注入浮水印 banner + 保護腳本 在 </body> 之前 */
$watermark = <<<HTML

<!-- EWNexus Preview Watermark -->
<style>
  #ewn-watermark{
    position:fixed;top:0;left:0;right:0;z-index:99999;
    background:linear-gradient(90deg,#0d1f16,#0a3d22);
    border-bottom:2px solid #5af0b3;
    padding:.625rem 1.5rem;
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:.5rem;
    font-family:'Space Grotesk',system-ui,sans-serif;
    box-shadow:0 4px 24px rgba(0,0,0,.4);
  }
  #ewn-watermark .ewn-left{display:flex;align-items:center;gap:.75rem;}
  #ewn-watermark .ewn-logo{font-weight:900;font-size:.9rem;color:#5af0b3;letter-spacing:-.02em;}
  #ewn-watermark .ewn-divider{width:1px;height:16px;background:rgba(90,240,179,.25);}
  #ewn-watermark .ewn-label{font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:rgba(90,240,179,.7);}
  #ewn-watermark .ewn-biz{font-size:.8rem;font-weight:600;color:#e8fdf4;}
  #ewn-watermark .ewn-right{display:flex;align-items:center;gap:1rem;}
  #ewn-watermark .ewn-warning{font-size:.65rem;color:rgba(255,200,100,.8);font-weight:600;letter-spacing:.05em;}
  #ewn-watermark .ewn-cta{background:#5af0b3;color:#003825;font-size:.65rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:.35rem .875rem;border-radius:.125rem;text-decoration:none;white-space:nowrap;}
  #ewn-watermark .ewn-cta:hover{opacity:.9;}
  #ewn-wm-overlay{
    position:fixed;inset:0;z-index:99998;pointer-events:none;
    background:repeating-linear-gradient(
      45deg,
      transparent,transparent 80px,
      rgba(90,240,179,.025) 80px,rgba(90,240,179,.025) 81px
    );
  }
  #ewn-wm-text{
    position:fixed;bottom:1.5rem;right:1.5rem;z-index:99998;pointer-events:none;
    font-family:'Space Grotesk',system-ui,sans-serif;
    font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
    color:rgba(90,240,179,.3);
    writing-mode:vertical-rl;
  }
  body{padding-top:46px!important;}
  @media(max-width:600px){
    #ewn-watermark .ewn-right{display:none;}
    #ewn-watermark .ewn-biz{display:none;}
  }
</style>
<div id="ewn-wm-overlay"></div>
<div id="ewn-wm-text">EWNEXUS DEMO · NOT FOR DISTRIBUTION</div>
<div id="ewn-watermark">
  <div class="ewn-left">
    <span class="ewn-logo">EWNexus</span>
    <div class="ewn-divider"></div>
    <span class="ewn-label">Demo Preview</span>
    <div class="ewn-divider"></div>
    <span class="ewn-biz">{$business}</span>
  </div>
  <div class="ewn-right">
    <span class="ewn-warning">⚠ Unpaid · Not for use or distribution{$daysLeft}</span>
    <a class="ewn-cta" href="https://www.ewnexus.com/contact.html?ref={$uuid}">Get This Site — $500</a>
  </div>
</div>
<script>
/* 基本保護：禁右鍵和 F12 視覺提示（技術上可繞過，但能嚇退普通用戶） */
document.addEventListener('contextmenu',function(e){e.preventDefault();});
document.addEventListener('keydown',function(e){
  if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&['I','J','C'].includes(e.key))||(e.ctrlKey&&e.key==='U')){
    e.preventDefault();
    alert('This is a protected EWNexus demo. To use this website, please complete payment at ewnexus.com');
  }
});
/* 禁止 iframe 嵌入（防止別人套框後去除浮水印） */
if(window.top!==window.self){window.top.location=window.self.location;}
</script>
HTML;

$html = str_ireplace('</body>', $watermark . "\n</body>", $html);

/* 輸出 HTML */
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo $html;

/* ── 工具函式 ── */
function previewError(string $title, string $msg): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>EWNexus Preview — {$title}</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;900&display=swap" rel="stylesheet"/>
  <style>
    body{margin:0;background:#060f0a;color:#e8fdf4;font-family:'Space Grotesk',sans-serif;
      display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:2rem;}
    .icon{font-size:3rem;margin-bottom:1rem;}
    h1{font-size:1.5rem;font-weight:900;letter-spacing:-.03em;margin:.5rem 0;}
    p{color:rgba(232,253,244,.6);font-size:.9rem;line-height:1.6;max-width:380px;}
    a{display:inline-block;margin-top:1.5rem;background:#5af0b3;color:#003825;padding:.5rem 1.5rem;
      border-radius:.125rem;font-weight:700;font-size:.75rem;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;}
  </style>
</head>
<body>
  <div>
    <div class="icon">🔗</div>
    <h1>{$title}</h1>
    <p>{$msg}</p>
    <a href="https://www.ewnexus.com">← Go to EWNexus</a>
  </div>
</body>
</html>
HTML;
}
