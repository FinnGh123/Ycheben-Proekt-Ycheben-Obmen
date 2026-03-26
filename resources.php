<?php
// Scan uploads directory for files to display as resources
$uploadDirectory = 'uploads/';
$resources_data = [];

$servername = "localhost";
$db_user = "root";
$db_pass = "";
$dbname = "pleaseloveme";

error_reporting(E_ERROR | E_PARSE);
$conn = new mysqli($servername, $db_user, $db_pass, $dbname);

if (!$conn->connect_error) {
    // 1. Ensure file_name column exists
    $result = $conn->query("SHOW COLUMNS FROM resources LIKE 'file_name'");
    if ($result && $result->num_rows == 0) {
        $conn->query("ALTER TABLE resources ADD COLUMN file_name VARCHAR(255)");
    }

    // 2. Sync physical files into the database if they are missing
    if (is_dir($uploadDirectory)) {
        $files = scandir($uploadDirectory);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $stmt = $conn->prepare("SELECT resource_id FROM resources WHERE file_name = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $file);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows == 0) {
                        // Insert orphan file
                        $mtime = date('Y-m-d H:i:s', filemtime($uploadDirectory . $file));
                        $title = htmlspecialchars($file);
                        $insert = $conn->prepare("INSERT INTO resources (resource_name, file_name, created_at) VALUES (?, ?, ?)");
                        if ($insert) {
                            $insert->bind_param("sss", $title, $file, $mtime);
                            $insert->execute();
                            $insert->close();
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
    
    // 3. Fetch all resources with uploader username
    $sql = "SELECT r.*, u.username FROM resources r LEFT JOIN users u ON r.created_by = u.user_id ORDER BY r.created_at DESC";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $resources_data[] = $row;
        }
    }
} else {
    // Fallback if DB fails
    if (is_dir($uploadDirectory)) {
        $files = scandir($uploadDirectory);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $resources_data[] = [
                    'resource_name' => $file,
                    'file_name' => $file,
                    'created_at' => date('Y-m-d H:i:s', filemtime($uploadDirectory . $file)),
                    'username' => null
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Resources</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .resource-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .search-bar {
            position: relative;
            flex: 1;
            max-width: 400px;
        }
        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.2rem;
        }
        .search-bar input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            color: var(--text-main);
            font-family: inherit;
            outline: none;
            transition: var(--transition);
        }
        .search-bar input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
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
        .resource-meta {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        .resource-meta i {
            margin-right: 5px;
            color: var(--primary);
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
            <li class="nav-item active"><a href="resources.php"><i class='bx bx-library'></i> Resources</a></li>
            <li class="nav-item"><a href="upload.php"><i class='bx bx-upload'></i> Publish</a></li>
            <li class="nav-item"><a href="chat.php"><i class='bx bx-message-square-dots'></i> Community</a></li>
        </ul>
        
        <a href="logout.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:12px; margin-top: auto; padding: 12px; background-color: rgba(0,0,0,0.2); border-radius: var(--border-radius); border: 1px solid var(--border-color); cursor:pointer;" class="user-profile-btn">
            <div class="avatar">Z</div>
            <div class="user-info">
                <div class="name">Z9</div>
                <div class="status">Click to Logout</div>
            </div>
        </a>
        <div style="font-size: 0.7rem; color: var(--text-muted); text-align: center; margin-top: 15px;">
            Made By Z9
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title">Library</h1>
            <div class="header-actions">
                <span style="color: var(--text-muted); margin-right: 20px; font-size: 0.9rem;"><?php echo date('l, F j'); ?></span>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </header>

        <div class="resource-header animate-fade-in">
            <p style="color: var(--text-muted); margin: 0;">Browse study materials shared by the community.</p>
            <div class="search-bar">
                <i class='bx bx-search'></i>
                <input type="text" id="resource-search" placeholder="Search by name, file, or uploader...">
            </div>
        </div>

        <div class="resource-grid animate-fade-in">
            <?php foreach ($resources_data as $res): ?>
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
                    
                    $uploader = $res['username'] ? htmlspecialchars($res['username']) : 'System';
                    $date = date('M j, Y, g:i a', strtotime($res['created_at']));
                ?>
                <div class="resource-card">
                    <i class='bx <?php echo $icon; ?> resource-icon'></i>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; word-break: break-all;" class="resource-title"><?php echo htmlspecialchars($res['resource_name']); ?></h3>
                    <span style="display: inline-block; padding: 4px 10px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 12px; font-size: 0.75rem; font-weight: 500; margin-bottom: 1rem; width: fit-content;">
                        <?php echo $type; ?>
                    </span>
                    
                    <div class="resource-meta">
                        <div><i class='bx bx-user'></i> <span class="search-author">Uploaded by: <?php echo $uploader; ?></span></div>
                        <div><i class='bx bx-time'></i> <?php echo $date; ?></div>
                    </div>
                    
                    <a href="uploads/<?php echo urlencode($res['file_name']); ?>" class="btn btn-outline" style="text-align: center; text-decoration: none;" download>
                        <i class='bx bx-download'></i> Download
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
    </main>
</div>

<script>
document.getElementById('resource-search').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.resource-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.resource-title').textContent.toLowerCase();
        const author = card.querySelector('.search-author').textContent.toLowerCase();
        
        if (title.includes(term) || author.includes(term)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
