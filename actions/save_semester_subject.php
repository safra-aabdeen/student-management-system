<?php
// Include database and SemesterSubject model
require_once '../config/database.php';
require_once '../models/SemesterSubject.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Create a new semester subject object
    $semesterSubject = new SemesterSubject($db);

    // Set semester subject property values
    $semesterSubject->semester_id = $_POST['semester_id'];
    $semesterSubject->subject_id = $_POST['subject_id'];

    // Create the semester subject
    if($semesterSubject->create()) {
        echo json_encode(["message" => "Subject added to semester successfully."]);
    } else {
        echo json_encode(["message" => "Unable to add subject to semester."]);
    }
} else {
    echo json_encode(["message" => "Invalid request method."]);
}
?>