<?php
require_once __DIR__ . '/config.php';
require_admin_login();

$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $notice = ['type' => 'danger', 'text' => 'Your session expired. Please try again.'];
    } else {
        delete_registration($_POST['delete_id']);
        $notice = ['type' => 'success', 'text' => 'Registration deleted.'];
    }
}

$records = array_reverse(read_registrations());
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $needle = mb_strtolower($search);
    $records = array_filter($records, function ($record) use ($needle) {
        $haystack = mb_strtolower(($record['full_name'] ?? '') . ' ' . ($record['email'] ?? '') . ' ' . ($record['course'] ?? ''));
        return mb_strpos($haystack, $needle) !== false;
    });
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard &middot; Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKVBQF4odlXh5iqT9vBj0v2R6p0hX+3PTx1bXzQe5KZz0eW3D1t54eXj9TRJ5S5nYbY8vVg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../style.css" />
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-primary mb-2">Admin</span>
                <h1 class="h3 mb-0">Registration Dashboard</h1>
                <p class="text-muted mb-0"><?= count($records) ?> registration<?= count($records) === 1 ? '' : 's' ?> <?= $search !== '' ? 'matching "' . safe($search) . '"' : 'total' ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-2"></i>Registration form</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
            </div>
        </div>

        <?php if ($notice): ?>
            <div class="alert alert-<?= safe($notice['type']) ?>"><?= safe($notice['text']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-center">
                    <div class="col-sm-8 col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Search by name, email, or course" value="<?= safe($search) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
                    </div>
                    <?php if ($search !== ''): ?>
                        <div class="col-auto">
                            <a href="index.php" class="btn btn-link">Clear</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Registered</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No registrations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <?php $src = photo_src($record['photo'] ?? null); ?>
                                <tr>
                                    <td>
                                        <?php
                                            $imgSrc = 'https://via.placeholder.com/44?text=%20';
                                            if ($src) {
                                                $imgSrc = (strpos($src, 'data:') === 0) ? $src : '../' . $src;
                                            }
                                        ?>
                                        <img src="<?= $imgSrc ?>" class="admin-thumb" alt="">
                                    </td>
                                    <td><?= safe($record['full_name'] ?? '') ?></td>
                                    <td><?= safe($record['email'] ?? '') ?></td>
                                    <td><?= safe($record['mobile'] ?? '') ?></td>
                                    <td><?= safe($record['gender'] ?? '') ?></td>
                                    <td><span class="badge bg-info text-dark"><?= safe($record['course'] ?? '') ?></span></td>
                                    <td class="text-nowrap small text-muted"><?= safe(date('d M Y, H:i', strtotime($record['created_at'] ?? 'now'))) ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Delete this registration?');">
                                            <input type="hidden" name="csrf_token" value="<?= safe($token) ?>">
                                            <input type="hidden" name="delete_id" value="<?= safe($record['id'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>
