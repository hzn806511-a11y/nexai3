<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$host = "sql105.infinityfree.com"; 
$username_db = "if0_39948816";      
$password_db = "147280021HZK";        
$dbname = "if0_39948816_blog";      

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

$userName = $_SESSION['user']['name'] ?? 'کاربر';
$userEmail = $_SESSION['user']['email'] ?? '';
$profilePic = $_SESSION['user']['profile_pic'] ?? null;
$uploadDir = 'uploads/profiles/';
$fullProfilePicPath = $uploadDir . $profilePic;

if (empty($profilePic) || !file_exists($fullProfilePicPath) || is_dir($fullProfilePicPath)) {
    $fullProfilePicPath = 'https://imagizer.imageshack.com/img923/3161/sxavmO.png';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد NexAI</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a1a; --card-bg: rgba(25, 20, 50, 0.7);
            --primary: #8a2be2; --secondary: #a855f7; --text: #f0e6ff;
            --border: rgba(138, 43, 226, 0.3); --success: #28a745; --danger: #dc3545;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', sans-serif; background: var(--bg-dark); color: var(--text);
            min-height: 100vh; padding: 2rem 1rem;
            background-image: radial-gradient(circle at 20% 80%, rgba(138, 43, 226, 0.2) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(168, 85, 247, 0.2) 0%, transparent 50%);
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 3rem; }
        .logo { width: 120px; border-radius: 50%; box-shadow: 0 0 30px rgba(138, 43, 226, 0.6); margin-bottom: 1rem; animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .title { font-size: 2.5rem; background: linear-gradient(45deg, #8a2be2, #ffffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0.5rem; }
        .subtitle { font-size: 1.1rem; opacity: 0.8; }
        .user-info { text-align: center; margin-bottom: 2rem; padding: 1.5rem; background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border); backdrop-filter: blur(10px); position: relative; }
        .profile-section { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
        .profile-pic-wrapper { position: relative; width: 100px; height: 100px; }
        .profile-pic { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); box-shadow: 0 0 20px rgba(138, 43, 226, 0.5); }
        .edit-pic-btn { position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: all 0.3s; z-index: 10; }
        .edit-pic-btn:hover { background: var(--secondary); transform: scale(1.1); }
        .user-details p { margin: 0.4rem 0; font-size: 1.1rem; }
        .user-details strong { color: var(--secondary); }
        #userEmailDisplay { font-size: 0.95rem; color: #c084fc; }
        .logout { position: absolute; top: 20px; left: 20px; padding: 10px 20px; background: linear-gradient(135deg, #ff4757, #ff6b81); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4); }
        .logout:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(255, 71, 87, 0.6); }
        .hidden { display: none; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .card { background: var(--card-bg); border-radius: 20px; padding: 2rem; text-align: center; border: 1px solid var(--border); backdrop-filter: blur(15px); transition: all 0.4s ease; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(138, 43, 226, 0.3); border-color: var(--secondary); }
        .card-icon { width: 80px; height: 80px; margin: 0 auto 1.5rem; }
        .card h3 { font-size: 1.6rem; margin-bottom: 1rem; color: var(--secondary); }
        .card p { font-size: 1rem; opacity: 0.85; margin-bottom: 1.5rem; line-height: 1.6; }
        .btn { display: inline-block; padding: 0.8rem 1.8rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; transition: all 0.4s ease; box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3); }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(138, 43, 226, 0.5); }
        .edit-profile-btn { margin-top: 1rem; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px); display: flex; justify-content: center; align-items: center; opacity: 0; visibility: hidden; transition: opacity 0.3s, visibility 0.3s; z-index: 1000; }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        .modal-content { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; width: 90%; max-width: 500px; transform: scale(0.9); transition: transform 0.3s; }
        .modal-overlay.show .modal-content { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-header h3 { font-size: 1.5rem; color: var(--secondary); }
        .close-btn { background: none; border: none; color: var(--text); font-size: 2rem; cursor: pointer; }
        .form-group { margin-bottom: 1.2rem; text-align: right; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 1rem; }
        .modal-footer { margin-top: 1.5rem; display: flex; justify-content: flex-end; align-items: center; }
        #form-message { margin-right: auto; }
        #form-message.success { color: var(--success); }
        #form-message.error { color: var(--danger); }
        .spinner { width: 24px; height: 24px; border: 3px solid rgba(255, 255, 255, 0.3); border-top-color: var(--secondary); border-radius: 50%; animation: spin 1s linear infinite; }
        
        .upload-loader-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            display: none; justify-content: center; align-items: center;
            z-index: 2000;
            padding: 1rem;
            overflow: hidden;
        }
        .loader-content { display: flex; flex-direction: column; align-items: center; gap: 2rem; }
        
        .image-preview-container {
            position: relative;
            transition: width 0.3s ease, height 0.3s ease;
        }
        .image-preview-container::before {
            content: '';
            position: absolute;
            top: -8px; left: -8px;
            width: calc(100% + 16px);
            height: calc(100% + 16px);
            border: 8px solid rgba(255, 255, 255, 0.2);
            border-top-color: var(--secondary);
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
        }
        #imagePreview {
            width: 100%; height: 100%;
            object-fit: cover; border-radius: 50%;
            display: block; background-color: var(--bg-dark);
        }
        .loader-text { color: var(--text); font-size: 1.1rem; max-width: 450px; line-height: 1.8; text-align: center; opacity: 0.9; }

        .corner-glow {
            position: absolute;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.4) 0%, transparent 70%);
            opacity: 0;
            animation: pulse 2.5s infinite ease-in-out;
        }
        .top-left { top: -50px; left: -50px; }
        .top-right { top: -50px; right: -50px; animation-delay: 0.6s; }
        .bottom-left { bottom: -50px; left: -50px; animation-delay: 1.2s; }
        .bottom-right { bottom: -50px; right: -50px; animation-delay: 1.8s; }

        @keyframes pulse { 50% { opacity: 0.6; transform: scale(1.2); } }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <a href="logout.php" class="logout">خروج</a>
    <div class="container">
        <div class="header">
            <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" alt="NexAI Logo" class="logo">
            <h1 class="title">داشبورد NexAI</h1>
            <p class="subtitle">به پنل کاربری خود خوش آمدید</p>
        </div>
        <div class="user-info">
            <div class="profile-section">
                <div class="profile-pic-wrapper">
                    <img src="<?= htmlspecialchars($fullProfilePicPath) ?>?t=<?= time() ?>" alt="عکس پروفایل" class="profile-pic" id="profilePic">
                    <button class="edit-pic-btn" title="تغییر عکس پروفایل" onclick="document.getElementById('fileInput').click()">&#9998;</button>
                    <form id="uploadPicForm" class="hidden">
                        <input type="file" id="fileInput" name="profile_pic" accept="image/*" onchange="uploadProfilePic()">
                    </form>
                </div>
                <div class="user-details">
                    <p>خوش آمدید، <strong><?= htmlspecialchars($userName) ?></strong>!</p>
                    <?php if ($userEmail): ?>
                        <p id="userEmailDisplay">ایمیل: <?= htmlspecialchars($userEmail) ?></p>
                    <?php endif; ?>
                </div>
                <button class="btn edit-profile-btn" id="openModalBtn">ویرایش پروفایل</button>
            </div>
        </div>
        <div class="grid">
            <div class="card"><img src="nima.png" alt="Image Icon" class="card-icon"><h3>چت با AI</h3><p>با هوش مصنوعی قدرتمند صحبت کنید، سوال بپرسید، کد بنویسید و ایده بگیرید.</p><a href="nexai-image.php" class="btn">شروع چت</a></div>
            <div class="card"><img src="nima.png" alt="Intro Icon" class="card-icon"><h3>معرفی NexAI</h3><p>با قابلیت‌ها، آینده و داستان ساخت NexAI بیشتر آشنا شوید.</p><a href="nexai-intro.php" class="btn">مشاهده معرفی</a></div>
        </div>
    </div>
    
    <div class="modal-overlay" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header"><h3>ویرایش اطلاعات پروفایل</h3><button class="close-btn" id="closeModalBtn">&times;</button></div>
            <form id="editProfileForm">
                <div class="form-group"><label for="email">ایمیل جدید (اختیاری)</label><input type="email" id="email" name="email" placeholder="ایمیل جدید را وارد کنید"></div>
                <div class="form-group"><label for="new_password">رمز عبور جدید (اختیاری)</label><input type="password" id="new_password" name="new_password" placeholder="حداقل ۶ کاراکتر"></div>
                <div class="form-group"><label for="confirm_password">تکرار رمز عبور جدید</label><input type="password" id="confirm_password" name="confirm_password" placeholder="رمز جدید را تکرار کنید"></div>
                <hr style="border-color: var(--border); margin: 1.5rem 0;">
                <div class="form-group"><label for="current_password">رمز عبور فعلی (برای تایید)</label><input type="password" id="current_password" name="current_password" required placeholder="برای ذخیره تغییرات الزامی است"></div>
                <div class="modal-footer"><div id="form-message"></div><div id="spinner" class="spinner hidden"></div><button type="submit" class="btn" id="saveChangesBtn">ذخیره تغییرات</button></div>
            </form>
        </div>
    </div>
    
    <div class="upload-loader-overlay" id="uploadLoader">
        <div class="corner-glow top-left"></div>
        <div class="corner-glow top-right"></div>
        <div class="corner-glow bottom-left"></div>
        <div class="corner-glow bottom-right"></div>
        <div class="loader-content">
            <div class="image-preview-container">
                <img src="" alt="پیش‌نمایش پروفایل" id="imagePreview">
            </div>
            <p class="loader-text">در صورتی که تصویر پروفایل شما نمایش داده نشد، یک بار سایت را رفرش کنید تا تغییرات کامل اعمال شود.</p>
        </div>
    </div>
    
    <script>
        async function uploadProfilePic() {
            const fileInput = document.getElementById('fileInput');
            const loader = document.getElementById('uploadLoader');
            const imagePreview = document.getElementById('imagePreview');
            const previewContainer = document.querySelector('.image-preview-container');
            const file = fileInput.files[0];
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const MIN_SIZE = 150;
                    const MAX_SIZE_PERCENT = 0.7;
                    const HARD_MAX_SIZE = 450;
                    const imgWidth = img.naturalWidth;
                    const imgHeight = img.naturalHeight;
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    const maxAllowedFromViewport = Math.min(viewportWidth, viewportHeight) * MAX_SIZE_PERCENT;
                    const finalMaxSize = Math.min(maxAllowedFromViewport, HARD_MAX_SIZE);
                    const largestImageDim = Math.max(imgWidth, imgHeight);
                    const finalSize = Math.max(MIN_SIZE, Math.min(largestImageDim, finalMaxSize));

                    previewContainer.style.width = `${finalSize}px`;
                    previewContainer.style.height = `${finalSize}px`;
                    
                    imagePreview.src = e.target.result;
                    loader.style.display = 'flex';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('profile_pic', file);

            try {
                const response = await fetch('upload_profile.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('profilePic').src = result.imagePath + '?t=' + new Date().getTime();
                } else {
                    alert('خطا: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('خطا در ارتباط با سرور.');
            } finally {
                setTimeout(() => {
                    loader.style.display = 'none';
                    previewContainer.style.width = '';
                    previewContainer.style.height = '';
                }, 500);
                fileInput.value = '';
            }
        }

        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalOverlay = document.getElementById('editProfileModal');
        const editProfileForm = document.getElementById('editProfileForm');
        const saveChangesBtn = document.getElementById('saveChangesBtn');
        const spinner = document.getElementById('spinner');
        const formMessage = document.getElementById('form-message');

        openModalBtn.addEventListener('click', () => modalOverlay.classList.add('show'));
        const closeModal = () => {
             modalOverlay.classList.remove('show');
             formMessage.textContent = '';
             editProfileForm.reset();
        };
        closeModalBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });

        editProfileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            spinner.classList.remove('hidden');
            saveChangesBtn.classList.add('hidden');
            formMessage.textContent = '';
            const formData = new FormData(this);
            try {
                const response = await fetch('update_profile.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    formMessage.className = 'success';
                    if (result.newEmail) {
                        document.getElementById('userEmailDisplay').textContent = 'ایمیل: ' + result.newEmail;
                    }
                    setTimeout(() => { closeModal(); }, 2000);
                } else {
                    formMessage.className = 'error';
                }
                formMessage.textContent = result.message;
            } catch (error) {
                formMessage.className = 'error';
                formMessage.textContent = 'خطا در ارتباط با سرور.';
                console.error('Error:', error);
            } finally {
                spinner.classList.add('hidden');
                saveChangesBtn.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>