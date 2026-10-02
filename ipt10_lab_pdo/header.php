<?php
// Set $page_title before including this file, e.g. $page_title = 'All Students';
$page_title = $page_title ?? 'Angeles University Foundation - Student Records';

// Load students for the "Student Details" dropdown (works with mysqli or PDO)
$nav_students = [];
try {
    $nav_sql = 'SELECT id, first_name, last_name FROM students ORDER BY last_name, first_name';
    if (isset($pdo)) {
        $nav_students = $pdo->query($nav_sql)->fetchAll();
    } elseif (isset($conn)) {
        $nav_res = $conn->query($nav_sql);
        $nav_students = $nav_res->fetch_all(MYSQLI_ASSOC);
        $nav_res->free();
    }
} catch (Throwable $e) {
    $nav_students = []; // never break the page because of the menu
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Angeles University Foundation - Student Records</a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="index.php">Student Directory</a>

            <div class="dropdown">
                <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Student Details
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (empty($nav_students)): ?>
                        <li><span class="dropdown-item-text text-muted">No students yet</span></li>
                    <?php else: ?>
                        <?php foreach ($nav_students as $ns): ?>
                            <li>
                                <a class="dropdown-item" href="view.php?id=<?= urlencode($ns['id']) ?>">
                                    <?= htmlspecialchars($ns['last_name'] . ', ' . $ns['first_name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <a class="btn btn-success btn-sm" href="create.php">+ Add Student</a>
        </div>
    </div>
</nav>
<main class="container">