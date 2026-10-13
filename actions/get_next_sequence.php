<?php
// actions/get_next_sequence.php

// Include database and necessary models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Student.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if required parameters are provided
if (!isset($_GET['department_id']) || !isset($_GET['enrollment_year']) || !isset($_GET['enrollment_type'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters',
        'next_sequence' => 1
    ]);
    exit;
}

// Get parameters
$department_id = $_GET['department_id'];
$enrollment_year = $_GET['enrollment_year'];
$enrollment_type = $_GET['enrollment_type'];

// Validate parameters
if (empty($department_id) || empty($enrollment_year) || empty($enrollment_type)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Empty parameters are not allowed',
        'next_sequence' => 1
    ]);
    exit;
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get department code
    $departmentModel = new Department($db);
    $departmentModel->department_id = $department_id;
    if (!$departmentModel->readOne()) {
        throw new Exception("Department not found");
    }
    $department_code = $departmentModel->department_code;

    // Get the next sequential number
    $query = "SELECT MAX(SUBSTRING_INDEX(student_number, '/', -1)) as max_num 
              FROM students 
              WHERE student_number LIKE 'KUR/{$department_code}/{$enrollment_year}/{$enrollment_type}/%'";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $next_num = 1;
    if ($row && $row['max_num']) {
        $next_num = intval($row['max_num']) + 1;
    }

    // Return the next sequence
    echo json_encode([
        'status' => 'success',
        'department_code' => $department_code,
        'enrollment_year' => $enrollment_year,
        'enrollment_type' => $enrollment_type,
        'next_sequence' => $next_num
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'next_sequence' => 1
    ]);
}
?>