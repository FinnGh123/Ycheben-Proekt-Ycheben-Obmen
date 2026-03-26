<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$db_user = "root";
$db_pass = "";
$dbname = "pleaseloveme";

error_reporting(E_ERROR | E_PARSE);
$conn = new mysqli($servername, $db_user, $db_pass, $dbname);

$user_info = null;
$my_resources = [];
$msg = '';
$msg_type = 'success';

if (!$conn->connect_error) {
    // Force find the true user ID to prevent old session bugs
    $uid = 0;
    $u_lookup = $conn->query("SELECT user_id FROM users WHERE username = '" . $conn->real_escape_string($_SESSION['username']) . "'");
    if ($u_lookup && $u_lookup->num_rows > 0) {
        $u_row = $u_lookup->fetch_assoc();
        $uid = intval($u_row['user_id']);
        $_SESSION['user_id'] = $uid; // Refresh session
    }

    // Check for deletion request
    if (isset($_GET['delete_id']) && $uid > 0) {
        $del_id = intval($_GET['delete_id']);
        
        // Ensure user actually owns this file before deletion
        $check = $conn->query("SELECT file_name FROM resources WHERE resource_id = $del_id AND created_by = $uid");
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $del_file = $row['file_name'];
            $filepath = __DIR__ . '/uploads/' . $del_file;
            if ($del_file && file_exists($filepath)) {
                @unlink($filepath); // Force absolute path removal from filesystem
            }
            
            // Delete from database
            if ($conn->query("DELETE FROM resources WHERE resource_id = $del_id AND created_by = $uid")) {
                $msg = "Resource successfully deleted.";
                $msg_type = "success";
            } else {
                $msg = "Error deleting resource from database.";
                $msg_type = "error";
            }
        } else {
            $msg = "Unauthorized or resource not found.";
            $msg_type = "error";
        }
    }

    // Get User Details
    $u_sql = "SELECT user_id, created_at FROM users WHERE username = '" . $conn->real_escape_string($_SESSION['username']) . "'";
    $u_res = $conn->query($u_sql);
    if ($u_res && $u_res->num_rows > 0) {
        $u_row = $u_res->fetch_assoc();
        $user_info = [
            'id' => $u_row['user_id'],
            'joined' => $u_row['created_at'] ? date('F j, Y', strtotime($u_row['created_at'])) : 'Recently'
        ];
    }
    
    // Get User's Uploaded Resources
    $r_sql = "SELECT * FROM resources WHERE created_by = " . intval($_SESSION['user_id']) . " ORDER BY created_at DESC";
    $r_res = $conn->query($r_sql);
    if ($r_res) {
        while ($row = $r_res->fetch_assoc()) {
            $my_resources[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - My Profile</title>
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, rgba(0,0,0,0.4), rgba(0,0,0,0.1));
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 3rem;
            display: flex;
            align-items: center;
            gap: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent 50%);
            z-index: 0;
            pointer-events: none;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: bold;
            color: white;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
            z-index: 1;
        }
        .profile-info {
            z-index: 1;
        }
        .profile-name {
            font-size: 2.5rem;
            font-family: 'Outfit', sans-serif;
            margin-bottom: 0.5rem;
        }
        .profile-stats {
            display: flex;
            gap: 2rem;
            color: var(--text-muted);
            font-size: 1rem;
        }
        .profile-stats span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .profile-stats i {
            color: var(--primary);
            font-size: 1.2rem;
        }
        .resource-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .resource-card {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        .resource-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .resource-icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
            font-size: 1.2rem;
            transition: var(--transition);
            cursor: pointer;
        }
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-2px);
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
            <li class="nav-item"><a href="chat.php"><i class='bx bx-message-square-dots'></i> Community</a></li>
        </ul>
        
        <a href="profile.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:12px; margin-top: auto; padding: 12px; background-color: rgba(99, 102, 241, 0.2); border-radius: var(--border-radius); border: 1px solid var(--primary); cursor:pointer;" class="user-profile-btn">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
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
            <h1 class="page-title">My Profile</h1>
            <div class="header-actions">
                <span style="color: var(--text-muted); margin-right: 20px; font-size: 0.9rem;"><?php echo date('l, F j'); ?></span>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </header>

        <?php if($msg): ?>
            <div class="notification <?php echo $msg_type; ?> animate-fade-in" style="margin-bottom: 2rem;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="profile-header animate-fade-in">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                <div class="profile-stats">
                    <span title="Your User ID"><i class='bx bx-id-card'></i> #<?php echo isset($user_info['id']) ? $user_info['id'] : $_SESSION['user_id']; ?></span>
                    <span title="Date Joined"><i class='bx bx-calendar'></i> <?php echo isset($user_info['joined']) ? $user_info['joined'] : 'Unknown'; ?></span>
                    <span title="Total Files Shared"><i class='bx bx-share-alt'></i> <?php echo count($my_resources); ?> Uploads</span>
                </div>
            </div>
        </div>

        <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem; font-family: 'Outfit', sans-serif;" class="animate-fade-in">My Shared Files</h3>

        <div class="resource-grid animate-fade-in">
            <?php foreach ($my_resources as $res): ?>
                <?php
                    // Determine icon
                    $file = strtolower($res['file_name']);
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $icon = 'bx-file';
                    $type = 'Document';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $icon = 'bx-image'; $type = 'Image'; }
                    if (in_array($ext, ['pdf'])) { $icon = 'bxs-file-pdf'; $type = 'PDF'; }
                    if (in_array($ext, ['zip', 'rar'])) { $icon = 'bx-archive'; $type = 'Archive'; }
                    if (in_array($ext, ['mp4', 'mkv'])) { $icon = 'bx-video'; $type = 'Video'; }
                ?>
                <div class="resource-card">
                    <i class='bx <?php echo $icon; ?> resource-icon'></i>
                    <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem; word-break: break-all;"><?php echo htmlspecialchars($res['resource_name']); ?></h4>
                    <span style="display: inline-block; padding: 4px 10px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; font-size: 0.75rem; font-weight: 500; width: fit-content; margin-bottom: 0.5rem;">
                        <?php echo $type; ?>
                    </span>
                    <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 1rem;">
                        <i class='bx bx-time' style="color:var(--primary); margin-right:4px;"></i> 
                        <?php echo date('M j, Y', strtotime($res['created_at'])); ?>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="uploads/<?php echo urlencode($res['file_name']); ?>" class="btn btn-outline" style="flex: 1; text-align: center; text-decoration: none;" download>
                            <i class='bx bx-download'></i> Download
                        </a>
                        <a href="profile.php?delete_id=<?php echo $res['resource_id']; ?>" class="btn-delete" title="Delete this file" onclick="return confirm('Are you sure you want to permanently delete this file?');">
                            <i class='bx bx-trash'></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($my_resources)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: rgba(0,0,0,0.2); border-radius: var(--border-radius); border: 1px dashed var(--border-color); color: var(--text-muted);">
                    <i class='bx bx-cloud-upload' style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>You haven't published any files yet. Share something with the community!</p>
                </div>
            <?php endif; ?>
        </div>
        
    </main>
</div>

</body>
</html>
