<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION["id"];
$events = [];

$sql = "SELECT id, title, description, status, priority, due_date FROM tasks WHERE user_id = ? AND due_date IS NOT NULL";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while($task = mysqli_fetch_assoc($result)){
        $events[] = [
            'id' => $task['id'],
            'title' => $task['title'],
            'start' => $task['due_date'],
            'allDay' => true,
            'extendedProps' => [
                'description' => $task['description'],
                'status' => $task['status'],
                'priority' => $task['priority']
            ]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($events);

mysqli_close($conn);

function getColorForPriority($priority) {
    switch($priority) {
        case 'high':
            return '#EF4444'; 
        case 'medium':
            return '#FBBF24'; 
        case 'low':
            return '#3B82F6'; 
        default:
            return '#3B82F6';
    }
} 