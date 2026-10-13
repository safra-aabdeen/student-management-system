<?php
// Include database and SemesterSubject model
require_once '../config/database.php';
require_once '../models/SemesterSubject.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->semester_subject_id)) {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Create a new semester subject object
    $semesterSubject = new SemesterSubject($db);

    // Set semester subject ID to be deleted
    $semesterSubject->semester_subject_id = $data->semester_subject_id;

    // Delete the semester subject
    if($semesterSubject->delete()) {
        echo json_encode(["message" => "Subject removed from semester successfully."]);
    } else {
        echo json_encode(["message" => "Unable to remove subject from semester."]);
    }
} else {
    echo json_encode(["message" => "Missing required parameters."]);
}
?>