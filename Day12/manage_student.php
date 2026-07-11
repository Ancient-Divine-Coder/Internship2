<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$is_edit = false;

// Initialize empty variables for Form Fields
$name = $email = $course = $image_path = "";
$student_id = 0;

// Check if we are in Edit Mode
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $student_id = intval($_GET['edit_id']);
    
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $student = $res->fetch_assoc();
        $name = $student['name'];
        $email = $student['email'];
        $course = $student['course'];
        $image_path = $student['image_path'];
    } else {
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

// Handle Form Processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $student_id = intval($_POST['student_id']);
    $is_edit = $student_id > 0;
    
    // Process File Upload if it exists
    $uploaded_file_path = $_POST['existing_image'];
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['student_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['student_image']['name']);
        
        // Ensure folder directory exists
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $target_path = $upload_dir . $file_name;
        
        // Move file onto application root
        if (move_uploaded_file($file_tmp, $target_path)) {
            $uploaded_file_path = $target_path;
        }
    }

    if (!empty($name) && !empty($email) && !empty($course)) {
        if ($is_edit) {
            // Update Existing Record
            $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, course = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $course, $uploaded_file_path, $student_id);
        } else {
            // Create New Record
            $stmt = $conn->prepare("INSERT INTO students (name, email, course, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $course, $uploaded_file_path);
        }

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Execution error. Please check your data variables.";
        }
    } else {
        $error = "Please fill in all mandatory text inputs.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Student' : 'Add Student'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { width: 100%; max-width: 500px; padding: 2.5rem; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .preview-box img { max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>

<div class="form-container">
    <form action="manage_student.php" method="POST" enctype="multipart/form-data">
        <h3 class="fw-bold mb-1 text-center"><?php echo $is_edit ? 'Update Record' : 'Register Student'; ?></h3>
        <p class="text-muted text-center small mb-4">Complete fields below to proceed</p>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($image_path); ?>">

        <div class="mb-3">
            <label class="form-label small fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter name">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter email">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Course Module</label>
            <input type="text" name="course" class="form-control" required value="<?php echo htmlspecialchars($course); ?>" placeholder="Enter course name">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold">Profile Picture</label>
            <input type="file" name="student_image" id="student_image" class="form-control" accept="image/*">
            
            <div class="preview-box mt-3 text-center d-none" id="previewContainer">
                <p class="text-muted small mb-1">Image Preview:</p>
                <img id="imagePreview" src="#" class="img-thumbnail" alt="Preview">
            </div>
            
            <?php if($is_edit && !empty($image_path)): ?>
                <div class="text-center mt-2" id="currentImageContainer">
                    <p class="text-muted small mb-1">Current Picture:</p>
                    <img src="<?php echo htmlspecialchars($image_path); ?>" class="img-thumbnail" style="max-width: 90px;" alt="Current Profile">
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Save Entry</button>
            </div>
            <div class="col-6">
                <a href="dashboard.php" class="btn btn-outline-secondary w-100 fw-semibold">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('student_image').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
                
                // Hide current photo indicator if updating
                const currentImgBox = document.getElementById('currentImageContainer');
                if(currentImgBox) currentImgBox.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>