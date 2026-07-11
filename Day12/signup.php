<?php
session_start();
require_once 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username is already taken.";
                $stmt->close();
            } else {
                $stmt->close();
                
                // Insert new user (Note: Plain text for your current testing layout)
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $insert_stmt->bind_param("ss", $username, $password);
                
                if ($insert_stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
                $insert_stmt->close();
            }
        }
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
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .signup-container {
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

<div class="signup-container">
    <form action="signup.php" method="POST">
        <h2 class="text-center fw-bold mb-1">Create Account</h2>
        <p class="text-muted text-center small mb-4">Join us to access your dashboard</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small" role="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold">Username</label>
            <input type="text" id="username" name="username" class="form-control" required placeholder="Choose a username">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Create a password">
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label small fw-semibold">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Confirm your password">
        </div>

        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold mb-3">Register</button>

        <p class="text-center text-muted small mb-0">Already have an account? <a href="index.php" class="text-decoration-none">Log In</a></p>
    </form>
</div>

</body>
</html>