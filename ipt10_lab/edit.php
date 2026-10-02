<?php
require_once 'db_connect.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

// Load the existing record (needed for GET pre-fill and to detect a bad id on POST)
$s = $conn->prepare(
    'SELECT first_name, middle_name, last_name, birthday, sex, email,
            student_number, program, enrolment_date
     FROM students WHERE id = ?'
);
$s->bind_param('s', $id);
$s->execute();
$existing = $s->get_result()->fetch_assoc();
$s->close();

if (!$existing) {
    $page_title = 'Student Not Found';
    include 'header.php';
    echo '<div class="alert alert-warning">Student not found.</div>';
    echo '<a class="btn btn-secondary" href="index.php">Back to list</a>';
    $conn->close();
    include 'footer.php';
    exit;
}

$errors = [];
$success = '';
$notice = '';

// Start with the stored values (GET); POST overwrites them below
$first_name     = $existing['first_name'];
$middle_name    = $existing['middle_name'] ?? '';
$last_name      = $existing['last_name'];
$birthday       = $existing['birthday'];
$sex            = $existing['sex'];
$email          = $existing['email'];
$student_number = $existing['student_number'];
$program        = $existing['program'];
$enrolment_date = $existing['enrolment_date'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO(15): collect + trim, then the SAME rules as create.php
    $first_name     = trim($_POST['first_name'] ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $birthday       = trim($_POST['birthday'] ?? '');
    $sex            = trim($_POST['sex'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $program        = trim($_POST['program'] ?? '');
    $enrolment_date = trim($_POST['enrolment_date'] ?? '');

    if (empty($first_name) || strlen($first_name) < 2 || strlen($first_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $first_name)) {
        $errors['first_name'] = 'First name is required (2-100 letters and spaces only).';
    }
    if (empty($last_name) || strlen($last_name) < 2 || strlen($last_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $last_name)) {
        $errors['last_name'] = 'Last name is required (2-100 letters and spaces only).';
    }
    if ($middle_name !== '' && (strlen($middle_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $middle_name))) {
        $errors['middle_name'] = 'Middle name may contain letters and spaces only (max 100).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'A valid email address is required.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)
        || !checkdate((int)substr($birthday, 5, 2), (int)substr($birthday, 8, 2), (int)substr($birthday, 0, 4))) {
        $errors['birthday'] = 'Birthday must be a real date in YYYY-MM-DD format.';
    }
    if (!in_array($sex, ['Male', 'Female'], true)) {
        $errors['sex'] = 'Please select Male or Female.';
    }
    if ($student_number === '' || strlen($student_number) > 50
        || !preg_match('/^[A-Za-z0-9]+$/', $student_number)) {
        $errors['student_number'] = 'Student number is required (letters and numbers only, max 50).';
    }
    if (strlen($program) > 200) {
        $errors['program'] = 'Program must be 200 characters or fewer.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrolment_date)
        || !checkdate((int)substr($enrolment_date, 5, 2), (int)substr($enrolment_date, 8, 2), (int)substr($enrolment_date, 0, 4))) {
        $errors['enrolment_date'] = 'Enrolment date must be a real date in YYYY-MM-DD format.';
    }

    if (empty($errors)) {
        try {
            // TODO(16): nine editable columns, then WHERE id = ?
            $stmt = $conn->prepare(
                'UPDATE students
                 SET first_name = ?, middle_name = ?, last_name = ?,
                     birthday = ?, sex = ?, email = ?, student_number = ?,
                     program = ?, enrolment_date = ?
                 WHERE id = ?'
            );

            $middle_param = ($middle_name === '') ? null : $middle_name;

            // ten strings; the UUID is the LAST value, matching the final placeholder
            $stmt->bind_param(
                'ssssssssss',
                $first_name, $middle_param, $last_name, $birthday, $sex,
                $email, $student_number, $program, $enrolment_date, $id
            );
            $stmt->execute();

            // TODO(17): execute() succeeding is not the same as a row changing
            if ($stmt->affected_rows === 1) {
                $success = 'Student updated successfully!';
            } else {
                // The id was verified above, so 0 here means the row matched
                // but every value was identical to what was already stored.
                $notice = 'No changes were made (the values were identical).';
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $errors['form'] = 'That email or student number belongs to another student.';
            } else {
                error_log('Update failed: ' . $e->getMessage());
                $errors['form'] = 'Could not update the student. Please try again.';
            }
        }
    }
}

$page_title = 'Edit Student';
include 'header.php';

function field_error(array $errors, string $key): void {
    if (isset($errors[$key])) {
        echo '<div class="text-danger small mt-1">' . htmlspecialchars($errors[$key]) . '</div>';
    }
}
?>

<h2 class="mb-3">Edit Student</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($notice): ?>
    <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>
<?php if (isset($errors['form'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['form']) ?></div>
<?php endif; ?>

<form method="POST" novalidate class="card card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="first_name">First Name</label>
            <input class="form-control" type="text" id="first_name" name="first_name"
                   value="<?= htmlspecialchars($first_name) ?>">
            <?php field_error($errors, 'first_name'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="middle_name">Middle Name (optional)</label>
            <input class="form-control" type="text" id="middle_name" name="middle_name"
                   value="<?= htmlspecialchars($middle_name) ?>">
            <?php field_error($errors, 'middle_name'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="last_name">Last Name</label>
            <input class="form-control" type="text" id="last_name" name="last_name"
                   value="<?= htmlspecialchars($last_name) ?>">
            <?php field_error($errors, 'last_name'); ?>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="birthday">Birthday</label>
            <input class="form-control" type="date" id="birthday" name="birthday"
                   value="<?= htmlspecialchars($birthday) ?>">
            <?php field_error($errors, 'birthday'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="sex">Sex</label>
            <select class="form-select" id="sex" name="sex">
                <option value="">-- Select --</option>
                <option value="Male"   <?= $sex === 'Male'   ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
            <?php field_error($errors, 'sex'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="text" id="email" name="email"
                   value="<?= htmlspecialchars($email) ?>">
            <?php field_error($errors, 'email'); ?>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="student_number">Student Number</label>
            <input class="form-control" type="text" id="student_number" name="student_number"
                   value="<?= htmlspecialchars($student_number) ?>">
            <?php field_error($errors, 'student_number'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="program">Program (optional)</label>
            <input class="form-control" type="text" id="program" name="program"
                   value="<?= htmlspecialchars($program) ?>">
            <?php field_error($errors, 'program'); ?>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="enrolment_date">Enrolment Date</label>
            <input class="form-control" type="date" id="enrolment_date" name="enrolment_date"
                   value="<?= htmlspecialchars($enrolment_date) ?>">
            <?php field_error($errors, 'enrolment_date'); ?>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-secondary" href="view.php?id=<?= urlencode($id) ?>">View</a>
        <a class="btn btn-secondary" href="index.php">Back</a>
    </div>
</form>

<?php
$conn->close();
include 'footer.php';