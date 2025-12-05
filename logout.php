<?php
// logout.php
session_start();

// 1. خالی کردن تمام متغیرهای سشن
$_SESSION = array();

// 2. حذف کوکی سشن (اگر وجود دارد)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. نابود کردن نهایی سشن
session_destroy();

// 4. انتقال به صفحه اصلی یا ورود
header("Location: index.php");
exit();
?>