# Task Manager Web Application

A professional task management web application built with PHP, MySQL, HTML, CSS, and JavaScript. This application allows users to create, edit, and manage their tasks efficiently.

## Features

- User authentication (login/register)
- Create, edit, and delete tasks
- Task prioritization (low, medium, high)
- Task status tracking (pending, in progress, completed)
- Due date management
- Responsive design
- Secure password handling
- Session management

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP or similar web server stack
- Web browser with JavaScript enabled

## Installation

1. Clone or download this repository to your local machine
2. Place the files in your XAMPP's htdocs directory (e.g., `C:\xampp\htdocs\task-manager`)
3. Start XAMPP and ensure Apache and MySQL services are running
4. Open phpMyAdmin (http://localhost/phpmyadmin)
5. Create a new database named `task_manager`
6. Import the `database.sql` file into your newly created database
7. Configure the database connection in `config.php` if needed

## Configuration

The default database configuration in `config.php` is set for XAMPP's default settings:

- Host: localhost
- Username: root
- Password: (empty)
- Database: task_manager

If you're using different settings, update the `config.php` file accordingly.

## Usage

1. Access the application through your web browser: `http://localhost/task-manager`
2. Register a new account or login with existing credentials
3. Start creating and managing your tasks

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements to prevent SQL injection
- Session management for user authentication
- Input validation and sanitization
- XSS prevention through output escaping

## Best Practices

- Keep your PHP and MySQL versions updated
- Regularly backup your database
- Use strong passwords
- Keep your server software updated
- Monitor server logs for suspicious activity

## Contributing

Feel free to fork this repository and submit pull requests for any improvements or bug fixes.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
