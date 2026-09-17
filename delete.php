<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Cấu hình
define('UPLOAD_DIR', 'uploads/');

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ'
    ]);
    exit;
}

// Kiểm tra tham số filename
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tên tệp không được cung cấp'
    ]);
    exit;
}

$filename = $_POST['filename'];

// Làm sạch tên tệp để tránh path traversal
$filename = basename($filename);
$filePath = UPLOAD_DIR . $filename;

try {
    // Kiểm tra tệp có tồn tại không
    if (!file_exists($filePath) || !is_file($filePath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tệp không tồn tại'
        ]);
        exit;
    }
    
    // Kiểm tra tệp có nằm trong thư mục uploads không (bảo mật)
    $realPath = realpath($filePath);
    $uploadRealPath = realpath(UPLOAD_DIR);
    
    if (!$realPath || !$uploadRealPath || strpos($realPath, $uploadRealPath) !== 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Truy cập bị từ chối'
        ]);
        exit;
    }
    
    // Lấy kích thước tệp trước khi xóa
    $fileSize = filesize($realPath);
    
    // Xóa tệp
    if (unlink($realPath)) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa tệp thành công',
            'filename' => $filename,
            'size' => $fileSize
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể xóa tệp'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error in delete.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xóa tệp: ' . $e->getMessage()
    ]);
}
?>