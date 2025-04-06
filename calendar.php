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
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- FullCalendar Scripts -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-tasks w-5 h-5 mr-3"></i>
                        All Tasks
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-2 bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
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
                        <h1 class="text-2xl font-bold">Calendar View</h1>
                        <p class="text-gray-400">View and manage your tasks in calendar format</p>
                    </div>
                    <button onclick="openNewTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        New Task
                    </button>
                </div>

                <!-- Calendar Container -->
                <div class="bg-[#2D2D2D] rounded-lg p-6">
                    <div id="calendar" class="fc-theme-standard"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Details Modal -->
    <div id="taskDetailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="bg-[#2D2D2D] rounded-lg shadow-xl w-full max-w-lg mx-4 z-10">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold task-title"></h2>
                    <button onclick="closeTaskDetailsModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-400">Description</h3>
                        <p class="mt-1 task-description"></p>
                    </div>
                    <div class="flex space-x-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-400">Priority</h3>
                            <p class="mt-1 task-priority"></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-400">Status</h3>
                            <p class="mt-1 task-status"></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-400">Due Date</h3>
                            <p class="mt-1 task-due-date"></p>
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
                    // Add priority-based colors
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

            // Custom styling for calendar
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
    </script>

    <style>
        /* Calendar customization */
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