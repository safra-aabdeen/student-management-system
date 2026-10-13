<?php
// actions/save_attendance.php
include_once '../config/database.php';
include_once '../models/StudentAttendance.php';
include_once '../models/SubjectLecture.php';
include_once '../models/ExamEligibility.php';
include_once '../models/Student.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $lecture_id = $_POST['lecture_id'] ?? null;
    $semester_subject_id = $_POST['semester_subject_id'] ?? null;
    $student_ids = $_POST['student_ids'] ?? [];
    $attendance = $_POST['attendance'] ?? [];

    // Validate required fields
    if (empty($lecture_id) || empty($student_ids)) {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>Required fields cannot be empty</p>
        </div>';
        exit;
    }

    // If semester_subject_id is not passed directly, get it from the lecture
    if (empty($semester_subject_id)) {
        $lectureObj = new SubjectLecture($db);
        $lectureObj->lecture_id = $lecture_id;
        $lectureData = $lectureObj->readOne();
        if ($lectureData) {
            $semester_subject_id = $lectureData['semester_subject_id'];
        } else {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>Could not find semester subject ID</p>
            </div>';
            exit;
        }
    }

    // Instantiate models
    $attendanceObj = new StudentAttendance($db);
    $eligibility = new ExamEligibility($db);
    $studentObj = new Student($db);

    // Track success count and record attendance data
    $success_count = 0;
    $attendance_data = [];

    // Process each student's attendance
    foreach ($student_ids as $student_id) {
        $attendanceObj->student_id = $student_id;
        $attendanceObj->lecture_id = $lecture_id;
        $attendanceObj->is_present = isset($attendance[$student_id]) ? intval($attendance[$student_id]) : 0;

        if ($attendanceObj->record()) {
            $success_count++;

            // Update eligibility status if we have semester_subject_id
            if (!empty($semester_subject_id)) {
                $percentage = $attendanceObj->getAttendancePercentage($student_id, $semester_subject_id);
                $is_eligible = ($percentage >= 80) ? 1 : 0;

                $eligibility->student_id = $student_id;
                $eligibility->semester_subject_id = $semester_subject_id;
                $eligibility->is_eligible = $is_eligible;
                $eligibility->attendance_percentage = $percentage;
                $eligibility->remarks = $is_eligible ? "Eligible (≥80% attendance)" : "Not eligible (<80% attendance)";
                $eligibility->save();

                // Get student details for display
                $studentObj->student_id = $student_id;
                $student_data = $studentObj->readOneStudent();
                $student_name = $student_data ? $student_data['first_name'] . ' ' . $student_data['last_name'] : "Student #" . $student_id;
                $student_number = $student_data ? $student_data['student_number'] : "-";

                // Add to attendance data array
                $attendance_data[] = [
                    "student_id" => $student_id,
                    "student_name" => $student_name,
                    "student_number" => $student_number,
                    "is_present" => $attendanceObj->is_present,
                    "percentage" => round($percentage, 2),
                    "is_eligible" => $is_eligible
                ];
            }
        }
    }

    // Get lecture details for the success message
    $lectureObj = new SubjectLecture($db);
    $lectureObj->lecture_id = $lecture_id;
    $lecture_details = $lectureObj->readOne();
    $lecture_date = date('d M Y', strtotime($lecture_details['lecture_date'] ?? 'now'));

    // Output the success alert
    if ($success_count > 0) {
        // Success alert
        echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success!</p>
            <p>Attendance recorded successfully for ' . $success_count . ' out of ' . count($student_ids) . ' students.</p>
        </div>';

        // Get subject info if available
        $subject_info = "";
        if (!empty($semester_subject_id)) {
            $query = "SELECT sub.subject_code, sub.subject_name
                      FROM semester_subjects ss 
                      JOIN subjects sub ON ss.subject_id = sub.subject_id
                      WHERE ss.semester_subject_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $semester_subject_id);
            $stmt->execute();
            $subject_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($subject_data) {
                $subject_info = $subject_data['subject_code'] . ' - ' . $subject_data['subject_name'];
            }
        }

        // Attendance summary table
        echo '<div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <h4 class="text-lg font-medium mb-2">Attendance Summary ' . (!empty($subject_info) ? 'for ' . htmlspecialchars($subject_info) : '') . '</h4>
            <p class="text-sm text-gray-500 mb-4">Lecture Date: ' . $lecture_date . '</p>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="py-2 px-4 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-2 px-4 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
                            <th class="py-2 px-4 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Eligibility</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">';

        // Display the attendance data in the table
        foreach ($attendance_data as $record) {
            $status_class = $record['is_present'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $status_text = $record['is_present'] ? 'Present' : 'Absent';

            $percentage_class = 'text-gray-800';
            if ($record['percentage'] >= 80) {
                $percentage_class = 'text-green-600';
            } elseif ($record['percentage'] >= 60) {
                $percentage_class = 'text-yellow-600';
            } else {
                $percentage_class = 'text-red-600';
            }

            $eligibility_class = $record['is_eligible'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            $eligibility_text = $record['is_eligible'] ? 'Eligible' : 'Not Eligible';

            echo '<tr>
                <td class="py-3 px-4 text-sm text-gray-500">' . htmlspecialchars($record['student_number']) . '</td>
                <td class="py-3 px-4 text-sm text-gray-900">' . htmlspecialchars($record['student_name']) . '</td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_class . '">
                        ' . $status_text . '
                    </span>
                </td>
                <td class="py-3 px-4 text-center font-medium ' . $percentage_class . '">' . $record['percentage'] . '%</td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $eligibility_class . '">
                        ' . $eligibility_text . '
                    </span>
                </td>
            </tr>';
        }

        echo '</tbody>
                </table>
            </div>
        </div>';

        // Add a refresh button
        echo '<div class="flex justify-end">
            <button hx-get="pages/fragments/lectures_by_subject.php?semester_subject_id=' . $semester_subject_id . '" 
                    hx-target="#lecture-container" 
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors">
                Return to Lectures
            </button>
        </div>';

    } else {
        // Error alert
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">Error!</p>
            <p>Failed to record attendance. Please try again.</p>
        </div>';
    }
} else {
    // Method not allowed
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
        <p>Invalid request method</p>
    </div>';
}
?>