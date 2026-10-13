<?php
// actions/update_eligibility.php
include_once '../config/database.php';
include_once '../models/ExamEligibility.php';
include_once '../models/StudentAttendance.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Prepare response array
$response = array(
    "status" => false,
    "message" => "",
    "data" => null
);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $student_id = $_POST['student_id'] ?? null;
    $semester_subject_id = $_POST['semester_subject_id'] ?? null;
    $is_eligible = isset($_POST['is_eligible']) ? (int)$_POST['is_eligible'] : null;
    $remarks = $_POST['remarks'] ?? "";

    // Validate required fields
    if (empty($student_id) || empty($semester_subject_id) || $is_eligible === null) {
        $response["message"] = "Required fields cannot be empty";
        echo json_encode($response);
        exit;
    }

    // Instantiate models
    $eligibility = new ExamEligibility($db);
    $attendance = new StudentAttendance($db);

    // Get attendance percentage
    $percentage = $attendance->getAttendancePercentage($student_id, $semester_subject_id);

    // Set eligibility properties
    $eligibility->student_id = $student_id;
    $eligibility->semester_subject_id = $semester_subject_id;
    $eligibility->is_eligible = $is_eligible;
    $eligibility->attendance_percentage = $percentage;

    // Add default remarks if empty
    if (empty($remarks)) {
        if ($is_eligible == 1) {
            if ($percentage >= 80) {
                $eligibility->remarks = "Eligible (≥80% attendance)";
            } else {
                $eligibility->remarks = "Eligible (manual override)";
            }
        } else {
            if ($percentage < 80) {
                $eligibility->remarks = "Not eligible (<80% attendance)";
            } else {
                $eligibility->remarks = "Not eligible (manual override)";
            }
        }
    } else {
        $eligibility->remarks = $remarks;
    }

    // Save eligibility
    if ($eligibility->save()) {
        $response["status"] = true;
        $response["message"] = "Eligibility updated successfully";
        $response["data"] = [
            "student_id" => $student_id,
            "semester_subject_id" => $semester_subject_id,
            "is_eligible" => $is_eligible,
            "attendance_percentage" => round($percentage, 2),
            "remarks" => $eligibility->remarks
        ];
    } else {
        $response["message"] = "Failed to update eligibility";
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>