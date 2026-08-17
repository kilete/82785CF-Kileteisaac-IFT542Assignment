<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('admin');

$pdo = get_db();

$students = $pdo->query(
    "SELECT id, matric_no, full_name, email, created_at
     FROM users WHERE role = 'student' ORDER BY created_at DESC"
)->fetchAll();

$enrolStmt = $pdo->prepare(
    'SELECT c.course_code, c.title, e.registered_at
     FROM enrolments e JOIN courses c ON c.id = e.course_id
     WHERE e.user_id = :u AND e.status = "active"'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Students</h1>
    <nav>
        <a href="/admin/index.php">Overview</a> |
        <a href="/admin/courses.php">Manage Courses</a> |
        <a href="/admin/students.php">View Students</a>
    </nav>

    <?php foreach ($students as $s): ?>
        <h3><?= e($s['full_name']) ?> (<?= e($s['matric_no'] ?? 'N/A') ?>)</h3>
        <p><?= e($s['email']) ?> - joined <?= e($s['created_at']) ?></p>
        <ul>
            <?php
            $enrolStmt->execute([':u' => $s['id']]);
            $rows = $enrolStmt->fetchAll();
            foreach ($rows as $r): ?>
                <li><?= e($r['course_code']) ?> - <?= e($r['title']) ?></li>
            <?php endforeach; ?>
            <?php if (!$rows): ?><li>No active enrolments.</li><?php endif; ?>
        </ul>
    <?php endforeach; ?>
    <?php if (!$students): ?><p>No students yet.</p><?php endif; ?>
</main>
</body>
</html>
