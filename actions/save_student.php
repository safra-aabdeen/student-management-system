<?php
// actions/save_student.php

// Include database and Student model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Department.php';

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

// Check if it's POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Set student properties from POST data
        $student->student_id = isset($_POST['student_id']) && !empty($_POST['student_id']) ? $_POST['student_id'] : null;
        $student->first_name = $_POST['first_name'];
        $student->last_name = $_POST['last_name'];
        $student->email = $_POST['email'];
        $student->phone = $_POST['phone'] ?? '';
        $student->address = $_POST['address'] ?? '';
        $student->dob = $_POST['dob'];
        $student->gender = $_POST['gender'];
        $student->status = $_POST['status'];

        // For insert operation
        if (empty($student->student_id)) {
            // These fields are only needed for new records
            $student->department_id = $_POST['department_id'];
            $student->enrollment_year = $_POST['enrollment_year'];
            $student->enrollment_type = $_POST['enrollment_type'];

            // Get department code for student number generation
            $departmentModel = new Department($db);
            $departmentModel->department_id = $student->department_id;
            if ($departmentModel->readOne()) {
                // Generate student number
                $student->student_number = $student->generateStudentNumber(
                    $departmentModel->department_code,
                    $student->enrollment_year,
                    $student->enrollment_type
                );

                // Create the student record
                if ($student->create()) {
                    $response["status"] = true;
                    $response["message"] = "Student registered successfully";
                    $response["data"] = [
                        "student_id" => $student->student_id,
                        "student_number" => $student->student_number
                    ];
                } else {
                    $response["message"] = "Unable to register student";
                }
            } else {
                $response["message"] = "Department not found";
            }
        } else {
            // Update existing student record
            if ($student->update()) {
                $response["status"] = true;
                $response["message"] = "Student updated successfully";
                $response["data"] = [
                    "student_id" => $student->student_id
                ];
            } else {
                $response["message"] = "Unable to update student";
            }
        }
    } catch (Exception $e) {
        $response["message"] = "Error: " . $e->getMessage();
    }
} else {
    $response["message"] = "Invalid request method";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>