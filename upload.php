<?php
if ($_FILES['image']['error'] == 0) {
    $target_dir = "uploads/";
    $original_filename = basename($_FILES["image"]["name"]);
    
    // Güvenlik: Dosya uzantısı kontrolü
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, PNG, GIF and WebP files are allowed']);
        exit;
    }
    
    // Güvenlik: MIME type kontrolü
    $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_mime = $_FILES['image']['type'];
    
    if (!in_array($file_mime, $allowed_mimes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
        exit;
    }
    
    // Güvenlik: Dosya boyutu kontrolü (5MB max)
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_size = $_FILES['image']['size'];
    
    if ($file_size > $max_size) {
        $file_size_mb = round($file_size / (1024 * 1024), 2);
        $max_size_mb = round($max_size / (1024 * 1024), 2);
        echo json_encode([
            'status' => 'error', 
            'message' => "Dosya boyutu çok büyük! Dosya: {$file_size_mb}MB, Maksimum: {$max_size_mb}MB"
        ]);
        exit;
    }
    
    // Güvenlik: Benzersiz dosya adı oluştur (dosya üzerine yazma önlemi)
    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Güvenlik: Gerçek resim dosyası mı kontrol et
    if (!getimagesize($_FILES["image"]["tmp_name"])) {
        echo json_encode(['status' => 'error', 'message' => 'File is not a valid image']);
        exit;
    }
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo json_encode(['status' => 'success', 'file' => $file_name]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload']);
    }
} else {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File upload was interrupted',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error_code = $_FILES['image']['error'];
    $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error';
    
    echo json_encode(['status' => 'error', 'message' => $error_message]);
}
