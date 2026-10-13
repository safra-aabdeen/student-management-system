<?php
// actions/save_semester.php

header('Content-Type: application/json');

// Include database and semester model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Semester.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create new semester object
$semester = new Semester($db);

// Get posted data
$semester->semester_id = isset($_POST['semester_id']) && !empty($_POST['semester_id']) ? $_POST['semester_id'] : null;
$semester->department_id = $_POST['department_id'];
$semester->year_number = $_POST['year_number'];
$semester->semester_number = $_POST['semester_number'];
$semester->start_date = $_POST['start_date'];
$semester->end_date = $_POST['end_date'];
$semester->is_active = isset($_POST['is_active']) ? $_POST['is_active'] : 0;

// Create or update semester
if (empty($semester->semester_id)) {
    // Create semester
    if ($semester->create()) {
        echo json_encode(array('message' => 'Semester created successfully.'));
    } else {
        echo json_encode(array('message' => 'Error: Unable to create semester.'));
    }
} else {
    // Update semester
    if ($semester->update()) {
        echo json_encode(array('message' => 'Semester updated successfully.'));
    } else {
        echo json_encode(array('message' => 'Error: Unable to update semester.'));
    }
}
?>