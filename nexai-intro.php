<?php
// nexai-intro.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرفی NexAI</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css');

        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.6);
            --glass-bg: rgba(15, 15, 30, 0.85);
            --glass-border: rgba(139, 92, 246, 0.3);
            --text: #e0e7ff;
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
            overflow-x: hidden;
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

        .intro-container {
            width: 100%; max-width: 700px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 500px; /* حداقل ارتفاع برای جلوگیری از پرش */
        }

        .logo {
            width: 90px; height: 90px; border-radius: 50%;
            border: 3px solid var(--primary);
            box-shadow: 0 0 30px var(--primary-glow);
            margin: 0 auto 20px;
            animation: pulse 3s infinite alternate;
        }
        @keyframes pulse { from { transform: scale(1); box-shadow: 0 0 20px var(--primary-glow); } to { transform: scale(1.05); box-shadow: 0 0 40px var(--primary); } }

        h1 {
            font-size: 2.2rem; margin-bottom: 20px;
            background: linear-gradient(to right, #fff, #a78bfa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .content-wrapper {
            flex: 1; /* پر کردن فضای خالی */
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            margin-bottom: 30px;
        }

        .slide {
            display: none; opacity: 0; width: 100%;
            flex-direction: column; align-items: center;
            transition: opacity 0.4s ease;
        }
        .slide.active { display: flex; opacity: 1; animation: fadeIn 0.6s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .slide-icon {
            color: var(--primary); margin-bottom: 20px;
            background: rgba(139, 92, 246, 0.1); padding: 20px; border-radius: 50%;
        }

        .text-box {
            font-size: 1.1rem; line-height: 1.8; color: #cbd5e1;
            background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px;
            width: 100%; border: 1px solid rgba(255,255,255,0.05);
        }
        .text-box strong { color: white; color: var(--primary); }

        .nav-buttons {
            display: flex; justify-content: space-between; gap: 15px;
            margin-top: auto; /* چسبیدن به پایین */
            padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);
        }

        .btn {
            padding: 12px 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05); color: #e0e7ff; cursor: pointer;
            font-family: inherit; font-size: 1rem; transition: 0.3s;
            display: flex; align-items: center; gap: 8px; user-select: none;
        }
        .btn:hover:not(:disabled) { background: var(--primary); color: white; border-color: var(--primary); }
        .btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .start-btn {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white; border: none; padding: 15px 30px; border-radius: 50px;
            font-weight: bold; font-size: 1.1rem; cursor: pointer; margin-top: 20px;
            box-shadow: 0 5px 20px var(--primary-glow); transition: 0.3s;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .start-btn:hover { transform: scale(1.05); box-shadow: 0 10px 30px var(--primary-glow); }

        /* Indicators */
        .dots { display: flex; justify-content: center; gap: 8px; margin-bottom: 20px; }
        .dot { width: 10px; height: 10px; background: rgba(255,255,255,0.2); border-radius: 50%; transition: 0.3s; }
        .dot.active { background: var(--primary); width: 30px; border-radius: 10px; }

        @media (max-width: 480px) {
            .intro-container { padding: 25px; min-height: 550px; }
            h1 { font-size: 1.8rem; }
            .text-box { font-size: 0.95rem; }
            .nav-buttons { flex-direction: row; }
            .btn { flex: 1; justify-content: center; font-size: 0.9rem; }
        }
    </style>
</head>
<body>

    <video class="video-bg" autoplay muted loop playsinline>
        <source src="https://assets.mixkit.co/videos/preview/mixkit-stars-in-space-1610-large.mp4" type="video/mp4">
    </video>
    <div class="grid-overlay"></div>

    <div class="intro-container">
        <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" alt="NexAI" class="logo">
        <h1>معرفی NexAI</h1>

        <div class="dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>

        <div class="content-wrapper">
            <!-- Slide 1 -->
            <div class="slide active">
                <div class="slide-icon"><i data-lucide="bot" size="48"></i></div>
                <div class="text-box">
                    <p>به دنیای <strong>NexAI</strong> خوش آمدید.</p>
                    <p>این یک پلتفرم هوشمند است که برای درک زبان طبیعی، تولید کد و حل مسائل پیچیده طراحی شده است. ما اینجا هستیم تا فاصله بین ایده و واقعیت را کم کنیم.</p>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="slide">
                <div class="slide-icon"><i data-lucide="shield-check" size="48"></i></div>
                <div class="text-box">
                    <p>امنیت و دقت در اولویت ماست.</p>
                    <p>تمام گفتگوهای شما به صورت امن ذخیره می‌شوند و پاسخ‌های هوش مصنوعی با دقت بالا تولید می‌شوند. طراحی کاربری ساده باعث می‌شود تمرکز شما فقط روی محتوا باشد.</p>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="slide">
                <div class="slide-icon"><i data-lucide="zap" size="48"></i></div>
                <div class="text-box">
                    <p>آماده شروع هستید؟</p>
                    <p>همین حالا اولین گفتگوی خود را آغاز کنید و قدرت هوش مصنوعی نسل جدید را تجربه کنید.</p>
                    <button class="start-btn" onclick="location.href='nexai-image.php'">
                        ورود به پنل چت <i data-lucide="arrow-left"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="nav-buttons">
            <button class="btn" id="prevBtn" onclick="changeSlide(-1)" disabled>
                <i data-lucide="chevron-right"></i> قبلی
            </button>
            <button class="btn" id="nextBtn" onclick="changeSlide(1)">
                بعدی <i data-lucide="chevron-left"></i>
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        function changeSlide(direction) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');

            currentSlide += direction;

            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');

            prevBtn.disabled = (currentSlide === 0);
            
            // اگر اسلاید آخر بودیم، دکمه بعدی مخفی شود
            if (currentSlide === slides.length - 1) {
                nextBtn.style.visibility = 'hidden'; 
            } else {
                nextBtn.style.visibility = 'visible';
                nextBtn.disabled = false;
            }
        }
    </script>
</body>
</html>