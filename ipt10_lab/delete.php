<?php
require_once 'db_connect.php';

// The id comes from the URL on GET, and from the hidden field on POST
$id = trim($_GET['id'] ?? $_POST['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

// Step 1: look up the student so the confirmation screen can name them
// TODO(18): prepared SELECT of first_name and last_name by id
$s = $conn->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$s->bind_param('s', $id);
$s->execute();
$r = $s->get_result()->fetch_assoc();
$s->close();

$page_title = 'Delete Student';
include 'header.php';

if (!$r) {
    echo '<div class="alert alert-warning">Student not found.</div>';
    echo '<a class="btn btn-secondary" href="index.php">Back to list</a>';
    $conn->close();
    include 'footer.php';
    exit;
}

$name = $r['first_name'] . ' ' . $r['last_name'];

// Step 2: delete only when the confirmation form was submitted (POST)
// TODO(19): the request condition is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $conn->prepare('DELETE FROM students WHERE id = ?');
    $d->bind_param('s', $id);
    $d->execute();

    // One row really removed, not just "no error"
    if ($d->affected_rows === 1) {
        echo '<div class="alert alert-success">Student deleted successfully.</div>';
    } else {
        echo '<div class="alert alert-danger">Failed to delete student.</div>';
    }
    $d->close();
    echo '<a class="btn btn-primary" href="index.php">Back to list</a>';
    $conn->close();
} else {
    // TODO(20): confirmation screen (GET) with a hidden id and Yes/Cancel
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
    $conn->close();
}

include 'footer.php';