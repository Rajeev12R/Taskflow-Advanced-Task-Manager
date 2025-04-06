<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Get task statistics
$user_id = $_SESSION["id"];

// Get task counts by status
$status_query = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM tasks WHERE user_id = $user_id GROUP BY status");
$status_stats = [];
while($row = mysqli_fetch_assoc($status_query)) {
    $status_stats[$row['status']] = $row['count'];
}

// Get task counts by priority
$priority_query = mysqli_query($conn, "SELECT priority, COUNT(*) as count FROM tasks WHERE user_id = $user_id GROUP BY priority");
$priority_stats = [];
while($row = mysqli_fetch_assoc($priority_query)) {
    $priority_stats[$row['priority']] = $row['count'];
}

// Get tasks completed this week
$week_completed = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status = 'completed' AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)");
$completed_this_week = mysqli_fetch_assoc($week_completed)['count'];

// Get overdue tasks
$overdue_tasks = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE user_id = $user_id AND status != 'completed' AND due_date < CURDATE()");
$overdue_count = mysqli_fetch_assoc($overdue_tasks)['count'];
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#1A1A1A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-full bg-[#1A1A1A] text-white">
    <div class="min-h-screen flex">
        <!-- Sidebar (same as dashboard) -->
        <div class="w-64 bg-[#2D2D2D] border-r border-gray-700">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">TaskFlow</h1>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>
                    <a href="tasks.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-tasks w-5 h-5 mr-3"></i>
                        All Tasks
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-calendar w-5 h-5 mr-3"></i>
                        Calendar
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2 bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
                        <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                        Analytics
                    </a>
                </nav>
            </div>
            <!-- User Profile Section (same as dashboard) -->
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
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">Analytics Dashboard</h1>
                    <p class="text-gray-400">Track your task management performance</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-tasks text-blue-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-400 text-sm">Total Tasks</h3>
                                <p class="text-2xl font-bold"><?php echo array_sum($status_stats); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-check text-green-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-400 text-sm">Completed This Week</h3>
                                <p class="text-2xl font-bold"><?php echo $completed_this_week; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-clock text-yellow-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-400 text-sm">In Progress</h3>
                                <p class="text-2xl font-bold"><?php echo $status_stats['in_progress'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-400 text-sm">Overdue Tasks</h3>
                                <p class="text-2xl font-bold"><?php echo $overdue_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task Status Distribution -->
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <h2 class="text-lg font-bold mb-4">Task Status Distribution</h2>
                        <canvas id="statusChart" class="w-full"></canvas>
                    </div>

                    <!-- Task Priority Distribution -->
                    <div class="bg-[#2D2D2D] p-6 rounded-lg">
                        <h2 class="text-lg font-bold mb-4">Task Priority Distribution</h2>
                        <canvas id="priorityChart" class="w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed'],
                    datasets: [{
                        data: [
                            <?php echo $status_stats['pending'] ?? 0; ?>,
                            <?php echo $status_stats['in_progress'] ?? 0; ?>,
                            <?php echo $status_stats['completed'] ?? 0; ?>
                        ],
                        backgroundColor: ['#4B5563', '#FBBF24', '#34D399']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#D1D5DB'
                            }
                        }
                    }
                }
            });

            // Priority Chart
            new Chart(document.getElementById('priorityChart'), {
                type: 'bar',
                data: {
                    labels: ['Low', 'Medium', 'High'],
                    datasets: [{
                        label: 'Tasks by Priority',
                        data: [
                            <?php echo $priority_stats['low'] ?? 0; ?>,
                            <?php echo $priority_stats['medium'] ?? 0; ?>,
                            <?php echo $priority_stats['high'] ?? 0; ?>
                        ],
                        backgroundColor: ['#3B82F6', '#FBBF24', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#D1D5DB'
                            },
                            grid: {
                                color: '#374151'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#D1D5DB'
                            },
                            grid: {
                                color: '#374151'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 