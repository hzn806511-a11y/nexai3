<?php
// nexai-image.php
// نسخه تک‌مرحله‌ای (بدون Double-Check)
ob_start();
session_start();

// تنظیمات دیتابیس
$host = "sql105.infinityfree.com";
$username_db = "if0_39948816";
$password_db = "147280021HZK";
$dbname = "if0_39948816_blog";

// ==========================================
// بخش 1: مدیریت درخواست‌های AJAX (بک‌اند)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 0);
    
    $response = ['success' => false];

    if (!isset($_SESSION['user']['id'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $userId = $_SESSION['user']['id'];

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ساخت جداول (اگر نباشند)
        $conn->exec("CREATE TABLE IF NOT EXISTS `conversations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `title` VARCHAR(255) DEFAULT 'گفتگوی جدید',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $conn->exec("CREATE TABLE IF NOT EXISTS `messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `conversation_id` INT NOT NULL,
            `role` ENUM('user', 'model') NOT NULL,
            `content` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $action = $_POST['action'];

        // 1. شروع چت جدید
        if ($action === 'start_conversation') {
            $stmt = $conn->prepare("INSERT INTO conversations (user_id, title) VALUES (:uid, 'گفتگوی جدید')");
            $stmt->execute([':uid' => $userId]);
            $response = ['success' => true, 'conversation_id' => $conn->lastInsertId()];
        }
        // 2. ذخیره پیام
        elseif ($action === 'save_message') {
            $convId = $_POST['conversation_id'] ?? null;
            $role = $_POST['role'] ?? 'user';
            $content = $_POST['content'] ?? '';
            if ($convId && $content) {
                $check = $conn->prepare("SELECT id FROM conversations WHERE id = :cid AND user_id = :uid");
                $check->execute([':cid' => $convId, ':uid' => $userId]);
                if($check->rowCount() > 0) {
                    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, role, content) VALUES (:cid, :role, :content)");
                    $stmt->execute([':cid' => $convId, ':role' => $role, ':content' => $content]);
                    $response = ['success' => true];
                }
            }
        }
        // 3. آپدیت عنوان
        elseif ($action === 'update_title') {
            $convId = $_POST['conversation_id'] ?? null;
            $newTitle = $_POST['title'] ?? '';
            if ($convId && $newTitle) {
                $stmt = $conn->prepare("UPDATE conversations SET title = :title WHERE id = :cid AND user_id = :uid");
                $stmt->execute([':title' => $newTitle, ':cid' => $convId, ':uid' => $userId]);
                $response = ['success' => true];
            }
        }
        // 4. دریافت لیست سایدبار
        elseif ($action === 'get_sidebar_list') {
            $stmt = $conn->prepare("SELECT id, title FROM conversations WHERE user_id = :uid ORDER BY created_at DESC");
            $stmt->execute([':uid' => $userId]);
            $response = ['success' => true, 'list' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }
        // 5. لود کردن چت
        elseif ($action === 'load_chat') {
            $convId = $_POST['conversation_id'] ?? null;
            if ($convId) {
                $check = $conn->prepare("SELECT id FROM conversations WHERE id = :cid AND user_id = :uid");
                $check->execute([':cid' => $convId, ':uid' => $userId]);
                if($check->rowCount() > 0) {
                    $stmt = $conn->prepare("SELECT role, content FROM messages WHERE conversation_id = :cid ORDER BY created_at ASC");
                    $stmt->execute([':cid' => $convId]);
                    $response = ['success' => true, 'messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
                }
            }
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    ob_clean();
    echo json_encode($response);
    exit();
}

// ==========================================
// بخش 2: رابط کاربری (Frontend)
// ==========================================
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NexAI Studio - Advanced</title>
    <link rel="icon" href="https://imagizer.imageshack.com/img923/3161/sxavmO.png" type="image/png">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        @import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css');

        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.5);
            --glass-bg: rgba(13, 13, 25, 0.85);
            --glass-border: rgba(139, 92, 246, 0.3);
            --text: #e0e7ff;
            --ai-msg-bg: rgba(35, 35, 50, 0.9);
            --user-msg-bg: linear-gradient(135deg, #7c3aed, #4f46e5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #000;
            color: var(--text);
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .video-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            object-fit: cover; filter: brightness(0.4) contrast(1.1);
        }
        .grid-overlay {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(rgba(139, 92, 246, 0.1) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(139, 92, 246, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle, black 30%, transparent 80%);
        }

        /* --- Layout Grid --- */
        .app-container {
            width: 95%; max-width: 1400px; height: 92vh;
            display: grid;
            grid-template-columns: 280px 1fr;
            grid-template-rows: 75px 1fr 85px;
            gap: 20px;
            perspective: 1000px;
        }

        .panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            opacity: 0;
        }

        /* --- Animations --- */
        .sidebar-anim { animation: slideInSidebar 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; transform: translateX(50px); }
        @keyframes slideInSidebar { to { transform: translateX(0); opacity: 1; } }

        .header-anim { animation: slideDown 0.8s 0.2s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; transform: translateY(-50px); }
        @keyframes slideDown { to { transform: translateY(0); opacity: 1; } }

        .chat-anim { animation: zoomIn 0.8s 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; transform: scale(0.9); }
        @keyframes zoomIn { to { transform: scale(1); opacity: 1; } }

        .input-anim { animation: slideUp 0.8s 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards, flashGlow 2s 1.5s infinite alternate; transform: translateY(50px); }
        @keyframes slideUp { to { transform: translateY(0); opacity: 1; } }
        
        @keyframes flashGlow {
            0% { box-shadow: 0 0 5px rgba(139, 92, 246, 0.1); border-color: rgba(139, 92, 246, 0.3); }
            100% { box-shadow: 0 0 25px rgba(139, 92, 246, 0.6); border-color: #c4b5fd; }
        }

        /* --- Sidebar Content --- */
        .panel-sidebar { grid-row: 1 / -1; display: flex; flex-direction: column; padding: 20px; z-index: 100; }
        
        .user-card {
            display: flex; align-items: center; gap: 15px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 15px;
        }
        .user-card img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover; }
        
        .new-chat-btn {
            background: linear-gradient(90deg, var(--primary), #6d28d9);
            color: white; border: none; padding: 12px; border-radius: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-weight: bold; margin-bottom: 20px; transition: 0.3s; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
        }
        .new-chat-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(139, 92, 246, 0.5); }

        .history-list { flex: 1; overflow-y: auto; padding-right: 5px; }
        .history-item {
            padding: 12px; border-radius: 12px; color: #cbd5e1; cursor: pointer; transition: 0.2s;
            margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            background: rgba(255,255,255,0.03); border: 1px solid transparent; font-size: 0.9rem;
        }
        .history-item:hover { background: rgba(139, 92, 246, 0.1); color: white; }
        .history-item.active { background: rgba(139, 92, 246, 0.2); border-color: var(--primary); color: white; }

        /* --- Header Content --- */
        .panel-header { grid-column: 2; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; }
        
        .logo-wrapper { display: flex; align-items: center; gap: 15px; }
        .app-logo { 
            width: 45px; height: 45px; border-radius: 50%; box-shadow: 0 0 20px var(--primary);
            opacity: 0; transform: scale(0) rotate(-180deg);
            animation: logoEntrance 1.2s 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes logoEntrance { to { opacity: 1; transform: scale(1) rotate(0deg); } }
        
        .status-badge { background: rgba(34, 197, 94, 0.1); color: #4ade80; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; border: 1px solid rgba(34, 197, 94, 0.2); }

        /* --- Chat Content --- */
        .panel-chat { grid-column: 2; display: flex; flex-direction: column; position: relative; }
        #chat-container { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px; scroll-behavior: smooth; }
        
        .message { max-width: 80%; padding: 15px 20px; border-radius: 20px; line-height: 1.8; position: relative; font-size: 1rem; }
        .message.user { align-self: flex-end; background: var(--user-msg-bg); color: white; border-bottom-right-radius: 4px; box-shadow: 0 5px 20px rgba(124, 58, 237, 0.4); animation: msgPopRight 0.4s ease; }
        .message.ai { align-self: flex-start; background: var(--ai-msg-bg); border-bottom-left-radius: 4px; border: 1px solid rgba(255,255,255,0.1); animation: msgPopLeft 0.4s ease; }
        
        @keyframes msgPopRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes msgPopLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        /* --- Input Content --- */
        .panel-input { grid-column: 2; padding: 15px; display: flex; align-items: center; }
        .input-box {
            width: 100%; height: 100%; background: rgba(0,0,0,0.3); border-radius: 18px;
            display: flex; align-items: center; padding: 0 15px; gap: 10px; border: 1px solid rgba(255,255,255,0.1);
        }
        #message-input { flex: 1; background: transparent; border: none; color: white; padding: 10px; font-family: inherit; font-size: 1rem; outline: none; resize: none; max-height: 100px; }
        
        .icon-btn { 
            width: 45px; height: 45px; border-radius: 12px; background: transparent; border: none; 
            color: #94a3b8; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; 
        }
        .icon-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        #send-btn { background: var(--primary); color: white; box-shadow: 0 0 15px var(--primary-glow); }
        #send-btn:hover { transform: scale(1.05); box-shadow: 0 0 25px var(--primary); }

        /* --- File Preview --- */
        .preview-area { display: none; width: 45px; height: 45px; position: relative; margin-left: 10px; }
        .preview-area img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; border: 1px solid var(--primary); }
        .remove-img { position: absolute; inset: 0; background: rgba(0,0,0,0.6); color: #ff5555; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; border-radius: 10px; transition: 0.2s; }
        .preview-area:hover .remove-img { opacity: 1; }

        /* --- Mobile Responsive --- */
        .mobile-menu { display: none; background: none; border: none; color: white; cursor: pointer; }
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 90; backdrop-filter: blur(5px); }

        @media (max-width: 768px) {
            .app-container { grid-template-columns: 1fr; grid-template-rows: 65px 1fr 75px; width: 100%; height: 100%; border-radius: 0; }
            .panel { border-radius: 0; border: none; border-bottom: 1px solid rgba(255,255,255,0.1); }
            
            .panel-sidebar {
                position: fixed; top: 0; right: 0; bottom: 0; width: 280px; background: #0b0b15;
                transform: translateX(100%); transition: transform 0.3s ease;
                box-shadow: -10px 0 40px rgba(0,0,0,0.8);
            }
            .panel-sidebar.open { transform: translateX(0); }
            .panel-header, .panel-chat, .panel-input { grid-column: 1; }
            .mobile-menu { display: block; }
            .overlay.active { display: block; }
        }

        /* --- Loading Line --- */
        .scan-line { position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, transparent, var(--primary), transparent); display: none; animation: scan 1.5s infinite; z-index: 10; }
        @keyframes scan { 0% { left: -100%; } 100% { left: 100%; } }
    </style>
</head>
<body>

    <video class="video-bg" autoplay muted loop playsinline>
        <source src="./video/popo.mp4" type="video/mp4">
    </video>
    <div class="grid-overlay"></div>
    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="app-container">
        
        <!-- Sidebar -->
        <div class="panel panel-sidebar sidebar-anim" id="sidebar">
            <div class="user-card">
                <img src="<?= isset($_SESSION['user']['profile_pic']) ? 'uploads/profiles/'.$_SESSION['user']['profile_pic'] : 'https://imagizer.imageshack.com/img923/3161/sxavmO.png' ?>">
                <div>
                    <h4 style="color: white;"><?= htmlspecialchars($_SESSION['user']['name']) ?></h4>
                    <span style="font-size: 0.8rem; color: #94a3b8;">کاربر حرفه‌ای</span>
                </div>
            </div>

            <button class="new-chat-btn" onclick="App.startNewChat()">
                <i data-lucide="plus-circle"></i> گفتگوی جدید
            </button>

            <div style="margin-bottom: 10px; font-size: 0.85rem; color: #64748b;">تاریخچه گفتگوها</div>
            <div class="history-list" id="history-container"></div>

            <div style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <button class="icon-btn" style="width: 100%; justify-content: flex-start; gap: 10px; color: #ef4444;" onclick="location.href='dashboard.php'">
                    <i data-lucide="layout-dashboard"></i> بازگشت به داشبورد
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="panel panel-header header-anim">
            <div class="logo-wrapper">
                <button class="mobile-menu" onclick="toggleSidebar()"><i data-lucide="menu"></i></button>
                <img src="https://imagizer.imageshack.com/img923/3161/sxavmO.png" class="app-logo">
                <span style="font-size: 1.4rem; font-weight: 800; background: linear-gradient(to right, white, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">NexAI</span>
            </div>
            <div class="status-badge">سیستم فعال</div>
        </div>

        <!-- Chat Area -->
        <div class="panel panel-chat chat-anim">
            <div class="scan-line" id="loader"></div>
            <div id="chat-container"></div>
        </div>

        <!-- Input Area -->
        <div class="panel panel-input input-anim" id="input-panel">
            <div class="input-box">
                <input type="file" id="file-input" hidden accept="image/*">
                <button class="icon-btn" onclick="document.getElementById('file-input').click()">
                    <i data-lucide="paperclip"></i>
                </button>
                
                <div class="preview-area" id="preview-area">
                    <img id="preview-img" src="">
                    <div class="remove-img" onclick="App.removeFile()"><i data-lucide="x"></i></div>
                </div>

                <textarea id="message-input" placeholder="اینجا بنویسید..." rows="1"></textarea>
                <button class="icon-btn" id="send-btn" onclick="App.handleSend()"><i data-lucide="send"></i></button>
            </div>
        </div>

    </div>

    <script>
        // ============================
        // !!! کلید API خود را دقیقاً اینجا قرار دهید !!!
        const API_KEY = 'AIzaSyDdd9zqKPjG_UtkJsg3fXp-M8bEeNyxCxk'; 
        // ============================

        const API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" + API_KEY;
        lucide.createIcons();

        // 10 ثانیه چشمک زدن ورودی
        setTimeout(() => {
            const inputPanel = document.getElementById('input-panel');
            inputPanel.style.animation = 'slideUp 0.8s 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards';
            inputPanel.style.borderColor = 'rgba(139, 92, 246, 0.3)';
            inputPanel.style.boxShadow = 'none';
        }, 10000);

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.querySelector('.overlay').classList.toggle('active');
        }

        const App = {
            currentChatId: null,
            attachedFile: null,

            init() {
                if(API_KEY === 'YOUR_GEMINI_API_KEY') {
                    alert('هشدار: کلید API در کد وارد نشده است.');
                }
                this.loadSidebar();
                
                document.getElementById('message-input').addEventListener('keydown', (e) => {
                    if(e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this.handleSend(); }
                });

                document.getElementById('file-input').addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if(file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.attachedFile = { mimeType: file.type, data: ev.target.result.split(',')[1] };
                            document.getElementById('preview-img').src = ev.target.result;
                            document.getElementById('preview-area').style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },

            removeFile() {
                this.attachedFile = null;
                document.getElementById('file-input').value = '';
                document.getElementById('preview-area').style.display = 'none';
            },

            async apiCall(formData) {
                try {
                    const res = await fetch('nexai-image.php', { method: 'POST', body: formData });
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        console.error("Invalid JSON:", text);
                        return { success: false, message: 'Invalid JSON response' };
                    }
                } catch(e) {
                    console.error("Network Error:", e);
                    return { success: false, message: 'Network Error' };
                }
            },

            async loadSidebar() {
                const formData = new FormData();
                formData.append('action', 'get_sidebar_list');
                const data = await this.apiCall(formData);
                
                const container = document.getElementById('history-container');
                container.innerHTML = '';
                
                if(data.success && data.list) {
                    data.list.forEach(chat => {
                        const div = document.createElement('div');
                        div.className = `history-item ${this.currentChatId == chat.id ? 'active' : ''}`;
                        div.innerHTML = `<i data-lucide="message-square" style="width:14px; display:inline-block; vertical-align:middle; margin-left:5px;"></i> ${chat.title}`;
                        div.onclick = () => this.loadChat(chat.id);
                        container.appendChild(div);
                    });
                    lucide.createIcons();
                }
            },

            async startNewChat() {
                this.currentChatId = null;
                document.getElementById('chat-container').innerHTML = '';
                this.appendMessage('ai', 'سلام! یک گفتگوی جدید آغاز شد. چطور می‌توانم کمک کنم؟', true);
                this.loadSidebar();
                if(window.innerWidth < 768) toggleSidebar();
            },

            async loadChat(id) {
                this.currentChatId = id;
                const formData = new FormData();
                formData.append('action', 'load_chat');
                formData.append('conversation_id', id);
                
                const data = await this.apiCall(formData);
                const container = document.getElementById('chat-container');
                container.innerHTML = '';
                
                if(data.success && data.messages) {
                    data.messages.forEach(msg => {
                        this.appendMessage(msg.role === 'user' ? 'user' : 'ai', msg.content, true);
                    });
                }
                this.loadSidebar();
                if(window.innerWidth < 768) toggleSidebar();
            },

            appendMessage(role, text, skipTyping = false) {
                const container = document.getElementById('chat-container');
                const div = document.createElement('div');
                div.className = `message ${role}`;
                container.appendChild(div);
                
                if(role === 'ai' && !skipTyping) {
                    div.innerHTML = '';
                    let i = 0;
                    const speed = 15; 
                    const words = text.split(' ');
                    
                    function type() {
                        if(i < words.length) {
                            div.innerHTML = marked.parse(words.slice(0, i+1).join(' '));
                            container.scrollTop = container.scrollHeight;
                            i++;
                            setTimeout(type, speed);
                        } else {
                            div.innerHTML = marked.parse(text);
                            lucide.createIcons();
                        }
                    }
                    type();
                } else {
                    div.innerHTML = marked.parse(text);
                    container.scrollTop = container.scrollHeight;
                    lucide.createIcons();
                }
            },

            async handleSend() {
                const input = document.getElementById('message-input');
                const text = input.value.trim();
                
                if(!text && !this.attachedFile) return;
                
                // 1. نمایش پیام کاربر
                this.appendMessage('user', text.replace(/\n/g, '<br>'));
                input.value = '';
                
                // 2. ساخت چت جدید در صورت نیاز
                const isNewConversation = (this.currentChatId === null);
                if(isNewConversation) {
                    const formData = new FormData();
                    formData.append('action', 'start_conversation');
                    const data = await this.apiCall(formData);
                    if(data.success) {
                        this.currentChatId = data.conversation_id;
                    } else {
                        alert('خطا در دیتابیس (ساخت چت)');
                        return;
                    }
                }

                // 3. ذخیره پیام کاربر
                const saveForm = new FormData();
                saveForm.append('action', 'save_message');
                saveForm.append('conversation_id', this.currentChatId);
                saveForm.append('role', 'user');
                saveForm.append('content', text);
                this.apiCall(saveForm);

                // 4. ارسال به هوش مصنوعی (Single Request)
                const historyParts = [{ text: text }]; 
                if(this.attachedFile) {
                    historyParts.push({ inline_data: this.attachedFile });
                    this.removeFile();
                }

                document.getElementById('loader').style.display = 'block';

                try {
                    // فقط یک درخواست اصلی ارسال می‌شود
                    const res = await fetch(API_URL, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ contents: [{ role: 'user', parts: historyParts }] })
                    });
                    
                    if(!res.ok) throw new Error("API Error: " + res.status);
                    
                    const data = await res.json();
                    if(!data.candidates) throw new Error("No response from AI");
                    
                    const finalResponse = data.candidates[0].content.parts[0].text;

                    // نمایش و ذخیره پاسخ
                    this.appendMessage('ai', finalResponse);
                    
                    const saveAi = new FormData();
                    saveAi.append('action', 'save_message');
                    saveAi.append('conversation_id', this.currentChatId);
                    saveAi.append('role', 'model');
                    saveAi.append('content', finalResponse);
                    await this.apiCall(saveAi);

                    // 5. اگر چت جدید بود، عنوان بساز
                    if(isNewConversation) {
                        const titlePrompt = `Generate a very short Persian title (max 5 words) for this chat based on user input: "${text}". Do not use quotes.`;
                        const resTitle = await fetch(API_URL, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ contents: [{ parts: [{ text: titlePrompt }] }] })
                        });
                        const dataTitle = await resTitle.json();
                        const newTitle = dataTitle.candidates[0].content.parts[0].text;

                        const titleForm = new FormData();
                        titleForm.append('action', 'update_title');
                        titleForm.append('conversation_id', this.currentChatId);
                        titleForm.append('title', newTitle);
                        await this.apiCall(titleForm);
                        
                        this.loadSidebar();
                    }

                } catch (error) {
                    this.appendMessage('ai', 'خطا در برقراری ارتباط با هوش مصنوعی. (لطفا VPN را چک کنید)');
                    console.error("AI Error:", error);
                } finally {
                    document.getElementById('loader').style.display = 'none';
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            App.init();
            App.appendMessage('ai', 'سلام! سیستم آماده است. چطور می‌توانم کمک کنم؟', true);
        });
    </script>
</body>
</html>