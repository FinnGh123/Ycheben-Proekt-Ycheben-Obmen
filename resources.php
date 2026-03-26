<?php
// Scan uploads directory for files to display as resources
$uploadDirectory = 'uploads/';
$resources = [];

if (is_dir($uploadDirectory)) {
    $files = scandir($uploadDirectory);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            // Determine type by extension
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $icon = 'bx-file';
            $type = 'Document';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $icon = 'bx-image'; $type = 'Image'; }
            if (in_array($ext, ['pdf'])) { $icon = 'bxs-file-pdf'; $type = 'PDF'; }
            if (in_array($ext, ['zip', 'rar'])) { $icon = 'bx-archive'; $type = 'Archive'; }
            if (in_array($ext, ['mp4', 'mkv'])) { $icon = 'bx-video'; $type = 'Video'; }
            
            $resources[] = [
                'title' => $file,
                'type' => $type,
                'link' => $uploadDirectory . $file,
                'icon' => $icon
            ];
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
        .resource-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
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
                <a href="upload.php" class="btn btn-primary">Publish Resource <i class='bx bx-plus'></i></a>
            </div>
        </header>

        <div class="card animate-fade-in">
            <div class="card-header">
                <h2 class="card-title">Educational Resources</h2>
                <p class="card-subtitle">Browse study materials shared by the community.</p>
            </div>

            <div class="resource-grid">
                <?php foreach ($resources as $resource): ?>
                    <div class="resource-card">
                        <i class='bx <?php echo isset($resource['icon']) ? $resource['icon'] : 'bx-file'; ?> resource-icon'></i>
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-main); font-family: 'Outfit', sans-serif;">
                            <?php echo htmlspecialchars($resource['title']); ?>
                        </h3>
                        <span style="font-size: 0.85rem; color: var(--primary); font-weight: 500; margin-bottom: 1.5rem; display: inline-block; background: rgba(99, 102, 241, 0.1); padding: 4px 10px; border-radius: 12px;">
                            <?php echo htmlspecialchars($resource['type']); ?>
                        </span>
                        
                        <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <a href="<?php echo htmlspecialchars($resource['link']); ?>" class="btn btn-outline" style="width: 100%;" target="_blank">
                                Access Resource <i class='bx bx-link-external'></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 2rem;">
            &copy; <?php echo date('Y'); ?> Obmen 2.0 - Built for Excellence
        </div>
    </main>
</div>

</body>
</html>
