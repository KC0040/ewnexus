<?php
/**
 * EWNexus 客戶詢問表單處理
 * 接收聯絡資料 + 網站需求 + 照片上傳
 * 儲存到 uploads/ 並寄 email 通知
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://www.ewnexus.com');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

/* ── 基本欄位 ── */
$name      = trim(strip_tags($_POST['name']      ?? ''));
$email     = trim($_POST['email']                ?? '');
$phone     = trim(strip_tags($_POST['phone']     ?? ''));
$business  = trim(strip_tags($_POST['business']  ?? ''));
$industry  = trim(strip_tags($_POST['industry']  ?? ''));
$city      = trim(strip_tags($_POST['city']      ?? ''));
$service   = trim(strip_tags($_POST['service']   ?? ''));  // website / ai-bot / both
$budget    = trim(strip_tags($_POST['budget']    ?? ''));
$timeline  = trim(strip_tags($_POST['timeline']  ?? ''));
$colors    = trim(strip_tags($_POST['colors']    ?? ''));
$notes     = trim(strip_tags($_POST['notes']     ?? ''));
$ai_chat   = trim(strip_tags($_POST['ai_chat']   ?? 'No'));
$ref       = trim(strip_tags($_POST['referral']  ?? ''));

/* ── 驗證必填欄位 ── */
if (!$name || !$email || !$business) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: name, email, business']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

/* ── 建立上傳目錄 ── */
$slug      = preg_replace('/[^a-z0-9]+/', '-', strtolower($business));
$date      = date('Ymd');
$uploadDir = __DIR__ . "/uploads/{$date}_{$slug}/";
$uploadUrl = "https://www.ewnexus.com/uploads/{$date}_{$slug}/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* ── 照片上傳處理 ── */
$allowedTypes  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$maxSize       = 10 * 1024 * 1024; // 10MB
$uploadedFiles = [];
$uploadErrors  = [];

$fileFields = ['logo', 'reference_photos', 'brand_assets'];
foreach ($fileFields as $field) {
    if (!isset($_FILES[$field])) continue;

    /* 支援 multiple 上傳（PHP 多檔案陣列格式） */
    $files = $_FILES[$field];
    if (!is_array($files['name'])) {
        /* 單一檔案，轉成陣列格式 */
        $files = array_map(fn($v) => [$v], $files);
    }

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($files['size'][$i] > $maxSize) {
            $uploadErrors[] = "{$files['name'][$i]} exceeds 10MB limit";
            continue;
        }
        if (!in_array($files['type'][$i], $allowedTypes)) {
            $uploadErrors[] = "{$files['name'][$i]} is not an allowed file type";
            continue;
        }

        $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $safe     = preg_replace('/[^a-z0-9_-]/i', '', pathinfo($files['name'][$i], PATHINFO_FILENAME));
        $filename = "{$field}_{$i}_{$safe}.{$ext}";
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
            $uploadedFiles[] = ['field' => $field, 'file' => $filename, 'url' => $uploadUrl . $filename];
        } else {
            $uploadErrors[] = "Failed to save {$files['name'][$i]}";
        }
    }
}

/* ── 儲存 JSON 紀錄 ── */
$record = [
    'received_at' => date('c'),
    'name'        => $name,
    'email'       => $email,
    'phone'       => $phone,
    'business'    => $business,
    'industry'    => $industry,
    'city'        => $city,
    'service'     => $service,
    'budget'      => $budget,
    'timeline'    => $timeline,
    'colors'      => $colors,
    'ai_chat'     => $ai_chat,
    'notes'       => $notes,
    'referral'    => $ref,
    'files'       => $uploadedFiles,
    'status'      => 'new',
];
file_put_contents($uploadDir . 'inquiry.json', json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

/* ── 組合 Email 內容 ── */
$to      = 'info@ewnexus.com';
$subject = "🆕 New EWNexus Inquiry: {$business} ({$industry})";

$fileList = '';
foreach ($uploadedFiles as $f) {
    $fileList .= "  • [{$f['field']}] {$f['file']}\n    → {$f['url']}\n";
}
if (empty($fileList)) $fileList = "  (none uploaded)\n";

$errorList = '';
foreach ($uploadErrors as $e) {
    $errorList .= "  ⚠ $e\n";
}

$body = <<<TEXT
New Client Inquiry — EWNexus
════════════════════════════════════════

CONTACT
  Name:     $name
  Email:    $email
  Phone:    $phone
  Referral: $ref

BUSINESS
  Name:     $business
  Industry: $industry
  City:     $city

PROJECT
  Service:  $service
  Budget:   $budget
  Timeline: $timeline
  AI Chat:  $ai_chat
  Colors:   $colors

NOTES
$notes

UPLOADED FILES
$fileList
{$errorList}
════════════════════════════════════════
Files saved to: $uploadDir
Reply-to this email to reach the client directly.
TEXT;

$headers  = "From: noreply@ewnexus.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: EWNexus-Intake/2.0\r\n";

if (mail($to, $subject, $body, $headers)) {
    echo json_encode([
        'success'  => true,
        'message'  => 'Inquiry received! We\'ll be in touch within 1 business day.',
        'files'    => count($uploadedFiles),
        'warnings' => $uploadErrors,
    ]);
} else {
    /* Email 失敗但檔案和 JSON 已儲存，仍算成功 */
    echo json_encode([
        'success'  => true,
        'message'  => 'Inquiry saved! We\'ll be in touch within 1 business day.',
        'files'    => count($uploadedFiles),
        'warnings' => array_merge($uploadErrors, ['Email delivery may be delayed']),
    ]);
}
