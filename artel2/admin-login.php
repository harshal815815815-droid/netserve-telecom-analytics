<?php
session_start();

require_once 'config.php';
include 'db.php';

if (isset($_POST['login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                // Security: regenerate session ID on login (prevents session fixation)
                session_regenerate_id(true);
                $_SESSION['admin']    = true;
                $_SESSION['admin_id'] = $admin['id'] ?? 1;
                header("Location: view-booking.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No admin account found with that username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a0000 0%, #5c0000 50%, #e60000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(12px);
            border-radius: 22px;
            padding: 44px 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-logo {
            width: 62px; height: 62px;
            background: linear-gradient(135deg, #e60000, #8b0000);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 26px;
            box-shadow: 0 6px 18px rgba(230,0,0,.4);
        }
        .login-title {
            font-size: 1.7rem;
            font-weight: 700;
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .login-sub {
            text-align: center;
            color: #888;
            font-size: .88rem;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            font-size: .875rem;
            color: #444;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1.5px solid #e0e0e0;
            font-size: .92rem;
            transition: all .25s;
        }
        .form-control:focus {
            border-color: #e60000;
            box-shadow: 0 0 0 3px rgba(230,0,0,.12);
        }
        .input-group-text {
            background: #f8f8f8;
            border-radius: 10px 0 0 10px;
            border: 1.5px solid #e0e0e0;
            border-right: none;
            color: #bbb;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
            border-left: none;
        }
        .input-group:focus-within .input-group-text {
            border-color: #e60000;
        }
        .btn-login {
            background: linear-gradient(135deg, #e60000, #cc0000);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            width: 100%;
            transition: all .25s;
            box-shadow: 0 4px 15px rgba(230,0,0,.35);
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(230,0,0,.45);
            color: white;
        }
        .alert-error {
            background: #fff0f0;
            border: 1px solid #ffd0d0;
            border-radius: 10px;
            color: #c0392b;
            padding: 12px 16px;
            font-size: .88rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            text-align: center;
            margin-top: 18px;
        }
        .back-link a {
            color: rgba(255,255,255,.7);
            font-size: .85rem;
            text-decoration: none;
            transition: color .2s;
        }
        .back-link a:hover { color: white; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo"><i class="bi bi-broadcast"></i></div>
        <h2 class="login-title">Admin Login</h2>
        <p class="login-sub">NetServe Admin Panel</p>

        <?php if (isset($error)): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" class="form-control"
                           placeholder="Enter username" required autocomplete="username">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="pwdField" class="form-control"
                           placeholder="Enter password" required autocomplete="current-password">
                </div>
            </div>
            <button type="submit" name="login" class="btn-login">
                <i class="bi bi-shield-lock-fill me-2"></i>Sign In
            </button>
        </form>
    </div>
    <div class="back-link">
        <a href="index.php"><i class="bi bi-arrow-left me-1"></i>Back to NetServe Home</a>
    </div>
</div>
</body>
</html>
