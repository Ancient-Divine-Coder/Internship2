<?php
require_once __DIR__ . '/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Your session expired or the form was resubmitted. Please try again.';
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$course = trim($_POST['course'] ?? '');
$address = trim($_POST['address'] ?? '');
$consent = isset($_POST['consent']);

if ($fullName === '') {
    $errors[] = 'Full name is required.';
} elseif (preg_match('/\d/', $fullName)) {
    $errors[] = 'Full name cannot contain numbers.';
} elseif (mb_strlen($fullName) < 3) {
    $errors[] = 'Full name must be at least 3 characters long.';
}

if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($mobile === '') {
    $errors[] = 'Mobile number is required.';
} elseif (!preg_match('/^[0-9+\s()\-]{7,20}$/', $mobile)) {
    $errors[] = 'Please enter a valid mobile number.';
}

$allowedGenders = ['Male', 'Female', 'Other'];
if ($gender === '' || !in_array($gender, $allowedGenders, true)) {
    $errors[] = 'Gender selection is required.';
}

$allowedCourses = ['B.Tech CSE', 'B.Sc IT', 'BBA', 'Class 12 Science', 'Diploma Engineering'];
if ($course === '' || !in_array($course, $allowedCourses, true)) {
    $errors[] = 'Please choose your course.';
}

if ($address === '') {
    $errors[] = 'Address is required.';
} elseif (mb_strlen($address) < 12) {
    $errors[] = 'Address must be at least 12 characters long.';
}

if (!$consent) {
    $errors[] = 'Please confirm that your details are correct.';
}

$photo = handle_photo_upload($errors);

$registrationId = null;
if (!$errors) {
    $saved = save_registration([
        'full_name' => $fullName,
        'email' => $email,
        'mobile' => $mobile,
        'gender' => $gender,
        'course' => $course,
        'address' => $address,
        'photo' => $photo,
    ]);
    if ($saved) {
        $registrationId = $saved['id'];
    }
    // Issue a fresh CSRF token so a page refresh doesn't resubmit silently.
    unset($_SESSION['csrf_token']);
}

$photoSrc = photo_src($photo);
if ($photoSrc && strpos($photoSrc, 'data:') !== 0) {
    $photoSrc = '../' . $photoSrc;
}
?>
<!DOCTYPE html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKVBQF4odlXh5iqT9vBj0v2R6p0hX+3PTx1bXzQe5KZz0eW3D1t54eXj9TRJ5S5nYbY8vVg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css" />
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-9">
                <div class="card shadow-sm confirmation-card">
                    <div class="card-body p-5">
                        <?php if ($errors): ?>
                            <div class="alert alert-danger d-flex align-items-start">
                                <div class="me-3 mt-1"><i class="fa-solid fa-triangle-exclamation fa-lg"></i></div>
                                <div>
                                    <h5 class="alert-heading">Please fix the following errors:</h5>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= safe($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-outline-primary">Return to registration form</a>
                            </div>
                        <?php else: ?>
                            <div class="confirmation-header rounded-4 mb-4 p-4 text-white d-print-none">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div>
                                        <span class="badge bg-light text-success mb-2">Confirmation</span>
                                        <h1 class="h3 mb-2"><i class="fa-solid fa-circle-check me-2"></i>Registration complete</h1>
                                        <p class="mb-0 text-white-75">Your student details were captured successfully. Save or print this confirmation for your records.</p>
                                    </div>
                                    <div class="text-md-end">
                                        <span class="badge bg-white text-primary fs-6"><i class="fa-solid fa-user-graduate me-1"></i> <?= safe($course) ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($registrationId): ?>
                                <p class="text-muted small mb-4">Reference ID: <code><?= safe($registrationId) ?></code></p>
                            <?php endif; ?>

                            <div class="row gy-4">
                                <div class="col-lg-4 text-center">
                                    <div class="profile-card p-4 rounded bg-white shadow-sm">
                                        <div class="profile-photo mb-3">
                                            <img src="<?= $photoSrc ? $photoSrc : 'https://via.placeholder.com/220?text=Profile+Image' ?>" alt="Profile photo" class="img-fluid rounded-circle" />
                                        </div>
                                        <p class="mb-1 text-muted">Selected course</p>
                                        <h5><?= safe($course) ?></h5>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="confirmation-field">
                                                <span>Name</span>
                                                <strong><?= safe($fullName) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="confirmation-field">
                                                <span>Email</span>
                                                <strong><?= safe($email) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="confirmation-field">
                                                <span>Mobile</span>
                                                <strong><?= safe($mobile) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="confirmation-field">
                                                <span>Gender</span>
                                                <strong><?= safe($gender) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="confirmation-field">
                                                <span>Address</span>
                                                <strong class="d-block text-wrap"><?= nl2br(safe($address)) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4 d-print-none">
                                <button type="button" class="btn btn-outline-secondary btn-lg me-2" onclick="window.print()">
                                    <i class="fa-solid fa-print me-2"></i>Print confirmation
                                </button>
                                <a href="index.php" class="btn btn-primary btn-lg">Register another student</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>