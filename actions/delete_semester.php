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

// Set semester id to be deleted
$semester->semester_id = $data->semester_id;

// Delete semester
if ($semester->delete()) {
    echo json_encode(array('message' => 'Semester deleted successfully.'));
} else {
    echo json_encode(array('message' => 'Error: Unable to delete semester.'));
}
?>