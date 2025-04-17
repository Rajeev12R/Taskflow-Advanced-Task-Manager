<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $task_id = $_POST["task_id"];
    $status = $_POST["status"];
    
    $check_sql = "SELECT user_id FROM tasks WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $check_sql)){
        mysqli_stmt_bind_param($stmt, "i", $task_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task = mysqli_fetch_assoc($result);
        
        if($task && $task['user_id'] == $_SESSION["id"]){
            $update_sql = "UPDATE tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            if($update_stmt = mysqli_prepare($conn, $update_sql)){
                mysqli_stmt_bind_param($update_stmt, "si", $status, $task_id);
                
                if(mysqli_stmt_execute($update_stmt)){
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error updating task']);
                }
                
                mysqli_stmt_close($update_stmt);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
} 