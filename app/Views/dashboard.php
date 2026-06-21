<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gunslinger</title>
    <!-- Favicon -->
    <link rel="icon" href="https://placehold.co/32x32/3b82f6/ffffff?text=G">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom font import for a cleaner look */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        /* --- Custom CSS for Sidebar Animations and States --- */

        /* Base styles for the sidebar */
        #sidebar {
            transition: width 0.3s ease-in-out;
        }
        
        /* Base styles for the main content area */
        #main-content {
            transition: margin-left 0.3s ease-in-out;
        }

        /* Default open state for desktop */
        body.sidebar-open #sidebar {
            width: 16rem; /* Default width for desktop (lg:w-64) */
        }
        
        body.sidebar-open #main-content {
            margin-left: 16rem; /* Push main content to the right */
        }

        /* Collapsed state for desktop */
        body.sidebar-collapsed #sidebar {
            width: 5rem; /* Collapsed width (icon-only) */
        }

        body.sidebar-collapsed #main-content {
            margin-left: 5rem; /* Adjust margin for collapsed sidebar */
        }

        /* Hide text on collapsed sidebar */
        body.sidebar-collapsed .sidebar-text {
            display: none;
        }

        /* Mobile specific styles */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                width: 16rem; /* Full width when open on mobile */
            }

            body.sidebar-open #sidebar {
                transform: translateX(0); /* Slide in from the left */
            }

            /* No margin push on mobile, sidebar will overlay content */
            body.sidebar-open #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 z-20 flex-shrink-0 bg-gray-800 text-white shadow-xl h-full flex flex-col overflow-y-auto">
        <!-- Sidebar Header with Logo -->
        <div class="p-4 lg:p-6 mb-4 flex-shrink-0">
            <a href="#" class="flex items-center space-x-2 w-full sidebar-open-state">
                <img src="https://placehold.co/40x40/3b82f6/ffffff?text=Logo" alt="Company Logo" class="rounded-full flex-shrink-0">
                <span class="font-bold text-lg sidebar-text">Gunslinger</span>
            </a>
        </div>
        
        <!-- Sidebar menu content -->
        <nav class="flex-1 px-2 py-4 space-y-2 lg:space-y-1">
            <a href="/dashboard" class="flex items-center px-4 py-2 text-sm font-semibold text-white bg-gray-700 rounded-lg shadow-md hover:bg-gray-700 transition duration-150 ease-in-out space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="/bulkemail" class="flex items-center px-4 py-2 text-sm text-gray-400 rounded-lg hover:bg-gray-700 hover:text-white transition duration-150 ease-in-out space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="sidebar-text">Campaigns</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content Container -->
    <div id="main-content" class="flex flex-col flex-1">
        <!-- Main Header -->
        <header class="flex items-center w-full h-16 bg-white shadow-md p-4 sticky top-0 z-10">
            <!-- Sidebar toggle button -->
            <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 mr-4">
                <svg id="toggle-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path id="toggle-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <div class="ml-auto text-sm text-gray-500">
                Welcome, User
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
            <!-- Dashboard overview section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card 1: Total Users -->
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M12 4.354a4 4 0 100 5.292m0-5.292a4 4 0 110-5.292m0 5.292a4 4 0 100-5.292M12 15a7 7 0 017 7H5a7 7 0 017-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Users</h2>
                        <p class="text-3xl font-bold text-blue-600">1,234</p>
                    </div>
                </div>
                <!-- Card 2: Revenue -->
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9v6m-4-6v6m-4-6v6M3 9h18M3 15h18m-9-3a3 3 0 11-6 0 3 3 0 016 0zm-3 0a3 3 0 10-6 0 3 3 0 006 0zm-3 0a3 3 0 11-6 0 3 3 0 016 0zm-3 0a3 3 0 10-6 0 3 3 0 006 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Revenue</h2>
                        <p class="text-3xl font-bold text-green-600">$56,789</p>
                    </div>
                </div>
                <!-- Card 3: Orders -->
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 01-2-2M15 12h-6M12 12v6m0-6a3 3 0 10-6 0 3 3 0 006 0zm-3 0a3 3 11-6 0 3 3 0 016 0zm-3 0a3 3 0 10-6 0 3 3 0 006 0zm-3 0a3 3 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Orders</h2>
                        <p class="text-3xl font-bold text-orange-600">456</p>
                    </div>
                </div>
                <!-- Card 4: Active Sessions -->
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Active Sessions</h2>
                        <p class="text-3xl font-bold text-purple-600">78</p>
                    </div>
                </div>
            </div>

            <!-- Additional Dashboard Section (e.g., charts or tables) -->
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h2>
                <!-- Placeholder for a list or table -->
                <ul class="space-y-4">
                    <li class="p-4 bg-gray-100 rounded-lg">New user registered: John Doe</li>
                    <li class="p-4 bg-gray-100 rounded-lg">Order #10123 processed successfully.</li>
                    <li class="p-4 bg-gray-100 rounded-lg">Server uptime at 99.9%.</li>
                </ul>
            </div>
        </main>

        <!-- Footer -->
        <!-- <footer class="w-full bg-gray-800 text-white p-4 text-center text-sm rounded-t-xl">
            &copy; 2025 Your Company. All rights reserved.
        </footer> -->
    </div>

    <!-- JavaScript for sidebar functionality -->
    <script>
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const body = document.body;
        const togglePath = document.getElementById('toggle-path');
        const menuIconPath = "M4 6h16M4 12h16M4 18h16";
        const closeIconPath = "M6 18L18 6M6 6l12 12";
        
        // This function will be triggered on button click
        function toggleSidebar() {
            // Check if the screen is large (desktop)
            if (window.innerWidth >= 1024) {
                // For desktop, toggle between open and collapsed states
                if (body.classList.contains('sidebar-open')) {
                    body.classList.remove('sidebar-open');
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                    body.classList.add('sidebar-open');
                }
            } else {
                // For mobile, simply toggle the open/closed state
                body.classList.toggle('sidebar-open');
                // Toggle the icon
                if (body.classList.contains('sidebar-open')) {
                    togglePath.setAttribute('d', closeIconPath);
                } else {
                    togglePath.setAttribute('d', menuIconPath);
                }
            }
        }

        // Event listener for the toggle button
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Ensure proper initial state on load and resize
        function handleResize() {
            if (window.innerWidth >= 1024) {
                // On desktop, default to the open state
                body.classList.add('sidebar-open');
                body.classList.remove('sidebar-collapsed');
                // Ensure the icon is the menu icon on desktop
                togglePath.setAttribute('d', menuIconPath);
            } else {
                // On mobile, default to the collapsed (hidden) state
                body.classList.remove('sidebar-open');
                body.classList.remove('sidebar-collapsed');
                // Ensure the icon is the menu icon on mobile when collapsed
                togglePath.setAttribute('d', menuIconPath);
            }
        }
        
        // Set initial state on page load
        handleResize();

        // Listen for window resize to adjust the layout
        window.addEventListener('resize', handleResize);
    </script>

</body>
</html>
