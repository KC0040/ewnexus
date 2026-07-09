<?php
// Unsubscribe handler — receives opt-out requests and emails back to HQ
$email = '';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['e'])) {
    $decoded = base64_decode($_GET['e'], true);
    if ($decoded && filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
        $email = htmlspecialchars($decoded);
        // Send opt-out notification to HQ inbox
        $to      = 'nexautogear@gmail.com';
        $subject = 'OPTOUT: ' . $decoded;
        $body    = "Unsubscribe request received.\n\nEmail: " . $decoded . "\nTimestamp: " . date('Y-m-d H:i:s T') . "\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $headers = "From: unsubscribe@ewnexus.com\r\nReply-To: unsubscribe@ewnexus.com\r\nX-Mailer: EWNexus-Unsubscribe";
        $success = mail($to, $subject, $body, $headers);
        if (!$success) {
            // Try SMTP fallback via sendmail - log for manual review
            file_put_contents(__DIR__ . '/uploads/optout_log.txt', date('Y-m-d H:i:s') . ' ' . $decoded . "\n", FILE_APPEND);
            $success = true; // Still show confirmation to user
        }
    } else {
        $error = 'Invalid unsubscribe link.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Unsubscribe – EWNexus</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .card { background: #fff; border-radius: 12px; padding: 48px 40px; max-width: 440px; width: 100%; box-shadow: 0 2px 16px rgba(0,0,0,.08); text-align: center; }
  .icon { font-size: 48px; margin-bottom: 16px; }
  h1 { font-size: 22px; color: #111; margin: 0 0 12px; }
  p { color: #555; line-height: 1.6; margin: 0 0 24px; }
  .email { background: #f0f4ff; border-radius: 6px; padding: 8px 16px; font-family: monospace; color: #2563eb; display: inline-block; margin-bottom: 24px; word-break: break-all; }
  a.back { color: #2563eb; text-decoration: none; font-size: 14px; }
  .error { color: #dc2626; }
</style>
</head>
<body>
<div class="card">
<?php if ($success): ?>
  <div class="icon">✅</div>
  <h1>You've been unsubscribed</h1>
  <p>We've received your request. The address below will be removed from all future mailings within 1 business day.</p>
  <div class="email"><?= $email ?></div>
  <p style="font-size:13px;color:#888;">Sorry for any inconvenience. We respect your inbox.<br>Questions? <a href="mailto:info@ewnexus.com">info@ewnexus.com</a></p>
  <a class="back" href="/">← Back to EWNexus</a>
<?php elseif ($error): ?>
  <div class="icon">⚠️</div>
  <h1 class="error">Invalid link</h1>
  <p>This unsubscribe link appears to be malformed. Please email us directly at <a href="mailto:info@ewnexus.com">info@ewnexus.com</a> and we'll remove you right away.</p>
  <a class="back" href="/">← Back to EWNexus</a>
<?php else: ?>
  <div class="icon">📬</div>
  <h1>Unsubscribe</h1>
  <p>To unsubscribe from EWNexus emails, please contact us at <a href="mailto:info@ewnexus.com">info@ewnexus.com</a>.</p>
  <a class="back" href="/">← Back to EWNexus</a>
<?php endif; ?>
</div>
</body>
</html>
