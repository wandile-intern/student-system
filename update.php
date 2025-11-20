<?php
require 'config.php';
define('STATUS_ACTIVE', 1);
define('STATUS_PENDING', 0);
define('STATUS_INACTIVE', 2);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) die('Invalid ID');

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id'=>$id]);
$student = $stmt->fetch();
if (!$student) die('Student not found');

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $enrollment_date = trim($_POST['enrollment_date'] ?? '');
    $status = intval($_POST['status'] ?? STATUS_ACTIVE);

    if ($full_name === '') $errors[] = 'Full name required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required';

    if (empty($errors)) {
        $sql = "UPDATE students SET full_name = :full_name, email = :email, course = :course, dob = :dob, enrollment_date = :enrollment_date, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name'=>$full_name,
            ':email'=>$email,
            ':course'=>$course,
            ':dob'=>$dob,
            ':enrollment_date'=>$enrollment_date,
            ':status'=>$status,
            ':id'=>$id
        ]);
        $success = 'Student updated';
        // refresh data
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        $student = $stmt->fetch();
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit Student</title></head><body class="container">
<h4>Edit <?=htmlspecialchars($student['full_name'])?></h4>
<?php if ($errors): ?><div style="color:red"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>";?></ul></div><?php endif;?>
<?php if ($success): ?><div style="color:green"><?=htmlspecialchars($success)?></div><?php endif;?>

<form method="post">
  <label>Full name <input name="full_name" value="<?=htmlspecialchars($student['full_name'])?>"></label><br>
  <label>Email <input name="email" value="<?=htmlspecialchars($student['email'])?>"></label><br>
  <label>DOB <input name="dob" type="date" value="<?=htmlspecialchars($student['dob'])?>"></label><br>
  <label>Course <input name="course" value="<?=htmlspecialchars($student['course'])?>"></label><br>
  <label>Enrollment <input name="enrollment_date" type="date" value="<?=htmlspecialchars($student['enrollment_date'])?>"></label><br>
  <label>Status
    <select name="status">
      <option value="1" <?= $student['status']==1 ? 'selected' : '' ?>>Active</option>
      <option value="0" <?= $student['status']==0 ? 'selected' : '' ?>>Pending</option>
      <option value="2" <?= $student['status']==2 ? 'selected' : '' ?>>Inactive</option>
    </select>
  </label><br>
  <button type="submit">Save</button>
</form>
</body></html>
