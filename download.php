<?php
// Cấu hình
define('UPLOAD_DIR', 'uploads/');

// Helper lấy biểu tượng vector SVG theo mã lỗi
function getErrorIconSvg($type) {
    switch ($type) {
        case 'warning':
            return '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
        case 'forbidden':
            return '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
        case 'serverError':
            return '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
        case 'notFound':
        default:
            return '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9.5" y1="12.5" x2="14.5" y2="17.5"></line><line x1="14.5" y1="12.5" x2="9.5" y2="17.5"></line></svg>';
    }
}

// Hàm hiển thị trang thông báo lỗi đẹp mắt
function showErrorPage($title, $message, $statusCode = 404, $iconType = 'notFound', $fileHint = '') {
    http_response_code($statusCode);
    $iconSvg = getErrorIconSvg($iconType);
    $isWarning = ($statusCode === 400);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - BK Space</title>
        <link rel="stylesheet" href="css/style.css">
        <script>
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body>
        <div class="error-page-wrapper">
            <main class="error-card">
                <div class="error-status-badge <?php echo $isWarning ? 'warning' : ''; ?>">
                    <?php echo $iconSvg; ?>
                </div>
                <h1 class="error-title"><?php echo htmlspecialchars($title); ?></h1>
                <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
                <?php if (!empty($fileHint)): ?>
                    <div class="error-file-pill">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        <span class="error-file-name"><?php echo htmlspecialchars(basename($fileHint)); ?></span>
                    </div>
                <?php endif; ?>
                <div class="error-actions">
                    <a href="index.html" class="error-primary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Về trang chủ</span>
                    </a>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Kiểm tra tham số filename
if (!isset($_GET['file']) || empty(trim($_GET['file']))) {
    showErrorPage('Thiếu thông tin', 'Tên tệp không được cung cấp hoặc để trống.', 400, 'warning');
}

$filename = trim($_GET['file']);

// Làm sạch tên tệp để tránh path traversal
$sanitizedFilename = basename($filename);
$filePath = UPLOAD_DIR . $sanitizedFilename;

// Kiểm tra tệp có tồn tại không
if (!file_exists($filePath) || !is_file($filePath)) {
    showErrorPage('Không tìm thấy tệp', 'Tệp tin này có thể đã bị xóa, hết hạn hoặc đường dẫn liên kết không chính xác.', 404, 'notFound', $filename);
}

// Kiểm tra tệp có nằm trong thư mục uploads không (bảo mật)
$realPath = realpath($filePath);
$uploadRealPath = realpath(UPLOAD_DIR);

if (!$realPath || !$uploadRealPath || strpos($realPath, $uploadRealPath) !== 0) {
    showErrorPage('Truy cập bị từ chối', 'Bạn không có quyền truy cập vào khu vực này để bảo mật.', 403, 'forbidden', $filename);
}

// Lấy thông tin tệp
$fileSize = filesize($realPath);
$mimeType = 'application/octet-stream';

// Xác định MIME type
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $realPath);
    if ($detectedMime) {
        $mimeType = $detectedMime;
    }
    finfo_close($finfo);
} else {
    // Fallback cho một số extension phổ biến
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'zip' => 'application/zip',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    
    if (isset($mimeTypes[$extension])) {
        $mimeType = $mimeTypes[$extension];
    }
}

// Thiết lập headers để download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Ngăn chặn timeout cho tệp lớn
set_time_limit(0);

// Đọc và xuất tệp theo chunk để tiết kiệm memory
$chunkSize = 8192; // 8KB chunks
$handle = fopen($realPath, 'rb');

if ($handle === false) {
    showErrorPage('Lỗi hệ thống', 'Không thể mở tệp tin để chuẩn bị tải xuống. Vui lòng thử lại sau.', 500, 'serverError', $filename);
}

// Làm sạch output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Xuất tệp theo chunks
while (!feof($handle)) {
    $chunk = fread($handle, $chunkSize);
    if ($chunk === false) {
        break;
    }
    echo $chunk;
    
    // Flush output để gửi ngay lập tức
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

fclose($handle);
exit;
?>