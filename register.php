<!-- register.php (top of file include config.php then the form logic) -->
<?php
require 'config.php';

/* Constants for status */
define('STATUS_ACTIVE', 1);
define('STATUS_PENDING', 0);
define('STATUS_INACTIVE', 2);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize + validate
    $full_name = trim($_POST['full_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $enrollment_date = trim($_POST['enrollment_date'] ?? '');

    // basic validation examples
    if ($full_name === '' || strlen($full_name) < 3) $errors[] = 'Full name required (min 3 chars).';
    if (!preg_match('/^[A-Za-z0-9\-]+$/', $student_id)) $errors[] = 'Student ID: only letters, numbers, hyphen.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!strtotime($dob)) $errors[] = 'Valid date of birth required.';
    if (!strtotime($enrollment_date)) $errors[] = 'Valid enrollment date required.';

    if (empty($errors)) {
        // prepared statement to prevent SQL injection
        $sql = "INSERT INTO students (full_name, student_id, email, dob, course, enrollment_date, status)
                VALUES (:full_name, :student_id, :email, :dob, :course, :enrollment_date, :status)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                ':full_name' => $full_name,
                ':student_id' => $student_id,
                ':email' => $email,
                ':dob' => $dob,
                ':course' => $course,
                ':enrollment_date' => $enrollment_date,
                ':status' => STATUS_ACTIVE
            ]);
            $success = 'Student registered successfully.';
        } catch (PDOException $e) {
            // handle duplicate keys and other errors
            if ($e->errorInfo[1] === 1062) {
                $errors[] = 'A student with that email or student ID already exists.';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student Registration</title>
  <!-- Materialize CSS CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body class="container">
  <h4>Student Registration (Admin)</h4>

  <?php if ($success): ?>
    <div class="card-panel green lighten-4 green-text text-darken-4"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="card-panel red lighten-4 red-text text-darken-4">
      <ul><?php foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="input-field">
      <input id="full_name" name="full_name" type="text" value="<?=htmlspecialchars($full_name ?? '')?>" required>
      <label for="full_name">Full Name</label>
    </div>

    <div class="input-field">
      <input id="student_id" name="student_id" type="text" value="<?=htmlspecialchars($student_id ?? '')?>" required>
      <label for="student_id">Student ID</label>
    </div>

    <div class="input-field">
      <input id="email" name="email" type="email" value="<?=htmlspecialchars($email ?? '')?>" required>
      <label for="email">Email</label>
    </div>

    <div class="input-field">
      <input id="dob" name="dob" type="date" value="<?=htmlspecialchars($dob ?? '')?>" required>
      <label for="dob" class="active">Date of Birth</label>
    </div>

    <div class="input-field">
      <input id="course" name="course" type="text" value="<?=htmlspecialchars($course ?? '')?>" required>
      <label for="course">Course of Study</label>
    </div>

    <div class="input-field">
      <input id="enrollment_date" name="enrollment_date" type="date" value="<?=htmlspecialchars($enrollment_date ?? '')?>" required>
      <label for="enrollment_date" class="active">Enrollment Date</label>
    </div>

    <button class="btn waves-effect waves-light" type="submit">Register</button>
  </form>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
