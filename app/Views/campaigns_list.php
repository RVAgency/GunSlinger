<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns List</title>
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
            /* This width will be active on mobile and when open on desktop */
            width: 16rem;
        }

        /* Base styles for the main content area */
        #main-content {
            transition: margin-left 0.3s ease-in-out;
            /* This margin will be active on mobile and when open on desktop */
            margin-left: 16rem;
        }

        /* Collapsed state for desktop */
        @media (min-width: 1024px) {
            body.sidebar-collapsed #sidebar {
                width: 5rem;
                /* Collapsed width (icon-only) */
            }

            body.sidebar-collapsed #main-content {
                margin-left: 5rem;
                /* Adjust margin for collapsed sidebar */
            }

            /* Show title on collapsed sidebar */
            body.sidebar-collapsed .sidebar-text {
                display: none;
            }

            /* Center logo on collapsed sidebar */
            body.sidebar-collapsed .sidebar-open-state {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            /* Hide full logo on collapsed sidebar */
            body.sidebar-collapsed .sidebar-open-state img {
                width: 32px;
                /* Adjust size as needed for a small icon */
            }
        }


        /* Mobile specific styles */
        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-open #sidebar {
                transform: translateX(0);
            }

            /* On mobile, main content won't have a margin and sidebar will overlay */
            #main-content {
                margin-left: 0;
            }
        }

        /* Custom styles for validation message */
        .validation-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /*Loader code started here*/
        .loader-ring {
            width: 48px;
            height: 48px;
            border: 6px solid #e5e7eb;       /* Gray-200 background ring */
            border-top-color: #3b82f6;       /* Blue-500 spinning part */
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        /*Loader code ended here*/
    </style>
</head>

<body class="flex flex-col min-h-screen sidebar-open">
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
            <h1 class="text-2xl font-bold text-gray-800">Campaigns</h1>
            <div class="ml-auto flex items-center space-x-4">
                <a href="/logout"
                    class="px-3 py-1 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 focus:outline-none">
                    Logout
                </a>
            </div>
        </header>
        <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="campaigns"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-md hover:bg-indigo-700 transition-colors duration-200 flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Create New Campaign
                </a>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100 rounded-t-lg">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Campaign Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Sender
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Date and Time Sent
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Recipient Group
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">
                                    Number of Emails Sent
                                </th>
                            </tr>
                        </thead>
                        <tbody id="campaigns-body" class="bg-white divide-y divide-gray-200">
                            <tr id="campaigns-loader">
                                <td colspan="5" class="px-6 py-10 text-center">
                                    <div class="flex justify-center items-center">
                                        <div class="loader-ring"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="loadmorecampaigns" class="mt-8 text-center hidden">
                <input type="hidden" name="next_offset" value = '' />
                <button id="loadMoreBtn" onClick="loadmorecampaigns(this)" class="px-4 py-2 bg-indigo-600 text-sm text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    Load More
                </button>
            </div>
        </main>
    </div>

    <script>
        //On document ready call this function here
        $(document).ready(function(){
            loadCampaignsRecords(); //Load the initial 50 records only
        });
        
        function loadCampaignsRecords(next_offset = null){
            //Get the recipient groups details here as per the need
            let offset_str = '';
            if(next_offset != null && next_offset != undefined && next_offset != ''){
                offset_str = '?offset=' + next_offset;
            }
            $.ajax({
                url: '/get-campaigns-list'+offset_str, // Replace with your backend URL
                type: 'GET',
                data: {},
                processData: false, // Prevents jQuery from processing the data
                contentType: false, // Prevents jQuery from setting the Content-Type header
                success: function (response) {
                    let campaigns_list_html = '';
                    $('#loadMoreBtn').text('Load more');
                    if(response?.status == 'success' && response?.data.length > 0){
                        $.each(response?.data, function(key, value){
                            campaigns_list_html += `<tr class="hover:bg-gray-100 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${value?.campaign_name ?? 'Not available'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${value?.sender ?? 'Not available'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${value?.created_datetime ?? 'Not available'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${value?.recipient_group_name ?? 'Not available'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                        ${value?.emails_sent ?? 0}
                                    </span>
                                </td>
                            </tr>`;
                        })

                        //If the response is having the more records in that case only show the load more button
                        $('#loadmorecampaigns').find('input[name=next_offset]').val(response?.next_offset);
                        if(response?.next_offset == null){
                            $('#loadmorecampaigns').addClass('hidden');
                        }else{
                            $('#loadmorecampaigns').removeClass('hidden');
                        }
                    }else{
                        campaigns_list_html = `<tr class="hover:bg-gray-100 transition-colors duration-150">
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center mt-5">
                                    No Campaign Records found!
                                </td>
                            </tr>`;
                    }
                    $('#campaigns-loader').remove();
                    $('#campaigns-body').append(campaigns_list_html);
                },
                error: function (xhr, status, error) {
                    
                }
            });
        }

        //Function to load more the campaigns
        function loadmorecampaigns(current_element){
            $(current_element).text('Loading....');
            let offset_next = $(current_element).parents('#loadmorecampaigns').find('input[name=next_offset]').val();
            if(offset_next!='' && offset_next!=null && offset_next !=undefined){
                loadCampaignsRecords(offset_next);
            }
        }
    </script>
</body>

</html>