<?php
session_start();
require_once "config.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$response = ['success' => false, 'message' => '', 'imported' => 0, 'failed' => 0];

try {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['file'];
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileType !== 'csv') {
        throw new Exception('Only CSV files are allowed');
    }

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception('Failed to open file');
    }

    $headers = fgetcsv($handle);
    if ($headers === false) {
        throw new Exception('Failed to read CSV headers');
    }

    $expectedHeaders = ['Title', 'Description', 'Priority', 'Status', 'Due Date'];
    $headerValid = true;
    foreach ($expectedHeaders as $header) {
        if (!in_array($header, $headers)) {
            $headerValid = false;
            break;
        }
    }

    if (!$headerValid) {
        throw new Exception('Invalid CSV format. Please use the correct template.');
    }

    $sql = "INSERT INTO tasks (user_id, title, description, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception('Failed to prepare database statement');
    }

    mysqli_begin_transaction($conn);

    $user_id = $_SESSION['id'];
    $imported = 0;
    $failed = 0;

    while (($row = fgetcsv($handle)) !== false) {
        try {
            $title = trim($row[0]);
            $description = trim($row[1]);
            $priority = strtolower(trim($row[2]));
            $status = strtolower(trim($row[3]));
            $due_date = !empty($row[4]) ? date('Y-m-d', strtotime($row[4])) : null;

            if (empty($title)) {
                throw new Exception('Title is required');
            }

            if (!in_array($priority, ['low', 'medium', 'high'])) {
                $priority = 'medium';
            }

            if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
                $status = 'pending';
            }

            mysqli_stmt_bind_param($stmt, "isssss", $user_id, $title, $description, $priority, $status, $due_date);

            if (mysqli_stmt_execute($stmt)) {
                $imported++;
            } else {
                $failed++;
            }
        } catch (Exception $e) {
            $failed++;
            continue;
        }
    }

    mysqli_commit($conn);

    fclose($handle);
    mysqli_stmt_close($stmt);

    $response['success'] = true;
    $response['message'] = "Successfully imported $imported tasks" . ($failed > 0 ? " ($failed failed)" : "");
    $response['imported'] = $imported;
    $response['failed'] = $failed;

} catch (Exception $e) {
    if (isset($conn) && mysqli_get_connection_stats($conn)) {
        mysqli_rollback($conn);
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
mysqli_close($conn);
?> 