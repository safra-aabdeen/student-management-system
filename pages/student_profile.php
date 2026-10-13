<?php
// pages/student_profile.php

// Include database and necessary models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Semester.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/SemesterSubject.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Check if student_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // For HTMX requests, return error message
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>Student ID is required to view profile. <a href="?page=student_registration" class="text-blue-600 hover:underline">Go back to student registration</a></p>
              </div>';
    } else {
        // For direct browser requests, redirect to student registration
        echo '<script>
                window.location.href = "?page=student_registration";
              </script>';
    }
    exit;
}

$student_id = $_GET['id'];

// Initialize Student model to get student details
$studentModel = new Student($db);
$studentModel->student_id = $student_id;
$student = $studentModel->readOne();

if (!$student) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p>Student not found. <a href="?page=student_registration" class="text-blue-600 hover:underline">Go back to student registration</a></p>
              </div>';
    } else {
        echo '<script>
                window.location.href = "?page=student_registration";
              </script>';
    }
    exit;
}

// Calculate age from DOB
$dob = new DateTime($studentModel->dob);
$now = new DateTime();
$age = $now->diff($dob)->y;

// Get student's department information
$departmentModel = new Department($db);
$departmentModel->department_id = $studentModel->department_id;
$departmentModel->readOne();

// Get student's current semester if set
$currentSemesterInfo = null;
if ($studentModel->current_semester_id) {
    $semesterModel = new Semester($db);
    $semesterModel->semester_id = $studentModel->current_semester_id;
    $result = $semesterModel->readOne();
    $currentSemesterInfo = $result->fetch(PDO::FETCH_ASSOC);
}

// Get student's semester history
$query = "SELECT h.*, s.year_number, s.semester_number 
          FROM student_semester_history h
          LEFT JOIN semesters s ON h.semester_id = s.semester_id
          WHERE h.student_id = ?
          ORDER BY s.year_number, s.semester_number";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $student_id);
$stmt->execute();
$semesterHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate CGPA
$totalGPA = 0;
$semesterCount = count($semesterHistory);
foreach ($semesterHistory as $history) {
    $totalGPA += $history['gpa'];
}
$cgpa = $semesterCount > 0 ? number_format($totalGPA / $semesterCount, 2) : 'N/A';

// Format enrollment year range
$enrollmentYearRange = $studentModel->enrollment_year;

// Format program duration
$totalSemesters = $departmentModel->total_years * $departmentModel->semesters_per_year;
$programDuration = $departmentModel->total_years . ' years (' . $totalSemesters . ' semesters)';

// Format current semester display
$currentSemesterDisplay = 'Not assigned';
if ($currentSemesterInfo) {
    $currentSemesterDisplay = 'Year ' . $currentSemesterInfo['year_number'] . ', Semester ' . $currentSemesterInfo['semester_number'];
}

$marksQuery = "SELECT sm.*, ss.semester_id, sub.subject_code, sub.subject_name, sub.credit_hours,
                sem.year_number, sem.semester_number
              FROM student_marks sm
              JOIN semester_subjects ss ON sm.semester_subject_id = ss.semester_subject_id
              JOIN subjects sub ON ss.subject_id = sub.subject_id
              JOIN semesters sem ON ss.semester_id = sem.semester_id
              WHERE sm.student_id = ?
              ORDER BY sem.year_number, sem.semester_number, sub.subject_code";

$marksStmt = $db->prepare($marksQuery);
$marksStmt->bindParam(1, $student_id);
$marksStmt->execute();
$allMarks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);

// Group marks by semester
$marksBySemester = [];
foreach ($allMarks as $mark) {
    $semesterKey = $mark['year_number'] . '-' . $mark['semester_number'];
    if (!isset($marksBySemester[$semesterKey])) {
        $marksBySemester[$semesterKey] = [
            'year_number' => $mark['year_number'],
            'semester_number' => $mark['semester_number'],
            'semester_id' => $mark['semester_id'],
            'subjects' => []
        ];
    }
    $marksBySemester[$semesterKey]['subjects'][] = $mark;
}

// Calculate overall statistics
$totalSubjects = count($allMarks);
$totalCreditHours = 0;
$weightedGradePoints = 0;
$gradeDistribution = [
    'A+' => 0, 'A' => 0, 'A-' => 0,
    'B+' => 0, 'B' => 0, 'B-' => 0,
    'C+' => 0, 'C' => 0, 'C-' => 0,
    'D+' => 0, 'D' => 0, 'F' => 0
];

// GPA conversion map (adjust according to your grading system)
$gradePointMap = [
    'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
    'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
    'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
    'D+' => 1.3, 'D' => 1.0, 'F' => 0.0
];

// Calculate total credit hours and grade distribution
foreach ($allMarks as $mark) {
    $totalCreditHours += $mark['credit_hours'];
    if (isset($gradeDistribution[$mark['grade']])) {
        $gradeDistribution[$mark['grade']]++;
    }

    // Calculate weighted grade points
    if (isset($gradePointMap[$mark['grade']])) {
        $weightedGradePoints += ($gradePointMap[$mark['grade']] * $mark['credit_hours']);
    }
}

// Calculate CGPA
$cgpa = $totalCreditHours > 0 ? number_format($weightedGradePoints / $totalCreditHours, 2) : 'N/A';

// Calculate program completion percentage
$programTotalCredits = 0;
$programQuery = "SELECT SUM(sub.credit_hours) as total_credits
                FROM subjects sub
                JOIN departments d ON sub.department_id = d.department_id
                WHERE d.department_id = ?";
$programStmt = $db->prepare($programQuery);
$programStmt->bindParam(1, $studentModel->department_id);
$programStmt->execute();
$programResult = $programStmt->fetch(PDO::FETCH_ASSOC);

if ($programResult && $programResult['total_credits'] > 0) {
    $programTotalCredits = $programResult['total_credits'];
    $completionPercentage = ($totalCreditHours / $programTotalCredits) * 100;
    $completionPercentage = min(100, round($completionPercentage)); // Ensure it doesn't exceed 100%
} else {
    $completionPercentage = 0;
}

// Helper function to get color for grade
function getGradeColor($grade) {
    switch ($grade) {
        case 'A+':
        case 'A':
        case 'A-':
            return 'text-green-600';
        case 'B+':
        case 'B':
        case 'B-':
            return 'text-blue-600';
        case 'C+':
        case 'C':
        case 'C-':
            return 'text-yellow-600';
        case 'D+':
        case 'D':
            return 'text-orange-600';
        case 'F':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
}

// Helper function to get color for status
function getStatusColor($status) {
    switch ($status) {
        case 'Pass':
            return 'bg-green-100 text-green-800';
        case 'Fail':
            return 'bg-red-100 text-red-800';
        case 'Incomplete':
            return 'bg-yellow-100 text-yellow-800';
        case 'Withdrawn':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

?>

<div x-data="{
    activeTab: 'info',
    editingForm: false,
    formTitle: 'Student Profile',
    buttonText: 'Edit Profile',
    studentId: <?= $student_id ?>,
    toggleEdit() {
        this.editingForm = !this.editingForm;
        this.buttonText = this.editingForm ? 'Cancel' : 'Edit Profile';

        if (!this.editingForm) {
            // Reset form if canceling edit
            document.getElementById('student-profile-form').reset();
        }
    }
}" x-init="editingForm = false"  class="container mx-auto py-6 px-4">

    <!-- Back button -->
    <div class="mb-4">
        <a href="index.php?page=student_registration" class="flex items-center text-blue-600 hover:text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Student Registration
        </a>
    </div>

    <!-- Student Header -->
    <div class="bg-white rounded-lg border p-6 mb-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
                <div class="h-20 w-20 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold mr-4">
                    <?= substr($studentModel->first_name, 0, 1) . substr($studentModel->last_name, 0, 1) ?>
                </div>
                <div>
                    <h1 class="text-2xl font-bold"><?= $studentModel->first_name . ' ' . $studentModel->last_name ?></h1>
                    <div class="text-gray-600 mb-2"><?= $studentModel->student_number ?></div>
                    <div class="flex items-center">
                        <?php if ($studentModel->status === 'Active'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?= $studentModel->status ?>
                                </span>
                        <?php elseif ($studentModel->status === 'Inactive'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= $studentModel->status ?>
                                </span>
                        <?php elseif ($studentModel->status === 'Graduated'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= $studentModel->status ?>
                                </span>
                        <?php elseif ($studentModel->status === 'Suspended'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <?= $studentModel->status ?>
                                </span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <?= $studentModel->status ?>
                                </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex flex-col space-y-2">
                <button
                        @click="toggleEdit"
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" x-show="!editingForm">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.793 8.793-3.536-3.535 8.793-8.793z" />
                        <path d="M2 12.93V17h4.07l8.485-8.486-4.07-4.071L2 12.93z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" x-show="editingForm">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="buttonText">Edit Profile</span>
                </button>
                <a href="#" class="flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    View Transcript
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <nav class="flex border-b border-gray-200">
            <button
                    @click="activeTab = 'info'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'info', 'text-gray-500 hover:text-gray-700': activeTab !== 'info'}"
                    class="py-4 px-6 font-medium"
            >
                Personal Information
            </button>
            <button
                    @click="activeTab = 'academic'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'academic', 'text-gray-500 hover:text-gray-700': activeTab !== 'academic'}"
                    class="py-4 px-6 font-medium"
            >
                Academic Details
            </button>
            <button
                    @click="activeTab = 'marks'"
                    :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'marks', 'text-gray-500 hover:text-gray-700': activeTab !== 'marks'}"
                    class="py-4 px-6 font-medium"
            >
                Marks & Performance
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-lg border p-6">

        <!-- Personal Information Tab -->
        <div x-show="activeTab === 'info'">
            <h2 class="text-xl font-semibold mb-4">Personal Information</h2>

            <!-- Non-editable view -->
            <div x-show="!editingForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Full Name</p>
                    <p class="font-medium"><?= $studentModel->first_name . ' ' . $studentModel->last_name ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Email Address</p>
                    <p class="font-medium"><?= $studentModel->email ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Phone Number</p>
                    <p class="font-medium"><?= $studentModel->phone ?: 'Not provided' ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Date of Birth</p>
                    <p class="font-medium"><?= date('Y/m/d', strtotime($studentModel->dob)) . ' (' . $age . ' years)' ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Gender</p>
                    <p class="font-medium"><?= $studentModel->gender ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Status</p>
                    <p class="font-medium"><?= $studentModel->status ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 mb-1">Address</p>
                    <p class="font-medium"><?= $studentModel->address ?: 'Not provided' ?></p>
                </div>
            </div>

            <!-- Editable form -->
            <form id="student-profile-form" x-show="editingForm" x-cloak @submit.prevent="submitForm"
                  hx-post="actions/save_student.php"
                  hx-trigger="submit"
                  hx-on:htmx:after-request="if(event.detail.successful && event.detail.xhr.responseText.includes('success')) { window.location.reload(); }"
                  class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" name="student_id" value="<?= $studentModel->student_id ?>">
                <div>
                    <label for="first-name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first-name" value="<?= $studentModel->first_name ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="last-name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last-name" value="<?= $studentModel->last_name ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?= $studentModel->email ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?= $studentModel->phone ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="<?= date('Y-m-d', strtotime($studentModel->dob)) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                    <select name="gender" id="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="Male" <?= $studentModel->gender == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $studentModel->gender == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $studentModel->gender == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="Active" <?= $studentModel->status == 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $studentModel->status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="On Leave" <?= $studentModel->status == 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                        <option value="Suspended" <?= $studentModel->status == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="Graduated" <?= $studentModel->status == 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= $studentModel->address ?></textarea>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Academic Details Tab -->
        <div x-show="activeTab === 'academic'">
            <h2 class="text-xl font-semibold mb-4">Academic Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Student Number</p>
                    <p class="font-medium"><?= htmlspecialchars($studentModel->student_number) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Department</p>
                    <p class="font-medium"><?= htmlspecialchars($departmentModel->department_name) ?> (<?= htmlspecialchars($departmentModel->department_code) ?>)</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Enrollment Year</p>
                    <p class="font-medium"><?= htmlspecialchars($enrollmentYearRange) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Enrollment Type</p>
                    <p class="font-medium"><?= htmlspecialchars($studentModel->enrollment_type) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Current Semester</p>
                    <p class="font-medium"><?= $currentSemesterDisplay ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Program Duration</p>
                    <p class="font-medium"><?= htmlspecialchars($programDuration) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Cumulative GPA</p>
                    <p class="font-medium"><?= $cgpa ?></p>
                </div>
            </div>

            <!-- Semester History -->
            <h3 class="text-lg font-semibold mb-3">Semester History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year/Semester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="hidden px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($semesterHistory)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No semester history available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($semesterHistory as $history): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Year <?= htmlspecialchars($history['year_number']) ?>, Semester <?= htmlspecialchars($history['semester_number']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M Y', strtotime($history['start_date'])) ?> -
                                    <?= date('M Y', strtotime($history['end_date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= number_format($history['gpa'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php if ($history['status'] === 'completed'): ?>
                                        bg-green-100 text-green-800
                                    <?php elseif ($history['status'] === 'failed'): ?>
                                        bg-red-100 text-red-800
                                    <?php elseif ($history['status'] === 'enrolled'): ?>
                                        bg-blue-100 text-blue-800
                                    <?php else: ?>
                                        bg-gray-100 text-gray-800
                                    <?php endif; ?>">
                                    <?= htmlspecialchars($history['status']) ?>
                                </span>
                                </td>
                                <td class="hidden px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="?page=semester_marks&history_id=<?= $history['history_id'] ?>"
                                       class="text-blue-600 hover:text-blue-900">View Marks</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Current Subjects -->
            <?php if ($studentModel->current_semester_id): ?>
                <h3 class="text-lg font-semibold my-6">Current Semester Subjects</h3>
                <?php
                // Get current semester subjects
                $semesterSubjectModel = new SemesterSubject($db);
                $result = $semesterSubjectModel->readBySemester($studentModel->current_semester_id);
                $currentSubjects = $result->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Hours</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($currentSubjects)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No subjects assigned for the current semester</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($currentSubjects as $subject): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($subject['subject_code']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($subject['subject_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($subject['credit_hours']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Marks & Performance Tab -->
        <div x-show="activeTab === 'marks'">
            <h2 class="text-xl font-semibold mb-4">Marks & Performance</h2>

            <?php if (empty($marksBySemester)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No marks data available for this student yet.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($marksBySemester as $semester): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-3">
                            Year <?= $semester['year_number'] ?>, Semester <?= $semester['semester_number'] ?>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($semester['subjects'] as $subject): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($subject['subject_code']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= number_format($subject['credit_hours'], 1) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($subject['marks']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?= getGradeColor($subject['grade']) ?>">
                                            <?= htmlspecialchars($subject['grade']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusColor($subject['status']) ?>">
                                            <?= htmlspecialchars($subject['status']) ?>
                                        </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Overall Performance Summary -->
            <div class="mt-8">
                <h3 class="text-lg font-medium mb-3">Overall Academic Performance</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Cumulative GPA</h4>
                        <p class="text-2xl font-bold <?= ($cgpa >= 3.5) ? 'text-green-600' : (($cgpa >= 2.0) ? 'text-blue-600' : 'text-red-600') ?>">
                            <?= $cgpa ?>
                        </p>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Completed Credit Hours</h4>
                        <p class="text-2xl font-bold text-blue-600">
                            <?= $totalCreditHours ?>
                        </p>
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Program Completion</h4>
                        <div class="flex items-center">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $completionPercentage ?>%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700"><?= $completionPercentage ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div class="mt-6">
                    <h4 class="text-md font-medium mb-2">Grade Distribution</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                        <?php
                        foreach ($gradeDistribution as $grade => $count):
                            if ($count > 0):
                                ?>
                                <div class="bg-white p-3 rounded-lg border border-gray-200 text-center">
                                    <div class="text-lg font-bold <?= getGradeColor($grade) ?>">
                                        <?= $grade ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?= $count ?>
                                        (<?= $totalSubjects > 0 ? round(($count / $totalSubjects) * 100) : 0 ?>%)
                                    </div>
                                </div>
                            <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>

                <!-- Semester GPA Trend -->
                <?php if (count($semesterHistory) > 0): ?>
                    <div class="mt-6">
                        <h4 class="text-md font-medium mb-2">GPA by Semester</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPA</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($semesterHistory as $history): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?= $history['year_number'] ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?= $history['semester_number'] ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium
                                    <?= ($history['gpa'] >= 3.5) ? 'text-green-600' : (($history['gpa'] >= 2.0) ? 'text-blue-600' : 'text-red-600') ?>">
                                            <?= number_format($history['gpa'], 2) ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusColor($history['status']) ?>">
                                        <?= $history['status'] ?>
                                    </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>