<?php
// pages/student_registration.php

// Include database and necessary models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Semester.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Department model to get all departments
$departmentModel = new Department($db);
$departments = $departmentModel->readAll();

// Get current active students for the table display
$studentModel = new Student($db);
$students = $studentModel->readAll(20); // Limit to recent 20 students

// Get enrollment years (current and previous years)
$currentYear = date('Y');
$enrollmentYears = [];
for ($i = 0; $i < 5; $i++) {
    $year = $currentYear - $i;
    $nextYear = $year + 1;
    $shortYear = substr($year, 2) . substr($nextYear, 2);
    $enrollmentYears[$shortYear] = $year . '-' . $nextYear;
}
?>

<div x-data="{
    searchTerm: '',
    selectedStatus: '',
    selectedDepartment: '',
    selectedBatch: '',
    studentId: null,
    studentNumber: '',  // Add this line
    departmentCode: '',
    enrollmentYear: '',
    enrollmentType: '',
    nextSequence: 0,
    isEditing: false,
    formTitle: 'Register New Student',
    buttonText: 'Register Student',
    generateStudentNumber() {
        if (this.departmentCode && this.enrollmentYear && this.enrollmentType) {
            let sequenceStr = String(this.nextSequence).padStart(4, '0');
            return 'KUR/' + this.departmentCode + '/' + this.enrollmentYear + '/' + this.enrollmentType + '/' + sequenceStr;
        }
        return '';
    },
    resetForm() {
        document.getElementById('student-registration-form').reset();
        this.studentId = null;
        this.departmentCode = '';
        this.enrollmentYear = '';
        this.enrollmentType = '';
        this.nextSequence = 0;
        this.isEditing = false;
        this.formTitle = 'Register New Student';
        this.buttonText = 'Register Student';

        // Enable fields that should be disabled during edit
        document.getElementById('department').disabled = false;
        document.getElementById('enrollment-year').disabled = false;
        document.getElementById('enrollment-type').disabled = false;
    },
    editStudent(studentId) {
        fetch('actions/get_student.php?id=' + studentId)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Set form in edit mode
                    this.isEditing = true;
                    this.formTitle = 'Edit Student Information';
                    this.buttonText = 'Update Student';

                    // Populate form fields
                    this.studentId = data.data.student_id;
                    this.studentNumber = data.data.student_number;  // Add this line
                    document.getElementById('student-number').value = data.data.student_number;
                    document.getElementById('first-name').value = data.data.first_name;
                    document.getElementById('last-name').value = data.data.last_name;
                    document.getElementById('email').value = data.data.email;
                    document.getElementById('phone').value = data.data.phone || '';
                    document.getElementById('address').value = data.data.address || '';
                    document.getElementById('dob').value = data.data.dob;
                    document.getElementById('gender').value = data.data.gender;
                    document.getElementById('status').value = data.data.status;

                    // For edit, these fields should be disabled
                    document.getElementById('department').value = data.data.department_id;
                    document.getElementById('department').disabled = true;

                    document.getElementById('enrollment-year').value = data.data.enrollment_year;
                    document.getElementById('enrollment-year').disabled = true;

                    document.getElementById('enrollment-type').value = data.data.enrollment_type;
                    document.getElementById('enrollment-type').disabled = true;

                    // Scroll to the form
                    document.getElementById('registration-form-section').scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Show error notification
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching student data');
            });
    },
    submitForm(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('student-registration-form'));

        fetch('actions/save_student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Show success notification
                alert(data.message);

                // Reset form for new registration
                this.resetForm();

                // Refresh student list
                document.dispatchEvent(new CustomEvent('student-updated'));

                // Reload the page to refresh the student list
                window.location.reload();
            } else {
                // Show error notification
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving student data');
        });
    }
}">
    <h2 class="text-2xl font-semibold mb-4">Student Registration</h2>

    <div id="registration-form-section">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Student Information</h3>
        <p class="mt-1 text-sm text-gray-500" x-text="formTitle">Register New Student</p>
    </div>

    <form id="student-registration-form" @submit.prevent="submitForm">

        <input type="hidden" name="student_id" id="student-id" x-bind:value="studentId">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="student-number" class="block text-sm font-medium text-gray-700">Student Number</label>
                <input type="text" name="student_number" id="student-number" x-bind:value="isEditing ? studentNumber : generateStudentNumber()" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" readonly>
                <p class="text-xs text-gray-500 mt-1">Auto-generated based on department, year and enrollment type</p>
            </div>
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                <select name="department_id" id="department" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required
                        x-on:change="departmentCode = $event.target.options[$event.target.selectedIndex].getAttribute('data-code');
                    if (departmentCode && enrollmentYear && enrollmentType) {
                        fetch('actions/get_next_sequence.php?department_id=' + $event.target.value + '&enrollment_year=' + enrollmentYear + '&enrollment_type=' + enrollmentType)
                        .then(response => response.json())
                        .then(data => { nextSequence = data.next_sequence })
                    }">
                    <option value="">Select Department</option>
                    <?php
                    while ($department = $departments->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"{$department['department_id']}\" data-code=\"{$department['department_code']}\">{$department['department_name']} ({$department['department_code']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="enrollment-year" class="block text-sm font-medium text-gray-700">Enrollment Year</label>
                <select name="enrollment_year" id="enrollment-year" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required
                        x-on:change="enrollmentYear = $event.target.value;
                    if(departmentCode && enrollmentYear && enrollmentType) {
                        fetch('actions/get_next_sequence.php?department_id=' + document.getElementById('department').value + '&enrollment_year=' + enrollmentYear + '&enrollment_type=' + enrollmentType)
                        .then(response => response.json())
                        .then(data => { nextSequence = data.next_sequence })
                    }">
                    <option value="">Select Year</option>
                    <?php foreach ($enrollmentYears as $yearCode => $yearDisplay): ?>
                        <option value="<?= $yearCode ?>"><?= $yearDisplay ?> (<?= $yearCode ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="enrollment-type" class="block text-sm font-medium text-gray-700">Enrollment Type</label>
                <select name="enrollment_type" id="enrollment-type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required
                        x-on:change="enrollmentType = $event.target.value;
                    if(departmentCode && enrollmentYear && enrollmentType) {
                        fetch('actions/get_next_sequence.php?department_id=' + document.getElementById('department').value + '&enrollment_year=' + enrollmentYear + '&enrollment_type=' + enrollmentType)
                        .then(response => response.json())
                        .then(data => { nextSequence = data.next_sequence })
                    }">
                    <option value="">Select Type</option>
                    <option value="F">Full-time (F)</option>
                    <option value="P">Part-time (P)</option>
                </select>
            </div>
            <div>
                <label for="first-name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" name="first_name" id="first-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="last-name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" name="last_name" id="last-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                <select name="gender" id="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="On Leave">On Leave</option>
                    <option value="Suspended">Suspended</option>
                    <option value="Graduated">Graduated</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4 space-x-3">
            <button type="button" @click="resetForm" class="bg-gray-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none" x-show="isEditing">
                Cancel
            </button>
            <button type="submit" id="save-button" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none">
                <span x-text="buttonText">Register Student</span>
            </button>
        </div>
    </form>

    <div class="mt-8">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Students List</h3>
        <div class="mb-4 flex flex-wrap justify-between items-center gap-4 mt-4">
            <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <input
                            type="text"
                            placeholder="Search students..."
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
                        x-model="selectedStatus"
                >
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="On Leave">On Leave</option>
                    <option value="Suspended">Suspended</option>
                    <option value="Graduated">Graduated</option>
                </select>
                <select
                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        x-model="selectedDepartment"
                >
                    <option value="">All Departments</option>
                    <?php
                    // Reset the pointer of departments result set
                    $departments->execute();
                    while ($department = $departments->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"{$department['department_code']}\">{$department['department_name']} ({$department['department_code']})</option>";
                    }
                    ?>
                </select>
                <select
                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        x-model="selectedBatch"
                >
                    <option value="">All Batches</option>
                    <?php
                    // Get distinct enrollment years from students
                    $batchQuery = $db->prepare("SELECT DISTINCT enrollment_year FROM students ORDER BY enrollment_year DESC");
                    $batchQuery->execute();
                    while ($batch = $batchQuery->fetch(PDO::FETCH_ASSOC)) {
                        $yearCode = $batch['enrollment_year'];
                        $yearDisplay = '20' . substr($yearCode, 0, 2) . '-20' . substr($yearCode, 2, 2);
                        echo "<option value=\"{$yearCode}\">{$yearDisplay} ({$yearCode})</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="students-table-body" hx-trigger="student-updated from:body">
                <?php foreach ($students as $student): ?>
                    <tr
                        x-show="
                            (searchTerm === '' ||
                             '<?= strtolower($student['student_number']) ?>'.includes(searchTerm.toLowerCase()) ||
                             '<?= strtolower($student['first_name'] . ' ' . $student['last_name']) ?>'.includes(searchTerm.toLowerCase()) ||
                             '<?= strtolower($student['email']) ?>'.includes(searchTerm.toLowerCase())) &&
                            (selectedStatus === '' || selectedStatus === '<?= $student['status'] ?>') &&
                            (selectedDepartment === '' || selectedDepartment === '<?= $student['department_code'] ?>') &&
                            (selectedBatch === '' || selectedBatch === '<?= $student['enrollment_year'] ?>')
                        "
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $student['student_id'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $student['student_number'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= $student['first_name'] . ' ' . $student['last_name'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $student['department_code'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $student['email'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if ($student['status'] === 'Active'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?= $student['status'] ?>
                                </span>
                            <?php elseif ($student['status'] === 'Inactive'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= $student['status'] ?>
                                </span>
                            <?php elseif ($student['status'] === 'Graduated'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= $student['status'] ?>
                                </span>
                            <?php elseif ($student['status'] === 'Suspended'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <?= $student['status'] ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <?= $student['status'] ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button @click="editStudent(<?= $student['student_id'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <a
                                    hx-get="pages/student_profile.php?id=<?= $student['student_id'] ?>"
                                    hx-target="#content"
                                    hx-push-url="?page=student_profile&id=<?= $student['student_id'] ?>"
                                    hx-history="true"
                                    hx-trigger="click"
                                    hx-indicator="#loading-indicator"
                                    class="text-blue-600 hover:text-blue-900"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>