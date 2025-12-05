<?php
// upload_profile.php
session_start();
header('Content-Type: application/json');

// تابع پاسخ‌دهی استاندارد
function sendJson($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit();
}

if (!isset($_SESSION['user']['id'])) {
    sendJson(false, 'لطفاً وارد شوید.');
}

if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    sendJson(false, 'فایلی انتخاب نشده یا خطا در ارسال.');
}

// تنظیمات دیتابیس
$host = "sql105.infinityfree.com";
$username_db = "if0_39948816";
$password_db = "147280021HZK";
$dbname = "if0_39948816_blog";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user']['id'];
    $file = $_FILES['profile_pic'];
    
    // 1. بررسی پسوند و حجم
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) sendJson(false, 'فقط فایل‌های تصویری مجاز هستند.');
    if ($file['size'] > 5 * 1024 * 1024) sendJson(false, 'حجم فایل نباید بیشتر از 5 مگابایت باشد.');

    // 2. پیدا کردن و حذف عکس قدیمی (بخش درخواستی شما)
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $oldPic = $stmt->fetchColumn();

    $uploadDir = 'uploads/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // اگر عکس قدیمی وجود دارد و فایلش در سرور هست، آن را پاک کن
    if ($oldPic && file_exists($uploadDir . $oldPic)) {
        unlink($uploadDir . $oldPic);
    }

    // 3. آپلود عکس جدید با نام یونیک
    $newFileName = 'user_' . $userId . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // 4. آپدیت دیتابیس
        $update = $conn->prepare("UPDATE users SET profile_pic = :pic WHERE id = :id");
        $update->execute([':pic' => $newFileName, ':id' => $userId]);

        // آپدیت سشن
        $_SESSION['user']['profile_pic'] = $newFileName;

        sendJson(true, 'عکس پروفایل با موفقیت تغییر کرد.', ['imagePath' => $destination]);
    } else {
        sendJson(false, 'خطا در ذخیره فایل در سرور.');
    }

} catch (PDOException $e) {
    sendJson(false, 'خطای دیتابیس: ' . $e->getMessage());
}
?>