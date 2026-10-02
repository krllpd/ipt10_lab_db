<?php
require_once 'db_connect.php';

$errors = [];
$first_name = $middle_name = $last_name = $birthday = $sex = '';
$email = $student_number = $program = $enrolment_date = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name'] ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $birthday       = trim($_POST['birthday'] ?? '');
    $sex            = trim($_POST['sex'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $program        = trim($_POST['program'] ?? '');
    $enrolment_date = trim($_POST['enrolment_date'] ?? '');

    // TODO(10): names - required, 2-100 chars, letters and spaces only
    if (empty($first_name) || strlen($first_name) < 2 || strlen($first_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $first_name)) {
        $errors['first_name'] = 'First name is required (2-100 letters and spaces only).';
    }
    if (empty($last_name) || strlen($last_name) < 2 || strlen($last_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $last_name)) {
        $errors['last_name'] = 'Last name is required (2-100 letters and spaces only).';
    }
    // Middle name is optional, but if given it follows the same character rule
    if ($middle_name !== '' && (strlen($middle_name) > 100
        || !preg_match('/^[A-Za-z\s]+$/', $middle_name))) {
        $errors['middle_name'] = 'Middle name may contain letters and spaces only (max 100).';
    }

    // TODO(11): email and birthday
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'A valid email address is required.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)
        || !checkdate((int)substr($birthday, 5, 2), (int)substr($birthday, 8, 2), (int)substr($birthday, 0, 4))) {
        $errors['birthday'] = 'Birthday must be a real date in YYYY-MM-DD format.';
    }

    // TODO(12): sex must match the ENUM values exactly
    if (!in_array($sex, ['Male', 'Female'], true)) {
        $errors['sex'] = 'Please select Male or Female.';
    }

    // Student number: required by the schema, alphanumeric, max 50
    if ($student_number === '' || strlen($student_number) > 50
        || !preg_match('/^[A-Za-z0-9]+$/', $student_number)) {
        $errors['student_number'] = 'Student number is required (letters and numbers only, max 50).';
    }

    // Program: optional, max 200
    if (strlen($program) > 200) {
        $errors['program'] = 'Program must be 200 characters or fewer.';
    }

    // Enrolment date: same format rule as birthday
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrolment_date)
        || !checkdate((int)substr($enrolment_date, 5, 2), (int)substr($enrolment_date, 8, 2), (int)substr($enrolment_date, 0, 4))) {
        $errors['enrolment_date'] = 'Enrolment date must be a real date in YYYY-MM-DD format.';
    }

    if (empty($errors)) {
        try {
            // TODO(13): id comes from UUID(); the other 9 columns are placeholders
            $stmt = $conn->prepare(
                'INSERT INTO students
                 (id, first_name, middle_name, last_name, birthday, sex, email,
                  student_number, program, enrolment_date)
                 VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            // Empty middle name is stored as NULL
            $middle_param = ($middle_name === '') ? null : $middle_name;

            // TODO(14): nine strings -> 'sssssssss', in the same order as the placeholders
            $stmt->bind_param(
                'sssssssss',
                $first_name, $middle_param, $last_name, $birthday, $sex,
                $email, $student_number, $program, $enrolment_date
            );
            $stmt->execute();

            // No insert_id with a UUID key: affected_rows confirms the write
            if ($stmt->affected_rows === 1) {
                $success = 'Student created successfully!';
                $first_name = $middle_name = $last_name = $birthday = $sex = '';
                $email = $student_number = $program = $enrolment_date = '';
            } else {
                $errors['form'] = 'The student could not be saved.';
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // 1062 = duplicate entry on a UNIQUE column (email or student_number)
            if ($e->getCode() === 1062) {
                $errors['form'] = 'That email or student number is already registered.';
            } else {
                error_log('Insert failed: ' . $e->getMessage());
                $errors['form'] = 'Could not save the student. Please try again.';
            }
        }
    }
}

$page_title = 'Add Student';
include 'header.php';

// Small helper to print a red error under a field
function field_error(array $errors, string $key): void {
    if (isset($errors[$key])) {
        echo '<div class="text-danger small mt-1">' . htmlspecialchars($errors[$key]) . '</div>';
    }
}
?>

<h2 class="mb-3">Add New Student</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
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
        <button type="submit" class="btn btn-primary">Save Student</button>
        <a class="btn btn-secondary" href="index.php">Cancel</a>
    </div>
</form>

<?php
$conn->close();
include 'footer.php';