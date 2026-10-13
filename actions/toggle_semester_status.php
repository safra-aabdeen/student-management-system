<?php
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
$data = json_decode(file_get_contents("php://input"));

// Set semester id and active status
$semester->semester_id = $data->semester_id;
$semester->is_active = $data->is_active;

// Toggle semester active status
if ($semester->toggleActive()) {
    echo json_encode(array('message' => 'Semester status updated successfully.'));
} else {
    echo json_encode(array('message' => 'Error: Unable to update semester status.'));
}
?>