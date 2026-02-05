<?php
/**
 * PetPal - Upload Pet Photo Page
 * Photo upload with preview and gallery display
 */

require_once '../config/config.php';

$conn = getDBConnection();

// Fetch all pet photos with user info
$stmt = $conn->query("
    SELECT pet_photos.*, users.username, users.full_name
    FROM pet_photos
    JOIN users ON pet_photos.user_id = users.id
    ORDER BY pet_photos.created_at DESC
");
$photos = $stmt->fetchAll();

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Share photos of your beloved pets with the PetPal community">
    <title>Pet Gallery -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
    <style>
        .upload-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 992px) {
            .upload-section {
                grid-template-columns: 1fr;
            }
        }

        .upload-preview {
            width: 100%;
            max-width: 300px;
            height: 300px;
            border-radius: var(--radius);
            object-fit: cover;
            display: none;
            margin: 20px auto 0;
        }

        .upload-preview.show {
            display: block;
        }

        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            border-radius: var(--radius);
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }

        .lightbox-info {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: white;
        }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Pet Gallery</span>
            </div>
            <h1>Pet Gallery</h1>
            <p>Share photos of your adorable companions</p>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo h($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Upload Section -->
            <section class="upload-section">
                <div>
                    <div class="card" style="padding: 30px;">
                        <h3 style="margin-bottom: 20px;">
                            <i class="fas fa-camera" style="color: var(--primary);"></i>
                            Upload Your Pet Photo
                        </h3>

                        <?php if (!isLoggedIn()): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Please <a href="<?php echo SITE_URL; ?>/pages/login.php"
                                        style="font-weight: 600;">login</a> to upload photos.</span>
                            </div>
                        <?php else: ?>
                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                                <div class="upload-area" id="uploadArea">
                                    <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;"
                                        required>
                                    <div class="upload-icon">📷</div>
                                    <div class="upload-text">
                                        <h4>Drag & Drop or Click to Upload</h4>
                                        <p>Supports JPG, PNG, GIF (Max 5MB)</p>
                                    </div>
                                </div>

                                <img id="previewImage" class="upload-preview" alt="Preview">

                                <div class="form-group" style="margin-top: 20px;">
                                    <label class="form-label">Pet's Name</label>
                                    <input type="text" name="pet_name" class="form-control"
                                        placeholder="What's your pet's name?" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="Tell us about this photo..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block" id="uploadBtn">
                                    <i class="fas fa-upload"></i>
                                    Upload Photo
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="card" style="padding: 30px; height: 100%;">
                        <h3 style="margin-bottom: 20px;">
                            <i class="fas fa-paw" style="color: var(--secondary);"></i>
                            Gallery Guidelines
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; align-items: flex-start; gap: 15px;">
                                <div
                                    style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h5>Clear, Well-Lit Photos</h5>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--gray-dark);">Make sure your pet
                                        is clearly visible in the photo.</p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 15px;">
                                <div
                                    style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <h5>Pets Only</h5>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--gray-dark);">Keep the focus on
                                        your adorable companions!</p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 15px;">
                                <div
                                    style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div>
                                    <h5>High Quality</h5>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--gray-dark);">Upload images at
                                        least 500x500 pixels for best display.</p>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 15px;">
                                <div
                                    style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h5>Family Friendly</h5>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--gray-dark);">All content must
                                        be appropriate for all ages.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Gallery Section -->
            <section>
                <div class="section-title">
                    <h2>Pet Gallery</h2>
                    <p>
                        <?php echo count($photos); ?> adorable pets shared by our community
                    </p>
                </div>

                <?php if (empty($photos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🐾</div>
                        <h3>No Photos Yet</h3>
                        <p>Be the first to share your pet!</p>
                    </div>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="gallery-item"
                                onclick="openLightbox('<?php echo h($photo['photo_url']); ?>', '<?php echo h($photo['pet_name']); ?>', '<?php echo h($photo['full_name']); ?>', '<?php echo h($photo['description']); ?>')">
                                <img src="<?php echo h($photo['photo_url']); ?>" alt="<?php echo h($photo['pet_name']); ?>"
                                    loading="lazy">
                                <div class="gallery-overlay">
                                    <h4>
                                        <?php echo h($photo['pet_name']); ?>
                                    </h4>
                                    <p>by
                                        <?php echo h($photo['username']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <img id="lightboxImage" src="" alt="">
        <div class="lightbox-info">
            <h3 id="lightboxName"></h3>
            <p id="lightboxBy"></p>
            <p id="lightboxDesc"></p>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const photoInput = document.getElementById('photoInput');
        const previewImage = document.getElementById('previewImage');

        // Click to upload
        uploadArea?.addEventListener('click', () => photoInput.click());

        // Drag and drop
        uploadArea?.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea?.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea?.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                photoInput.files = e.dataTransfer.files;
                previewFile(e.dataTransfer.files[0]);
            }
        });

        // File input change
        photoInput?.addEventListener('change', function () {
            if (this.files.length) {
                previewFile(this.files[0]);
            }
        });

        // Preview file
        function previewFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewImage.classList.add('show');
            };
            reader.readAsDataURL(file);
        }

        // Form submission
        document.getElementById('uploadForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = document.getElementById('uploadBtn');

            if (!photoInput.files.length) {
                alert('Please select a photo to upload.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch('<?php echo SITE_URL; ?>/api/upload-photo.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Photo uploaded successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Error uploading photo');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-upload"></i> Upload Photo';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload"></i> Upload Photo';
                });
        });

        // Lightbox functions
        function openLightbox(src, name, by, desc) {
            document.getElementById('lightboxImage').src = src;
            document.getElementById('lightboxName').textContent = name;
            document.getElementById('lightboxBy').textContent = 'by ' + by;
            document.getElementById('lightboxDesc').textContent = desc || '';
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close lightbox on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });

        // Close lightbox on click outside image
        document.getElementById('lightbox')?.addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });
    </script>
</body>

</html>