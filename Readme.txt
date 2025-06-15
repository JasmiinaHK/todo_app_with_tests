ToDo Application - Software Testing Project (SVVT @ IBU)

Project Overview

This repository contains a PHP-based ToDo list application developed and tested as part of the course "Software Verification, Validation, and Testing" at International Burch University. The application allows users to register, log in, add tasks, delete tasks, mark them as completed, and filter tasks.

Author

Jasmina Hasanović
jasmina.hasanovic@stu.ibu.edu.ba
Master Studies, Software Engineering
International Burch University

Project Purpose

The application was selected to apply software testing techniques such as static analysis, unit testing, integration testing, and system testing using both manual tools and automated PHPUnit scripts.

Project URL and GitHub Repository

Live App URL: http://todolistforyou.infy.uk

GitHub Repository: https://github.com/JasmiinaHK/todo_app_with_tests.git

The original version of the application is available on the following GitHub repository:
https://github.com/JasmiinaHK/ToDoApplication

How to Run the Application Locally

Prerequisites

PHP 7.x or newer

MySQL

XAMPP (for local server)

Composer (for PHPUnit)


Installation Steps

Clone the repository:

git clone https://github.com/JasmiinaHK/todo_app_with_tests.git

Move project to your XAMPP htdocs directory.

Start Apache and MySQL via XAMPP.

Import schema.sql into your local MySQL database.

Configure database credentials in config.php.

Open http://localhost/todo_app_with_tests/ in your browser.

Running Tests

Unit and Integration Testing

PHPUnit was used to automate testing. Tests are located in the /tests directory.


Run Tests

vendor/bin/phpunit tests/

Directory Structure

/tests
  /Integration
    - LoginTest.php
    - LogoutTest.php
    - TaskDeleteTest.php
    - TaskFilterTest.php
    - TaskStatusTest.php
    - TaskTest.php
    - UserLoginTest.php
    - UserRegistrationTest.php
    - UserRegistrationInvalidTest.php
  /Unit
    - TaskModelTest.php
    - UserModelTest.php


Test Coverage

The following features are covered by the test cases:

Homepage accessibility

User registration (positive and negative)

User login (positive, invalid credentials, and validation)

Task creation (valid, empty title, long title)

Task completion and status change

Task deletion (valid, non-existent, unauthorized)

Task filtering (All, Pending, Completed)

Logout session termination

Remember me functionality

Manual Testing Tool

WindSurf was used for UI validation and manual test confirmation.


Tools and Technologies Used:

PHP (Server-side language)

MySQL (Database)

HTML/CSS/JavaScript (Frontend)

PHPUnit (Test automation)

XAMPP (Local development server)

Google Chrome + DevTools (Browser testing)

WindSurf (Manual UI testing)

Git & GitHub (Version control and project hosting)


Notes

The application and all test scripts were developed solely by Jasmina Hasanović.

The application was not originally developed for this course, but the entire testing framework and methodology were built exclusively for the course project.


License

This project is private and owned by Jasmina Hasanović. For academic use only.

