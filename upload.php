<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Cáº¥u hÃ¬nh
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 15 * 1024 * 1024); // 15MB
define('MAX_TOTAL_STORAGE', 5 * 1024 * 1024 * 1024); // 5GB

// Táº¡o thÆ° má»¥c uploads náº¿u chÆ°a tá»n táº¡i
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'KhÃ´ng thá» táº¡o thÆ° má»¥c uploads'
        ]);
        exit;
    }
}

// Kiá»m tra phÆ°Æ¡ng thá»©c POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'PhÆ°Æ¡ng thá»©c khÃ´ng ÄÆ°á»£c há» trá»£'
    ]);
    exit;
}

// Kiá»m tra cÃ³ file ÄÆ°á»£c gá»­i khÃ´ng
if (!isset($_FILES['file'])) {
    echo json_encode([
        'success' => false,
        'message' => 'KhÃ´ng cÃ³ tá»p nÃ o ÄÆ°á»£c gá»­i'
    ]);
    exit;
}

$file = $_FILES['file'];

// Kiá»m tra lá»i upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Tá»p vÆ°á»£t quÃ¡ kÃ­ch thÆ°á»c cho phÃ©p cá»§a server',
        UPLOAD_ERR_FORM_SIZE => 'Tá»p vÆ°á»£t quÃ¡ kÃ­ch thÆ°á»c cho phÃ©p',
        UPLOAD_ERR_PARTIAL => 'Tá»p chá» ÄÆ°á»£c táº£i lÃªn má»t pháº§n',
        UPLOAD_ERR_NO_FILE => 'KhÃ´ng cÃ³ tá»p nÃ o ÄÆ°á»£c táº£i lÃªn',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiáº¿u thÆ° má»¥c táº¡m',
        UPLOAD_ERR_CANT_WRITE => 'KhÃ´ng thá» ghi tá»p ra ÄÄ©a',
        UPLOAD_ERR_EXTENSION => 'Táº£i lÃªn bá» dá»«ng bá»i extension'
    ];
    
    $message = $errorMessages[$file['error']] ?? 'Lá»i khÃ´ng xÃ¡c Äá»nh khi táº£i lÃªn';
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Kiá»m tra kÃ­ch thÆ°á»c tá»p
if ($file['size'] > MAX_FILE_SIZE) {
    echo json_encode([
        'success' => false,
        'message' => 'Tá»p vÆ°á»£t quÃ¡ giá»i háº¡n 15MB'
    ]);
    exit;
}

// Kiá»m tra tá»ng dung lÆ°á»£ng ÄÃ£ sá»­ dá»¥ng
function getTotalStorageUsed() {
    $total = 0;
    $files = glob(UPLOAD_DIR . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            $total += filesize($file);
        }
    }
    return $total;
}

$currentUsage = getTotalStorageUsed();
if ($currentUsage + $file['size'] > MAX_TOTAL_STORAGE) {
    echo json_encode([
        'success' => false,
        'message' => 'KhÃ´ng Äá»§ dung lÆ°á»£ng lÆ°u trá»¯'
    ]);
    exit;
}

// LÃ m sáº¡ch tÃªn tá»p
function sanitizeFilename($filename) {
    // Loáº¡i bá» cÃ¡c kÃ½ tá»± nguy hiá»m
    $filename = preg_replace('/[^a-zA-Z0-9._\-\s\p{L}]/u', '', $filename);
    // Thay tháº¿ nhiá»u khoáº£ng tráº¯ng báº±ng má»t khoáº£ng tráº¯ng
    $filename = preg_replace('/\s+/', ' ', $filename);
    // Trim khoáº£ng tráº¯ng
    $filename = trim($filename);
    // Náº¿u tÃªn rá»ng, táº¡o tÃªn máº·c Äá»nh
    if (empty($filename)) {
        $filename = 'file_' . date('Y-m-d_H-i-s');
    }
    return $filename;
}

$originalName = $file['name'];
$sanitizedName = sanitizeFilename($originalName);
$targetPath = UPLOAD_DIR . $sanitizedName;

// Kiá»m tra tá»p ÄÃ£ tá»n táº¡i, táº¡o tÃªn má»i náº¿u cáº§n
$counter = 1;
$pathInfo = pathinfo($sanitizedName);
$baseName = $pathInfo['filename'];
$extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

while (file_exists($targetPath)) {
    $newName = $baseName . '_' . $counter . $extension;
    $targetPath = UPLOAD_DIR . $newName;
    $counter++;
}

// Kiá»m tra loáº¡i MIME (báº£o máº­t cÆ¡ báº£n)
$allowedMimeTypes = [
    // Images
    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
    // Documents
    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain', 'application/rtf',
    // Spreadsheets
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv',
    // Presentations
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    // Archives
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip',
    // Audio
    'audio/mpeg', 'audio/wav', 'audio/flac', 'audio/aac', 'audio/mp4',
    // Video
    'video/mp4', 'video/avi', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv',
    // Code and text
    'text/html', 'text/css', 'application/javascript', 'application/x-php', 'text/x-python',
    'text/x-c', 'text/x-c++src', 'application/json', 'application/xml', 'text/xml'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);

// Náº¿u khÃ´ng nháº­n diá»n ÄÆ°á»£c MIME type, cho phÃ©p upload nhÆ°ng cáº£nh bÃ¡o
if (!$mimeType) {
    $mimeType = 'application/octet-stream';
}

// Di chuyá»n tá»p Äáº¿n thÆ° má»¥c ÄÃ­ch
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Äáº·t quyá»n cho tá»p
    chmod($targetPath, 0644);
    
    echo json_encode([
        'success' => true,
        'message' => 'Táº£i lÃªn thÃ nh cÃ´ng',
        'filename' => basename($targetPath),
        'size' => filesize($targetPath),
        'mime_type' => $mimeType
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'KhÃ´ng thá» di chuyá»n tá»p ÄÃ£ táº£i lÃªn'
    ]);
}
?>