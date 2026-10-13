<?php
// pages/fragments/eligibility_by_subject.php
// This fragment displays the eligibility status for students in a selected subject

// Include database and required models
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../models/Student.php';
include_once __DIR__ . '/../../models/SemesterSubject.php';
include_once __DIR__ . '/../../models/StudentAttendance.php';
include_once __DIR__ . '/../../models/ExamEligibility.php';
include_once __DIR__ . '/../../models/SubjectLecture.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get semester_subject_id from request
$semester_subject_id = $_GET['value'] ?? 0;

if (!$semester_subject_id) {
    echo '<div class="text-center text-gray-500 py-8">Please select a subject to view eligibility status</div>';
    exit;
}

// Get semester_id from semester_subject
$semester_subject = new SemesterSubject($db);
$semester_subject_data = $semester_subject->readOne($semester_subject_id);
$semester_id = $semester_subject_data['semester_id'] ?? 0;

// Get all students for this semester's department
$student = new Student($db);
$students = $student->getStudentsBySemester($semester_id);

// Get subject lecture count
$subject_lecture = new SubjectLecture($db);
$total_lectures = $subject_lecture->getLectureCount($semester_subject_id);

// Prepare student attendance class
$student_attendance = new StudentAttendance($db);

// Prepare exam eligibility class
$exam_eligibility = new ExamEligibility($db);

// Minimum attendance percentage required for eligibility (e.g., 75%)
$min_attendance_required = 75;
?>

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Lectures</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attended</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance %</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php if ($students->rowCount() > 0): ?>
            <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                // Get attendance data for this student
                $attended_lectures = $student_attendance->getAttendedLecturesCount($row['student_id'], $semester_subject_id);
                $attendance_percentage = $total_lectures > 0 ? ($attended_lectures / $total_lectures) * 100 : 0;
                $attendance_percentage = round($attendance_percentage, 2);

                // Get eligibility record if it exists
                $eligibility_stmt = $exam_eligibility->getEligibility($row['student_id'], $semester_subject_id);
                $eligibility_data = $eligibility_stmt->fetch(PDO::FETCH_ASSOC);
                $is_eligible = isset($eligibility_data['is_eligible']) ? $eligibility_data['is_eligible'] : ($attendance_percentage >= $min_attendance_required ? 1 : 0);
                $remarks = $eligibility_data['remarks'] ?? '';
                ?>

                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['student_number'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $total_lectures ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $attended_lectures ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $attendance_percentage ?>%</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($is_eligible): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Eligible
                                </span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Not Eligible
                                </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button
                            onclick="showUpdateEligibilityModal(<?= $row['student_id'] ?>, <?= $semester_subject_id ?>, <?= $is_eligible ?>, '<?= htmlspecialchars($remarks, ENT_QUOTES) ?>')"
                            class="text-indigo-600 hover:text-indigo-900">
                            Update
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No students found for this semester</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Update Eligibility Modal -->
<div id="update-eligibility-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Update Eligibility Status</h3>
            <button onclick="hideUpdateEligibilityModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="update-eligibility-form" hx-post="actions/update_eligibility.php" hx-target="#eligibility-container" hx-swap="innerHTML" hx-on::after-request="hideUpdateEligibilityModal()">
            <input type="hidden" id="update_student_id" name="student_id">
            <input type="hidden" id="update_semester_subject_id" name="semester_subject_id">

            <div class="mb-4">
                <label for="is_eligible" class="block text-sm font-medium text-gray-700 mb-1">Eligibility Status</label>
                <select id="is_eligible" name="is_eligible" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="1">Eligible</option>
                    <option value="0">Not Eligible</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"></textarea>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="hideUpdateEligibilityModal()" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-md mr-2 hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showUpdateEligibilityModal(studentId, semesterSubjectId, isEligible, remarks) {
        document.getElementById('update_student_id').value = studentId;
        document.getElementById('update_semester_subject_id').value = semesterSubjectId;
        document.getElementById('is_eligible').value = isEligible;
        document.getElementById('remarks').value = remarks;
        document.getElementById('update-eligibility-modal').classList.remove('hidden');
    }

    function hideUpdateEligibilityModal() {
        document.getElementById('update-eligibility-form').reset();
        document.getElementById('update-eligibility-modal').classList.add('hidden');
    }
</script>