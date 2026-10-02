<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

// Step 1: confirm screen lookup
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

$page_title = 'Delete Student';
include 'header.php';

// Not-found case
if (!$student) {
    echo '<div class="alert alert-warning">Student not found.</div>';
    echo '<a class="btn btn-secondary" href="index.php">Back to list</a>';
    include 'footer.php';
    exit;
}

$name = $student['first_name'] . ' ' . $student['last_name'];

// Step 2: deletion, only after the POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $d = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $d->execute([$id]);

        if ($d->rowCount() === 1) {
            $pdo->commit(); // required because autocommit is off
            echo '<div class="alert alert-success">Student deleted successfully.</div>';
        } else {
            $pdo->rollBack();
            echo '<div class="alert alert-danger">Failed to delete student.</div>';
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log('Delete failed: ' . $e->getMessage());
        echo '<div class="alert alert-danger">Failed to delete student.</div>';
    }
    echo '<a class="btn btn-primary" href="index.php">Back to list</a>';
} else {
?>
    <h2 class="mb-3">Delete Student</h2>
    <div class="card border-danger">
        <div class="card-body">
            <p class="mb-3">
                Are you sure you want to delete
                <strong><?= htmlspecialchars($name) ?></strong>?
                This cannot be undone.
            </p>
            <form method="POST" action="delete.php?id=<?= urlencode($id) ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                <button type="submit" class="btn btn-danger">Yes, delete</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </form>
        </div>
    </div>
<?php
}

include 'footer.php';