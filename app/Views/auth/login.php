<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

  <div id="loadingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 flex flex-col items-center space-y-4 max-w-sm w-full">
      <svg class="w-10 h-10 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <h2 class="text-xl font-semibold text-gray-800">Signing In...</h2>
      <p class="text-gray-600 text-center">Please wait while we verify your details.</p>
    </div>
  </div>

  <div class="bg-white shadow-2xl rounded-2xl w-full max-w-md p-8 sm:p-10">
    <div class="text-center mb-8">
      <div class="flex justify-center mb-2">
        <img src="<?php echo base_url();?>/uploads/gunslingerlogo.png" alt="GunSlinger Logo" class="w-[200px]" />
      </div>
      <!-- <h1 class="text-4xl font-extrabold text-gray-900">GunSlinger</h1> -->
      <p class="text-gray-500 mt-2 text-lg">Welcome back! Please login</p>
    </div>

    <form id="loginForm" class="space-y-6">
      <div>
        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
            <i class="fa-solid fa-envelope"></i>
          </span>
          <input type="email" id="email" name="email"
            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all duration-200"
            placeholder="Enter your email">
        </div>
        <p id="email-error-message" class="text-sm text-red-500 mt-2 hidden">
            Please enter a valid email.
        </p>
      </div>

      <div>
        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
            <i class="fa-solid fa-lock"></i>
          </span>
          <input type="password" id="password" name="password"
            class="w-full pl-12 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all duration-200"
            placeholder="Enter your password">
          
          <span id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 cursor-pointer">
            <i id="showIcon" class="fa-solid fa-eye text-gray-400"></i>
            <i id="hideIcon" class="fa-solid fa-eye-slash text-gray-400 hidden"></i>
          </span>
        </div>
        <p id="password-error-message" class="text-sm text-red-500 mt-2 hidden">
            Please enter your password.
        </p>
      </div>

      <button type="button" id="loginButton"
        class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-indigo-700 transition-all duration-200">
        Sign In
      </button>

      <p class="text-center text-gray-600 mt-4">
        Don’t have an account?
        <a href="#" class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors">Sign up</a>
      </p>
    </form>
  </div>

<script>
    $(document).ready(function() {
        const emailInput = $('#email');
        const passwordInput = $('#password');
        const emailError = $('#email-error-message');
        const passwordError = $('#password-error-message');
        const togglePassword = $('#togglePassword');
        const showIcon = $('#showIcon');
        const hideIcon = $('#hideIcon');
        
        function hideAllErrors() {
            emailError.addClass('hidden');
            passwordError.addClass('hidden');
        }

        emailInput.on('keyup', function() {
            if (emailError.is(':visible')) {
                emailError.addClass('hidden');
            }
        });

        passwordInput.on('keyup', function() {
            if (passwordError.is(':visible')) {
                passwordError.addClass('hidden');
            }
        });

        togglePassword.on('click', function() {
            const passwordFieldType = passwordInput.attr('type');
            if (passwordFieldType === 'password') {
                passwordInput.attr('type', 'text');
                showIcon.addClass('hidden');
                hideIcon.removeClass('hidden');
            } else {
                passwordInput.attr('type', 'password');
                hideIcon.addClass('hidden');
                showIcon.removeClass('hidden');
            }
        });

        $('#loginButton').on('click', function(event) {
            event.preventDefault();
            hideAllErrors();

            const email = emailInput.val().trim();
            const password = passwordInput.val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === '') {
                emailError.text('Please enter your email address.').removeClass('hidden');
                return;
            }

            if (!emailRegex.test(email)) {
                emailError.text('Please enter a valid email address.').removeClass('hidden');
                return;
            }
            
            if (password === '') {
                passwordError.removeClass('hidden');
                return;
            }

            let _thisbtn = $(this);
            const url = "/authenticate_user";

            // Use the loading modal
            $('#loadingModal').removeClass('hidden');
            _thisbtn.prop('disabled', true).addClass('cursor-not-allowed opacity-50');
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    email: email,
                    password: password
                },
                success: function(response) {
                    try {
                        let responsedata = JSON.parse(response);
                        if (responsedata?.status && responsedata?.status === 'success') {
                            window.location.href = responsedata?.redirect;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Failed',
                                text: responsedata.message || 'Invalid email or password. Please try again.',
                                confirmButtonColor: '#4f46e5' // Use a more brand-consistent color
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Response Error',
                            text: 'Failed to parse server response. Please try again.',
                            confirmButtonColor: '#4f46e5'
                        });
                        console.error("JSON Parse Error:", e);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again.',
                        confirmButtonColor: '#4f46e5'
                    });
                    console.error("AJAX Error:", status, error);
                },
                complete: function() {
                    $('#loadingModal').addClass('hidden');
                    _thisbtn.prop('disabled', false).removeClass('cursor-not-allowed opacity-50');
                }
            });
        });
    });
</script>
</body>
</html>