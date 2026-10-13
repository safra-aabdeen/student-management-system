<?php
include_once '../../config/database.php';
include_once '../../models/SubjectLecture.php';
include_once '../../models/SemesterSubject.php';
include_once '../../models/Subject.php';

// Get semester subject ID from request
$semester_subject_id = isset($_GET['value']) ? $_GET['value'] : '';

// Create response header
header('Content-Type: text/html');

if (empty($semester_subject_id)) {
    echo '<div class="text-center text-gray-500 py-8">
              Please select a subject to view lectures
          </div>';
    exit;
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Get subject details for heading
$query = "SELECT ss.semester_subject_id, s.subject_code, s.subject_name
          FROM semester_subjects ss
          JOIN subjects s ON ss.subject_id = s.subject_id
          WHERE ss.semester_subject_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $semester_subject_id);
$stmt->execute();
$subject_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Get lectures for this subject
$subjectLecture = new SubjectLecture($db);
$lectures = $subjectLecture->readBySubject($semester_subject_id);

// Get lecture count
$lecture_count = $lectures->rowCount();

// Enable the add button via JavaScript
echo '<script>document.getElementById("add-lecture-btn").disabled = false;</script>';
?>

<div class="mb-4">
    <h4 class="text-lg font-medium">
        <?= htmlspecialchars($subject_info['subject_code'] . ' - ' . $subject_info['subject_name']) ?>
    </h4>
    <p class="text-sm text-gray-500">
        Total Lectures: <?= $lecture_count ?>
    </p>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                #
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Description
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
            </th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php
        if ($lecture_count > 0) {
            $counter = 1;
            while ($row = $lectures->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $counter++ ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d M Y', strtotime($row['lecture_date'])) ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= htmlspecialchars($row['description'] ?: 'N/A') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="showAttendanceForm(<?= $row['lecture_id'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            Mark Attendance
                        </button>
                        <a href="#" class="text-red-600 hover:text-red-900"
                           hx-delete="actions/delete_lecture.php?id=<?= $row['lecture_id'] ?>"
                           hx-confirm="Are you sure you want to delete this lecture?"
                           hx-target="#lecture-container"
                           hx-include="#semester-subject">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                    No lectures found for this subject. Click "Add Lecture" to create one.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>