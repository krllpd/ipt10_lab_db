<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

$stmt = $pdo->prepare(
    'SELECT id, first_name, middle_name, last_name, birthday, sex, email,
            student_number, program, enrolment_date, created_at, updated_at
     FROM students WHERE id = ?'
);
// No bind_param in PDO: the id goes inside the execute() array
$stmt->execute([$id]);
$row = $stmt->fetch();

$page_title = 'Student Details';
include 'header.php';

if (!$row) {
    echo '<div class="alert alert-warning">Student not found.</div>';
    echo '<a class="btn btn-secondary" href="index.php">Back to list</a>';
} else {
    $full_name = preg_replace('/\s+/', ' ',
        trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']));
?>
    <h2 class="mb-3">Student Details</h2>
    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Student ID</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['id']) ?></dd>
                <dt class="col-sm-3">Full Name</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($full_name) ?></dd>
                <dt class="col-sm-3">Birthday</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['birthday']) ?></dd>
                <dt class="col-sm-3">Sex</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['sex']) ?></dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['email']) ?></dd>
                <dt class="col-sm-3">Student Number</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['student_number']) ?></dd>
                <dt class="col-sm-3">Program</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['program']) ?></dd>
                <dt class="col-sm-3">Enrolment Date</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['enrolment_date']) ?></dd>
                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['created_at']) ?></dd>
                <dt class="col-sm-3">Updated At</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($row['updated_at']) ?></dd>
            </dl>
        </div>
    </div>
    <div class="mt-3">
        <a class="btn btn-secondary" href="index.php">Back</a>
        <a class="btn btn-warning" href="edit.php?id=<?= urlencode($row['id']) ?>">Edit</a>
    </div>
<?php
}

include 'footer.php';