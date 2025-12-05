<?php
// update_profile.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
    exit();
}

$host = "sql105.infinityfree.com";
$username_db = "if0_39948816";
$password_db = "147280021HZK";
$dbname = "if0_39948816_blog";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user']['id'];
    $email = $_POST['email'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $currentPass = $_POST['current_password'] ?? '';

    // 1. بررسی رمز عبور فعلی (اجباری برای هر تغییری)
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPass, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'رمز عبور فعلی اشتباه است.']);
        exit();
    }

    // 2. آماده‌سازی آپدیت
    $updates = [];
    $params = [':id' => $userId];

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $updates[] = "Email = :email";
        $params[':email'] = $email;
        $_SESSION['user']['email'] = $email; // آپدیت سشن
    }

    if (!empty($newPass)) {
        if (strlen($newPass) < 6) {
            echo json_encode(['success' => false, 'message' => 'رمز جدید باید حداقل ۶ رقم باشد.']);
            exit();
        }
        $updates[] = "password = :pass";
        $params[':pass'] = password_hash($newPass, PASSWORD_DEFAULT);
    }

    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'هیچ تغییری اعمال نشد.']);
        exit();
    }

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
    $conn->prepare($sql)->execute($params);

    echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت بروزرسانی شد.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطای سرور (احتمالا ایمیل تکراری).']);
}
?>