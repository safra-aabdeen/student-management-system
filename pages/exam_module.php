<?php
// pages/exam_module.php
// Include database and required models
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Department.php';
include_once __DIR__ . '/../models/Semester.php';
include_once __DIR__ . '/../models/SemesterSubject.php';
include_once __DIR__ . '/../models/Subject.php';
include_once __DIR__ . '/../models/SubjectLecture.php';
include_once __DIR__ . '/../models/Student.php';
include_once __DIR__ . '/../models/StudentAttendance.php';
include_once __DIR__ . '/../models/ExamEligibility.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get departments for dropdown
$department = new Department($db);
$dept_stmt = $department->readAll();

// Get semesters for dropdown (will be filtered by department)
$semester = new Semester($db);
$sem_stmt = $semester->readAll();

// Title for the page
echo "<title>Exam Module</title>";
?>

<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Exam Eligibility Management</h2>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'attendance' }" class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px space-x-8">
                    <button @click="activeTab = 'attendance'"
                            :class="{'border-blue-500 text-blue-600': activeTab === 'attendance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'attendance'}"
                            class="py-4 px-1 border-b-2 font-medium text-sm">
                        Attendance Management
                    </button>
                    <button @click="activeTab = 'eligibility'"
                            :class="{'border-blue-500 text-blue-600': activeTab === 'eligibility', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'eligibility'}"
                            class="py-4 px-1 border-b-2 font-medium text-sm">
                        Eligibility Status
                    </button>
                </nav>
            </div>

            <!-- Attendance Management Tab -->
            <div x-show="activeTab === 'attendance'" class="pt-4">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Lecture Attendance Record</h3>

                    <!-- Filter Form -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="department" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/semesters_by_department.php"
                                    hx-target="#semester"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('department').value }">
                                <option value="">Select Department</option>
                                <?php while ($row = $dept_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $row['department_id'] ?>"><?= $row['department_code'] . ' - ' . $row['department_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select id="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/subjects_by_semester.php"
                                    hx-target="#semester-subject"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('semester').value }">
                                <option value="">Select Semester</option>
                            </select>
                        </div>

                        <div>
                            <label for="semester-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <select id="semester-subject" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/lectures_by_subject.php"
                                    hx-target="#lecture-container"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('semester-subject').value }">
                                <option value="">Select Subject</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button id="add-lecture-btn" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition-colors"
                                    onclick="showAddLectureForm()" disabled>
                                Add Lecture
                            </button>
                        </div>
                    </div>

                    <!-- Lecture List Container -->
                    <div id="lecture-container" class="mt-4">
                        <div class="text-center text-gray-500 py-8">
                            Please select a department, semester, and subject to view lectures
                        </div>
                    </div>

                    <!-- Add Lecture Modal -->
                    <div id="add-lecture-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Add New Lecture</h3>
                                <button onclick="hideAddLectureForm()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <form id="add-lecture-form" hx-post="actions/save_lecture.php" hx-target="#lecture-container" hx-swap="innerHTML" hx-on::after-request="hideAddLectureForm()">
                                <input type="hidden" id="semester_subject_id" name="semester_subject_id">

                                <div class="mb-4">
                                    <label for="lecture_date" class="block text-sm font-medium text-gray-700 mb-1">Lecture Date</label>
                                    <input type="date" id="lecture_date" name="lecture_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"></textarea>
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="button" onclick="hideAddLectureForm()" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-md mr-2 hover:bg-gray-200 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors">
                                        Save Lecture
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Mark Attendance Modal -->
                    <div id="mark-attendance-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Mark Student Attendance</h3>
                                <button onclick="hideAttendanceForm()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div id="attendance-form-container" class="max-h-96 overflow-y-auto">
                                <!-- Content will be loaded via HTMX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eligibility Status Tab -->
            <div x-show="activeTab === 'eligibility'" class="pt-4">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Exam Eligibility Status</h3>

                    <!-- Filter Form -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <label for="eligibility-department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="eligibility-department" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/semesters_by_department.php"
                                    hx-target="#eligibility-semester"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('eligibility-department').value }">
                                <option value="">Select Department</option>
                                <?php
                                // Reset the pointer of department statement to fetch from beginning again
                                $dept_stmt->execute();
                                while ($row = $dept_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $row['department_id'] ?>"><?= $row['department_code'] . ' - ' . $row['department_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="eligibility-semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select id="eligibility-semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/subjects_by_semester.php"
                                    hx-target="#eligibility-subject"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('eligibility-semester').value }">
                                <option value="">Select Semester</option>
                            </select>
                        </div>

                        <div>
                            <label for="eligibility-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <select id="eligibility-subject" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                    hx-get="pages/fragments/eligibility_by_subject.php"
                                    hx-target="#eligibility-container"
                                    hx-trigger="change"
                                    hx-vals="js:{ value: document.getElementById('eligibility-subject').value }">
                                <option value="">Select Subject</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button id="calculate-eligibility-btn" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md transition-colors"
                                    hx-get="pages/fragments/calculate_eligibility.php"
                                    hx-target="#eligibility-container"
                                    hx-include="#eligibility-subject"
                                    hx-trigger="click"
                                    disabled>
                                Calculate Eligibility
                            </button>
                        </div>
                    </div>

                    <!-- Eligibility List Container -->
                    <div id="eligibility-container" class="mt-4">
                        <div class="text-center text-gray-500 py-8">
                            Please select a department, semester, and subject to view eligibility status
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Let's add the JavaScript to handle the modals and enable/disable buttons -->
<script>
    // Functions for managing lecture modal
    function showAddLectureForm() {
        const semesterSubjectId = document.getElementById('semester-subject').value;
        document.getElementById('semester_subject_id').value = semesterSubjectId;
        document.getElementById('add-lecture-modal').classList.remove('hidden');
    }

    function hideAddLectureForm() {
        document.getElementById('add-lecture-form').reset();
        document.getElementById('add-lecture-modal').classList.add('hidden');
    }

    // Functions for managing attendance modal
    function showAttendanceForm(lectureId) {
        document.getElementById('attendance-form-container').innerHTML = '<div class="flex justify-center p-4"><div class="loader"></div></div>';
        document.getElementById('mark-attendance-modal').classList.remove('hidden');

        // Load the attendance form via HTMX
        htmx.ajax('GET', `pages/fragments/attendance_form.php?lecture_id=${lectureId}`, '#attendance-form-container');
    }

    function hideAttendanceForm() {
        document.getElementById('mark-attendance-modal').classList.add('hidden');
    }

    // Enable/disable buttons based on selections
    document.addEventListener('DOMContentLoaded', function() {
        // For attendance tab
        const semesterSubjectSelect = document.getElementById('semester-subject');
        const addLectureBtn = document.getElementById('add-lecture-btn');

        semesterSubjectSelect.addEventListener('change', function() {
            addLectureBtn.disabled = !this.value;
        });

        // For eligibility tab
        const eligibilitySubjectSelect = document.getElementById('eligibility-subject');
        const calculateEligibilityBtn = document.getElementById('calculate-eligibility-btn');

        eligibilitySubjectSelect.addEventListener('change', function() {
            calculateEligibilityBtn.disabled = !this.value;
        });
    });
</script>