<?php
// pages/settings.php
header('Content-Type: text/html');

// Include database and necessary models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Semester.php';
require_once __DIR__ . '/../models/SemesterSubject.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create new department object
$department = new Department($db);

// Create new subject object
$subject = new Subject($db);

// Create new semester object
$semester = new Semester($db);

// Create new semester subject object
$semesterSubject = new SemesterSubject($db);

// Get all departments
$stmt = $department->readAll();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all subjects
$stmt = $subject->readAll();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all semesters
$stmt = $semester->readAll();
$semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all semester subjects
$stmt = $semesterSubject->readAll();
$semesterSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for messages from redirects (optional)
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<div x-data="{
    activeTab: $persist('department'),
    searchTerm: '',
    subjectSearchTerm: '',
    selectedYears: '',
    selectedDepartment: '',
    editingDepartment: null,
    editingSubject: null,
    semesterSearchTerm: '',
    selectedDepartmentForSemester: '',
    editingSemester: null,
    semesterSubjectSearchTerm: '',
    selectedSemesterFilter: '',
    submitForm() {
        const form = document.getElementById('department-form');
        fetch('actions/save_department.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            //alert(data.message);
            if (data.message.includes('successfully')) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            //alert('An error occurred while saving the department');
        });
    },
    deleteDepartment(id) {
        if (confirm('Are you sure you want to delete this department?')) {
            fetch('actions/delete_department.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ department_id: id })
            })
            .then(response => response.json())
            .then(data => {
                //alert(data.message);
                if (data.message.includes('successfully')) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                //alert('An error occurred while deleting the department');
            });
        }
    },
    editDepartment(department) {
        this.editingDepartment = department;
        document.getElementById('department-id').value = department.department_id;
        document.getElementById('department-code').value = department.department_code;
        document.getElementById('department-name').value = department.department_name;
        document.getElementById('total-years').value = department.total_years;
        document.getElementById('semesters_per_year').value = department.semesters_per_year;
        document.getElementById('save-button').textContent = 'Update Department';
    },
    resetForm() {
        this.editingDepartment = null;
        document.getElementById('department-form').reset();
        document.getElementById('department-id').value = '';
        document.getElementById('save-button').textContent = 'Save Changes';
    },
    submitSubjectForm() {
        const form = document.getElementById('subject-form');
        fetch('actions/save_subject.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    deleteSubject(id) {
        if (confirm('Are you sure you want to delete this subject?')) {
            fetch('actions/delete_subject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ subject_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message.includes('successfully')) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    },
    editSubject(subject) {
        this.editingSubject = subject;
        document.getElementById('subject-id').value = subject.subject_id;
        document.getElementById('subject-code').value = subject.subject_code;
        document.getElementById('subject-name').value = subject.subject_name;
        document.getElementById('credit-hours').value = subject.credit_hours;
        document.getElementById('department_id').value = subject.department_id;
        document.getElementById('save-subject-button').textContent = 'Update Subject';
    },
    resetSubjectForm() {
        this.editingSubject = null;
        document.getElementById('subject-form').reset();
        document.getElementById('subject-id').value = '';
        document.getElementById('save-subject-button').textContent = 'Save Subject';
    },
    submitSemesterForm() {
        const form = document.getElementById('semester-form');
        fetch('actions/save_semester.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    deleteSemester(id) {
        if (confirm('Are you sure you want to delete this semester?')) {
            fetch('actions/delete_semester.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ semester_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message.includes('successfully')) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    },
    toggleSemesterStatus(id, currentStatus) {
        fetch('actions/toggle_semester_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                semester_id: id,
                is_active: currentStatus ? 0 : 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    editSemester(semester) {
        this.editingSemester = semester;
        document.getElementById('semester-id').value = semester.semester_id;
        document.getElementById('semester-department').value = semester.department_id;
        document.getElementById('year-number').value = semester.year_number;
        document.getElementById('semester-number').value = semester.semester_number;
        document.getElementById('start-date').value = semester.start_date;
        document.getElementById('end-date').value = semester.end_date;
        document.getElementById('is-active').checked = semester.is_active == 1;
        document.getElementById('save-semester-button').textContent = 'Update Semester';
    },
    resetSemesterForm() {
        this.editingSemester = null;
        document.getElementById('semester-form').reset();
        document.getElementById('semester-id').value = '';
        document.getElementById('save-semester-button').textContent = 'Save Semester';
    },
    submitSemesterSubjectForm() {
        const form = document.getElementById('semester-subject-form');
        fetch('actions/save_semester_subject.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    deleteSemesterSubject(id) {
        if (confirm('Are you sure you want to remove this subject from the semester?')) {
            fetch('actions/delete_semester_subject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ semester_subject_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message.includes('successfully')) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
}">
    <h2 class="text-2xl font-semibold mb-4">Settings</h2>

    <?php if (!empty($message)): ?>
        <div class="mb-4 p-4 rounded-lg text-white"
             :class="{'bg-green-500': '<?= strpos($message, 'success') !== false ?>', 'bg-red-500': '<?= strpos($message, 'error') !== false ?>'}">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="sm:flex sm:divide-x">
            <!-- Tabs -->
            <aside class="py-6 sm:w-80 px-2 sm:px-0 border-b sm:border-b-0">
                <nav class="space-y-1">
                    <!-- Department Management Tab -->
                    <button
                            @click="activeTab = 'department'"
                            :class="{'bg-blue-50 border-l-4 border-blue-500 text-blue-700': activeTab === 'department'}"
                            class="group flex items-center px-3 py-2 text-sm font-medium w-full text-left"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 mr-3"
                                :class="{'text-blue-500': activeTab === 'department'}"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                        >
                            <!-- Building/Department icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Department Management
                    </button>

                    <!-- Subject Management Tab -->
                    <button
                            @click="activeTab = 'subject'"
                            :class="{'bg-blue-50 border-l-4 border-blue-500 text-blue-700': activeTab === 'subject'}"
                            class="group flex items-center px-3 py-2 text-sm font-medium w-full text-left"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 mr-3"
                                :class="{'text-blue-500': activeTab === 'subject'}"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                        >
                            <!-- Book/Subject icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Subject Management
                    </button>

                    <!-- Semester Management Tab -->
                    <button
                            @click="activeTab = 'semester'"
                            :class="{'bg-blue-50 border-l-4 border-blue-500 text-blue-700': activeTab === 'semester'}"
                            class="group flex items-center px-3 py-2 text-sm font-medium w-full text-left"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 mr-3"
                                :class="{'text-blue-500': activeTab === 'semester'}"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                        >
                            <!-- Calendar/Semester icon -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Semester Management
                    </button>

                    <!-- Semester Subjects Tab -->
                    <button
                            @click="activeTab = 'semesterSubject'"
                            :class="{'bg-blue-50 border-l-4 border-blue-500 text-blue-700': activeTab === 'semesterSubject'}"
                            class="group flex items-center px-3 py-2 text-sm font-medium w-full text-left"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 mr-3"
                                :class="{'text-blue-500': activeTab === 'semesterSubject'}"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                        >
                            <!-- Stack/Layers icon for Semester Subjects -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Semester Subjects
                    </button>
                </nav>
            </aside>

            <!-- Tab Content -->
            <div class="p-6 sm:w-full">
                <!-- Department Settings -->
                <div x-show="activeTab === 'department'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Department Information</h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="editingDepartment ? 'Edit Department Information' : 'Add New Department Information'"></p>
                    </div>

                    <form id="department-form" @submit.prevent="submitForm">
                        <input type="hidden" name="department_id" id="department-id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="department-code" class="block text-sm font-medium text-gray-700">Department code</label>
                                <input type="text" name="department_code" id="department-code" placeholder="HNDIT" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="department-name" class="block text-sm font-medium text-gray-700">Department name</label>
                                <input type="text" name="department_name" id="department-name" placeholder="Information Technology" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="total-years" class="block text-sm font-medium text-gray-700">Total years</label>
                                <input type="number" name="total_years" id="total-years" placeholder="2" min="1" max="6" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="semesters_per_year" class="block text-sm font-medium text-gray-700">Semesters per year</label>
                                <input type="number" name="semesters_per_year" id="semesters_per_year" placeholder="2" min="1" max="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4 space-x-3">
                            <button type="button" @click="resetForm" class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none" x-show="editingDepartment">
                                Cancel
                            </button>
                            <button type="submit" id="save-button" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <div class="mb-4 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input
                                        type="text"
                                        placeholder="Search departments..."
                                        class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        x-model="searchTerm"
                                >
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            <select
                                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    x-model="selectedYears"
                            >
                                <option value="">All Years</option>
                                <option value="2">2 Years</option>
                                <option value="3">3 Years</option>
                                <option value="4">4 Years</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Years</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semesters/Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($departments as $dept): ?>
                                <tr
                                        x-show="
                                            (searchTerm === '' ||
                                             '<?= strtolower($dept['department_code']) ?>'.includes(searchTerm.toLowerCase()) ||
                                             '<?= strtolower($dept['department_name']) ?>'.includes(searchTerm.toLowerCase())) &&
                                            (selectedYears === '' || selectedYears === '<?= $dept['total_years'] ?>')
                                        "
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $dept['department_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= $dept['department_code'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $dept['department_name'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?= $dept['total_years'] ?> years
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= $dept['semesters_per_year'] ?> semesters
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y', strtotime($dept['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                                @click="editDepartment(<?= htmlspecialchars(json_encode($dept)) ?>)"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button
                                                @click="deleteDepartment(<?= $dept['department_id'] ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subject Settings -->
                <div x-show="activeTab === 'subject'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Subject Information</h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="editingSubject ? 'Edit Subject Information' : 'Add New Subject Information'"></p>
                    </div>

                    <form id="subject-form" @submit.prevent="submitSubjectForm">
                        <input type="hidden" name="subject_id" id="subject-id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="subject-code" class="block text-sm font-medium text-gray-700">Subject code</label>
                                <input type="text" name="subject_code" id="subject-code" placeholder="IT2022" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="subject-name" class="block text-sm font-medium text-gray-700">Subject name</label>
                                <input type="text" name="subject_name" id="subject-name" placeholder="Software Development" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="credit-hours" class="block text-sm font-medium text-gray-700">Credit Hours</label>
                                <input type="number" name="credit_hours" id="credit-hours" placeholder="3.0" min="0.5" max="6" step="0.5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                <select name="department_id" id="department_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['department_id'] ?>"><?= $dept['department_code'] ?> - <?= $dept['department_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4 space-x-3">
                            <button type="button" @click="resetSubjectForm" class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none" x-show="editingSubject">
                                Cancel
                            </button>
                            <button type="submit" id="save-subject-button" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                                Save Subject
                            </button>
                        </div>
                    </form>

                    <div class="mb-4 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input
                                        type="text"
                                        placeholder="Search subjects..."
                                        class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        x-model="subjectSearchTerm"
                                >
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            <select
                                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    x-model="selectedDepartment"
                            >
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id'] ?>"><?= $dept['department_code'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($subjects as $subj): ?>
                                <tr
                                        x-show="
                            (subjectSearchTerm === '' ||
                             '<?= strtolower($subj['subject_code']) ?>'.includes(subjectSearchTerm.toLowerCase()) ||
                             '<?= strtolower($subj['subject_name']) ?>'.includes(subjectSearchTerm.toLowerCase())) &&
                            (selectedDepartment === '' || selectedDepartment === '<?= $subj['department_id'] ?>')
                        "
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $subj['subject_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= $subj['subject_code'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $subj['subject_name'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?= $subj['credit_hours'] ?>
                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <?= $subj['department_code'] ?>
                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y', strtotime($subj['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                                @click="editSubject(<?= htmlspecialchars(json_encode($subj)) ?>)"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button
                                                @click="deleteSubject(<?= $subj['subject_id'] ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Semester Settings -->
                <div x-show="activeTab === 'semester'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Semester Configuration</h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="editingSemester ? 'Edit Semester Information' : 'Add New Semester'"></p>
                    </div>

                    <form id="semester-form" @submit.prevent="submitSemesterForm">
                        <input type="hidden" name="semester_id" id="semester-id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="semester-department" class="block text-sm font-medium text-gray-700">Department</label>
                                <select name="department_id" id="semester-department" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['department_id'] ?>"><?= $dept['department_code'] ?> - <?= $dept['department_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="year-number" class="block text-sm font-medium text-gray-700">Year Number</label>
                                <input type="number" name="year_number" id="year-number" placeholder="1" min="1" max="6" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="semester-number" class="block text-sm font-medium text-gray-700">Semester Number</label>
                                <input type="number" name="semester_number" id="semester-number" placeholder="1" min="1" max="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="flex items-center h-full">
                                <label class="inline-flex items-center mt-3">
                                    <input type="checkbox" name="is_active" id="is-active" class="form-checkbox h-5 w-5 text-blue-600" value="1">
                                    <span class="ml-2 text-gray-700">Active Semester</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4 space-x-3">
                            <button type="button" @click="resetSemesterForm" class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none" x-show="editingSemester">
                                Cancel
                            </button>
                            <button type="submit" id="save-semester-button" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                                Save Semester
                            </button>
                        </div>
                    </form>

                    <div class="mb-4 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input
                                        type="text"
                                        placeholder="Search semesters..."
                                        class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        x-model="semesterSearchTerm"
                                >
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            <select
                                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    x-model="selectedDepartmentForSemester"
                            >
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id'] ?>"><?= $dept['department_code'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($semesters as $sem): ?>
                                <tr
                                        x-show="
                        (semesterSearchTerm === '' ||
                         '<?= strtolower($sem['department_code']) ?>'.includes(semesterSearchTerm.toLowerCase()) ||
                         'year <?= $sem['year_number'] ?> semester <?= $sem['semester_number'] ?>'.includes(semesterSearchTerm.toLowerCase())) &&
                        (selectedDepartmentForSemester === '' || selectedDepartmentForSemester === '<?= $sem['department_id'] ?>')
                    "
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $sem['semester_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= $sem['department_code'] ?></div>
                                                <div class="text-sm text-gray-500"><?= $sem['department_name'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Year <?= $sem['year_number'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Semester <?= $sem['semester_number'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($sem['start_date'])) ?> - <?= date('M d, Y', strtotime($sem['end_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $sem['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $sem['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                                @click="editSemester(<?= htmlspecialchars(json_encode($sem)) ?>)"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button
                                                @click="toggleSemesterStatus(<?= $sem['semester_id'] ?>, <?= $sem['is_active'] ?>)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <?= $sem['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                        <button
                                                @click="deleteSemester(<?= $sem['semester_id'] ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Semester Subject Settings -->
                <div x-show="activeTab === 'semesterSubject'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Semester Subject Management</h3>
                        <p class="mt-1 text-sm text-gray-500">Assign subjects to specific semesters</p>
                    </div>

                    <form id="semester-subject-form" @submit.prevent="submitSemesterSubjectForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="semester-select" class="block text-sm font-medium text-gray-700">Semester</label>
                                <select name="semester_id" id="semester-select" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Semester</option>
                                    <?php foreach ($semesters as $sem): ?>
                                        <option value="<?= $sem['semester_id'] ?>">
                                            <?= $sem['department_code'] ?> - Year <?= $sem['year_number'] ?> - Semester <?= $sem['semester_number'] ?>
                                            <?= $sem['is_active'] ? '(Active)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="subject-select" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject_id" id="subject-select" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $sub): ?>
                                        <option value="<?= $sub['subject_id'] ?>"><?= $sub['subject_code'] ?> - <?= $sub['subject_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                                Add Subject to Semester
                            </button>
                        </div>
                    </form>

                    <div class="mb-4 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex space-x-2">
                            <div class="relative">
                                <input
                                        type="text"
                                        placeholder="Search semester subjects..."
                                        class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        x-model="semesterSubjectSearchTerm"
                                >
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                            <select
                                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    x-model="selectedSemesterFilter"
                            >
                                <option value="">All Semesters</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?= $sem['semester_id'] ?>">
                                        <?= $sem['department_code'] ?> - Year <?= $sem['year_number'] ?> - Semester <?= $sem['semester_number'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year/Semester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($semesterSubjects as $ss): ?>
                                <tr
                                        x-show="
                        (semesterSubjectSearchTerm === '' ||
                         '<?= strtolower($ss['subject_code']) ?>'.includes(semesterSubjectSearchTerm.toLowerCase()) ||
                         '<?= strtolower($ss['subject_name']) ?>'.includes(semesterSubjectSearchTerm.toLowerCase()) ||
                         '<?= strtolower($ss['department_code']) ?>'.includes(semesterSubjectSearchTerm.toLowerCase())) &&
                        (selectedSemesterFilter === '' || selectedSemesterFilter === '<?= $ss['semester_id'] ?>')
                    "
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $ss['semester_subject_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $ss['department_code'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Year <?= $ss['year_number'] ?> - Semester <?= $ss['semester_number'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $ss['subject_code'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $ss['subject_name'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $ss['credit_hours'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                                @click="deleteSemesterSubject(<?= $ss['semester_subject_id'] ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>