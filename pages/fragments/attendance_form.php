<?php
// pages/fragments/attendance_form.php
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/SubjectLecture.php';
include_once __DIR__ . '/../../models/Student.php';
include_once __DIR__ . '/../../models/StudentAttendance.php';

// Get lecture ID from request
$lecture_id = isset($_GET['lecture_id']) ? $_GET['lecture_id'] : '';

if (empty($lecture_id)) {
    echo '<div class="text-center text-red-500">Lecture ID is required</div>';
    exit;
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Get lecture details
$query = "SELECT sl.*, ss.semester_id, sub.subject_code, sub.subject_name
          FROM subject_lectures sl
          JOIN semester_subjects ss ON sl.semester_subject_id = ss.semester_subject_id
          JOIN subjects sub ON ss.subject_id = sub.subject_id
          WHERE sl.lecture_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $lecture_id);
$stmt->execute();
$lecture_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lecture_info) {
    echo '<div class="text-center text-red-500">Lecture not found</div>';
    exit;
}

// Get semester details
$query = "SELECT s.*, d.department_id 
          FROM semesters s
          JOIN departments d ON s.department_id = d.department_id
          WHERE s.semester_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $lecture_info['semester_id']);
$stmt->execute();
$semester_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Get students enrolled in this department and semester
$query = "SELECT s.student_id, s.student_number, s.first_name, s.last_name
          FROM students s
          WHERE s.department_id = ? AND s.current_semester_id = ?
          ORDER BY s.student_number";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $semester_info['department_id']);
$stmt->bindParam(2, $lecture_info['semester_id']);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get existing attendance for this lecture
$studentAttendance = new StudentAttendance($db);
$attendance_data = $studentAttendance->getByLecture($lecture_id);
$attendance_map = [];

while ($row = $attendance_data->fetch(PDO::FETCH_ASSOC)) {
    $attendance_map[$row['student_id']] = $row['is_present'];
}
?>

<form hx-post="actions/save_attendance.php" hx-target="#lecture-container" hx-include="#semester-subject" hx-on::after-request="hideAttendanceForm()">
    <input type="hidden" name="lecture_id" value="<?= $lecture_id ?>">

    <div class="mb-4">
        <h4 class="font-medium"><?= htmlspecialchars($lecture_info['subject_code'] . ' - ' . $lecture_info['subject_name']) ?></h4>
        <p class="text-sm text-gray-500">Lecture Date: <?= date('d M Y', strtotime($lecture_info['lecture_date'])) ?></p>
    </div>

    <div class="overflow-x-auto max-h-96">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Student ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Present
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Absent
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php
            if (count($students) > 0) {
                foreach ($students as $student) {
                    $is_present = isset($attendance_map[$student['student_id']]) ? $attendance_map[$student['student_id']] : null;
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($student['student_number']) ?>
                            <input type="hidden" name="student_ids[]" value="<?= $student['student_id'] ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $student['student_id'] ?>]" value="1" <?= ($is_present === '1' || $is_present === 1) ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <input type="radio" name="attendance[<?= $student['student_id'] ?>]" value="0" <?= ($is_present === '0' || $is_present === 0) ? 'checked' : '' ?> class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                        No students found for this semester.
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="flex justify-end mt-6">
        <button type="button" onclick="hideAttendanceForm()" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-md mr-2 hover:bg-gray-200 transition-colors">
            Cancel
        </button>
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors">
            Save Attendance
        </button>
    </div>
</form>