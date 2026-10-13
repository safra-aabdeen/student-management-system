<?php
// pages/dashboard.php
header('Content-Type: text/html');
require_once __DIR__ . '/../config/database.php';

// Instantiate Database and get connection
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die("Failed to connect to the database.");
}

// Fetch Total Students
$stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch Active Semesters
$stmt = $pdo->query("SELECT COUNT(*) as total FROM semesters WHERE is_active = 1");
$active_semesters = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch Total Departments
$stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
$total_departments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch Average GPA
$stmt = $pdo->query("SELECT AVG(gpa) as avg_gpa FROM student_semester_history WHERE gpa IS NOT NULL");
$avg_gpa = number_format($stmt->fetch(PDO::FETCH_ASSOC)['avg_gpa'], 2);

// Fetch Recent Student Registrations (last 5)
$stmt = $pdo->query("SELECT student_number, first_name, last_name, created_at 
                     FROM students 
                     ORDER BY created_at DESC 
                     LIMIT 5");
$recent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Semester Subjects (for active semesters)
$stmt = $pdo->query("SELECT COUNT(*) as total 
                     FROM semester_subjects ss 
                     JOIN semesters s ON ss.semester_id = s.semester_id 
                     WHERE s.is_active = 1");
$pending_subjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

?>

<title>Dashboard - Admin Panel</title>
<h2 class="text-2xl font-semibold mb-4">Dashboard Overview</h2>
<p class="mb-4">Welcome to your dashboard. Here's a quick overview of your key metrics.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-100 p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-blue-800">Total Students</h3>
        <p class="text-3xl font-bold text-blue-900"><?= $total_students ?></p>
        <p class="text-sm text-blue-700">Updated today</p>
    </div>
    <div class="bg-green-100 p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-green-800">Active Semesters</h3>
        <p class="text-3xl font-bold text-green-900"><?= $active_semesters ?></p>
        <p class="text-sm text-green-700">Currently running</p>
    </div>
    <div class="bg-purple-100 p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-purple-800">Total Departments</h3>
        <p class="text-3xl font-bold text-purple-900"><?= $total_departments ?></p>
        <p class="text-sm text-purple-700">All programs</p>
    </div>
    <div class="bg-orange-100 p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-orange-800">Average GPA</h3>
        <p class="text-3xl font-bold text-orange-900"><?= $avg_gpa ?></p>
        <p class="text-sm text-orange-700">Across all students</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Recent Student Registrations</h3>
        <div class="space-y-3">
            <?php foreach ($recent_students as $student): ?>
                <div class="flex items-center border-b pb-2">
                    <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> (<?= htmlspecialchars($student['student_number']) ?>)</p>
                        <p class="text-sm text-gray-500"><?= date('F j, Y, g:i a', strtotime($student['created_at'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Quick Tasks</h3>
        <div class="space-y-2">
            <?php
            $tasks = [
                'Review new student registrations',
                'Assign subjects to active semesters',
                'Update department details',
                'Check student marks for errors',
                'Toggle semester status'
            ];
            ?>
            <?php foreach ($tasks as $index => $task): ?>
                <div class="flex items-center" x-data="{ completed: false }">
                    <input type="checkbox" id="task-<?= $index ?>" class="mr-3" x-model="completed">
                    <label for="task-<?= $index ?>" class="flex-1" :class="{ 'line-through text-gray-400': completed }"><?= $task ?></label>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Today</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>