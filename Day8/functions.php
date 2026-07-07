<?php
/**
 * Shared helpers for the Student Registration System.
 * Used by index.php, confirm.php, and the admin panel.
 */

define('DATA_DIR', __DIR__ . '/../data');
define('DATA_FILE', DATA_DIR . '/registrations.json');
define('UPLOADS_DIR', __DIR__ . '/../uploads');

/**
 * Escape a value for safe HTML output.
 */
function safe($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Make sure the data + uploads directories exist and are writable.
 * Returns true on success, false if the app can't persist data
 * (the site still works, it just won't save a permanent record).
 */
function ensure_storage_ready()
{
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0775, true);
    }
    if (!is_dir(UPLOADS_DIR)) {
        @mkdir(UPLOADS_DIR, 0775, true);
    }

    // Prevent PHP files from ever being executed out of the uploads folder,
    // even though we only ever write validated image files there.
    $htaccess = UPLOADS_DIR . '/.htaccess';
    if (is_dir(UPLOADS_DIR) && !file_exists($htaccess)) {
        @file_put_contents($htaccess, "php_flag engine off\n");
    }

    if (!file_exists(DATA_FILE) && is_dir(DATA_DIR)) {
        @file_put_contents(DATA_FILE, json_encode([]));
    }

    return is_dir(DATA_DIR) && is_writable(DATA_DIR) && file_exists(DATA_FILE) && is_writable(DATA_FILE);
}

/**
 * Read all registrations from the JSON store.
 */
function read_registrations()
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $raw = @file_get_contents(DATA_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Append one registration record using an exclusive file lock
 * so concurrent submissions never clobber each other.
 */
function save_registration(array $record)
{
    if (!ensure_storage_ready()) {
        return false;
    }

    $handle = fopen(DATA_FILE, 'c+');
    if ($handle === false) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $size = filesize(DATA_FILE);
    $raw = $size > 0 ? fread($handle, $size) : '';
    $records = json_decode($raw, true);
    if (!is_array($records)) {
        $records = [];
    }

    $record['id'] = uniqid('reg_', true);
    $record['created_at'] = date('c');
    $records[] = $record;

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($records, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $record;
}

/**
 * Delete a registration (and its photo file, if any) by id.
 */
function delete_registration($id)
{
    $records = read_registrations();
    $kept = [];
    $removed = null;

    foreach ($records as $record) {
        if (($record['id'] ?? null) === $id) {
            $removed = $record;
            continue;
        }
        $kept[] = $record;
    }

    if ($removed === null) {
        return false;
    }

    if (!empty($removed['photo'])) {
        $photoPath = UPLOADS_DIR . '/' . basename($removed['photo']);
        if (is_file($photoPath)) {
            @unlink($photoPath);
        }
    }

    @file_put_contents(DATA_FILE, json_encode($kept, JSON_PRETTY_PRINT));
    return true;
}

/**
 * Validate and store an uploaded profile photo.
 * Returns the stored filename on success, null if no photo was sent,
 * or throws by adding to $errors on failure.
 */
function handle_photo_upload(&$errors)
{
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $photo = $_FILES['photo'];

    if ($photo['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading the profile photo.';
        return null;
    }

    $maxBytes = 3 * 1024 * 1024; // 3MB
    if ($photo['size'] > $maxBytes) {
        $errors[] = 'Profile photo must be smaller than 3MB.';
        return null;
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $photo['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedTypes[$mimeType])) {
        $errors[] = 'Profile photo must be a JPEG, PNG, or WEBP image.';
        return null;
    }

    if (!ensure_storage_ready()) {
        // Storage isn't writable on this server; fall back to embedding
        // the image directly so the confirmation page still works.
        $photoData = file_get_contents($photo['tmp_name']);
        return 'data:' . $mimeType . ';base64,' . base64_encode($photoData);
    }

    $extension = $allowedTypes[$mimeType];
    $filename = uniqid('photo_', true) . '.' . $extension;
    $destination = UPLOADS_DIR . '/' . $filename;

    if (!move_uploaded_file($photo['tmp_name'], $destination)) {
        $errors[] = 'The profile photo could not be saved. Please try again.';
        return null;
    }

    return 'uploads/' . $filename;
}

/**
 * Resolve a stored photo reference (either a relative uploads/ path
 * or a legacy base64 data URI) into a src attribute value.
 */
function photo_src($photo)
{
    if (!$photo) {
        return null;
    }
    if (strpos($photo, 'data:') === 0) {
        return $photo;
    }
    return safe($photo);
}

/* -------------------- CSRF helpers -------------------- */

function csrf_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . safe(csrf_token()) . '">';
}

function verify_csrf($token)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}
