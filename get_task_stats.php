<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION["id"];

$status_query = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM tasks WHERE user_id = $user_id GROUP BY status");
$status_stats = [
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0
];

while($row = mysqli_fetch_assoc($status_query)) {
    $status_stats[$row['status']] = (int)$row['count'];
}

$priority_query = mysqli_query($conn, "SELECT priority, COUNT(*) as count FROM tasks WHERE user_id = $user_id GROUP BY priority");
$priority_stats = [
    'low' => 0,
    'medium' => 0,
    'high' => 0
];

while($row = mysqli_fetch_assoc($priority_query)) {
    $priority_stats[$row['priority']] = (int)$row['count'];
}

$week_completed = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status = 'completed' AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)");
$completed_this_week = (int)mysqli_fetch_assoc($week_completed)['count'];

$overdue_tasks = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status != 'completed' AND due_date < CURDATE()");
$overdue_count = (int)mysqli_fetch_assoc($overdue_tasks)['count'];

$response = [
    'success' => true,
    'stats' => [
        'status' => $status_stats,
        'priority' => $priority_stats,
        'completed_this_week' => $completed_this_week,
        'overdue' => $overdue_count,
        'total' => array_sum($status_stats)
    ]
];

header('Content-Type: application/json');
echo json_encode($response); 