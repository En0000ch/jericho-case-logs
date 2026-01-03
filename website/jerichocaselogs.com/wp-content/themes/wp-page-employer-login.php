<?php
/**
 * Template Name: Employer Login
 * Description: Login page for employers to access job posting system
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Remove WordPress header and footer for standalone appearance
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Login - Jericho Case Logs</title>
    <script src="https://npmcdn.com/parse@3.4.1/dist/parse.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --jcl-orange: #EE6C4D;
            --jcl-gray: #2B3241;
            --jcl-white: #E0FBFC;
            --jcl-taupe: #483C32;
        }

        body {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-weight: 300;
            background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/hospital_bkgd.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: var(--jcl-orange);
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .logo p {
            color: var(--jcl-gray);
            font-size: 18px;
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: var(--jcl-taupe);
            font-weight: 400;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-weight: 300;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--jcl-orange);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: var(--jcl-orange);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 400;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #d65b3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 108, 77, 0.3);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-weight: 300;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: var(--jcl-orange);
            text-decoration: none;
            font-size: 14px;
            font-weight: 300;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--jcl-taupe);
            font-weight: 300;
        }

        .register-link a {
            color: var(--jcl-orange);
            font-weight: 400;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>📋 Jericho Case Logs</h1>
            <p>Employer Login</p>
        </div>

        <div id="message" class="message"></div>

        <form id="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn" id="submit-btn">Login</button>
        </form>

        <div class="forgot-password">
            <a href="#" onclick="alert('Please contact support to reset your password.'); return false;">Forgot Password?</a>
        </div>

        <div class="register-link">
            Don't have an account? <a href="<?php echo esc_url(home_url('/employer-registration')); ?>">Register here</a>
        </div>
    </div>

    <script>
        // Initialize Parse
        Parse.initialize("9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF", "xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI");
        Parse.serverURL = 'https://parseapi.back4app.com/';

        const form = document.getElementById('login-form');
        const messageDiv = document.getElementById('message');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';

            try {
                const user = await Parse.User.logIn(email, password);

                // Verify this is an employer account
                if (user.get('userType') !== 'employer') {
                    await Parse.User.logOut();
                    showMessage('This login is for employers only.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login';
                    return;
                }

                // Redirect to job posting page
                window.location.href = '<?php echo esc_url(home_url('/job-posting')); ?>';

            } catch (error) {
                console.error('Error:', error);
                showMessage('Login failed: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            }
        });

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.className = 'message ' + type;
        }
    </script>
</body>
</html>
