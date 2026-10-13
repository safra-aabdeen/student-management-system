<?php
// actions/delete_department.php
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

// Get department ID
$data = json_decode(file_get_contents("php://input"), true);

// If JSON parsing failed, try to get from POST
if (empty($data)) {
    $data = $_POST;
}

// Check if department ID is set
if (empty($data['department_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Department ID is required"));
    exit();
}

// Set department ID
$department->department_id = $data['department_id'];

// Delete the department
if ($department->delete()) {
    echo json_encode(array("message" => "Department deleted successfully"));
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(array("message" => "Unable to delete department. It may be referenced by other records."));
}
?>