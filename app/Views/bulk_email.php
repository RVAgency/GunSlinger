<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create campaign</title>
    <!--<link rel="icon" href="https://placehold.co/32x32/3b82f6/ffffff?text=AD">-->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/fontawesome.min.js"></script>
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
            width: 16rem;
            /* Default width for desktop (lg:w-64) */
        }

        body.sidebar-open #main-content {
            margin-left: 16rem;
            /* Push main content to the right */
        }

        /* Collapsed state for desktop */
        body.sidebar-collapsed #sidebar {
            width: 5rem;
            /* Collapsed width (icon-only) */
        }

        body.sidebar-collapsed #main-content {
            margin-left: 5rem;
            /* Adjust margin for collapsed sidebar */
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
                width: 16rem;
                /* Full width when open on mobile */
            }

            body.sidebar-open #sidebar {
                transform: translateX(0);
                /* Slide in from the left */
            }

            /* No margin push on mobile, sidebar will overlay content */
            body.sidebar-open #main-content {
                margin-left: 0;
            }
        }

        /* Custom styles for validation message */
        .validation-message {
            color: #ef4444;
            /* red-500 */
            font-size: 0.875rem;
            /* text-sm */
            margin-top: 0.25rem;
            /* mt-1 */
        }

        /* Full-page loader styles */
        #fullPageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 1000;
        }

        .loader-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            /* blue-500 */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            margin-top: 1rem;
            font-size: 1.25rem;
            /* text-xl */
            font-weight: 500;
            /* font-medium */
            color: #1f2937;
            /* gray-800 */
        }

        .customDropdownSelected_span span {
            display: none;
        }
        .custom-option-data.active,
        .custom-option-data.active{
            background-color: #e5e7eb;
        }

        #customDropdownMenu,
        #customDropdownMenu1, 
        #customDropdownBtn2 {
            max-height: 290px;
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen">

    <div id="fullPageLoader">
        <div class="loader-spinner"></div>
        <p class="loader-text">Sending...</p>
    </div>

    <aside id="sidebar"
        class="fixed top-0 left-0 z-20 flex-shrink-0 bg-gray-800 text-white shadow-xl h-full flex flex-col overflow-y-auto">
        <div class="p-4 lg:p-6 flex-shrink-0">
            <a href="#" class="flex items-center space-x-2 w-full sidebar-open-state">
                <img src="<?php echo base_url();?>/uploads/gunslingerlogo.png" alt="GunSlinger Logo" class="w-[200px]" />
            </a>
        </div>

        <?php 
            $current = uri_string(); // Get current path
            $menuItems = [
                [
                    'title' => 'New Campaign',
                    'url' => 'campaigns',
                    'icon' => ''
                ],
                [
                    'title' => 'Campaigns',
                    'url' => 'campaigns_list',
                    'icon' => '<i class="fas fa-bullhorn"></i>'
                ]
            ];
        ?>
        <nav class="flex-1 px-2 py-4 space-y-2 lg:space-y-1">
            <?php foreach($menuItems as $item): 
                $isActive = ($current == $item['url']);
            ?>
            <a href="<?= $item['url'] ?>"
                class="flex items-center px-4 py-2 text-sm rounded-lg space-x-3 transition duration-150 ease-in-out
                <?= $isActive ? 'text-white bg-gray-700 shadow-md' : 'text-gray-400 hover:bg-gray-700 hover:text-white' ?>">
                <?= $item['icon'] ?>
                <span class="sidebar-text">
                    <?= $item['title'] ?>
                </span>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <div id="main-content" class="flex flex-col flex-1">
        <header class="flex items-center w-full h-16 bg-white shadow-md p-4 sticky top-0 z-10">
            <!-- Sidebar toggle -->
            <!-- <button id="sidebar-toggle1"
                class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 mr-4">
                <svg id="toggle-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path id="toggle-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button> -->
            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-800">Bulk Emails</h1>
            <!-- Right section -->
            <div class="ml-auto flex items-center space-x-4">
                <a href="/logout"
                    class="px-3 py-1 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 focus:outline-none">
                    Logout
                </a>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-8 overflow-y-auto flex justify-center">
            <div class="bg-white p-6 rounded shadow mb-6 w-full max-w-4xl rounded-xl">
                <h1 class="text-lg font-semibold mb-4" style="font-size:25px;"><i
                        class="fa-solid fa-envelope mr-2"></i>Email Campaign</h1>
                <form id="emailForm" class="space-y-4">
                    <div>
                        <label class="block text-gray-700">Email Subject</label>
                        <input type="text" id="emailSubject" name="emailSubject"
                            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter your email subject">
                        <div class="validation-message"></div>
                    </div>
                    <div>
                        <label class="block text-gray-700">Campaign Name</label>
                        <input type="text" id="campaignName" name="campaignName"
                            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter your campaign name">
                            <div class="validation-message"></div>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- <div id="fileGroup" class="w-full" style="min-height: 98px;"> -->
                        <div id="RecipientGroup" class="w-full" style="min-height: 95px;">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-gray-700">Recipient Group</label>
                                <button type="button" id="openModalBtn" class="text-sm text-blue-600 hover:underline">
                                    Upload Excel
                                </button>
                            </div>
                            <div class="relative w-full">
                                <button id="customDropdownBtn1" type="button"
                                    class="w-full border border-gray-300 rounded px-3 py-2 mt-1 bg-white text-left flex items-center justify-between">
                                    <span id="customDropdownSelected1" class="customDropdownSelected_span">-- Select Recipient Group --</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="customDropdownMenu1" class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-10 hidden">
                                    <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data recipient_group_html_parent" data-value="">
                                        <div class="font-medium">-- Select Recipient Group --</div>
                                    </div>
                                    <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 needtoremoveoption">
                                        <div class="font-medium">Loading...</div>
                                    </div>
                                </div>
                                <input type="hidden" id="fromReceipeintGroup" name="fromReceipeintGroup" value="">
                                <input type="hidden" id="ReceipeintGroupId" name="ReceipeintGroupId" value="">
                                <input type="hidden" id="ReceipeintGroupName" name="ReceipeintGroupName" value="">
                                <input type="hidden" id="ReceipeintGroupCount" name="ReceipeintGroupCount" value="">
                            </div>
                            <div class="validation-message"></div>
                        </div>
                        <div id="fromEmailGroup" class="w-full" style="min-height: 100px;">
                            <label class="block text-gray-700 mb-2">Send From</label>
                            <div class="relative w-full">
                                <button id="customDropdownBtn" type="button"
                                    class="w-full border border-gray-300 rounded px-3 py-2 mt-1 bg-white text-left flex items-center justify-between">
                                    <span id="customDropdownSelected" class="customDropdownSelected_span">-- Select Sender Email --</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="customDropdownMenu" class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-10 hidden">
                                    <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data senders_list_html_parent">
                                        <div class="font-medium">-- Select Sender Email --</div>
                                    </div>
                                    <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 needtoremoveoption">
                                        <div class="font-medium">Loading...</div>
                                    </div>
                                </div>
                                <input type="hidden" id="fromEmailSelect" name="fromEmailSelect" value="">
                                <input type="hidden" id="fromEmailName" name="fromEmailName" value="">
                            </div>
                            <div class="validation-message"></div>
                        </div>
                    </div>
                    <h1 class="text-lg font-semibold mb-4" style="font-size:25px;"><i class="fa-solid fa-envelope-open-text mr-2"></i></i>Mail Content</h1>
                    <div id="templateGroup" class="w-full">
                        <label class="block text-gray-700">Select Designed Template</label>
                            <div class="relative w-full">
                                <button id="customDropdownBtn2" type="button"
                                    class="w-full border border-gray-300 rounded px-3 py-2 mt-1 bg-white text-left flex items-center justify-between">
                                    <span id="customDropdownSelected2" class="customDropdownSelected_span">-- Select Designed Template --</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="customDropdownMenu2" class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-10 hidden">
                                    <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data designed_template_html_parent" data-value="">
                                        <div class="font-medium">-- Select Designed Template --</div>
                                    </div>
                                    <?php if (!empty($templates)): ?>
                                        <?php foreach ($templates as $template): ?>
                                            <div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data" data-value="<?= esc($template['id']) ?>">
                                            <div class="font-medium"><?= esc($template['name']) ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="templateSelect" name="templateSelect" value="">
                            </div>
                            <div class="validation-message"></div>
                        </div>
                    <div class="pt-6">
                        <div class="flex justify-end space-x-2 mt-6">
                            <button type="button"
                                class="px-6 py-3 bg-gray-400 text-white font-bold rounded-lg shadow-md hover:bg-gray-700 btn-clear-form">Clear</button>
                            <button type="button" id="openModalBtn"
                                class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 btn-send-email">Send
                                Campaign</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <div id="confirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-80 backdrop-blur-sm hidden transition-opacity duration-300">
        <div class="relative w-full max-w-2xl p-10 bg-white rounded-3xl shadow-3xl transform transition-transform duration-300 scale-95">
            <button onclick="hideModalConfirm();" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-3xl"></i>
            </button>
            <div class="flex items-center gap-2">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50">
                    <i class="fa-solid fa-paper-plane text-xl text-indigo-600"></i>
                </div>
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-gray-900">Confirm Campaign Send</h2>
                    <p class="text-base text-gray-500">Please review the campaign details below before sending.</p>
                </div>
            </div>
            <div class="mt-8 space-y-5">
                <div class="flex flex-col py-2 border-b border-gray-200">
                    <p class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-envelope mr-3 text-indigo-500"></i>
                        Email Subject
                    </p>
                    <p id="email_subject_confirm" class="text-base font-semibold text-gray-800">
                        Loading...
                    </p>
                </div>
                <div class="flex flex-col py-2 border-b border-gray-200">
                    <div class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-bullhorn text-indigo-500 mr-2"></i>
                        <span>Campaign Name</span>
                    </div>
                    <p id="campaign_name_confirm" class="text-base font-semibold text-gray-800 mt-1">
                        Loading...
                    </p>
                </div>
                <div class="flex flex-col py-2 border-b border-gray-200">
                    <div class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-paper-plane text-indigo-500 mr-2"></i>
                        <span>Sender Email</span>
                    </div>
                    <p id="sender_email_confirm" class="text-base font-semibold text-gray-800 mt-1">
                        Loading...
                    </p>
                </div>
                <div class="flex flex-col py-2 border-b border-gray-200">
                    <p class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-users mr-3 text-indigo-500"></i>
                        Recipient Group
                    </p>
                    <p id="sender_recipient_group_confirm" class="text-base font-semibold text-gray-800">
                        Loading...
                    </p>
                </div>
                <div class="flex flex-col py-2 border-b border-gray-200">
                    <p class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-file-csv mr-3 text-indigo-500"></i>
                        Source Filename
                    </p>
                    <p id="sender_source_filename_confirm" class="text-base font-semibold text-gray-800">
                        Loading...
                    </p>
                </div>
                <div class="flex items-center justify-between py-2">
                    <p class="flex items-center text-base font-medium text-gray-500">
                        <i class="fa-solid fa-list-ol mr-3 text-indigo-500"></i>
                        Recipient List Count
                    </p>
                    <p id="sender_recipient_confirm" class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow-sm">0</p>
                </div>
            </div>
            <div class="mt-10 flex justify-end space-x-4">
                <button onclick="hideModalConfirm();" type="button" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-xl shadow-sm font-semibold text-base text-gray-700 bg-white hover:bg-gray-50 transition-colors">Cancel</button>
                <button id="confirm_campaign_send" type="button" class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-sm font-semibold text-base text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"> <i class="fa-solid fa-paper-plane mr-3"></i>Confirm & Send</button>
            </div>
        </div>
    </div>
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-96 p-6 text-center">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Confirm Logout</h2>
            <p class="text-gray-600 mb-6">Are you sure you want to log out?</p>
            <div class="flex justify-center space-x-4">
                <button id="cancelLogout"
                    class="px-4 py-2 rounded-lg bg-gray-300 text-gray-800 hover:bg-gray-400">Cancel</button>
                <button id="confirmLogout"
                    class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Logout</button>
            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div id="modalContainer"
        class="fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 hidden flex items-center justify-center p-4">
        <!-- Modal Content -->
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0"
            id="modalContent">
            <!-- Modal Header with Close Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Create Recipient Group</h2>
                <button id="closeModalBtn"
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200 flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-200    ">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Recipient Group Name Input Field -->
            <div class="mb-6">
                <label for="groupName" class="block text-sm font-medium text-gray-700 mb-2">Recipient Group Name</label>
                <input type="text" id="groupName" name="groupName"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 text-gray-900"
                    placeholder="e.g., Marketing Team List">
                <div class="validation-message"></div>
            </div>

            <!-- Upload Excel Div with Dots -->
            <div id="fileuploadmodaldivelement" class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Excel</label>
                <div class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg p-4 text-center text-gray-500 transition-colors duration-200 hover:border-indigo-500 hover:text-indigo-500">
                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    <div id="uploadText" class="text-center">
                        <p class="font-semibold">Drag & drop your file here</p>
                        <p class="text-sm">or click to browse</p>
                    </div>
                    <!-- This input is now properly positioned to only cover the drop zone -->
                    <input type="file" class="absolute inset-0 opacity-0 cursor-pointer" id="excelUploadInput"
                        accept=".xlsx, .xls">
                </div>
                <div class="validation-message"></div>
            </div>
            <div id="progressBarContainer" class="hidden w-full bg-gray-200 rounded-full h-2.5 mt-4">
                <div id="progressBar" class="bg-green-600 h-2.5 rounded-full" style="width:0%"></div>
            </div>
            <div id="progressText" class="hidden text-sm font-medium text-gray-700 mt-2">0%</div>

            <!-- Create Recipient Group Button, Cancel Button -->
            <div class="flex justify-end gap-2 mt-2">
                <button id="closeModalBtnbutton" type="button"
                    class="px-6 py-3 bg-gray-400 text-white font-bold rounded-lg shadow-md hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button id="createrecipientgroupbtn" type="button"
                    class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Recipient Group
                </button>
            </div>
        </div>
    </div>
    <script>
        //Global email campain data
        let formData = '';
        // Custom Dropdown for From Email
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('customDropdownBtn');
            const menu = document.getElementById('customDropdownMenu');
            const selected = document.getElementById('customDropdownSelected');
            const hiddenInput = document.getElementById('fromEmailSelect');
            const fromEmailName = document.getElementById('fromEmailName');
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            menu.addEventListener('click', function (e) {
                $('#customDropdownMenu .custom-option-data').removeClass('active'); //First clear the all dropdown active class
                const option = e.target.closest('.custom-option-data');
                if (option) {
                    const name = option.querySelector('.font-medium').textContent;
                    const email = option.querySelector('.text-sm') ? option.querySelector('.text-sm').textContent : '';
                    selected.textContent = name;
                    hiddenInput.value = option.getAttribute('data-value');
                    fromEmailName.value = option.getAttribute('data-name');
                    menu.classList.add('hidden');
                    option.classList.add('active'); //Mark it as active / selected
                    $('#fromEmailGroup').find('.validation-message').text('');
                }
            });
            document.addEventListener('click', function (e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });

            //dropdown 2 custom
            const btn1 = document.getElementById('customDropdownBtn1');
            const menu1 = document.getElementById('customDropdownMenu1');
            const selected1 = document.getElementById('customDropdownSelected1');
            const hiddenInput1 = document.getElementById('fromReceipeintGroup');
            const ReceipeintGroupId = document.getElementById('ReceipeintGroupId');

            const ReceipeintGroupName = document.getElementById('ReceipeintGroupName');
            const ReceipeintGroupCount = document.getElementById('ReceipeintGroupCount');

            btn1.addEventListener('click', function (e) {
                e.stopPropagation();
                menu1.classList.toggle('hidden');
            });
            menu1.addEventListener('click', function (e) {
                $('#customDropdownMenu1 .custom-option-data').removeClass('active'); //First clear the all dropdown active class
                const option = e.target.closest('.custom-option-data');
                if (option) {
                    const name = option.querySelector('.font-medium').textContent;
                    const email = option.querySelector('.text-sm') ? option.querySelector('.text-sm').textContent : '';
                    selected1.textContent = name;
                    hiddenInput1.value = option.getAttribute('data-value');
                    ReceipeintGroupId.value = option.getAttribute('data-id');

                    ReceipeintGroupName.value = option.getAttribute('data-recipientgroupname');
                    ReceipeintGroupCount.value = option.getAttribute('data-recipientcount');

                    menu1.classList.add('hidden');
                    option.classList.add('active'); //Mark it as active / selected
                    $('#RecipientGroup').find('.validation-message').text('');
                }
            });
            document.addEventListener('click', function (e) {
                if (!btn1.contains(e.target) && !menu1.contains(e.target)) {
                    menu1.classList.add('hidden');
                }
            });

            //dropdown 3 custom
            const btn2 = document.getElementById('customDropdownBtn2');
            const menu2 = document.getElementById('customDropdownMenu2');
            const selected2 = document.getElementById('customDropdownSelected2');
            const hiddenInput2 = document.getElementById('templateSelect');
            btn2.addEventListener('click', function (e) {
                e.stopPropagation();
                menu2.classList.toggle('hidden');
            });
            menu2.addEventListener('click', function (e) {
                $('#customDropdownMenu2 .custom-option-data').removeClass('active'); //First clear the all dropdown active class
                const option = e.target.closest('.custom-option-data');
                if (option) {
                    const name = option.querySelector('.font-medium').textContent;
                    selected2.textContent = name;
                    hiddenInput2.value = option.getAttribute('data-value');
                    menu2.classList.add('hidden');
                    option.classList.add('active'); //Mark it as active / selected
                    $('#templateGroup').find('.validation-message').text('');
                }
            });
            document.addEventListener('click', function (e) {
                if (!btn2.contains(e.target) && !menu2.contains(e.target)) {
                    menu2.classList.add('hidden');
                }
            });
        });
    </script>
    <script>
        function get_recipient_group(){
            //Get the recipient groups details here as per the need
            $.ajax({
                url: '/get-recipient-groups', // Replace with your backend URL
                type: 'GET',
                data: {},
                processData: false, // Prevents jQuery from processing the data
                contentType: false, // Prevents jQuery from setting the Content-Type header
                success: function (response) {
                    $('#customDropdownMenu1').find('.needtoremoveoption').remove();
                    let recipient_group_html = '';
                    if(response?.records.length > 0){
                        $.each(response?.records, function(key, value){
                            recipient_group_html += `<div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data needtoremoveoption"
                                data-value="${value.fields.filename}" data-id="${value.id}" data-recipientcount="${value.fields.number_of_emails}" data-recipientgroupname="${value.fields.name}">
                                <div class="font-medium">${value.fields.name}</div>
                                <div class="text-sm text-gray-500">${value.fields.filename} ,Total Emails = ${value.fields.number_of_emails}</div>
                            </div>`;
                        })
                    }
                    $(recipient_group_html).insertAfter('.recipient_group_html_parent');
                },
                error: function (xhr, status, error) {
                    // console.error("AJAX Error:", status, error);
                }
            });
        }

        function send_bulk_emails(){
            Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    hideModalConfirm(); //hide confirm popup
                    let formData = new FormData($('#emailForm')[0]);
                    $('#fullPageLoader').css('display', 'flex');
                    // Perform the AJAX request
                    $.ajax({
                        url: '/send-bulk-emails', // Replace with your backend URL
                        type: 'POST',
                        data: formData,
                        processData: false, // Prevents jQuery from processing the data
                        contentType: false, // Prevents jQuery from setting the Content-Type header
                        success: function (response) {
                            // Hide the loader
                            $('#fullPageLoader').css('display', 'none');
                            $('.btn-clear-form').trigger('click');
                            // Show success message
                            let response_message = response?.message ? response?.message : '';
                            Swal.fire({
                                icon: 'success',
                                title: 'Email Sent!',
                                text: response_message || 'Your bulk email campaign has been successfully initiated.',
                                html: response_message,
                                showConfirmButton: false,
                                timer: 2500
                            });
                            //Clear the 2 dropdowns here as per the requirement
                            $('#customDropdownSelected1').text('-- Select Recipient Group --');
                            $('#ReceipeintGroupId').val('');
                            $('#fromReceipeintGroup').val('');
                            $('#ReceipeintGroupName').val('');
                            $('#ReceipeintGroupCount').val('');
                            
                            //For the Sender emails
                            $('#customDropdownSelected').text('-- Select Sender Email --');
                            $('#fromEmailSelect').val('');
                            $('#fromEmailName').val('');

                            //clear for dropdown number 3
                            $('#customDropdownSelected2').text('-- Select Designed Template --');
                            $('#templateSelect').val('');
                        },
                        error: function (xhr, status, error) {
                            // Hide the loader
                            $('#fullPageLoader').css('display', 'none');

                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong! Please try again.',
                            });
                            console.error("AJAX Error:", status, error);
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    //keep calm for now
                }
            });
        }

        $(document).ready(function () {
            // Function to clear validation message for a given element
            const clearValidationError = (element) => {
                $(element).removeClass('border-red-500');
                $(element).parent().find('.validation-message').text('');
            };

            // Event listeners to clear validation on user interaction
            $('#emailSubject, #campaignName, #groupName').on('input', function () {
                clearValidationError(this);
            });
            $('#fromEmailSelect, #uploadedFileSelect, #templateSelect').on('change', function () {
                clearValidationError(this);
            });
            $('#fileInput').on('change', function () {
                clearValidationError($('#fileDrop'));
                clearValidationError($('#uploadedFileSelect'));
                $('#fileDrop').removeClass('border-red-500');
                $('#uploadedFileSelect').removeClass('border-red-500');
                $('#fileGroup .validation-message').text('');
                $('#uploadedFileGroup .validation-message').text('');
            });

            $(document).on('click', '.btn-send-email', function () {
                // Clear all previous validation messages
                $('.validation-message').text('');
                // Remove all red borders
                $('input, select').removeClass('border-red-500');
                $('#fileDrop').removeClass('border-red-500');

                let isValid = true;

                // Validate Email Subject
                const emailSubject = $('#emailSubject').val().trim();
                if (emailSubject === '') {
                    isValid = false;
                    $('#emailSubject').addClass('border-red-500');
                    $('#emailSubject').parent().find('.validation-message').text('Please enter an email subject.');
                }

                // Validate Campaign Name
                const campaignName = $('#campaignName').val().trim();
                if (campaignName === '') {
                    isValid = false;
                    $('#campaignName').addClass('border-red-500');
                    $('#campaignName').parent().find('.validation-message').text('Please enter an campaign name.');
                }

                // Validate "From Email" selection
                if ($('#fromEmailSelect').val() === "") {
                    isValid = false;
                    $('#fromEmailSelect').addClass('border-red-500');
                    $('#fromEmailGroup .validation-message').text('Please select a from email.');
                }

                // Validate "Recipient group" selection
                if ($('#fromReceipeintGroup').val() === "") {
                    isValid = false;
                    $('#fromReceipeintGroup').addClass('border-red-500');
                    $('#RecipientGroup .validation-message').text('Please select a recipient group.');
                }

                // Validate that either a new file is uploaded OR an old file is selected
                const fileSelected = $('#fileInput').val() !== "";
                const uploadedFileSelected = $('#uploadedFileSelect').val() !== "";

                if (!fileSelected && !uploadedFileSelected) {
                    isValid = false;
                    $('#fileGroup .validation-message').text('Please upload a new file or select a previously uploaded file.');
                    $('#uploadedFileGroup .validation-message').text('Please upload a new file or select a previously uploaded file.');
                    $('#fileDrop').addClass('border-red-500');
                    $('#uploadedFileSelect').addClass('border-red-500');
                }

                // Validate Template selection
                if ($('#templateSelect').val() === "") {
                    isValid = false;
                    $('#templateSelect').addClass('border-red-500');
                    $('#templateGroup .validation-message').text('Please select a template.');
                }

                if (isValid) {
                    showModalConfirm();
                }
            });

            $(document).on('click', '.btn-clear-form', function () {
                // Reset the form and clear validation states
                $('form')[0].reset();
                $('#fileName').text('');
                $('.validation-message').text('');
                // Remove the red border from all inputs/selects
                $('input, select').removeClass('border-red-500');
                $('#fileDrop').removeClass('border-red-500');
                
                //Clear 2 custom dropdowns as per the need
                $('#customDropdownSelected1').text('-- Select Recipient Group --');
                $('#ReceipeintGroupId').val('');
                $('#fromReceipeintGroup').val('');
                $('#ReceipeintGroupName').val('');
                $('#ReceipeintGroupCount').val('');
                //For the Sender emails
                $('#customDropdownSelected').text('-- Select Sender Email --');
                $('#fromEmailSelect').val('');
                $('#fromEmailName').val('');

                //clear for dropdown number 3
                $('#customDropdownSelected2').text('-- Select Designed Template --');
                $('#templateSelect').val('');
            });
            get_recipient_group();
            //Get the recipient groups details here as per the need
            $.ajax({
                url: '/get-senders-list', // Replace with your backend URL
                type: 'GET',
                data: {},
                processData: false, // Prevents jQuery from processing the data
                contentType: false, // Prevents jQuery from setting the Content-Type header
                success: function (response) {
                    $('#customDropdownMenu').find('.needtoremoveoption').remove();
                    let senders_list_html = '';
                    if(response?.records.length > 0){
                        $.each(response?.records, function(key, value){
                            senders_list_html += `<div class="cursor-pointer px-4 py-2 hover:bg-gray-100 custom-option-data"
                                data-value="${value.fields.Send_From_Email}" data-name="${value.fields.Location_Name}">
                                <div class="font-medium">${value.fields.Location_Name}</div>
                                <div class="text-sm text-gray-500">${value.fields.Send_From_Email}</div>
                            </div>`;
                        })
                    }
                    $(senders_list_html).insertAfter('.senders_list_html_parent');
                },
                error: function (xhr, status, error) {
                    
                }
            });
            $(document).on('click', '#createrecipientgroupbtn', function () {
                let _clickedbtn = $(this);
                let isValid = true;
                // Validate group name
                const groupName = $('#groupName').val().trim();
                if (groupName === '') {
                    isValid = false;
                    $('#groupName').addClass('border-red-500');
                    $('#groupName').parent().find('.validation-message').text('Please enter a group name.');
                }

                // Validate file upload
                const excelUploadInput = $('#excelUploadInput')[0];
                if (!excelUploadInput.files.length) {
                    isValid = false;
                    $('#excelUploadInput').addClass('border-red-500');
                    $('#fileuploadmodaldivelement').find('.validation-message').text('Please upload a file.');
                }

                if (isValid) {
                    let formData = new FormData();
                    formData.append('groupName', groupName);
                    formData.append('excelFile', excelUploadInput.files[0]);

                    $.ajax({
                        url: '/upload-recipient-group',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        xhr: function () {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function (evt) {
                                if (evt.lengthComputable) {
                                    const percentComplete = (evt.loaded / evt.total) * 100;
                                    $('#progressBar').css('width', percentComplete.toFixed(0) + '%');
                                    $('#progressText').text(percentComplete.toFixed(0) + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        beforeSend: function () {
                            // Show the progress bar and hide other elements
                            // $('#fileuploadmodaldivelement').find('svg, #uploadText').addClass('hidden');
                            $('#progressBarContainer, #progressText').removeClass('hidden');
                            _clickedbtn.prop('disabled', true).text('Uploading...');
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#closeModalBtn').trigger('click');
                                let response_message = response?.message ? response?.message : '';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'File uploaded successfully',
                                    html: response_message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    // Reset the form and validation messages
                                    $('#groupName').val('');
                                    $('#excelUploadInput').val('');
                                    $('.validation-message').text('');
                                    $('#groupName, #excelUploadInput').removeClass('border-red-500');

                                    // Update the UI with new recipient group data
                                    $('#ReceipeintGroupId').val(response.airtable_id);
                                    $('#fromReceipeintGroup').val(response.airtable_rec.fields.filename);
                                    $('#customDropdownSelected1').text(response.airtable_rec.fields.name);
                                    $('#ReceipeintGroupName').val(response.airtable_rec.fields.name);
                                    $('#ReceipeintGroupCount').val(response.airtable_rec.fields.number_of_emails);
                                    get_recipient_group();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Upload failed. Please try again.',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            let message = 'Upload failed. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: message,
                                confirmButtonColor: '#d33'
                            });
                        },
                        complete: function() {
                            // Re-enable the button and reset its text
                            _clickedbtn.prop('disabled', false).text('Create Recipient Group');
                            // Hide the progress bar and show the original upload elements
                            $('#progressBarContainer, #progressText').addClass('hidden');
                            // $('#fileuploadmodaldivelement').find('svg, #uploadText').removeClass('hidden');
                            $('#progressBar').css('width', '0%');
                            $('#progressText').text('0%');
                        }
                    });
                }
            });
        });
    </script>
    <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalBtnbutton = document.getElementById('closeModalBtnbutton');
        const modalContainer = document.getElementById('modalContainer');
        const modalContent = document.getElementById('modalContent');
        const excelUploadInput = document.getElementById('excelUploadInput');
        const uploadText = document.getElementById('uploadText');
        const confirm_campaign_send = document.getElementById('confirm_campaign_send');

        // Function to show the modal with a subtle animation
        function showModal() {
            modalContainer.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        // Function to show the modal confirm popup once we will have the all fields pushed
        const modalConfirmContainer = document.getElementById('confirmationModal');
        function showModalConfirm() {
            modalConfirmContainer.classList.remove('hidden');
            let formData = new FormData($('#emailForm')[0]);
            let confirm_popup = $('#confirmationModal');
            for (let [key, value] of formData.entries()) {
                switch (key) {
                    case 'ReceipeintGroupName':
                        confirm_popup.find('#sender_recipient_group_confirm').text(value);
                        break;
                    case 'fromReceipeintGroup':
                        confirm_popup.find('#sender_source_filename_confirm').text(value);
                        break;
                    case 'fromEmailSelect':
                        confirm_popup.find('#sender_email_confirm').text(value);
                        break;
                    case 'ReceipeintGroupCount':
                        confirm_popup.find('#sender_recipient_confirm').text(value);
                        break;
                    case 'emailSubject':
                        confirm_popup.find('#email_subject_confirm').text(value);
                        break;
                    case 'campaignName':
                        confirm_popup.find('#campaign_name_confirm').text(value);
                        break;
                    default:
                        break;
                }
            }
        }

        // Function to hide the modal with a subtle animation
        function hideModalConfirm() {
            modalConfirmContainer.classList.add('hidden');
        }

        // Function to hide the modal with a subtle animation
        function hideModal() {
            $('.fileuploadpopupptag').text('Drag & drop your file here');
            $('.fileuploadpopupptag').removeClass('text-indigo-600');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modalContainer.classList.add('hidden');
            }, 300); // Duration matches CSS transition
        }

        // Event listeners to control the modal
        openModalBtn.addEventListener('click', showModal);
        closeModalBtn.addEventListener('click', hideModal);
        closeModalBtnbutton.addEventListener('click', hideModal);
        confirm_campaign_send.addEventListener('click', send_bulk_emails);

        // Close the modal when clicking outside of it
        modalContainer.addEventListener('click', (event) => {
            if (event.target === modalContainer) {
                hideModal();
            }
        });

        // Event listener to display the selected file name
        excelUploadInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                let filename = file.name;
                let dotIndex = filename.lastIndexOf(".");
                let namePart = dotIndex !== -1 ? filename.substring(0, dotIndex) : filename;
                let ext = dotIndex !== -1 ? filename.substring(dotIndex) : "";

                // shorten namePart to 30 chars if needed
                let displayName = namePart.length > 30
                    ? namePart.substring(0, 30) + "..." + ext
                    : filename;

                // show shortened name but keep full name in title tooltip
                uploadText.innerHTML = `
                    <p class="font-semibold text-indigo-600 fileuploadpopupptag" title="${filename}">${displayName}</p>
                    <p class="text-sm text-gray-500">Click to change file</p>
                `;

                $('#fileuploadmodaldivelement').find('.validation-message').text('');
            } else {
                uploadText.innerHTML = `
                    <p class="font-semibold">Drag & drop your file here</p>
                    <p class="text-sm">or click to browse</p>
                `;
            }
        });
    </script>
</body>
</html>