<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

if(isset($_GET["id"]) && !empty($_GET["id"])){
    $task_id = $_GET["id"];
    $user_id = $_SESSION["id"];
    
    $check_sql = "SELECT user_id FROM tasks WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $check_sql)){
        mysqli_stmt_bind_param($stmt, "i", $task_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task = mysqli_fetch_assoc($result);
        
        if($task && $task['user_id'] == $user_id){
            $delete_sql = "DELETE FROM tasks WHERE id = ?";
            if($delete_stmt = mysqli_prepare($conn, $delete_sql)){
                mysqli_stmt_bind_param($delete_stmt, "i", $task_id);
                if(mysqli_stmt_execute($delete_stmt)){
                    header("location: tasks.php");
                    exit();
                }
            }
        }
    }
}

header("location: tasks.php");
exit();
?>