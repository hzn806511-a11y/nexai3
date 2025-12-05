<?php
session_start();

// اگر کاربر از قبل وارد شده باشد، به داشبورد منتقل می‌شود
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$errorMessage = '';

// بررسی می‌کند که آیا فرم ارسال شده است یا خیر
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // !!! اطلاعات اتصال به دیتابیس خود را وارد کنید !!!
    $host = "sql105.infinityfree.com"; 
    $username_db = "if0_39948816";      
    $password_db = "147280021HZK";        
    $dbname = "if0_39948816_blog";      

    try {
        // اتصال به دیتابیس با استفاده از PDO
        $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // دریافت ایمیل و رمز عبور از فرم
        $email = $_POST['email'];
        $password = $_POST['password'];

        // *** اصلاح شد: کوئری فقط ایمیل را بررسی می‌کند ***
        $stmt = $connection->prepare("SELECT * FROM users WHERE Email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // بررسی وجود کاربر و صحت رمز عبور
        if ($user && password_verify($password, $user['password'])) {
            // ذخیره اطلاعات کاربر در سشن
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'], // یا هر فیلد نام دیگری که در دیتابیس دارید
                'email' => $user['Email'],
                'profile_pic' => $user['profile_pic']
            ];
            // انتقال به صفحه داشبورد
            header("Location: dashboard.php");
            exit();
        } else {
            // پیام خطا در صورت اشتباه بودن اطلاعات
            $errorMessage = "ایمیل یا رمز عبور اشتباه است.";
        }

    } catch (PDOException $e) {
        // پیام خطا در صورت بروز مشکل در اتصال به دیتابیس
        $errorMessage = "خطا در اتصال به سرور. لطفاً بعداً تلاش کنید.";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به حساب کاربری NexAI</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        /* --- تغییرات ظاهری با تم سیاه و بنفش --- */
        :root {
            --primary-purple: #9b59b6; /* بنفش اصلی */
            --dark-background: #121212;  /* پس زمینه تیره */
            --container-background: #1e1e1e; /* پس زمینه کادر */
            --text-color: #ffffff;      /* رنگ متن اصلی */
            --input-background: #2d2d2d;  /* پس زمینه اینپوت */
            --input-border: #444444;    /* رنگ حاشیه اینپوت */
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: var(--dark-background);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .form-container {
            padding: 2.5rem;
            background: var(--container-background);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 420px;
            text-align: center;
            border: 1px solid var(--input-border);
        }
        .logo {
            width: 80px;
            margin-bottom: 1rem;
        }
        h2 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: var(--text-color);
        }
        .form-group {
            margin-bottom: 1.2rem;
            text-align: right;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-background);
            border: 1px solid var(--input-border);
            color: var(--text-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input::placeholder {
            color: #888;
        }
        input:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(155, 89, 182, 0.3);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-purple);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #8e44ad;
        }
        .error {
            color: #e74c3c;
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid #e74c3c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1.2rem;
        }
        p {
            margin-top: 1.5rem;
            color: #aaa;
        }
        a {
            color: var(--primary-purple);
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" alt="Logo" class="logo">
        <h2>ورود به NexAI</h2>
        <form action="login.php" method="post">
            <?php if (!empty($errorMessage)): ?>
                <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
            <?php endif; ?>
            <div class="form-group">
                <!-- *** اصلاح شد: نوع ورودی به ایمیل تغییر کرد و نام آن نیز اصلاح شد *** -->
                <input type="email" id="email" name="email" placeholder="ایمیل" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="رمز عبور" required>
            </div>
            <button type="submit">ورود</button>
        </form>
        <p>حساب کاربری ندارید؟ <a href="register.php">ثبت نام کنید</a></p>
    </div>
</body>
</html>