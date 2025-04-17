document.addEventListener('DOMContentLoaded', function () {

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });


    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });


    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function () {
            const selectedDate = new Date(this.value);
            const today = new Date();

            if (selectedDate < today) {
                this.setCustomValidity('Due date cannot be in the past');
            } else {
                this.setCustomValidity('');
            }
        });
    });


    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });


    const statusSelects = document.querySelectorAll('.task-status');
    statusSelects.forEach(select => {
        select.addEventListener('change', function () {
            const taskId = this.dataset.taskId;
            const newStatus = this.value;

            fetch(`update_status.php?id=${taskId}&status=${newStatus}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = this.closest('tr').querySelector('.status-badge');
                        badge.className = `badge bg-${getStatusColor(newStatus)} status-badge`;
                        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });


    const searchInput = document.getElementById('searchTasks');
    const statusFilter = document.getElementById('statusFilter');
    const taskCards = document.querySelectorAll('.task-card');

    function filterTasks() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        taskCards.forEach(card => {
            const title = card.querySelector('.task-title').textContent.toLowerCase();
            const description = card.querySelector('.task-description').textContent.toLowerCase();
            const status = card.querySelector('.task-status').textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || status === statusValue.toLowerCase();

            card.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
        });
    }

    if (searchInput && statusFilter) {
        searchInput.addEventListener('input', filterTasks);
        statusFilter.addEventListener('change', filterTasks);
    }
});

function getStatusColor(status) {
    switch (status) {
        case 'completed':
            return 'success';
        case 'in_progress':
            return 'primary';
        default:
            return 'secondary';
    }
}

function getPriorityColor(priority) {
    switch (priority) {
        case 'high':
            return 'danger';
        case 'medium':
            return 'warning';
        default:
            return 'info';
    }
}

const toastTypes = {
    success: { bgColor: 'bg-green-500', icon: 'fas fa-check-circle' },
    error: { bgColor: 'bg-red-500', icon: 'fas fa-exclamation-circle' },
    warning: { bgColor: 'bg-yellow-500', icon: 'fas fa-exclamation-triangle' },
    info: { bgColor: 'bg-blue-500', icon: 'fas fa-info-circle' }
};

function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    const { bgColor, icon } = toastTypes[type];

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center space-x-2 animate-slide-up`;
    toast.innerHTML = `
        <i class="${icon}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);


    toast.style.animation = 'slideUp 0.3s ease-out';

    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, duration);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes slideDown {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(100%); opacity: 0; }
    }
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }
`;
document.head.appendChild(style);

function toggleTaskStatus(taskId, currentStatus) {
    const newStatus = currentStatus === 'completed' ? 'pending' : 'completed';

    fetch('update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: `task_id=${taskId}&status=${newStatus}`
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Oops, we haven't gotten JSON!");
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (!taskCard) return;

                const checkbox = taskCard.querySelector('.task-checkbox');
                const title = taskCard.querySelector('.task-title');
                const statusBadge = taskCard.querySelector('.task-status');

                if (newStatus === 'completed') {
                    checkbox.classList.add('bg-green-500', 'border-green-500');
                    checkbox.innerHTML = '<i class="fas fa-check text-white text-sm"></i>';
                    title.classList.add('line-through', 'text-gray-400');
                    statusBadge.textContent = 'Completed';
                    statusBadge.className = 'task-status px-3 py-1 text-sm rounded-full bg-green-500 bg-opacity-20 text-green-500';
                    showToast('Task marked as completed', 'success');
                } else {
                    checkbox.classList.remove('bg-green-500', 'border-green-500');
                    checkbox.innerHTML = '';
                    title.classList.remove('line-through', 'text-gray-400');
                    statusBadge.textContent = 'Pending';
                    statusBadge.className = 'task-status px-3 py-1 text-sm rounded-full bg-gray-500 bg-opacity-20 text-gray-500';
                    showToast('Task marked as pending', 'info');
                }

                updateTaskCounters();
            } else {
                showToast(data.message || 'Failed to update task status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating task', 'error');
        });
}

function initializeTaskFilters() {
    const searchInput = document.getElementById('searchTasks');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const taskCards = document.querySelectorAll('.task-card');

    if (!searchInput || !statusFilter || !priorityFilter) return;

    function filterTasks() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const priorityValue = priorityFilter.value.toLowerCase();

        taskCards.forEach(card => {
            const title = card.querySelector('.task-title').textContent.toLowerCase();
            const description = card.querySelector('.task-description').textContent.toLowerCase();
            const status = card.querySelector('.task-status').textContent.toLowerCase();
            const priority = card.querySelector('.task-priority').textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || status.includes(statusValue);
            const matchesPriority = priorityValue === 'all' || priority.includes(priorityValue);

            card.style.display = matchesSearch && matchesStatus && matchesPriority ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTasks);
    statusFilter.addEventListener('change', filterTasks);
    priorityFilter.addEventListener('change', filterTasks);
}

function initializeTaskModal() {
    const modal = document.getElementById('taskModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const form = modal?.querySelector('form');

    if (!modal || !modalOverlay) return;

    window.openNewTaskModal = function () {
        modal.classList.remove('hidden');
        modalOverlay.classList.remove('hidden');
        if (form) form.reset();
    };

    window.closeTaskModal = function () {
        modal.classList.add('hidden');
        modalOverlay.classList.add('hidden');
    };

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeTaskModal();
        }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(this);

            fetch('add_task.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError("Response was not JSON");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('Task created successfully', 'success');
                        closeTaskModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Failed to create task');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast(error.message || 'An error occurred while creating task', 'error');
                });
        });
    }
}

function initializeTaskActions() {
    window.editTask = function (taskId) {
        window.location.href = `edit_task.php?id=${taskId}`;
    };

    window.deleteTask = function (taskId) {
        const confirmDelete = () => {
            fetch(`delete_task.php?id=${taskId}`, {
                method: 'GET'
            })
                .then(response => {
                    if (response.ok) {
                        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                        if (taskCard) {
                            taskCard.remove();
                            updateTaskCounters();
                            showToast('Task deleted successfully', 'success');
                        }
                    } else {
                        showToast('Failed to delete task', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while deleting task', 'error');
                });
        };

        const confirmToast = document.createElement('div');
        confirmToast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex flex-col space-y-2';
        confirmToast.innerHTML = `
            <p class="font-medium">Are you sure you want to delete this task?</p>
            <div class="flex justify-end space-x-2">
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600" onclick="this.parentElement.parentElement.remove()">Cancel</button>
                <button class="px-3 py-1 bg-red-500 rounded hover:bg-red-600" id="confirmDelete">Delete</button>
            </div>
        `;
        document.body.appendChild(confirmToast);

        document.getElementById('confirmDelete').onclick = () => {
            confirmToast.remove();
            confirmDelete();
        };
    };
}

function updateTaskCounters() {
    fetch('get_task_stats.php', {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Oops, we haven't gotten JSON!");
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const elements = {
                    totalTasks: document.getElementById('totalTasks'),
                    completedThisWeek: document.getElementById('completedThisWeek'),
                    inProgress: document.getElementById('inProgress'),
                    overdueTasks: document.getElementById('overdueTasks')
                };

                if (elements.totalTasks) elements.totalTasks.textContent = data.stats.total;
                if (elements.completedThisWeek) elements.completedThisWeek.textContent = data.stats.completed_this_week;
                if (elements.inProgress) elements.inProgress.textContent = data.stats.status.in_progress;
                if (elements.overdueTasks) elements.overdueTasks.textContent = data.stats.overdue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading task statistics', 'error');
        });
}

function initializeCalendar() {
    const calendar = document.getElementById('calendar');
    if (calendar) {
        const calendarInstance = new FullCalendar.Calendar(calendar, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: 'get_tasks_for_calendar.php',
            eventClick: function (info) {
                const task = info.event;
                showTaskDetails(task);
            }
        });
        calendarInstance.render();
    }
}

function initializeAnalytics() {
    fetch('get_task_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending', 'In Progress', 'Completed'],
                            datasets: [{
                                data: [
                                    data.stats.status.pending,
                                    data.stats.status.in_progress,
                                    data.stats.status.completed
                                ],
                                backgroundColor: ['#9CA3AF', '#FCD34D', '#34D399']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                }

                const priorityCtx = document.getElementById('priorityChart');
                if (priorityCtx) {
                    new Chart(priorityCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Low', 'Medium', 'High'],
                            datasets: [{
                                label: 'Tasks by Priority',
                                data: [
                                    data.stats.priority.low,
                                    data.stats.priority.medium,
                                    data.stats.priority.high
                                ],
                                backgroundColor: ['#60A5FA', '#FCD34D', '#EF4444']
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#fff'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#fff'
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
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Initializing TaskFlow...');

    initializeTaskFilters();
    initializeTaskModal();
    initializeTaskActions();
    initializeImportForm();
    updateTaskCardLayout();
    updateModalResponsiveness();

    document.querySelectorAll('.task-card').forEach(card => {
        if (!card.hasAttribute('data-task-id')) {
            const taskId = card.querySelector('[onclick*="toggleTaskStatus"]')
                ?.getAttribute('onclick')
                ?.match(/\d+/)?.[0];
            if (taskId) {
                card.setAttribute('data-task-id', taskId);
            }
        }
    });

    console.log('TaskFlow initialization complete.');
});

function showTaskDetails(event) {
    const modal = document.getElementById('taskDetailsModal');
    const title = modal.querySelector('.task-title');
    const description = modal.querySelector('.task-description');
    const priority = modal.querySelector('.task-priority');
    const status = modal.querySelector('.task-status');
    const dueDate = modal.querySelector('.task-due-date');

    title.textContent = event.title;
    description.textContent = event.extendedProps.description;
    priority.textContent = event.extendedProps.priority;
    status.textContent = event.extendedProps.status;
    dueDate.textContent = event.start.toLocaleDateString();

    modal.classList.remove('hidden');
}

function openImportModal() {
    const modal = document.getElementById('importModal');
    const modalOverlay = document.getElementById('modalOverlay');
    if (modal && modalOverlay) {
        modal.classList.remove('hidden');
        modalOverlay.classList.remove('hidden');
    }
}

function closeImportModal() {
    const modal = document.getElementById('importModal');
    const modalOverlay = document.getElementById('modalOverlay');
    if (modal && modalOverlay) {
        modal.classList.add('hidden');
        modalOverlay.classList.add('hidden');

        const form = document.getElementById('importForm');
        if (form) form.reset();
    }
}

function initializeImportForm() {
    const form = document.getElementById('importForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const fileInput = this.querySelector('input[type="file"]');

        if (!fileInput.files.length) {
            showToast('Please select a file to import', 'error');
            return;
        }

        const file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) { 
            showToast('File size must be less than 5MB', 'error');
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing...';

        fetch('import_tasks.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeImportModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Import failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'Failed to import tasks', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Initializing TaskFlow...');

    initializeSidebar();
    initializeTaskFilters();
    initializeTaskModal();
    initializeTaskActions();
    initializeImportForm();
    updateTaskCardLayout();
    updateModalResponsiveness();

}); 