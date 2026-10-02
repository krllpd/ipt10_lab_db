<?php
require_once 'db_connect.php';

// TODO(4): static query, no user input, so no placeholders needed
$sql = 'SELECT id, first_name, last_name, email, enrolment_date
        FROM students
        ORDER BY enrolment_date DESC, id DESC';
$result = mysqli_query($conn, $sql);

$page_title = 'All Students';
include 'header.php';
?>

<h2 class="mb-3">All Student Records</h2>

<table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Enrolled</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php // TODO(5): fetch one associative row per loop pass ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['enrolment_date']) ?></td>
                <td>
                    <a class="btn btn-sm btn-primary" href="view.php?id=<?= urlencode($row['id']) ?>">View</a>
                    <a class="btn btn-sm btn-warning" href="edit.php?id=<?= urlencode($row['id']) ?>">Edit</a>
                    <a class="btn btn-sm btn-danger" href="delete.php?id=<?= urlencode($row['id']) ?>">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
// TODO(6): free the result set first, then close the connection
mysqli_free_result($result);
mysqli_close($conn);

include 'footer.php';