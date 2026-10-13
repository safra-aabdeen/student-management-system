<?php
// actions/save_department.php
header('Content-Type: application/json');

// Include database and department model
require_once '../config/database.php';
require_once '../models/Department.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed"));
    exit();
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create new department object
$department = new Department($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Check if data is empty
if (empty($data)) {
    // Try to get data from POST array
    $data = $_POST;
}

// Check if required fields are set
if (
    empty($data['department_code']) ||
    empty($data['department_name']) ||
    empty($data['total_years']) ||
    empty($data['semesters_per_year'])
) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "All fields are required"));
    exit();
}

// Set department properties
$department->department_code = $data['department_code'];
$department->department_name = $data['department_name'];
$department->total_years = $data['total_years'];
$department->semesters_per_year = $data['semesters_per_year'];

// Check if department code already exists (for new departments)
if (empty($data['department_id']) && $department->codeExists()) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Department code already exists"));
    exit();
}

// If we have a department_id, update the department
if (!empty($data['department_id'])) {
    $department->department_id = $data['department_id'];

    // Update the department
    if ($department->update()) {
        echo json_encode(array("message" => "Department updated successfully"));
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("message" => "Unable to update department"));
    }
} else {
    // Create the department
    if ($department->create()) {
        http_response_code(201); // Created
        echo json_encode(array("message" => "Department created successfully"));
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("message" => "Unable to create department"));
    }
}
?>