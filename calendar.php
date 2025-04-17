<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#1A1A1A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-tasks w-4 h-4 mr-3"></i>
                        All Tasks
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-2 text-sm bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
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
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-xl font-bold">Calendar View</h1>
                            <p class="text-sm text-gray-400">Manage your tasks in calendar view</p>
                        </div>
                        <button onclick="openNewTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 text-sm rounded-md flex items-center transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            New Task
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-[88px] p-6 overflow-y-auto">
                <div id="calendar" class="bg-[#2D2D2D] p-4 rounded-lg"></div>
            </div>
        </div>
    </div>

    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#2D2D2D] rounded-lg w-full max-w-md mx-4">
            <div class="p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold" id="modalTitle">Task Details</h3>
                    <button onclick="closeTaskModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="taskDetails" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Title</label>
                        <p id="taskTitle" class="text-sm"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                        <p id="taskDescription" class="text-sm"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Priority</label>
                            <p id="taskPriority" class="text-sm"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                            <p id="taskStatus" class="text-sm"></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Due Date</label>
                        <p id="taskDueDate" class="text-sm"></p>
                    </div>
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
                        $user_id = $_SESSION["id"];
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
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'standard',
                height: 'auto',
                contentHeight: 'auto',
                aspectRatio: 2,
                events: {
                    url: 'get_tasks_for_calendar.php',
                    method: 'GET',
                    failure: function() {
                        showToast('Error loading calendar events', 'error');
                    }
                },
                eventClick: function(info) {
                    showTaskDetails(info.event);
                },
                eventDidMount: function(info) {
                    const priorityColors = {
                        'high': '#EF4444',
                        'medium': '#F59E0B',
                        'low': '#3B82F6'
                    };
                    info.el.style.backgroundColor = priorityColors[info.event.extendedProps.priority] || '#3B82F6';
                },
                loading: function(isLoading) {
                    if (!isLoading) {
                        const events = calendar.getEvents();
                        if (events.length === 0) {
                            showToast('No tasks found for the selected period', 'info');
                        }
                    }
                },
                eventAdd: function(info) {
                    showToast('Task added to calendar', 'success');
                },
                eventChange: function(info) {
                    showToast('Task updated', 'success');
                },
                eventRemove: function(info) {
                    showToast('Task removed from calendar', 'info');
                },
                dateClick: function(info) {
                    openNewTaskModal();
                    const dateInput = document.querySelector('input[type="date"]');
                    if (dateInput) {
                        dateInput.value = info.dateStr;
                    }
                }
            });
            calendar.render();

            document.querySelectorAll('.fc-button').forEach(button => {
                button.classList.add('bg-[#1A1A1A]', 'text-white', 'border-gray-700', 'hover:bg-[#363636]');
            });
        });

        function closeTaskDetailsModal() {
            document.getElementById('taskDetailsModal').classList.add('hidden');
        }

        function showTaskDetails(event) {
            const modal = document.getElementById('taskDetailsModal');
            modal.querySelector('.task-title').textContent = event.title;
            modal.querySelector('.task-description').textContent = event.extendedProps.description || 'No description';
            modal.querySelector('.task-priority').textContent = event.extendedProps.priority || 'Not set';
            modal.querySelector('.task-status').textContent = event.extendedProps.status || 'Not set';
            modal.querySelector('.task-due-date').textContent = event.start ? event.start.toLocaleDateString() : 'No due date';
            modal.classList.remove('hidden');
        }

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

    <style>
        .fc {
            background: #2D2D2D;
            border-radius: 0.5rem;
            color: white;
        }
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #4B5563;
        }
        .fc-theme-standard .fc-scrollgrid {
            border-color: #4B5563;
        }
        .fc-day-today {
            background: #374151 !important;
        }
        .fc-button {
            background: #1A1A1A !important;
            border-color: #4B5563 !important;
            color: white !important;
        }
        .fc-button:hover {
            background: #363636 !important;
        }
        .fc-button-active {
            background: #2563EB !important;
        }
        .fc-event {
            border: none;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .fc-event-title {
            font-weight: 500;
        }
        .fc-toolbar-title {
            color: white;
        }
    </style>
</body>
</html> 