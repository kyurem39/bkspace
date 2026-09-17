<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // Chặn cache trình duyệt
header("Pragma: no-cache"); // Cho HTTP/1.0
header("Expires: 0"); // Cho proxy
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Cáº¥u hÃ¬nh
define('UPLOAD_DIR', 'uploads/');

// Táº¡o thÆ° má»¥c uploads náº¿u chÆ°a tá»n táº¡i
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'KhÃ´ng thá» táº¡o thÆ° má»¥c uploads',
            'files' => [],
            'totalSize' => 0
        ]);
        exit;
    }
}

try {
    $files = [];
    $totalSize = 0;
    
    // Láº¥y danh sÃ¡ch táº¥t cáº£ tá»p trong thÆ° má»¥c uploads
    $fileList = glob(UPLOAD_DIR . '*');
    
    if ($fileList === false) {
        throw new Exception('KhÃ´ng thá» Äá»c thÆ° má»¥c uploads');
    }
    
    foreach ($fileList as $filePath) {
        // Chá» xá»­ lÃ½ tá»p, khÃ´ng xá»­ lÃ½ thÆ° má»¥c
        if (!is_file($filePath)) {
            continue;
        }
        
        $filename = basename($filePath);
        $fileSize = filesize($filePath);
        $modified = filemtime($filePath);
        
        // Bá» qua tá»p áº©n (báº¯t Äáº§u báº±ng dáº¥u cháº¥m)
        if (strpos($filename, '.') === 0) {
            continue;
        }
        
        // Kiá»m tra tÃ­nh há»£p lá» cá»§a tá»p
        if ($fileSize === false || $modified === false) {
            continue;
        }
        
        $files[] = [
            'name' => $filename,
            'size' => $fileSize,
            'modified' => $modified,
            'path' => $filePath
        ];
        
        $totalSize += $fileSize;
    }
    
    // Sáº¯p xáº¿p tá»p theo thá»i gian sá»­a Äá»i (má»i nháº¥t trÆ°á»c)
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    // Loáº¡i bá» path khá»i response Äá» báº£o máº­t
    $responseFiles = array_map(function($file) {
        return [
            'name' => $file['name'],
            'size' => $file['size'],
            'modified' => $file['modified']
        ];
    }, $files);
    
    echo json_encode([
        'success' => true,
        'files' => $responseFiles,
        'totalSize' => $totalSize,
        'count' => count($files)
    ]);

} catch (Exception $e) {
    error_log('Error in files.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Lá»i khi láº¥y danh sÃ¡ch tá»p: ' . $e->getMessage(),
        'files' => [],
        'totalSize' => 0,
        'count' => 0
    ]);
}
?>