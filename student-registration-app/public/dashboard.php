<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <h1>Welcome, <?= e($_SESSION['user_name']) ?></h1>
    <p><a href="/courses.php">Register for courses</a></p>
    <p><a href="/upload.php">Upload a document</a></p>
    <p><a href="/preview.php">Preview a course resource link</a></p>
    <p><a href="/logout.php">Log out</a></p>
</main>
</body>
</html>
