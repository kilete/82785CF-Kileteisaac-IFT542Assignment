<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('admin');

$pdo = get_db();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code  = trim($_POST['course_code'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $units = filter_var($_POST['units'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 6]]);
        $cap   = filter_var($_POST['capacity'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);

        $validCode = (bool)preg_match('/^[A-Z]{2,6}\d{3}$/', $code);

        if (!$validCode || mb_strlen($title) < 2 || mb_strlen($title) > 150 || $units === false || $cap === false) {
            log_event('VALIDATION_REJECTED', $_SESSION['user_email'], 'Admin add-course form invalid');
            $message = 'Invalid course details. Code format example: IFT542.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO courses (course_code, title, units, capacity) VALUES (:code, :title, :units, :cap)'
                );
                $stmt->execute([':code' => $code, ':title' => $title, ':units' => $units, ':cap' => $cap]);
                log_event('ADMIN_COURSE_ADD', $_SESSION['user_email'], "Added $code");
                $message = 'Course added.';
            } catch (PDOException $e) {
                error_log('Add course error: ' . $e->getMessage());
                $message = 'Could not add course (code may already exist).';
            }
        }
    } elseif ($action === 'delete') {
        $courseId = filter_var($_POST['course_id'] ?? '', FILTER_VALIDATE_INT);
        if ($courseId) {
            $stmt = $pdo->prepare('DELETE FROM courses WHERE id = :id');
            $stmt->execute([':id' => $courseId]);
            log_event('ADMIN_COURSE_DELETE', $_SESSION['user_email'], "Deleted course id $courseId");
            $message = 'Course deleted.';
        }
    }
}

$courses = $pdo->query('SELECT id, course_code, title, units, capacity FROM courses ORDER BY course_code')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Manage Courses</h1>
    <nav>
        <a href="/admin/index.php">Overview</a> |
        <a href="/admin/courses.php">Manage Courses</a> |
        <a href="/admin/students.php">View Students</a>
    </nav>

    <?php if ($message): ?><p class="alert"><?= e($message) ?></p><?php endif; ?>

    <h2>Add Course</h2>
    <form method="POST" action="/admin/courses.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <label>Course Code (e.g. IFT542)</label>
        <input type="text" name="course_code" maxlength="10" required>
        <label>Title</label>
        <input type="text" name="title" maxlength="150" required>
        <label>Units</label>
        <input type="number" name="units" min="1" max="6" value="3" required>
        <label>Capacity</label>
        <input type="number" name="capacity" min="1" max="1000" value="50" required>
        <button type="submit">Add Course</button>
    </form>

    <h2>Existing Courses</h2>
    <table>
        <tr><th>Code</th><th>Title</th><th>Units</th><th>Capacity</th><th></th></tr>
        <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= e($c['course_code']) ?></td>
            <td><?= e($c['title']) ?></td>
            <td><?= e((string)$c['units']) ?></td>
            <td><?= e((string)$c['capacity']) ?></td>
            <td>
                <form method="POST" action="/admin/courses.php" style="display:inline"
                      onsubmit="return confirm('Delete this course?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>
</body>
</html>
