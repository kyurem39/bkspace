<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // Chặn cache trình duyệt
header("Pragma: no-cache"); // Cho HTTP/1.0
header("Expires: 0"); // Cho proxy
$files = array_diff(scandir("uploads"), array(".", ".."));
$result = [];

foreach ($files as $file) {
    $path = "uploads/" . $file;

    // Kiểm tra xem có phải file thật không (tránh folder)
    if (is_file($path)) {
        $result[] = [
            'name' => $file,
            'size' => filesize($path),      // dung lượng tính bằng byte
            'time' => filemtime($path)      // thời gian chỉnh sửa cuối (timestamp)
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($result);
?>
