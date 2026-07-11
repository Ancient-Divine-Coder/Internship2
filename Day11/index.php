<?php
session_start();
require_once 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Secure SQL statement to prevent SQL Injection
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For production, use: if (password_verify($password, $user['password']))
            if ($password === $user['password']) { 
                // Session Created
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to Protected Dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="login-container">
    <form action="index.php" method="POST" class="login-form">
        <h2 class="text-center fw-bold mb-1">Welcome Back</h2>
        <p class="text-muted text-center small mb-4">Enter your credentials to access your account</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold">Username</label>
            <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 small">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
            </div>
            <a href="#" class="text-decoration-none">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3" style="background-color: #0d6efd;">Log In</button>

<p class="text-center text-muted small mb-0">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>    </form>
</div>

</body>
</html>