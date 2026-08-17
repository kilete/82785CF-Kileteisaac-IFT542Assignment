<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$pdo = get_db();
$userId = (int)$_SESSION['user_id'];
$message = null;

// --- Handle course registration (state-changing action -> CSRF protected) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $courseId = filter_var($_POST['course_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$courseId) {
        log_event('VALIDATION_REJECTED', $_SESSION['user_email'], 'Invalid course_id on registration');
        $message = 'Invalid course selection.';
    } else {
        // Parameterized - safe from injection
        $exists = $pdo->prepare('SELECT id FROM courses WHERE id = :id');
        $exists->execute([':id' => $courseId]);

        if (!$exists->fetch()) {
            $message = 'That course does not exist.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO enrolments (user_id, course_id) VALUES (:u, :c)
                     ON DUPLICATE KEY UPDATE status = "active"'
                );
                $stmt->execute([':u' => $userId, ':c' => $courseId]);
                $message = 'Course registration successful.';
            } catch (PDOException $e) {
                error_log('Enrolment error: ' . $e->getMessage());
                $message = 'Could not complete registration. Please try again.';
            }
        }
    }
}

// --- Load data for display ---
$courses = $pdo->query('SELECT id, course_code, title, units FROM courses ORDER BY course_code')->fetchAll();

$myCoursesStmt = $pdo->prepare(
    'SELECT c.course_code, c.title, e.registered_at
     FROM enrolments e JOIN courses c ON c.id = e.course_id
     WHERE e.user_id = :u AND e.status = "active"'
);
$myCoursesStmt->execute([':u' => $userId]);
$myCourses = $myCoursesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Registration</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Course Registration</h1>
    <p>Logged in as <?= e($_SESSION['user_name']) ?> - <a href="/logout.php">Log out</a></p>

    <?php if ($message): ?>
        <!-- Task 3 XSS demo: this field is user/system-generated text and is
             ALWAYS passed through e() (htmlspecialchars) before output, so
             it can never be interpreted as HTML/JS by the browser. -->
        <p class="alert"><?= e($message) ?></p>
    <?php endif; ?>

    <h2>Available Courses</h2>
    <table>
        <tr><th>Code</th><th>Title</th><th>Units</th><th></th></tr>
        <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= e($c['course_code']) ?></td>
            <td><?= e($c['title']) ?></td>
            <td><?= e((string)$c['units']) ?></td>
            <td>
                <form method="POST" action="/courses.php" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                    <button type="submit">Register</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>My Registered Courses</h2>
    <ul>
        <?php foreach ($myCourses as $mc): ?>
            <li><?= e($mc['course_code']) ?> - <?= e($mc['title']) ?> (registered <?= e($mc['registered_at']) ?>)</li>
        <?php endforeach; ?>
        <?php if (!$myCourses): ?><li>No courses registered yet.</li><?php endif; ?>
    </ul>
</main>
</body>
</html>
