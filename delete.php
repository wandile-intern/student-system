<?php
// delete.php
declare(strict_types=1);

// DEV: show errors while we debug. Remove or set to 0 in production.
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // must point to your PDO $pdo

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

try {
    // Start transaction so select, delete, and log are atomic
    $pdo->beginTransaction();

    // Lock the row for safety and fetch full row for logging
    $select = $pdo->prepare("SELECT * FROM students WHERE id = :id FOR UPDATE");
    $select->execute([':id' => $id]);
    $student = $select->fetch();

    if (!$student) {
        $pdo->rollBack();
        header('Location: dashboard.php?msg=notfound');
        exit;
    }

    // Delete the record
    $del = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $del->execute([':id' => $id]);

    // Prepare log entry (structured JSON)
    $logEntry = [
        'deleted_at'    => date('c'),
        'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'script'        => basename(__FILE__),
        'record'        => $student
    ];
    $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // Append to log (creates file if missing)
    $logFile = __DIR__ . '/deleted_records.log';
    if (file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX) === false) {
        // Logging failed — roll back to avoid silent data loss
        $pdo->rollBack();
        http_response_code(500);
        exit('Failed to write delete log. Check file permissions for ' . $logFile);
    }

    $pdo->commit();

    // Redirect back to dashboard with success flag
    header('Location: dashboard.php?msg=deleted');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    // In dev show message; in production log to a server-side error log instead
    exit('Server error: ' . $e->getMessage());
}

