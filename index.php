<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag and Drop Image Upload Using PHP</title>

    <!-- Style CSS -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .upload-container {
            text-align: center;
            width: 400px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .drop-area {
            border: 2px dashed #007bff;
            padding: 50px;
            background-color: #fff;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .drop-area.dragover {
            background-color: #e9f7fe;
        }

        .drop-area p {
            font-size: 16px;
            color: #666;
        }

        .drop-area span {
            color: #007bff;
            cursor: pointer;
        }

        .preview-container {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        .preview-container img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .preview-container button {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .progress-container {
            display: none;
            margin-top: 15px;
            width: 100%;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: 0%;
            border-radius: 10px;
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-text {
            text-align: center;
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }

        .upload-speed {
            font-size: 12px;
            color: #888;
            text-align: center;
            margin-top: 2px;
        }

        .btn-upload {
            background-color: #28a745;
            color: #fff;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: #fff;
        }

        .gallery {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .gallery img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="upload-container">
        <h1>Drag and Drop Image Upload</h1>
        <div id="drop-area" class="drop-area">
            <p>Drag & Drop your images here or <span id="browse">Browse</span></p>
            <p style="font-size: 12px; color: #888; margin-top: 10px;">
                Kabul edilen formatlar: JPG, PNG, GIF, WebP<br>
                Maksimum dosya boyutu: 5MB
            </p>
            <input type="file" id="fileElem" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display:none">
        </div>

        <!-- Preview and confirmation buttons -->
        <div id="preview-container" class="preview-container">
            <img id="preview-image" alt="Image Preview">

            <!-- Progress Bar -->
            <div id="progress-container" class="progress-container">
                <div class="progress-bar">
                    <div id="progress-fill" class="progress-fill"></div>
                </div>
                <div id="progress-text" class="progress-text">Yükleniyor... 0%</div>
                <div id="upload-speed" class="upload-speed"></div>
            </div>

            <div id="button-container">
                <button class="btn-upload" id="upload-btn">Upload</button>
                <button class="btn-cancel" id="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Gallery to display uploaded images -->
    <h2 style="text-align: center; color: #333; margin-top: 40px;">Yüklenen Resimler</h2>
    <div class="gallery" id="gallery"></div>

    <!-- Script JS -->
    <script>
        const dropArea = document.getElementById('drop-area');
        const fileElem = document.getElementById('fileElem');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const uploadBtn = document.getElementById('upload-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const gallery = document.getElementById('gallery');
        const progressContainer = document.getElementById('progress-container');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        const uploadSpeed = document.getElementById('upload-speed');
        let selectedFile;

        // Highlight the drop area when dragging over
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        document.getElementById('browse').addEventListener('click', () => {
            fileElem.click();
        });

        fileElem.addEventListener('change', () => {
            handleFiles(fileElem.files);
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];

                // Frontend dosya tipi kontrolü
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Sadece JPG, PNG, GIF ve WebP dosyaları kabul edilir!');
                    return;
                }

                // Frontend dosya boyutu kontrolü (5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB
                const fileSize = formatFileSize(file.size);
                const maxSizeFormatted = formatFileSize(maxSize);

                if (file.size > maxSize) {
                    alert(`Dosya boyutu çok büyük!\nDosya boyutu: ${fileSize}\nMaksimum izin verilen: ${maxSizeFormatted}`);
                    return;
                }

                selectedFile = file;
                previewImage.src = URL.createObjectURL(selectedFile);
                previewContainer.style.display = 'block';
                dropArea.style.display = 'none';

                // Dosya bilgilerini göster
                const fileInfo = document.createElement('div');
                fileInfo.id = 'file-info';
                fileInfo.style.cssText = 'margin-top: 10px; color: #666; font-size: 14px;';
                fileInfo.innerHTML = `
                    <strong>Dosya:</strong> ${file.name}<br>
                    <strong>Boyut:</strong> ${fileSize}<br>
                    <strong>Tip:</strong> ${file.type}
                `;

                // Önceki dosya bilgisini temizle
                const existingInfo = document.getElementById('file-info');
                if (existingInfo) {
                    existingInfo.remove();
                }

                previewContainer.appendChild(fileInfo);
            }
        }

        cancelBtn.addEventListener('click', () => {
            resetUploadForm();
        });

        uploadBtn.addEventListener('click', () => {
            if (selectedFile) {
                const formData = new FormData();
                formData.append('image', selectedFile);
                uploadImage(formData);
            }
        });

        function uploadImage(formData) {
            // Upload sırasında butonları gizle ve progress bar göster
            document.getElementById('button-container').style.display = 'none';
            progressContainer.style.display = 'block';

            const xhr = new XMLHttpRequest();
            const startTime = Date.now();

            // Progress tracking
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    const elapsed = (Date.now() - startTime) / 1000; // saniye
                    const speed = e.loaded / elapsed; // bytes per second
                    const remaining = (e.total - e.loaded) / speed; // saniye

                    // Progress bar güncelle
                    progressFill.style.width = percentComplete + '%';
                    progressText.textContent = `Yükleniyor... ${Math.round(percentComplete)}%`;

                    // Upload hızı ve kalan süre hesapla
                    const speedText = formatSpeed(speed);
                    const remainingText = remaining > 0 ? formatTime(remaining) : '';
                    uploadSpeed.textContent = `Hız: ${speedText}${remainingText ? ` - Kalan: ${remainingText}` : ''}`;
                }
            });

            // Upload tamamlandığında
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);

                        if (data.status === 'success') {
                            // Progress bar'ı 100% yap
                            progressFill.style.width = '100%';
                            progressText.textContent = 'Yükleme tamamlandı! 100%';
                            uploadSpeed.textContent = 'Tamamlandı!';

                            // Append the uploaded image to the gallery
                            const img = document.createElement('img');
                            img.src = 'uploads/' + data.file;
                            img.title = `Orijinal: ${data.original_name}\nDosya: ${data.file}\nBoyut: ${data.file_size}`;
                            gallery.appendChild(img);

                            // 1 saniye sonra reset et
                            setTimeout(() => {
                                resetUploadForm();
                                alert(`Resim başarıyla yüklendi!\n\nOrijinal dosya: ${data.original_name}\nYeni dosya adı: ${data.file}\nDosya boyutu: ${data.file_size}`);
                            }, 1000);
                        } else {
                            resetUploadForm();
                            alert('Hata: ' + data.message);
                        }
                    } catch (error) {
                        resetUploadForm();
                        alert('Sunucu yanıtı işlenirken hata oluştu!');
                    }
                } else {
                    resetUploadForm();
                    alert('Yükleme sırasında bir hata oluştu!');
                }
            });

            // Hata durumunda
            xhr.addEventListener('error', function() {
                resetUploadForm();
                alert('Yükleme sırasında bir hata oluştu!');
            });

            // Upload isteğini gönder
            xhr.open('POST', 'upload.php');
            xhr.send(formData);
        }

        function resetUploadForm() {
            // Reset the preview and form
            previewContainer.style.display = 'none';
            previewImage.src = '';
            selectedFile = null;
            dropArea.style.display = 'block';
            progressContainer.style.display = 'none';
            progressFill.style.width = '0%';
            progressText.textContent = 'Yükleniyor... 0%';
            uploadSpeed.textContent = '';

            // Butonları tekrar göster ve aktif et
            document.getElementById('button-container').style.display = 'block';
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload';

            // Dosya bilgilerini temizle
            const fileInfo = document.getElementById('file-info');
            if (fileInfo) {
                fileInfo.remove();
            }
        }

        function formatSpeed(bytesPerSecond) {
            if (bytesPerSecond < 1024) {
                return Math.round(bytesPerSecond) + ' B/s';
            } else if (bytesPerSecond < 1024 * 1024) {
                return Math.round(bytesPerSecond / 1024) + ' KB/s';
            } else {
                return (bytesPerSecond / (1024 * 1024)).toFixed(1) + ' MB/s';
            }
        }

        function formatTime(seconds) {
            if (seconds < 60) {
                return Math.round(seconds) + 's';
            } else {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.round(seconds % 60);
                return `${minutes}m ${remainingSeconds}s`;
            }
        }

        // Sayfa yüklendiğinde mevcut resimleri yükle
        function loadExistingImages() {
            fetch('load_images.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        data.images.forEach(imageData => {
                            const img = document.createElement('img');
                            img.src = 'uploads/' + imageData.filename;
                            img.title = `Dosya: ${imageData.filename}\nBoyut: ${imageData.size}\nTarih: ${imageData.date}`;
                            gallery.appendChild(img);
                        });
                    }
                })
                .catch(error => {
                    console.error('Mevcut resimler yüklenirken hata:', error);
                });
        }

        // Sayfa yüklendiğinde mevcut resimleri yükle
        document.addEventListener('DOMContentLoaded', loadExistingImages);
    </script>
</body>

</html>