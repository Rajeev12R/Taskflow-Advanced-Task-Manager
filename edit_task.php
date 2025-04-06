<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

$title = $description = $priority = $status = $due_date = "";
$title_err = $description_err = $priority_err = $status_err = $due_date_err = "";

if(isset($_GET["id"]) && !empty($_GET["id"])){
    $task_id = $_GET["id"];
    $user_id = $_SESSION["id"];
    
    // Verify task belongs to user and get current values
    $sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($task = mysqli_fetch_assoc($result)){
            $title = $task["title"];
            $description = $task["description"];
            $priority = $task["priority"];
            $status = $task["status"];
            $due_date = $task["due_date"];
        } else {
            header("location: tasks.php");
            exit();
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $task_id = $_POST["id"];
    
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))){
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }
    
    // Validate priority
    if(empty(trim($_POST["priority"]))){
        $priority_err = "Please select a priority.";
    } else {
        $priority = trim($_POST["priority"]);
    }
    
    // Validate status
    if(empty(trim($_POST["status"]))){
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }
    
    // Validate due date
    if(!empty(trim($_POST["due_date"]))){
        $due_date = trim($_POST["due_date"]);
    }
    
    // Check input errors before updating
    if(empty($title_err) && empty($description_err) && empty($priority_err) && empty($status_err)){
        $sql = "UPDATE tasks SET title=?, description=?, priority=?, status=?, due_date=? WHERE id=? AND user_id=?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssii", $title, $description, $priority, $status, $due_date, $task_id, $_SESSION["id"]);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: tasks.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#1A1A1A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - TaskFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="h-full bg-[#1A1A1A] text-white">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-[#2D2D2D] border-r border-gray-700">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">TaskFlow</h1>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>
                    <a href="tasks.php" class="flex items-center px-4 py-2 bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
                        <i class="fas fa-tasks w-5 h-5 mr-3"></i>
                        All Tasks
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-calendar w-5 h-5 mr-3"></i>
                        Calendar
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                        Analytics
                    </a>
                </nav>
            </div>
            <!-- User Profile Section -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <?php echo strtoupper(substr($_SESSION["username"], 0, 1)); ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center text-gray-300 hover:text-white transition-colors duration-200">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="max-w-2xl mx-auto">
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold">Edit Task</h1>
                        <p class="text-gray-400">Update your task details</p>
                    </div>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                        <input type="hidden" name="id" value="<?php echo $task_id; ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" 
                                class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo (!empty($title_err)) ? 'border-red-500' : ''; ?>">
                            <span class="text-red-500 text-sm"><?php echo $title_err; ?></span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                            <textarea name="description" rows="4" 
                                class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo (!empty($description_err)) ? 'border-red-500' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                            <span class="text-red-500 text-sm"><?php echo $description_err; ?></span>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Priority</label>
                                <select name="priority" class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="low" <?php if($priority == "low") echo "selected"; ?>>Low</option>
                                    <option value="medium" <?php if($priority == "medium") echo "selected"; ?>>Medium</option>
                                    <option value="high" <?php if($priority == "high") echo "selected"; ?>>High</option>
                                </select>
                                <span class="text-red-500 text-sm"><?php echo $priority_err; ?></span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Status</label>
                                <select name="status" class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="pending" <?php if($status == "pending") echo "selected"; ?>>Pending</option>
                                    <option value="in_progress" <?php if($status == "in_progress") echo "selected"; ?>>In Progress</option>
                                    <option value="completed" <?php if($status == "completed") echo "selected"; ?>>Completed</option>
                                </select>
                                <span class="text-red-500 text-sm"><?php echo $status_err; ?></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Due Date</label>
                            <input type="date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>" 
                                class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="text-red-500 text-sm"><?php echo $due_date_err; ?></span>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="tasks.php" class="px-4 py-2 text-gray-400 hover:text-white transition-colors duration-200">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors duration-200">
                                Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 