<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#1A1A1A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - All Tasks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FullCalendar for Calendar View -->
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js'></script>
    <!-- Custom JavaScript -->
    <script src="js/script.js" defer></script>
</head>
<body class="h-full bg-[#1A1A1A] text-white">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-[#2D2D2D] border-r border-gray-700">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">TaskFlow</h1>
                <nav class="space-y-2">
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
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
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-2xl font-bold">All Tasks</h1>
                        <p class="text-gray-400">Manage and organize your tasks</p>
                    </div>
                    <div class="flex space-x-4">
                        <div class="flex space-x-2">
                            <input type="text" id="searchTasks" placeholder="Search tasks..." 
                                class="bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select id="statusFilter" class="bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <select id="priorityFilter" class="bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="all">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <button onclick="openNewTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            New Task
                        </button>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="space-y-4">
                    <?php while($task = mysqli_fetch_assoc($result)): 
                        $status_color = [
                            'pending' => 'gray',
                            'in_progress' => 'yellow',
                            'completed' => 'green'
                        ][$task['status']];
                        
                        $priority_color = [
                            'low' => 'blue',
                            'medium' => 'yellow',
                            'high' => 'red'
                        ][$task['priority']];
                    ?>
                    <div class="task-card bg-[#2D2D2D] p-6 rounded-lg hover:bg-[#363636] transition-colors duration-200" data-task-id="<?php echo $task['id']; ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleTaskStatus(<?php echo $task['id']; ?>, '<?php echo $task['status']; ?>')" 
                                    class="task-checkbox w-6 h-6 rounded-full border-2 border-gray-500 flex items-center justify-center hover:border-blue-500 transition-colors duration-200 <?php echo $task['status'] === 'completed' ? 'bg-green-500 border-green-500' : ''; ?>">
                                    <?php if($task['status'] === 'completed'): ?>
                                        <i class="fas fa-check text-white text-sm"></i>
                                    <?php endif; ?>
                                </button>
                                <div>
                                    <h3 class="text-lg font-medium task-title <?php echo $task['status'] === 'completed' ? 'line-through text-gray-400' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h3>
                                    <p class="text-gray-400 task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="task-priority px-3 py-1 text-sm rounded-full bg-<?php echo $priority_color; ?>-500 bg-opacity-20 text-<?php echo $priority_color; ?>-500">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                                <span class="task-status px-3 py-1 text-sm rounded-full bg-<?php echo $status_color; ?>-500 bg-opacity-20 text-<?php echo $status_color; ?>-500">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                                <?php if($task['due_date']): ?>
                                <span class="text-sm text-gray-400">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                </span>
                                <?php endif; ?>
                                <div class="flex items-center space-x-2">
                                    <button onclick="editTask(<?php echo $task['id']; ?>)" class="text-gray-400 hover:text-white transition-colors duration-200">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" class="text-gray-400 hover:text-red-500 transition-colors duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="modalOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    <div id="taskModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="bg-[#2D2D2D] rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Create New Task</h2>
                    <button onclick="closeTaskModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="add_task.php" method="post" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Title</label>
                        <input type="text" name="title" required
                            class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                        <textarea name="description" rows="4" required
                            class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Priority</label>
                            <select name="priority" required
                                class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Due Date</label>
                            <input type="date" name="due_date" required
                                class="w-full bg-[#1A1A1A] text-white px-4 py-2 rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeTaskModal()" 
                            class="px-4 py-2 text-gray-400 hover:text-white transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors duration-200">
                            Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 