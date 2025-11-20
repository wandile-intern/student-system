<?php
require 'config.php';
define('STATUS_ACTIVE', 1);
define('STATUS_PENDING', 0);
define('STATUS_INACTIVE', 2);

function getStudentById(PDO $pdo, int $id) {
    $sql = "SELECT * FROM students WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = $id ? getStudentById($pdo, $id) : null;
if (!$student) {
    die('Student not found.');
}

$status_map = [
  STATUS_ACTIVE => 'Active',
  STATUS_PENDING => 'Pending',
  STATUS_INACTIVE => 'Inactive'
];
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Profile</title></head><body class="container">
<h4><?=htmlspecialchars($student['full_name'])?> — Profile</h4>
<ul>
  <li><strong>Student ID:</strong> <?=htmlspecialchars($student['student_id'])?></li>
  <li><strong>Email:</strong> <?=htmlspecialchars($student['email'])?></li>
  <li><strong>DOB:</strong> <?=htmlspecialchars($student['dob'])?></li>
  <li><strong>Course:</strong> <?=htmlspecialchars($student['course'])?></li>
  <li><strong>Enrollment:</strong> <?=htmlspecialchars($student['enrollment_date'])?></li>
  <li><strong>Status:</strong> <?=htmlspecialchars($status_map[(int)$student['status']])?></li>
</ul>

<p>
  <a href="update.php?id=<?=urlencode($student['id'])?>">Edit</a>
  <a href="dashboard.php">Back to dashboard</a>
</p>
</body></html>

