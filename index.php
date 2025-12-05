<?php
// index.php
session_start();
$isLoggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexAI - هوش مصنوعی نسل آینده</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css');

        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.6);
            --glass-bg: rgba(15, 15, 30, 0.7);
            --text: #e0e7ff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #000;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-align: center;
        }

        .video-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            object-fit: cover; filter: brightness(0.4);
        }
        .grid-overlay {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(rgba(139, 92, 246, 0.1) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(139, 92, 246, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(circle, black 20%, transparent 80%);
        }

        .hero-content {
            z-index: 1; padding: 20px;
            animation: fadeIn 1.5s ease-out;
        }

        .logo-container {
            position: relative; width: 150px; height: 150px; margin: 0 auto 30px;
        }
        .logo-img {
            width: 100%; height: 100%; border-radius: 50%;
            border: 4px solid var(--primary);
            box-shadow: 0 0 50px var(--primary-glow);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }

        h1 {
            font-size: 3.5rem; margin-bottom: 10px; font-weight: 900;
            background: linear-gradient(to bottom right, #fff, #a78bfa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
        }

        p.tagline {
            font-size: 1.2rem; color: #cbd5e1; margin-bottom: 40px;
            max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.8;
        }

        .btn-group {
            display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;
        }

        .btn {
            padding: 15px 40px; border-radius: 50px; font-size: 1.1rem; font-weight: bold;
            text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 10px;
            backdrop-filter: blur(10px);
        }

        .btn-primary {
            background: var(--primary); color: white;
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .btn-primary:hover {
            transform: scale(1.05); box-shadow: 0 0 40px var(--primary);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1); color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2); border-color: white;
        }

        .features {
            margin-top: 60px; display: flex; gap: 40px; justify-content: center;
            opacity: 0.7;
        }
        .feature-item { display: flex; flex-direction: column; align-items: center; gap: 10px; font-size: 0.9rem; }

        @media (max-width: 600px) {
            h1 { font-size: 2.5rem; }
            .btn-group { flex-direction: column; width: 100%; max-width: 300px; margin: 0 auto; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <video class="video-bg" autoplay muted loop playsinline>
        <source src="./video/popo.mp4" type="video/mp4">
    </video>
    <div class="grid-overlay"></div>

    <div class="hero-content">
        <div class="logo-container">
            <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" alt="NexAI" class="logo-img">
        </div>
        
        <h1>NexAI</h1>
        <p class="tagline">
            دستیار هوشمند شما برای کدنویسی، خلاقیت و حل مسائل پیچیده.<br>
            قدرت گرفته از جدیدترین مدل‌های زبانی جهان.
        </p>

        <div class="btn-group">
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="btn btn-primary">
                    <i data-lucide="layout-dashboard"></i> ورود به داشبورد
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">
                    <i data-lucide="log-in"></i> ورود به حساب
                </a>
                <a href="register.php" class="btn btn-secondary">
                    <i data-lucide="user-plus"></i> ثبت نام رایگان
                </a>
            <?php endif; ?>
        </div>

        <div class="features">
            <div class="feature-item">
                <i data-lucide="zap" color="#eab308"></i>
                <span>سرعت بالا</span>
            </div>
            <div class="feature-item">
                <i data-lucide="shield-check" color="#22c55e"></i>
                <span>امنیت کامل</span>
            </div>
            <div class="feature-item">
                <i data-lucide="brain" color="#a855f7"></i>
                <span>هوش پیشرفته</span>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>