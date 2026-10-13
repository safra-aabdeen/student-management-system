<?php
// actions/save_lecture.php
include_once '../config/database.php';
include_once '../models/SubjectLecture.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Prepare response array
$response = array(
    "status" => false,
    "message" => "",
    "lecture_id" => null
);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create SubjectLecture instance
    $lecture = new SubjectLecture($db);

    // Set lecture properties from POST data
    $lecture->semester_subject_id = $_POST['semester_subject_id'] ?? null;
    $lecture->lecture_date = $_POST['lecture_date'] ?? null;
    $lecture->description = $_POST['description'] ?? null;

    // Validate required fields
    if (empty($lecture->semester_subject_id) || empty($lecture->lecture_date)) {
        $response["message"] = "Required fields cannot be empty";
        echo json_encode($response);
        exit;
    }

    // Create lecture record
    $lecture_id = $lecture->create();

    if ($lecture_id) {
        $response["status"] = true;
        $response["message"] = "Lecture added successfully";
        $response["lecture_id"] = $lecture_id;
    } else {
        $response["message"] = "Failed to add lecture";
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>