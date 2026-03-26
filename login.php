<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$error = '';
// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple authentication (replace with database check in real application)
    if ($username === 'user' && $password === 'pass') {
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.15), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(236, 72, 153, 0.1), transparent 40%);
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 3rem;
        }
        .auth-logo {
            text-align: center;
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-main);
        }
        .auth-logo span { color: var(--primary); }
        .auth-logo i { color: var(--primary); margin-right: 8px; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="card auth-card animate-fade-in">
        <div class="auth-logo">
            <i class='bx bx-hive'></i>OBMEN<span>2.0</span>
        </div>
        
        <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem; text-align: center;">Welcome Back</h2>
        <p style="color: var(--text-muted); text-align: center; margin-bottom: 2rem; font-size: 0.9rem;">Sign in to access educational resources</p>
        
        <?php if ($error): ?>
            <div class="notification error" style="padding: 10px; font-size: 0.85rem; text-align: center;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div style="position: relative;">
                    <i class='bx bx-user' style="position: absolute; left: 12px; top: 14px; color: var(--text-muted);"></i>
                    <input type="text" id="username" name="username" class="form-control" style="padding-left: 35px;" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <i class='bx bx-lock-alt' style="position: absolute; left: 12px; top: 14px; color: var(--text-muted);"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left: 35px;" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">Login</button>
            
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Register</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>