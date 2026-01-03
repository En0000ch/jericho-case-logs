<?php
/**
 * Template Name: Employer Registration
 * Description: Registration page for employers to create accounts and post jobs
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
    <title>Employer Registration - Jericho Case Logs</title>
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
            max-width: 600px;
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

        label .required {
            color: var(--jcl-orange);
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
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

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .info-text {
            background: var(--jcl-white);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            color: var(--jcl-taupe);
            font-weight: 300;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: var(--jcl-taupe);
            font-weight: 300;
        }

        .login-link a {
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
            <p>Employer Registration</p>
        </div>

        <div class="info-text">
            Register your healthcare facility to post job opportunities for anesthesia professionals.
        </div>

        <div id="message" class="message"></div>

        <form id="registration-form">
            <div class="form-group">
                <label for="facName">Facility Name <span class="required">*</span></label>
                <input type="text" id="facName" name="facName" required>
            </div>

            <div class="form-group">
                <label for="facContactName">Contact Person Name <span class="required">*</span></label>
                <input type="text" id="facContactName" name="facContactName" required>
            </div>

            <div class="form-group">
                <label for="facContactEmail">Contact Email <span class="required">*</span></label>
                <input type="email" id="facContactEmail" name="facContactEmail" required>
            </div>

            <div class="form-group">
                <label for="facContactPhone">Contact Phone <span class="required">*</span></label>
                <input type="tel" id="facContactPhone" name="facContactPhone" placeholder="(555) 555-5555" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirmPassword" name="confirmPassword" minlength="6" required>
            </div>

            <button type="submit" class="btn" id="submit-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="<?php echo esc_url(home_url('/employer-login')); ?>">Login here</a>
        </div>
    </div>

    <script>
        // Initialize Parse
        Parse.initialize("9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF", "xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI");
        Parse.serverURL = 'https://parseapi.back4app.com/';

        const form = document.getElementById('registration-form');
        const messageDiv = document.getElementById('message');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const facName = document.getElementById('facName').value;
            const facContactName = document.getElementById('facContactName').value;
            const facContactEmail = document.getElementById('facContactEmail').value;
            const facContactPhone = document.getElementById('facContactPhone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Validate passwords match
            if (password !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';

            try {
                // Create Parse User
                const user = new Parse.User();
                user.set("username", facContactEmail);
                user.set("password", password);
                user.set("email", facContactEmail);
                user.set("facName", facName);
                user.set("facContactName", facContactName);
                user.set("facContactPhone", facContactPhone);
                user.set("userType", "employer");

                await user.signUp();

                showMessage('Account created successfully! Redirecting to login...', 'success');

                setTimeout(() => {
                    window.location.href = '<?php echo esc_url(home_url('/employer-login')); ?>';
                }, 2000);

            } catch (error) {
                console.error('Error:', error);
                showMessage('Registration failed: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        });

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.className = 'message ' + type;
        }
    </script>
</body>
</html>
