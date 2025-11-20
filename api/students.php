<?php
// C:\xampp\htdocs\student-system\api\students.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // allow React (localhost:3000) to fetch

require __DIR__ . '/../config.php'; // link to your database config

try {
    $stmt = $pdo->query("SELECT id, full_name, student_id, email, course, enrollment_date, status FROM students ORDER BY full_name");
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
