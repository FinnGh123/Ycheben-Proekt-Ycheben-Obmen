<?php
session_start();
$message = '';
$msg_type = '';

// Check if a file is uploaded
if (isset($_FILES['file'])) {
    $uploadDirectory = 'uploads/';
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }
    
    $fileName = $_FILES['file']['name'];
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];

    if ($fileError === 0) {
        $destination = $uploadDirectory . $fileName;
        if (move_uploaded_file($fileTmpPath, $destination)) {
            $message = 'File uploaded successfully: ' . htmlspecialchars($fileName);
            $msg_type = 'success';
            
            // Database integration: save metadata
            $servername = "localhost";
            $db_user = "root";
            $db_pass = "";
            $dbname = "pleaseloveme";
            $conn = new mysqli($servername, $db_user, $db_pass, $dbname);
            
            if (!$conn->connect_error) {
                // Ensure file_name column exists
                $col_check = $conn->query("SHOW COLUMNS FROM resources LIKE 'file_name'");
                if ($col_check && $col_check->num_rows == 0) {
                    $conn->query("ALTER TABLE resources ADD COLUMN file_name VARCHAR(255)");
                }
                
                $title = isset($_POST['title']) ? $_POST['title'] : $fileName;
                $desc = isset($_POST['description']) ? $_POST['description'] : '';
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
                
                $stmt = $conn->prepare("INSERT INTO resources (resource_name, resource_description, created_by, file_name) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssis", $title, $desc, $user_id, $fileName);
                    $stmt->execute();
                    $stmt->close();
                }
                $conn->close();
            }
        } else {
            $message = 'Error moving the uploaded file.';
            $msg_type = 'error';
        }
    } else {
        $message = 'Error uploading the file. Error code: ' . $fileError;
        $msg_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Publish Resource</title>
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
            <li class="nav-item"><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li class="nav-item"><a href="resources.php"><i class='bx bx-library'></i> Resources</a></li>
            <li class="nav-item active"><a href="upload.php"><i class='bx bx-upload'></i> Publish</a></li>
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
            <h1 class="page-title">Upload</h1>
            <div class="header-actions">
                <span style="color: var(--text-muted); margin-right: 20px; font-size: 0.9rem;"><?php echo date('l, F j'); ?></span>
                <a href="profile.php" class="btn btn-outline" style="border: 1px solid var(--primary); color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 6px 16px; border-radius: 20px; margin-right: 15px;">
                    <i class='bx bx-user-circle' style="font-size: 1.2rem; color: var(--primary);"></i> My Profile
                </a>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </header>

        <?php if($message): ?>
            <div class="notification <?php echo $msg_type; ?> animate-fade-in">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card animate-fade-in">
            <div class="card-header">
                <h2 class="card-title">Publish a Resource</h2>
                <p class="card-subtitle">Contribute to the collective knowledge shared by the community.</p>
            </div>

            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">What are you sharing? *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. bloodstrike offsets" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tell us more about it</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="yes sir"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">The Document / File *</label>
                    <div class="file-drop-zone" onclick="document.getElementById('file-upload').click()">
                        <i class='bx bx-cloud-upload'></i>
                        <p>Click to browse or drag and drop your file here</p>
                        <p class="helper-text">Max size 25MB (PDF, DOCX, ZIP, JPG)</p>
                        <input type="file" id="file-upload" name="file" required onclick="event.stopPropagation()">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Publish Resource <i class='bx bx-send'></i></button>
                </div>
            </form>
        </div>
        
        <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 2rem;">
            &copy; <?php echo date('Y'); ?> Obmen 2.0 - Built for Excellence
        </div>
    </main>
</div>

<script>
    // Simple script to show selected file name in drop zone
    document.getElementById('file-upload').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const dropZone = e.target.closest('.file-drop-zone');
            dropZone.querySelector('p:not(.helper-text)').innerHTML = `<strong>Selected:</strong> ${fileName}`;
        }
    });
</script>

</body>
</html>