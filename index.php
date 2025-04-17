<?php
session_start();
require_once "config.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: tasks.php");
    exit;
}

if(isset($_SESSION["registration_success"])) {
    echo "<script>alert('Registration successful! Please login.');</script>";
    unset($_SESSION["registration_success"]);
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#1A1A1A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Your Modern Task Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full bg-[#1A1A1A] text-white">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-4xl font-bold tracking-tight mb-2">TaskFlow</h1>
            <h2 class="text-center text-lg text-gray-400 mb-8">Your thoughts and tasks, organized.</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-[#2D2D2D] py-8 px-4 shadow-xl rounded-lg sm:px-10">
                <div class="flex justify-center space-x-4 mb-8">
                    <button id="loginTab" class="text-lg font-medium px-4 py-2 rounded-md transition-all duration-200 bg-[#1A1A1A] text-white">
                        Login
                    </button>
                    <button id="registerTab" class="text-lg font-medium px-4 py-2 rounded-md transition-all duration-200 text-gray-400 hover:text-white">
                        Register
                    </button>
                </div>

                <div id="loginForm" class="form-fade-in">
                    <form action="login.php" method="post" class="space-y-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                                <input type="text" id="username" name="username" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-500"></i>
                                </div>
                                <input type="password" id="password" name="password" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Sign in
                            </button>
                        </div>
                    </form>
                </div>

                <div id="registerForm" class="hidden form-fade-in">
                    <form action="register.php" method="post" class="space-y-6">
                        <div>
                            <label for="reg_username" class="block text-sm font-medium text-gray-300">Username</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                                <input type="text" id="reg_username" name="username" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <label for="reg_email" class="block text-sm font-medium text-gray-300">Email</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-500"></i>
                                </div>
                                <input type="email" id="reg_email" name="email" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <label for="reg_password" class="block text-sm font-medium text-gray-300">Password</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-500"></i>
                                </div>
                                <input type="password" id="reg_password" name="password" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-500"></i>
                                </div>
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                    class="bg-[#1A1A1A] block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white">
                            </div>
                        </div>

                        <div>
                            <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-sm text-gray-400">
            <p>By signing up, you agree to our Terms of Service and Privacy Policy</p>
        </div>
    </div>

    <script>
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

        loginTab.addEventListener('click', () => {
            loginTab.classList.add('bg-[#1A1A1A]', 'text-white');
            loginTab.classList.remove('text-gray-400');
            registerTab.classList.remove('bg-[#1A1A1A]', 'text-white');
            registerTab.classList.add('text-gray-400');
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        });

        registerTab.addEventListener('click', () => {
            registerTab.classList.add('bg-[#1A1A1A]', 'text-white');
            registerTab.classList.remove('text-gray-400');
            loginTab.classList.remove('bg-[#1A1A1A]', 'text-white');
            loginTab.classList.add('text-gray-400');
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });
    </script>
</body>
</html> 