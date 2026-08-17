<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$pdo = get_db();
$userId = (int)$_SESSION['user_id'];
$message = null;

// Only these are accepted, checked by real content sniffing (finfo), not just the filename extension
const ALLOWED_MIME = [
    'application/pdf' => 'pdf',
    'image/jpeg'       => 'jpg',
    'image/png'        => 'png',
];
const MAX_BYTES = 2 * 1024 * 1024; // 2MB
const UPLOAD_DIR = __DIR__ . '/../uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    if (empty($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please choose a valid file to upload.';
    } else {
        $file = $_FILES['document'];

        if ($file['size'] > MAX_BYTES) {
            log_event('VALIDATION_REJECTED', $_SESSION['user_email'], 'Upload exceeded size limit');
            $message = 'File is too large (max 2MB).';
        } else {
            // Detect the REAL mime type from file content - never trust
            // $_FILES['document']['type'], which the browser/attacker controls.
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset(ALLOWED_MIME[$realMime])) {
                log_event('VALIDATION_REJECTED', $_SESSION['user_email'], "Upload rejected mime=$realMime");
                $message = 'Only PDF, JPG or PNG files are allowed.';
            } else {
                // Never trust the original filename for storage - generate a
                // random name with a safe extension to prevent path traversal
                // and to stop any embedded executable content from being
                // served/interpreted as the original name might suggest.
                $ext = ALLOWED_MIME[$realMime];
                $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
                $destination = UPLOAD_DIR . $storedName;

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $stmt = $pdo->prepare(
                        'INSERT INTO documents (user_id, original_name, stored_name, mime_type, size_bytes)
                         VALUES (:u, :orig, :stored, :mime, :size)'
                    );
                    // original_name is stored for display only, and is ALWAYS
                    // escaped with e() on output - never used as a filesystem path.
                    $stmt->execute([
                        ':u'      => $userId,
                        ':orig'   => mb_substr(basename($file['name']), 0, 255),
                        ':stored' => $storedName,
                        ':mime'   => $realMime,
                        ':size'   => $file['size'],
                    ]);
                    log_event('UPLOAD_SUCCESS', $_SESSION['user_email'], "Stored as $storedName");
                    $message = 'File uploaded successfully.';
                } else {
                    error_log('move_uploaded_file failed for ' . $file['tmp_name']);
                    $message = 'Upload failed. Please try again.';
                }
            }
        }
    }
}

$docsStmt = $pdo->prepare('SELECT original_name, mime_type, size_bytes, uploaded_at FROM documents WHERE user_id = :u ORDER BY uploaded_at DESC');
$docsStmt->execute([':u' => $userId]);
$docs = $docsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Document</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Upload Document</h1>
    <p><a href="/dashboard.php">Back to dashboard</a></p>

    <?php if ($message): ?><p class="alert"><?= e($message) ?></p><?php endif; ?>

    <form method="POST" action="/upload.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="document">Document (PDF, JPG, or PNG, max 2MB)</label>
        <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
        <button type="submit">Upload</button>
    </form>

    <h2>Your Uploads</h2>
    <table>
        <tr><th>File name</th><th>Type</th><th>Size</th><th>Uploaded</th></tr>
        <?php foreach ($docs as $d): ?>
        <tr>
            <td><?= e($d['original_name']) ?></td>
            <td><?= e($d['mime_type']) ?></td>
            <td><?= number_format($d['size_bytes'] / 1024, 1) ?> KB</td>
            <td><?= e($d['uploaded_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$docs): ?><tr><td colspan="4">No documents uploaded yet.</td></tr><?php endif; ?>
    </table>
</main>
</body>
</html>
