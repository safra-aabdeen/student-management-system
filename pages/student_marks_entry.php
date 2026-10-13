<?php
// pages/student_marks_entry.php


// Title for the page
echo "<title>Student Marks Entry</title>";
?>
<div>
    <h2 class="text-2xl font-semibold mb-6">Student Marks Entry</h2>

    <!-- Filter Section -->
    <div class="bg-blue-50 p-6 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-4 text-blue-800">Select Criteria</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Department Selection -->
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select id="department" name="department_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        hx-get="pages/fragments/semesters_by_department.php"
                        hx-target="#semester-container"
                        hx-trigger="change"
                        hx-indicator="#loading-indicator">
                    <option value="">Select Department</option>
                    <!-- PHP will populate departments -->
                </select>
            </div>

            <!-- Semester Selection -->
            <div id="semester-container">
                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                <select id="semester" name="semester_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        hx-get="pages/fragments/subjects_by_semester.php"
                        hx-target="#subject-container"
                        hx-trigger="change"
                        hx-indicator="#loading-indicator">
                    <option value="">Select Semester</option>
                    <!-- Will be populated via HTMX -->
                </select>
            </div>

            <!-- Subject Selection -->
            <div id="subject-container">
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select id="subject" name="semester_subject_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                        hx-get="pages/fragments/students_by_subject.php"
                        hx-target="#students-list"
                        hx-trigger="change"
                        hx-indicator="#loading-indicator">
                    <option value="">Select Subject</option>
                    <!-- Will be populated via HTMX -->
                </select>
            </div>

            <!-- Assessment Type -->
            <div>
                <label for="assessment_type" class="block text-sm font-medium text-gray-700 mb-1">Assessment Type</label>
                <select id="assessment_type" name="assessment_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="assignment">Assignment</option>
                    <option value="final">Final Exam</option>
                </select>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="htmx-indicator flex items-center justify-center my-4">
            <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading...</span>
        </div>
    </div>

    <!-- Students List and Marks Entry -->
    <div id="students-list" class="bg-white rounded-lg shadow">
        <!-- Initially empty, will be populated by HTMX -->
        <div class="p-8 text-center text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-lg font-medium">No Students Selected</p>
            <p class="mt-1">Please select a department, semester, and subject to view students</p>
        </div>
    </div>
</div>

<!-- Toast Notification for Success/Error -->
<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-init="
            window.addEventListener('marks-saved', (e) => {
                message = e.detail.message;
                type = e.detail.type;
                show = true;
                setTimeout(() => { show = false }, 3000);
            })
        "
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-90"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-90"
    class="fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg"
    :class="type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
>
    <div class="flex items-center">
        <svg x-show="type === 'success'" class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <svg x-show="type === 'error'" class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span x-text="message"></span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Populate departments on page load
        fetch('actions/get_departments.php')
            .then(response => response.json())
            .then(data => {
                const departmentSelect = document.getElementById('department');
                data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.department_id;
                    option.textContent = dept.department_name;
                    departmentSelect.appendChild(option);
                });
            });

        // Handle form submission through htmx events
        document.body.addEventListener('htmx:afterSwap', function(event) {
            if (event.detail.target.id === 'students-list') {
                // Initialize form validation and submission for newly loaded content
                setupFormHandlers();
            }
        });
    });

    function setupFormHandlers() {
        const form = document.querySelector('#marks-form');
        if (form) {
            form.addEventListener('submit', function(event) {
                // Prevent default form submission
                event.preventDefault();

                // Collect all input data
                const formData = new FormData(form);

                // Send data via fetch API
                fetch('actions/save_marks.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        // Dispatch custom event for notification
                        const event = new CustomEvent('marks-saved', {
                            detail: {
                                message: data.success ? 'Marks saved successfully!' : 'Error saving marks: ' + data.message,
                                type: data.success ? 'success' : 'error'
                            }
                        });
                        window.dispatchEvent(event);

                        // Refresh student list if successful
                        if (data.success) {
                            const subjectSelect = document.getElementById('subject');
                            htmx.trigger(subjectSelect, 'change');
                        }
                    })
                    .catch(error => {
                        const event = new CustomEvent('marks-saved', {
                            detail: {
                                message: 'System error occurred. Please try again.',
                                type: 'error'
                            }
                        });
                        window.dispatchEvent(event);
                    });
            });
        }
    }
</script>