// actions/delete_subject.php
<?php
// Include database and subject model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Subject.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create new subject object
$subject = new Subject($db);

// Get subject ID
$data = json_decode(file_get_contents("php://input"));
$subject->subject_id = $data->subject_id;

// Delete subject
$result = $subject->delete();
$message = $result ? "Subject deleted successfully" : "Error deleting subject";

// Return response
echo json_encode(array("message" => $message, "success" => $result));
?>