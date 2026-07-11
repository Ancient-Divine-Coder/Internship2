<?php
require_once __DIR__ . '/config.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['is_admin'] = true;
            header('Location: index.php');
            exit;
        }
        $error = 'Incorrect username or password.';
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login &middot; Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKVBQF4odlXh5iqT9vBj0v2R6p0hX+3PTx1bXzQe5KZz0eW3D1t54eXj9TRJ5S5nYbY8vVg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../style.css" />
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="text-primary fs-2 mb-2"><i class="fa-solid fa-lock"></i></div>
                    <h1 class="h4 mb-1">Admin Login</h1>
                    <p class="text-muted mb-0">Sign in to view student registrations.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= safe($error) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= safe($token) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign in
                    </button>
                </form>

                <p class="text-muted small text-center mt-4 mb-0">
                    Demo credentials: <code>admin</code> / <code>orientation2026</code>
                </p>
            </div>
        </div>
    </main>
</body>

</html>
