<?php
// chat.php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Update with XAMPP default
$password = ""; // Update with XAMPP default
$dbname = "pleaseloveme"; // The user's actual database name

// Suppress mysqli warnings if DB not fully set up yet for clean UI
error_reporting(E_ERROR | E_PARSE);
$conn = new mysqli($servername, $username, $password, $dbname);

$db_connected = true;
if ($conn->connect_error) {
    $db_connected = false;
}

// Fetch chat messages
function fetchMessages($conn) {
    if (!$conn) return [];
    
    // Create table if not exists with a unique name to avoid legacy schema conflicts
    $conn->query("CREATE TABLE IF NOT EXISTS community_messages (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50), message TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    
    $sql = "SELECT * FROM community_messages ORDER BY created_at ASC LIMIT 50";
    $result = $conn->query($sql);
    $messages = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    return $messages;
}

// Add new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db_connected) {
    $message = $_POST['message'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';
    $stmt = $conn->prepare("INSERT INTO community_messages (username, message) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $username, $message);
        $stmt->execute();
        $stmt->close();
    }
    
    // If it's an AJAX request just for posting, clean exit
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        exit;
    }
}

// If AJAX request to load messages
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $msgs = fetchMessages($conn);
    $current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';
    foreach ($msgs as $msg) {
        $is_me = ($msg['username'] === $current_user);
        $align = $is_me ? 'flex-end' : 'flex-start';
        $bg = $is_me ? 'linear-gradient(135deg, var(--primary), var(--secondary))' : 'rgba(255, 255, 255, 0.05)';
        $radius = $is_me ? '16px 16px 0 16px' : '16px 16px 16px 0';
        
        echo "<div style='display: flex; flex-direction: column; align-items: {$align}; margin-bottom: 1rem;'>";
        if (!$is_me) {
            echo "<span style='font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;'>" . htmlspecialchars($msg['username']) . "</span>";
        }
        echo "<div style='background: {$bg}; padding: 12px 18px; border-radius: {$radius}; max-width: 80%; border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.95rem; box-shadow: var(--shadow-sm);'>";
        echo htmlspecialchars($msg['message']);
        echo "</div></div>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Community</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 12rem);
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
        }
        .chat-messages {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .chat-input-area {
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.3);
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
        }
        .chat-input {
            flex: 1;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 12px 20px;
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }
        .chat-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .chat-send-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 1.25rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }
    </style>
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class='bx bx-hive'></i> OBMEN<span>2.0</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li class="nav-item"><a href="resources.php"><i class='bx bx-library'></i> Resources</a></li>
            <li class="nav-item"><a href="upload.php"><i class='bx bx-upload'></i> Publish</a></li>
            <li class="nav-item active"><a href="chat.php"><i class='bx bx-message-square-dots'></i> Community</a></li>
        </ul>
        
        <a href="profile.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:12px; margin-top: auto; padding: 12px; background-color: rgba(99, 102, 241, 0.2); border-radius: var(--border-radius); border: 1px solid var(--primary); cursor:pointer;" class="user-profile-btn">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?></div>
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                <div class="status" style="color: var(--primary);">View Profile</div>
            </div>
        </a>
        <div style="font-size: 0.7rem; color: var(--text-muted); text-align: center; margin-top: 15px;">
            Made By Z9
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title">Community Chat</h1>
            <div class="header-actions" style="display: flex; align-items: center;">
                <span style="color: var(--text-muted); font-size: 0.9rem; margin-right: 20px;">
                    <?php if(!$db_connected) echo "<span style='color:var(--danger)'><i class='bx bx-error'></i> Database Error</span>"; else echo "<i class='bx bx-wifi'></i> Live"; ?>
                </span>
                <a href="profile.php" class="btn btn-outline" style="border: 1px solid var(--primary); color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 6px 16px; border-radius: 20px; margin-right: 15px;">
                    <i class='bx bx-user-circle' style="font-size: 1.2rem; color: var(--primary);"></i> My Profile
                </a>
                <a href="logout.php" class="logout-btn" style="text-decoration: none;">LOGOUT</a>
            </div>
        </header>

        <div class="card animate-fade-in" style="padding: 0; background: transparent; border: none; box-shadow: none;">
            <div class="chat-container">
                <div class="chat-messages" id="messages">
                    <!-- Messages loaded via AJAX -->
                    <div style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Loading messages...</div>
                </div>
                
                <form id="chat-form" class="chat-input-area">
                    <input type="text" id="message" name="message" class="chat-input" placeholder="Type a message to the community..." required <?php echo (!$db_connected) ? 'disabled' : ''; ?>>
                    <button type="submit" class="chat-send-btn" <?php echo (!$db_connected) ? 'disabled' : ''; ?>>
                        <i class='bx bx-send'></i>
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    $(document).ready(function() {
        loadMessages();
        
        // Auto refresh every 3 seconds
        setInterval(loadMessages, 3000);
        
        $('#chat-form').submit(function(e) {
            e.preventDefault();
            var msg = $('#message').val();
            if(!msg.trim()) return;
            
            $.post('chat.php', $(this).serialize(), function() {
                $('#message').val('');
                loadMessages();
            });
        });
    });
    
    function loadMessages() {
        $.get('chat.php?ajax=1', function(data) {
            // Only update and scroll if new data exists
            var $messages = $('#messages');
            var isAtBottom = $messages[0].scrollHeight - $messages.scrollTop() <= $messages.outerHeight() + 50;
            
            $messages.html(data);
            
            if(isAtBottom) {
                $messages.scrollTop($messages[0].scrollHeight);
            }
        });
    }
</script>

</body>
</html>

