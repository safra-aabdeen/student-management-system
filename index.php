<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="src/output.css">
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <!-- HTMX History Extension -->
    <script src="https://unpkg.com/htmx.org/dist/ext/history.js"></script>
</head>
<body class="bg-[#FAFBFF] font-sans" hx-ext="history" hx-history="true">
<div x-cloak class="flex h-screen" x-data="{ sidebarOpen: $persist(true) }">
    <!-- Sidebar -->
    <div class="bg-[#21263C] text-white w-64 flex-shrink-0 transition-all duration-300 p-2" :class="{'w-64': sidebarOpen, 'w-16': !sidebarOpen}">
        <div class="p-4 flex justify-between items-center">
            <h1 class="font-bold text-xl" x-show="sidebarOpen">ATI Dashboard</h1>
            <button @click="sidebarOpen = !sidebarOpen" class="text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <nav class="mt-6">
            <div class="flex flex-col gap-2">
                <a
                        hx-get="pages/dashboard.php"
                        hx-target="#content"
                        hx-push-url="?page=dashboard"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Dashboard</span>
                </a>
                <a
                        hx-get="pages/student_registration.php"
                        hx-target="#content"
                        hx-push-url="?page=student_registration"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Students</span>
                </a>
                <a
                        hx-get="pages/exam_module.php"
                        hx-target="#content"
                        hx-push-url="?page=exam_module"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 3.586a2 2 0 010 2.828L9.707 14.121a1 1 0 01-.293.207l-5 2a1 1 0 01-1.316-1.316l2-5a1 1 0 01.207-.293l7.707-7.707a2 2 0 012.828 0z" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Exam Eligibility</span>
                </a>
                <a
                        hx-get="pages/student_marks_entry.php"
                        hx-target="#content"
                        hx-push-url="?page=student_marks_entry"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 00-1 1v1H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2h-3V3a1 1 0 00-1-1H9zM8 4V3h4v1H8z" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Marks Entry</span>
                </a>
                <a
                        hx-get="pages/analytics.php"
                        hx-target="#content"
                        hx-push-url="?page=analytics"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="hidden flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Analytics</span>
                </a>
                <a
                        hx-get="pages/settings.php"
                        hx-target="#content"
                        hx-push-url="?page=settings"
                        hx-history="true"
                        hx-trigger="click"
                        hx-indicator="#loading-indicator"
                        class="flex items-center cursor-pointer py-3 px-4 text-gray-300 rounded-lg hover:bg-[#408DFB] hover:text-white transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-3" x-show="sidebarOpen">Settings</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Navbar -->
        <header class="bg-white shadow rounded-lg mx-4 mt-4 relative">
            <div class="px-4 py-3 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-gray-800 font-semibold text-lg">Welcome to Your Dashboard</span>
                    <!-- Loading indicator -->
                    <div id="loading-indicator" class="htmx-indicator ml-3">
                        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center">
                    <button class="relative p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-full focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="ml-4 relative" x-data="{ open: false }">
                        <div>
                            <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900 focus:outline-none">
                                <img class="h-8 w-8 rounded-full object-cover" src="https://lh3.googleusercontent.com/ogw/AF2bZygre_fmBuZpn9KPNJG2zH9osL02r9DQT59YydR2yyAMyiw=s64-c-mo" alt="User avatar">
                                <span class="ml-2">Dhanuka Shamen</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div
                                x-show="open"
                                x-cloak
                                @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1"
                        >
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-4 bg-[#FAFBFF]">
            <div id="content" class="bg-white rounded-lg shadow p-6">
                <!-- Default content - will be replaced by HTMX requests -->
                <h2 class="text-2xl font-semibold mb-4">Dashboard Overview</h2>
                <p class="mb-4">Welcome to your dashboard. Click on the sidebar items to navigate to different sections.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-blue-800">Total Users</h3>
                        <p class="text-3xl font-bold text-blue-900">1,254</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-green-800">Revenue</h3>
                        <p class="text-3xl font-bold text-green-900">$34,567</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-purple-800">New Orders</h3>
                        <p class="text-3xl font-bold text-purple-900">89</p>
                    </div>
                    <div class="bg-orange-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-orange-800">Pending Tasks</h3>
                        <p class="text-3xl font-bold text-orange-900">12</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Initialize Alpine.js data store and handle URL parameters -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to highlight active sidebar item
        const highlightActive = () => {
            // Get current page from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'dashboard';

            // Remove active class from all menu items
            document.querySelectorAll('nav a').forEach(item => {
                item.classList.remove('sidebar-active');
            });

            // Add active class to current page menu item
            const activeItem = document.querySelector(`a[hx-push-url="?page=${page}"]`) ||
                document.querySelector(`a[hx-push-url*="?page=${page}"]`);
            if (activeItem) {
                activeItem.classList.add('sidebar-active');
            }
        };

        // Get current page from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'dashboard';
        const id = urlParams.get('id'); // Get the ID parameter too

        // Load the appropriate page content
        if (page === 'student_profile' && id) {
            // Handle case where we need to load student profile with an ID
            htmx.ajax('GET', `pages/student_profile.php?id=${id}`, {target: '#content'});
        } else {
            const pageElement = document.querySelector(`a[hx-push-url="?page=${page}"]`);
            if (pageElement) {
                htmx.trigger(pageElement, 'click');
            } else {
                // Default to dashboard if page not found
                htmx.trigger(document.querySelector('a[hx-push-url="?page=dashboard"]'), 'click');
            }
        }

        // Highlight active sidebar on initial load
        highlightActive();

        // Event listener for content updates to change page title
        document.body.addEventListener('htmx:afterOnLoad', function(event) {
            const titleElement = event.detail.xhr.responseText.match(/<title>(.*?)<\/title>/);
            if (titleElement && titleElement[1]) {
                document.title = titleElement[1];
            } else {
                // Set default title based on current page
                const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
                document.title = currentPage.charAt(0).toUpperCase() + currentPage.slice(1) + ' - Dashboard';
            }

            // Highlight active sidebar item after content loads
            highlightActive();
        });

        // Handle HTMX history cache misses (when navigating to a page not in cache)
        document.addEventListener('htmx:historyCacheMiss', function(event) {
            const path = event.detail.path;
            const params = new URLSearchParams(path.split('?')[1]);
            const pageParam = params.get('page') || 'dashboard';
            const idParam = params.get('id');

            // Manually load the content with proper parameters
            if (pageParam === 'student_profile' && idParam) {
                htmx.ajax('GET', `pages/${pageParam}.php?id=${idParam}`, {target: '#content'});
            } else {
                htmx.ajax('GET', `pages/${pageParam}.php`, {target: '#content'});
            }

            // Highlight active sidebar item
            setTimeout(highlightActive, 50); // Small delay to ensure DOM is updated
        });

        // Save page state before history save
        document.body.addEventListener('htmx:beforeHistorySave', function(event) {
            // Make sure history object exists
            if (!event.detail.history) {
                event.detail.history = {};
            }

            // Extract the page from the URL and other parameters
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 'dashboard';
            event.detail.history.page = currentPage;

            // Save other parameters too (like id)
            for (const [key, value] of urlParams.entries()) {
                if (key !== 'page') {
                    event.detail.history[key] = value;
                }
            }
        });

        // Handle browser popstate events (back/forward buttons)
        window.addEventListener('popstate', function(event) {
            const state = event.state || {};
            const urlParams = new URLSearchParams(window.location.search);
            const page = state.page || urlParams.get('page') || 'dashboard';
            const id = state.id || urlParams.get('id');

            // Handle page with ID parameters
            if (page === 'student_profile' && id) {
                htmx.ajax('GET', `pages/${page}.php?id=${id}`, {target: '#content'});
            } else {
                htmx.ajax('GET', `pages/${page}.php`, {target: '#content'});
            }

            // Highlight active sidebar item
            setTimeout(highlightActive, 50); // Small delay to ensure DOM is updated
        });

        // Add click event to highlight active menu when clicking on sidebar items
        document.querySelectorAll('nav a').forEach(item => {
            item.addEventListener('click', function() {
                // Slight delay to ensure the URL has been updated
                setTimeout(highlightActive, 50);
            });
        });
    });
</script>
</body>
</html>