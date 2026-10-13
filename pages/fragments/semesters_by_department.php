<?php
include_once '../../config/database.php';
include_once '../../models/Semester.php';

// Get department ID from request
$department_id = isset($_GET['value']) ? $_GET['value'] : '';

// Create response header
header('Content-Type: text/html');

// Default option
echo '<option value="">Select Semester</option>';

if (!empty($department_id)) {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query semesters by department
    $semester = new Semester($db);
    $query = "SELECT semester_id, year_number, semester_number 
              FROM semesters 
              WHERE department_id = ? 
              ORDER BY year_number, semester_number";

    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $department_id);
    $stmt->execute();

    // Generate options
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . $row['semester_id'] . '">Year ' . $row['year_number'] .
            ' - Semester ' . $row['semester_number'] . '</option>';
    }
}
?>