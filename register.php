<?php
// register.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// اگر کاربر لاگین است، به داشبورد برود
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$msgType = ''; // success or error

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // تنظیمات دیتابیس
    $host = "sql105.infinityfree.com";
    $username_db = "if0_39948816";
    $password_db = "147280021HZK";
    $dbname = "if0_39948816_blog";

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm_password) {
        $message = "لطفاً تمام فیلدها را پر کنید.";
        $msgType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "فرمت ایمیل نامعتبر است.";
        $msgType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "رمز عبور و تکرار آن مطابقت ندارند.";
        $msgType = "error";
    } elseif (strlen($password) < 6) {
        $message = "رمز عبور باید حداقل ۶ کاراکتر باشد.";
        $msgType = "error";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // بررسی تکراری بودن ایمیل
            $stmt = $conn->prepare("SELECT id FROM users WHERE Email = :email");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $message = "این ایمیل قبلاً ثبت‌نام شده است.";
                $msgType = "error";
            } else {
                // ثبت کاربر جدید
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, Email, password, profile_pic) VALUES (:name, :email, :pass, NULL)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pass' => $hashed_password
                ]);

                // لاگین خودکار بعد از ثبت‌نام
                $userId = $conn->lastInsertId();
                $_SESSION['user'] = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'profile_pic' => null
                ];

                header("Location: dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = "خطا در اتصال به پایگاه داده. لطفاً بعداً تلاش کنید.";
            $msgType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت‌نام در NexAI</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css');

        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.6);
            --glass-bg: rgba(15, 15, 30, 0.7);
            --glass-border: rgba(139, 92, 246, 0.3);
            --text: #e0e7ff;
            --error-bg: rgba(220, 38, 38, 0.2);
            --error-text: #fca5a5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #000;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .video-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            object-fit: cover; filter: brightness(0.4);
        }
        .grid-overlay {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(rgba(139, 92, 246, 0.1) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(139, 92, 246, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle, black 30%, transparent 80%);
        }

        .register-container {
            width: 100%; max-width: 450px; padding: 40px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7);
            text-align: center;
            opacity: 0; transform: translateY(50px);
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            position: relative;
        }

        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }

        /* افکت نوری دور کادر */
        .register-container::before {
            content: ''; position: absolute; inset: -1px; border-radius: 24px;
            background: linear-gradient(45deg, transparent, var(--primary), transparent);
            z-index: -1; opacity: 0.5; animation: rotateBorder 4s linear infinite;
        }
        @keyframes rotateBorder { 0% { opacity: 0.3; } 50% { opacity: 0.7; } 100% { opacity: 0.3; } }

        .logo {
            width: 80px; height: 80px; border-radius: 50%;
            border: 3px solid var(--primary);
            box-shadow: 0 0 25px var(--primary-glow);
            margin-bottom: 15px;
            animation: pulseLogo 3s infinite alternate;
        }
        @keyframes pulseLogo { from { box-shadow: 0 0 15px var(--primary-glow); transform: scale(1); } to { box-shadow: 0 0 35px var(--primary); transform: scale(1.05); } }

        h2 { font-size: 1.8rem; margin-bottom: 10px; color: white; }
        p.subtitle { color: #94a3b8; font-size: 0.9rem; margin-bottom: 25px; }

        .input-group { position: relative; margin-bottom: 15px; text-align: right; }
        
        .input-group input {
            width: 100%; padding: 12px 45px 12px 15px;
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; color: white; font-family: inherit; font-size: 0.95rem;
            transition: 0.3s;
        }
        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
            outline: none; background: rgba(139, 92, 246, 0.1);
        }
        
        .input-icon {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; width: 18px; transition: 0.3s;
        }
        .input-group input:focus + .input-icon { color: var(--primary); }

        .btn-submit {
            width: 100%; padding: 14px; margin-top: 15px;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white; border: none; border-radius: 12px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer;
            transition: 0.3s; box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.6);
        }

        .alert-box {
            background: var(--error-bg); color: var(--error-text);
            padding: 10px; border-radius: 8px; margin-bottom: 20px;
            border: 1px solid rgba(220, 38, 38, 0.3); font-size: 0.9rem;
        }

        .footer-link { margin-top: 25px; font-size: 0.9rem; color: #cbd5e1; }
        .footer-link a { color: var(--primary); text-decoration: none; font-weight: bold; }
        .footer-link a:hover { text-decoration: underline; color: #c4b5fd; }
    </style>
</head>
<body>

    <video class="video-bg" autoplay muted loop playsinline>
        <source src="./video/popo.mp4" type="video/mp4">
    </video>
    <div class="grid-overlay"></div>

    <div class="register-container">
        <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" alt="NexAI" class="logo">
        <h2>عضویت در NexAI</h2>
        <p class="subtitle">به جمع کاربران هوش مصنوعی بپیوندید</p>

        <?php if ($message): ?>
            <div class="alert-box"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div class="input-group">
                <input type="text" name="name" placeholder="نام نمایشی" required>
                <i data-lucide="user" class="input-icon"></i>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="آدرس ایمیل" required>
                <i data-lucide="mail" class="input-icon"></i>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="رمز عبور (حداقل ۶ رقم)" required>
                <i data-lucide="lock" class="input-icon"></i>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="تکرار رمز عبور" required>
                <i data-lucide="lock-keyhole" class="input-icon"></i>
            </div>

            <button type="submit" class="btn-submit">ثبت نام</button>
        </form>

        <div class="footer-link">
            قبلاً ثبت‌نام کرده‌اید؟ <a href="login.php">وارد شوید</a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>