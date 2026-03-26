<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Using XAMPP default root user
$password = ""; // Using XAMPP default empty password
$dbname = "pleaseloveme"; // The user's actual database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$success_msg = '';
$error_msg = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Securely hash the password according to the schema password_hash column
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user's details using the correct columns -> username, email, password_hash
    $sql = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password_hash')";
    
    if ($conn->query($sql) === TRUE) {
        $success_msg = "Account created successfully! You can now log in.";
    } else {
        $error_msg = "Registration failed: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obmen 2.0 - Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.15), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(236, 72, 153, 0.1), transparent 40%);
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            margin: 2rem 0;
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
        
        <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem; text-align: center;">Create an Account</h2>
        <p style="color: var(--text-muted); text-align: center; margin-bottom: 2rem; font-size: 0.9rem;">Join the educational resources platform</p>
        
        <?php if ($error_msg): ?>
            <div class="notification error" style="padding: 10px; font-size: 0.85rem; text-align: center; margin-bottom: 1.5rem;">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_msg): ?>
            <div class="notification success" style="padding: 10px; font-size: 0.85rem; text-align: center; margin-bottom: 1.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; border-radius: 8px;">
                <?= $success_msg ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name" class="form-label">Username</label>
                <div style="position: relative;">
                    <i class='bx bx-user' style="position: absolute; left: 12px; top: 14px; color: var(--text-muted);"></i>
                    <input type="text" id="name" name="name" class="form-control" style="padding-left: 35px;" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <i class='bx bx-envelope' style="position: absolute; left: 12px; top: 14px; color: var(--text-muted);"></i>
                    <input type="email" id="email" name="email" class="form-control" style="padding-left: 35px;" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <i class='bx bx-lock-alt' style="position: absolute; left: 12px; top: 14px; color: var(--text-muted);"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left: 35px;" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">Register Account</button>
            
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

