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
        <!-- Sidebar with fixed height -->
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
                    <a href="calendar.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#1A1A1A] rounded-md transition-colors duration-200">
                        <i class="fas fa-calendar w-4 h-4 mr-3"></i>
                        Calendar
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2 text-sm bg-[#1A1A1A] text-white rounded-md transition-colors duration-200">
                        <i class="fas fa-chart-bar w-4 h-4 mr-3"></i>
                        Analytics
                    </a>
                </nav>
                
                <!-- User Profile Section -->
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

        <!-- Main Content with fixed header and scrollable content -->
        <div class="flex-1 ml-64">
            <!-- Fixed Header -->
            <div class="bg-[#1A1A1A] fixed top-0 right-0 left-64 z-10">
                <div class="p-6 border-b border-gray-700">
                    <h1 class="text-xl font-bold">Analytics Dashboard</h1>
                    <p class="text-sm text-gray-400">Track your task management performance</p>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="mt-[88px] p-6 overflow-y-auto">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-tasks text-blue-500 text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-gray-400 text-xs">Total Tasks</h3>
                                <p class="text-xl font-bold"><?php echo array_sum($status_stats); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-check text-green-500 text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-gray-400 text-xs">Completed This Week</h3>
                                <p class="text-xl font-bold"><?php echo $completed_this_week; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-clock text-yellow-500 text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-gray-400 text-xs">In Progress</h3>
                                <p class="text-xl font-bold"><?php echo $status_stats['in_progress'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-500 bg-opacity-20 rounded-md">
                                <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-gray-400 text-xs">Overdue Tasks</h3>
                                <p class="text-xl font-bold"><?php echo $overdue_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task Status Distribution -->
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <h2 class="text-base font-bold mb-4">Task Status Distribution</h2>
                        <canvas id="statusChart" class="w-full"></canvas>
                    </div>

                    <!-- Task Priority Distribution -->
                    <div class="bg-[#2D2D2D] p-4 rounded-lg">
                        <h2 class="text-base font-bold mb-4">Task Priority Distribution</h2>
                        <canvas id="priorityChart" class="w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
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
        // Add these functions before the existing chart initialization code
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

        // Close modal when clicking outside
        document.getElementById('profileModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });

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