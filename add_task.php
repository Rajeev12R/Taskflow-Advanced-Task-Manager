<?php
session_start();
require_once "config.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        $title = trim($_POST["title"]);
        $description = trim($_POST["description"]);
        $priority = trim($_POST["priority"]);
        $due_date = !empty($_POST["due_date"]) ? $_POST["due_date"] : NULL;
        
        if(empty($title)) {
            throw new Exception("Title is required");
        }
        
        $sql = "INSERT INTO tasks (user_id, title, description, priority, due_date, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "issss", $param_user_id, $param_title, $param_description, $param_priority, $param_due_date);
            
            $param_user_id = $_SESSION["id"];
            $param_title = $title;
            $param_description = $description;
            $param_priority = $priority;
            $param_due_date = $due_date;
            
            if(mysqli_stmt_execute($stmt)){
                echo json_encode([
                    'success' => true,
                    'message' => 'Task created successfully'
                ]);
            } else {
                throw new Exception("Failed to create task");
            }

            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Database error");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 