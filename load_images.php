<?php
$upload_dir = 'uploads/';
$images = [];

if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);

    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $upload_dir . $file;
            $file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Sadece resim dosyalarını al
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $file_size = filesize($file_path);
                $file_size_formatted = $file_size > 1024 * 1024
                    ? round($file_size / (1024 * 1024), 2) . ' MB'
                    : round($file_size / 1024, 2) . ' KB';

                $images[] = [
                    'filename' => $file,
                    'size' => $file_size_formatted,
                    'date' => date('d.m.Y H:i', filemtime($file_path))
                ];
            }
        }
    }
}

// Dosyaları tarihe göre sırala (en yeni önce)
usort($images, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'images' => $images]);
