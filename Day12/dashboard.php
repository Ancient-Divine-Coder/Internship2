<?php
session_start();
require_once 'db.php';

// 1. Session Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Handle Delete Operation
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Optional: Delete the image file from server first
    $img_stmt = $conn->prepare("SELECT image_path FROM students WHERE id = ?");
    $img_stmt->bind_param("i", $delete_id);
    $img_stmt->execute();
    $img_res = $img_stmt->get_result()->fetch_assoc();
    if ($img_res && !empty($img_res['image_path']) && file_exists($img_res['image_path'])) {
        unlink($img_res['image_path']);
    }
    $img_stmt->close();

    $del_stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $del_stmt->bind_param("i", $delete_id);
    $del_stmt->execute();
    $del_stmt->close();
    header("Location: dashboard.php");
    exit();
}

// 3. Handle Search and Fetch Main Student List
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $query = "SELECT * FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ? ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $students_result = $stmt->get_result();
} else {
    $students_result = $conn->query("SELECT * FROM students ORDER BY id DESC");
}

// 4. Bonus Widget Data: Fetch Recent Registered Users
$recent_users = $conn->query("SELECT username, id FROM users ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Bonus Point: Fade-in animation for cards */
        .fade-in-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .student-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-mortarboard-fill me-2"></i>SMS Admin</a>
    <div class="d-flex align-items-center">
        <span class="navbar-text text-white me-3">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Log out</a>
    </div>
</nav>

<div class="container-fluid my-4 px-4">
    <div class="row g-4">
        
        <div class="col-lg-3 col-md-4 fade-in-card">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-people-fill me-2"></i>Recent Registrations
                </div>
                <ul class="list-group list-group-flush">
                    <?php if($recent_users && $recent_users->num_rows > 0): ?>
                        <?php while($row = $recent_users->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center small">
                                <span><i class="bi bi-user me-2 text-secondary"></i><?php echo htmlspecialchars($row['username']); ?></span>
                                <span class="badge bg-secondary rounded-pill">UID: <?php echo $row['id']; ?></span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted small text-center py-3">No recent sign-ups found.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="col-lg-9 col-md-8 fade-in-card" style="animation-delay: 0.1s;">
            <div class="card shadow-sm border-0 p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="fw-bold text-dark m-0">Manage Students</h3>
                    
                    <a href="manage_student.php" class="btn btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill"></i> Add Student
                    </a>
                </div>
                
                <hr class="my-3">

                <form action="dashboard.php" method="GET" class="row g-2 mb-3">
                    <div class="col-md-9 col-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search student by name, email, or course...">
                        </div>
                    </div>
                    <div class="col-md-3 col-4 d-grid">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead class="table-light">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students_result && $students_result->num_rows > 0): ?>
                                <?php while($student = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($student['image_path']) && file_exists($student['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($student['image_path']); ?>" class="student-img border shadow-sm" alt="Student">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center border shadow-sm" style="width: 45px; height: 45px;"><i class="bi bi-person-fill"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold text-secondary"><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['course']); ?></span></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="manage_student.php?edit_id=<?php echo $student['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                <a href="dashboard.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this student profile?');"><i class="bi bi-trash-fill"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No student records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>