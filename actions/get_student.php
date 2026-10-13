<?php
// actions/get_student.php

// Include database and Student model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

// Initialize response array
$response = array(
    "status" => false,
    "message" => "",
    "data" => null
);

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Prepare Student object
$student = new Student($db);

// Check if student_id is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $student->student_id = $_GET['id'];

    // Read student details
    if ($student->readOne()) {
        $response["status"] = true;
        $response["message"] = "Student found";
        $response["data"] = [
            "student_id" => $student->student_id,
            "student_number" => $student->student_number,
            "first_name" => $student->first_name,
            "last_name" => $student->last_name,
            "email" => $student->email,
            "phone" => $student->phone,
            "address" => $student->address,
            "dob" => $student->dob,
            "gender" => $student->gender,
            "department_id" => $student->department_id,
            "enrollment_year" => $student->enrollment_year,
            "enrollment_type" => $student->enrollment_type,
            "status" => $student->status
        ];
    } else {
        $response["message"] = "Student not found";
    }
} else {
    $response["message"] = "Missing student ID";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>