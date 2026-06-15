<?php
/**
 * EWNexus 後台：產生新 UUID 預覽連結
 *
 * 使用方式（只開給你自己，加密碼保護）：
 *   https://www.ewnexus.com/generate-preview.php?key=YOUR_SECRET_KEY
 *
 * POST 參數：
 *   business      客戶公司名稱
 *   file          demo HTML 相對路徑 (e.g. demos/DEMO_TireShop.html)
 *   client_email  客戶 email
 *   days          預覽有效天數（預設 30）
 */

/* ── 後台密碼 — 部署前改掉 ── */
define('ADMIN_KEY', getenv('EWN_ADMIN_KEY') ?: 'aegis');

$key = $_GET['key'] ?? $_POST['key'] ?? '';
if ($key !== ADMIN_KEY) {
    http_response_code(403);
    /* 顯示假 404，不暴露後台存在 */
    header('Content-Type: text/html');
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

/* ── 處理 POST（產生新連結） ── */
$message = '';
$newLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business     = trim(strip_tags($_POST['business']     ?? ''));
    $file         = trim($_POST['file']                    ?? '');
    $clientEmail  = trim($_POST['client_email']            ?? '');
    $days         = max(1, min(365, (int)($_POST['days']   ?? 30)));

    if ($business && $file) {
        /* 驗證 demo 檔案存在 */
        $demoPath = __DIR__ . '/' . ltrim($file, '/');
        if (!file_exists($demoPath)) {
            $message = "❌ File not found: $file";
        } else {
            /* 產生 UUID */
            $uuid = bin2hex(random_bytes(8)); // 16 char hex

            /* 確保目錄存在 */
            if (!is_dir(__DIR__ . '/previews')) {
                mkdir(__DIR__ . '/previews', 0755, true);
            }

            $config = [
                'uuid'         => $uuid,
                'business'     => $business,
                'file'         => ltrim($file, '/'),
                'client_email' => $clientEmail,
                'created'      => date('c'),
                'expires'      => date('c', strtotime("+{$days} days")),
                'active'       => true,
                'paid'         => false,
            ];

            $configFile = __DIR__ . "/previews/{$uuid}.json";
            file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

            $newLink = "https://www.ewnexus.com/preview.php?id={$uuid}";
            $message = "✅ Preview created! Expires in {$days} days.";
        }
    } else {
        $message = '❌ Business name and file are required.';
    }
}

/* ── 列出現有 previews ── */
$previews = [];
$previewDir = __DIR__ . '/previews/';
if (is_dir($previewDir)) {
    foreach (glob($previewDir . '*.json') as $f) {
        $p = json_decode(file_get_contents($f), true);
        if ($p) {
            $p['_expired'] = !empty($p['expires']) && strtotime($p['expires']) < time();
            $previews[] = $p;
        }
    }
    usort($previews, fn($a,$b) => strtotime($b['created']) - strtotime($a['created']));
}

/* ── 可用的 demo 檔案列表 ── */
$demoFiles = [];
foreach (glob(__DIR__ . '/demos/*.html') as $f) {
    $demoFiles[] = 'demos/' . basename($f);
}
foreach (glob(__DIR__ . '/clients/*.json') as $f) {
    $slug = basename($f, '.json');
    /* 如果對應的 html 存在 */
    if (file_exists(__DIR__ . "/demos/DEMO_{$slug}.html")) {
        /* 已列出 */
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>EWNexus Preview Manager</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;}
    body{background:#060f0a;color:#e8fdf4;font-family:'Space Grotesk',sans-serif;padding:2rem;min-height:100vh;}
    h1{font-size:1.5rem;font-weight:900;letter-spacing:-.03em;color:#5af0b3;margin-bottom:.25rem;}
    .sub{font-size:.75rem;color:rgba(232,253,244,.4);letter-spacing:.1em;text-transform:uppercase;margin-bottom:2.5rem;}
    .card{background:#0a1a10;border:1px solid rgba(90,240,179,.15);border-radius:.25rem;padding:1.75rem;margin-bottom:1.5rem;}
    .card h2{font-size:.8rem;letter-spacing:.25em;text-transform:uppercase;color:#5af0b3;margin-bottom:1.25rem;}
    label{display:block;font-size:.65rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(232,253,244,.5);margin-bottom:.375rem;margin-top:1rem;}
    label:first-of-type{margin-top:0;}
    input,select{width:100%;background:#060f0a;border:1px solid rgba(90,240,179,.2);color:#e8fdf4;
      padding:.75rem 1rem;border-radius:.125rem;font-size:.85rem;font-family:'Space Grotesk',sans-serif;outline:none;}
    input:focus,select:focus{border-color:#5af0b3;}
    select option{background:#0a1a10;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    button[type=submit]{margin-top:1.25rem;background:#5af0b3;color:#003825;font-family:'Space Grotesk',sans-serif;
      font-weight:800;font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;
      padding:.75rem 1.75rem;border:none;border-radius:.125rem;cursor:pointer;width:100%;}
    button[type=submit]:hover{opacity:.9;}
    .msg{padding:.875rem 1rem;border-radius:.125rem;margin-bottom:1rem;font-weight:600;font-size:.85rem;}
    .msg.ok{background:rgba(90,240,179,.1);border:1px solid rgba(90,240,179,.3);color:#5af0b3;}
    .msg.err{background:rgba(255,80,80,.1);border:1px solid rgba(255,80,80,.3);color:#ff8080;}
    .link-box{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;
      background:#060f0a;border:1px solid rgba(90,240,179,.3);border-radius:.125rem;margin-top:.75rem;}
    .link-box code{font-family:'DM Mono',monospace;font-size:.8rem;color:#5af0b3;word-break:break-all;flex:1;}
    .copy-btn{background:rgba(90,240,179,.15);color:#5af0b3;border:1px solid rgba(90,240,179,.3);
      font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
      padding:.35rem .75rem;border-radius:.125rem;cursor:pointer;white-space:nowrap;font-family:'Space Grotesk',sans-serif;}
    table{width:100%;border-collapse:collapse;font-size:.78rem;}
    th{font-size:.6rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(232,253,244,.4);
      padding:.625rem .75rem;border-bottom:1px solid rgba(90,240,179,.1);text-align:left;}
    td{padding:.75rem .75rem;border-bottom:1px solid rgba(90,240,179,.07);vertical-align:middle;}
    tr:hover td{background:rgba(90,240,179,.03);}
    .badge{display:inline-block;padding:.2rem .5rem;border-radius:.125rem;font-size:.6rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;}
    .badge.active{background:rgba(90,240,179,.1);color:#5af0b3;border:1px solid rgba(90,240,179,.25);}
    .badge.expired{background:rgba(255,100,100,.1);color:#ff8080;border:1px solid rgba(255,100,100,.2);}
    .badge.paid{background:rgba(100,180,255,.1);color:#80c0ff;border:1px solid rgba(100,180,255,.2);}
    .badge.inactive{background:rgba(100,100,100,.15);color:#888;border:1px solid rgba(100,100,100,.2);}
    a.open-link{color:#5af0b3;text-decoration:none;font-size:.72rem;}
    a.open-link:hover{text-decoration:underline;}
    .uuid{font-family:'DM Mono',monospace;font-size:.72rem;color:rgba(232,253,244,.5);}
  </style>
</head>
<body>
<h1>EWNexus Preview Manager</h1>
<div class="sub">Admin only · Generate & manage client demo links</div>

<?php if ($message): ?>
  <div class="msg <?= str_starts_with($message,'✅') ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
  <?php if ($newLink): ?>
  <div class="link-box">
    <code id="new-link"><?= htmlspecialchars($newLink) ?></code>
    <button class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('new-link').textContent).then(()=>this.textContent='Copied!')">Copy</button>
    <a href="<?= htmlspecialchars($newLink) ?>" target="_blank" class="copy-btn" style="text-decoration:none;">Open</a>
  </div>
  <?php endif; ?>
<?php endif; ?>

<!-- 產生新預覽 -->
<div class="card">
  <h2>Generate New Preview Link</h2>
  <form method="POST" action="?key=<?= htmlspecialchars($key) ?>">
    <div class="grid">
      <div>
        <label>Business Name *</label>
        <input type="text" name="business" placeholder="Smith's Tire Shop" required/>
      </div>
      <div>
        <label>Client Email</label>
        <input type="email" name="client_email" placeholder="client@email.com"/>
      </div>
    </div>
    <label>Demo File *</label>
    <select name="file" required>
      <option value="" disabled selected>Select a demo file…</option>
      <?php foreach ($demoFiles as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="grid">
      <div>
        <label>Expires in (days)</label>
        <input type="number" name="days" value="30" min="1" max="365"/>
      </div>
    </div>
    <button type="submit">Generate Preview Link →</button>
  </form>
</div>

<!-- 現有 previews 列表 -->
<div class="card">
  <h2>Existing Previews (<?= count($previews) ?>)</h2>
  <?php if (empty($previews)): ?>
    <p style="color:rgba(232,253,244,.4);font-size:.82rem;">No previews yet. Create one above.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Business</th>
        <th>UUID</th>
        <th>Status</th>
        <th>Expires</th>
        <th>Link</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($previews as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['business']) ?>
          <?php if (!empty($p['paid'])): ?><br><span class="badge paid">Paid</span><?php endif; ?></td>
        <td><span class="uuid"><?= htmlspecialchars($p['uuid']) ?></span></td>
        <td>
          <?php if ($p['_expired']): ?>
            <span class="badge expired">Expired</span>
          <?php elseif (!$p['active']): ?>
            <span class="badge inactive">Inactive</span>
          <?php else: ?>
            <span class="badge active">Active</span>
          <?php endif; ?>
        </td>
        <td style="font-size:.72rem;color:rgba(232,253,244,.5);">
          <?= !empty($p['expires']) ? date('M j, Y', strtotime($p['expires'])) : '—' ?>
        </td>
        <td>
          <a class="open-link" href="/preview.php?id=<?= htmlspecialchars($p['uuid']) ?>" target="_blank">Open ↗</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

</body>
</html>
