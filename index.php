<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Dashboard</title>
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class='bx bx-hive'></i> OBMEN<span>2.0</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item active"><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li class="nav-item"><a href="resources.php"><i class='bx bx-library'></i> Resources</a></li>
            <li class="nav-item"><a href="upload.php"><i class='bx bx-upload'></i> Publish</a></li>
            <li class="nav-item"><a href="chat.php"><i class='bx bx-message-square-dots'></i> Community</a></li>
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
            <h1 class="page-title">Dashboard</h1>
            <div class="header-actions">
                <span style="color: var(--text-muted); margin-right: 20px; font-size: 0.9rem;"><?php echo date('l, F j'); ?></span>
                <a href="profile.php" class="btn btn-outline" style="border: 1px solid var(--primary); color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 6px 16px; border-radius: 20px; margin-right: 15px;">
                    <i class='bx bx-user-circle' style="font-size: 1.2rem; color: var(--primary);"></i> My Profile
                </a>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </header>

        <div class="card animate-fade-in">
            <div class="card-header">
                <h2 class="card-title">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p class="card-subtitle">Here is an overview of the educational resources platform.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <i class='bx bx-library' style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;">Total Resources</h3>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">24</p>
                </div>
                
                <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <i class='bx bx-upload' style="font-size: 2rem; color: var(--secondary); margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;">Your Uploads</h3>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">5</p>
                </div>
                
                <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <i class='bx bx-star' style="font-size: 2rem; color: #fbbf24; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;">Avg Rating</h3>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">4.8</p>
                </div>
            </div>
            
            <div style="margin-top: 2.5rem;">
                <a href="upload.php" class="btn btn-primary" style="margin-right: 1rem;">Publish New <i class='bx bx-right-arrow-alt'></i></a>
                <a href="resources.php" class="btn btn-outline">Browse Library</a>
            </div>
        </div>

        <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 2rem;">
            &copy; <?php echo date('Y'); ?> Obmen 2.0 - Built for Excellence
        </div>
    </main>
</div>

</body>
</html>