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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js'></script>
    <script src="js/script.js" defer></script>
</head>
<body class="h-full bg-[#1A1A1A] text-white">
    <div class="min-h-screen flex">
        <div class="w-64 bg-[#2D2D2D] border-r border-gray-700 h-screen fixed left-0">
            <div class="p-4 h-full flex flex-col">
                <h1 class="text-lg font-bold mb-6">TaskFlow</h1>
                <nav class="space-y-2">
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-home w-4 h-4 mr-3"></i>
                        Dashboard
                    </a>
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-sm bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
                        <i class="fas fa-tasks w-4 h-4 mr-3"></i>
                        All Tasks
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-calendar w-4 h-4 mr-3"></i>
                        Calendar
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-chart-bar w-4 h-4 mr-3"></i>
                        Analytics
                    </a>
                </nav>
                
                <div class="mt-auto p-4 border-t border-gray-700">
                    <div class="flex items-center mb-4 cursor-pointer hover:bg-[#363636] p-2 rounded-md transition-colors duration-200" onclick="openProfileModal()">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-sm">
                            <?php echo strtoupper(substr($_SESSION["username"], 0, 1)); ?>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                        </div>
                    </div>
                    <a href="logout.php" class="flex items-center text-sm text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-sign-out-alt w-4 h-4 mr-3"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-1 ml-64">
            <div class="bg-[#1A1A1A] fixed top-0 right-0 left-64 z-10">
                <div class="p-6 border-b border-gray-700">
                    <?php if(isset($_GET['error']) && $_GET['error'] === 'export_failed'): ?>
                    <div class="mb-4 bg-red-500 bg-opacity-20 text-red-500 px-4 py-2 rounded-md text-sm">
                        <p>Failed to export tasks. Please try again.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-xl font-bold">All Tasks</h1>
                            <p class="text-sm text-gray-400">Manage and organize your tasks</p>
                        </div>
                        <div class="flex space-x-4">
                            <div class="relative">
                                <button id="exportDropdownBtn" type="button" class="bg-[#2D2D2D] text-white px-3 py-2 text-sm rounded-md flex items-center space-x-2 hover:bg-[#363636] transition-colors duration-200">
                                    <i class="fas fa-file-export"></i>
                                    <span>Export/Import</span>
                                    <i class="fas fa-chevron-down ml-2" id="dropdownArrow"></i>
                                </button>
                                <div id="exportDropdownMenu" class="hidden absolute right-0 text-sm mt-2 w-40 bg-[#2D2D2D] rounded-md shadow-lg z-50">
                                    <a href="export_tasks.php?format=csv" class="block px-2 py-2 text-white hover:bg-[#363636] transition-colors duration-200">
                                        <i class="fas fa-file-csv mr-2"></i>Export as CSV
                                    </a>
                                    <a href="export_tasks.php?format=pdf" class="block px-2 py-2 text-white hover:bg-[#363636] transition-colors duration-200">
                                        <i class="fas fa-file-pdf mr-2"></i>Export as PDF
                                    </a>
                                    <button onclick="openImportModal()" class="w-full text-left px-2 py-2 text-white hover:bg-[#363636] transition-colors duration-200">
                                        <i class="fas fa-file-import mr-2"></i>Import Tasks
                                    </button>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <input type="text" id="searchTasks" placeholder="Search tasks..." 
                                    class="bg-[#1A1A1A] text-white px-3 py-2 text-sm rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <select id="statusFilter" class="bg-[#1A1A1A] text-white px-3 py-2 text-sm rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <select id="priorityFilter" class="bg-[#1A1A1A] text-white px-3 py-2 text-sm rounded-md border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <button onclick="openNewTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 text-sm rounded-md flex items-center transition-colors duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                New Task
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-[120px] p-6 overflow-y-auto">
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
                    <div class="task-card bg-[#2D2D2D] p-4 rounded-lg hover:bg-[#363636] transition-colors duration-200" data-task-id="<?php echo $task['id']; ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleTaskStatus(<?php echo $task['id']; ?>, '<?php echo $task['status']; ?>')" 
                                    class="task-checkbox w-5 h-5 rounded-full border-2 border-gray-500 flex items-center justify-center hover:border-blue-500 transition-colors duration-200 <?php echo $task['status'] === 'completed' ? 'bg-green-500 border-green-500' : ''; ?>">
                                    <?php if($task['status'] === 'completed'): ?>
                                        <i class="fas fa-check text-white text-xs"></i>
                                    <?php endif; ?>
                                </button>
                                <div>
                                    <h3 class="text-base font-medium task-title <?php echo $task['status'] === 'completed' ? 'line-through text-gray-400' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-400 task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="task-priority px-2 py-1 text-xs rounded-full bg-<?php echo $priority_color; ?>-500 bg-opacity-20 text-<?php echo $priority_color; ?>-500">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                                <span class="task-status px-2 py-1 text-xs rounded-full bg-<?php echo $status_color; ?>-500 bg-opacity-20 text-<?php echo $status_color; ?>-500">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                                <?php if($task['due_date']): ?>
                                <span class="text-xs text-gray-400">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                </span>
                                <?php endif; ?>
                                <div class="flex items-center space-x-2">
                                    <button onclick="editTask(<?php echo $task['id']; ?>)" class="text-gray-400 hover:text-white transition-colors duration-200">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button onclick="deleteTask(<?php echo $task['id']; ?>)" class="text-gray-400 hover:text-red-500 transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
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

    <div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg w-full max-w-md mx-4">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold">Import Tasks</h3>
                        <button onclick="closeImportModal()" class="text-gray-400 hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="importForm" class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Select CSV File</label>
                            <input type="file" name="file" accept=".csv" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="text-sm text-gray-400">
                            <p>File must be a CSV with the following columns:</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>Title (required)</li>
                                <li>Description</li>
                                <li>Priority (low/medium/high)</li>
                                <li>Status (pending/in_progress/completed)</li>
                                <li>Due Date (YYYY-MM-DD)</li>
                            </ul>
                        </div>
                        <div class="flex justify-end space-x-2 mt-6">
                            <button type="button" onclick="closeImportModal()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="profileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#2D2D2D] rounded-lg w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Profile Details</h3>
                    <button onclick="closeProfileModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-6">
                    <div class="flex items-center justify-center">
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center text-2xl">
                            <?php echo strtoupper(substr($_SESSION["username"], 0, 1)); ?>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Username</label>
                            <p class="text-base bg-[#1A1A1A] p-3 rounded-md"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Account Created</label>
                            <p class="text-base bg-[#1A1A1A] p-3 rounded-md">
                                <?php 
                                    $created_date = new DateTime($_SESSION["created_at"]);
                                    echo $created_date->format('F j, Y');
                                ?>
                            </p>
                        </div>
                        <?php
                        $status_query = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM tasks WHERE user_id = $user_id GROUP BY status");
                        $status_stats = [];
                        while($row = mysqli_fetch_assoc($status_query)) {
                            $status_stats[$row['status']] = $row['count'];
                        }
                        ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Tasks Statistics</label>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-[#1A1A1A] p-3 rounded-md">
                                    <p class="text-lg font-bold"><?php echo array_sum($status_stats); ?></p>
                                    <p class="text-xs text-gray-400">Total Tasks</p>
                                </div>
                                <div class="bg-[#1A1A1A] p-3 rounded-md">
                                    <p class="text-lg font-bold"><?php echo $status_stats['completed'] ?? 0; ?></p>
                                    <p class="text-xs text-gray-400">Completed</p>
                                </div>
                                <div class="bg-[#1A1A1A] p-3 rounded-md">
                                    <p class="text-lg font-bold"><?php echo $status_stats['in_progress'] ?? 0; ?></p>
                                    <p class="text-xs text-gray-400">In Progress</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBtn = document.getElementById('exportDropdownBtn');
            const dropdownMenu = document.getElementById('exportDropdownMenu');
            const dropdownArrow = document.getElementById('dropdownArrow');
            let isOpen = false;

            dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                isOpen = !isOpen;
                if (isOpen) {
                    dropdownMenu.classList.remove('hidden');
                    dropdownArrow.classList.add('transform', 'rotate-180');
                } else {
                    dropdownMenu.classList.add('hidden');
                    dropdownArrow.classList.remove('transform', 'rotate-180');
                }
            });

            document.addEventListener('click', function(e) {
                if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                    dropdownArrow.classList.remove('transform', 'rotate-180');
                    isOpen = false;
                }
            });
        });

        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        document.getElementById('profileModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });

    </script>
</body>
</html> 