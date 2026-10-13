<?php
// Include database and subject model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Subject.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create new subject object
$subject = new Subject($db);

// Set subject property values
$subject->subject_id = isset($_POST['subject_id']) && !empty($_POST['subject_id']) ? $_POST['subject_id'] : null;
$subject->subject_code = $_POST['subject_code'];
$subject->subject_name = $_POST['subject_name'];
$subject->credit_hours = $_POST['credit_hours'];
$subject->department_id = $_POST['department_id'];

// Create or update subject
if ($subject->subject_id) {
    $result = $subject->update();
    $message = $result ? "Subject updated successfully" : "Error updating subject";
} else {
    $result = $subject->create();
    $message = $result ? "Subject added successfully" : "Error adding subject";
}

// Return response
echo json_encode(array("message" => $message, "success" => $result));
?>