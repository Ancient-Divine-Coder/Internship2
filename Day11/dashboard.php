<?php
session_start();

// Session Guard: check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card p-5 shadow-sm text-center">
        <h1 class="text-success fw-bold">Protected Dashboard</h1>
        <p class="lead text-muted mt-3">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        <p>Before today, your Student Management System was open to the world. Right now, it's protected.</p>
        <div class="mt-4">
            <a href="logout.php" class="btn btn-danger px-4">Logout</a>
        </div>
    </div>
</div>

</body>
</html>