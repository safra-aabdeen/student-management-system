<?php
include_once '../../config/database.php';
include_once '../../models/SemesterSubject.php';
include_once '../../models/Subject.php';

// Get semester ID from request
$semester_id = isset($_GET['value']) ? $_GET['value'] : '';

// Create response header
header('Content-Type: text/html');

// Default option
echo '<option value="">Select Subject</option>';

if (!empty($semester_id)) {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query subjects by semester
    $semesterSubject = new SemesterSubject($db);
    $stmt = $semesterSubject->readBySemester($semester_id);

    // Generate options
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['semester_subject_id'] . '">' .
            $row['subject_code'] . ' - ' . $row['subject_name'] .
            ' (' . $row['credit_hours'] . ' credits)</option>';
    }
}
?>