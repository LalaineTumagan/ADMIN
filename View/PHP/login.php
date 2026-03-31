<?php
session_start();
// If already logged in, stay in the same folder
if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | IMPERIAL HOUSE</title>
    <link rel="stylesheet" href="../assets/login_style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="login-container">
            <div class="login-panel">
                <div class="accent-bar"></div>
                <div class="content-top">
                    <div class="logo-section">
                        <img src="../assets/img/logo/imperialhouse_logo.png" alt="Logo" class="logo-img">
                    </div>

                    <?php if(isset($_GET['error'])): ?>
                        <div style="background: rgba(255, 0, 0, 0.1); border-left: 4px solid #ff4d4d; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="color: #ff4d4d; font-size: 13px; font-weight: 600; margin: 0;">
                                ACCESS DENIED: Invalid credentials.
                            </p>
                        </div>
                    <?php endif; ?>

                    <h1>LOGIN</h1>
                    <p class="welcome-text">WELCOME BACK!</p>

                    <form action="login_process.php" method="POST" class="login-form">
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <button type="submit" class="login-btn">Login</button>
                    </form>
                </div>
            </div>

            <div class="illustration-panel">
                <div class="vertical-accent"></div>
                <img src="../assets/img/logo/imperialhouse_banner.png" alt="Banner" class="illustration-img">
            </div>
        </div>
    </div>

    <script src="../javascript/logIn.js"></script>
</body>
</html>