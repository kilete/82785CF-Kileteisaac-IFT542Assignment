<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role('admin'); // Elevation-of-Privilege control: only role=admin gets past this

$pdo = get_db();
$studentCount = (int)$pdo->query('SELECT COUNT(*) c FROM users WHERE role = "student"')->fetch()['c'];
$courseCount  = (int)$pdo->query('SELECT COUNT(*) c FROM courses')->fetch()['c'];
$enrolCount   = (int)$pdo->query('SELECT COUNT(*) c FROM enrolments WHERE status = "active"')->fetch()['c'];

$recentLogs = $pdo->query(
    'SELECT event_type, username, ip_address, detail, created_at
     FROM audit_log ORDER BY id DESC LIMIT 15'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Admin Dashboard</h1>
    <p>Logged in as <?= e($_SESSION['user_name']) ?> (admin) - <a href="/logout.php">Log out</a></p>
    <nav>
        <a href="/admin/index.php">Overview</a> |
        <a href="/admin/courses.php">Manage Courses</a> |
        <a href="/admin/students.php">View Students</a>
    </nav>

    <h2>Summary</h2>
    <ul>
        <li>Students: <?= $studentCount ?></li>
        <li>Courses: <?= $courseCount ?></li>
        <li>Active enrolments: <?= $enrolCount ?></li>
    </ul>

    <h2>Recent Security Events</h2>
    <table>
        <tr><th>Event</th><th>User</th><th>IP</th><th>Detail</th><th>When</th></tr>
        <?php foreach ($recentLogs as $log): ?>
        <tr>
            <td><?= e($log['event_type']) ?></td>
            <td><?= e($log['username'] ?? '-') ?></td>
            <td><?= e($log['ip_address'] ?? '-') ?></td>
            <td><?= e($log['detail'] ?? '') ?></td>
            <td><?= e($log['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recentLogs): ?><tr><td colspan="5">No events yet.</td></tr><?php endif; ?>
    </table>
    <p><small>This table doubles as your evidence source for Task 3 redacted logs -
       note it deliberately excludes passwords and full request bodies.</small></p>
</main>
</body>
</html>
