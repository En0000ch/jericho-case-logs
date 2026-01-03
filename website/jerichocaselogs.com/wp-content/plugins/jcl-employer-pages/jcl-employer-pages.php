<?php
/**
 * Plugin Name: JCL Employer Pages V2.1
 * Description: Login modal popup + styled registration page (Forces override of V1)
 * Version: 2.1
 * Author: JCL Development
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Force remove any existing shortcodes with the same name (in case V1 is still active)
add_action('init', 'jcl_force_override_shortcodes', 999);

function jcl_force_override_shortcodes() {
    // Remove any existing registration shortcode
    remove_shortcode('jcl_employer_registration');
    // Re-register with our new version
    add_shortcode('jcl_employer_registration', 'jcl_employer_registration_page_v2');
}

// Add login modal to footer on all pages
add_action('wp_footer', 'jcl_employer_login_modal');

function jcl_employer_login_modal() {
    ?>
    <!-- Login Modal Trigger (hidden, triggered by V35) -->
    <div id="jcl-login-modal" style="display: none;">
        <div class="jcl-modal-overlay"></div>
        <div class="jcl-modal-content">
            <button class="jcl-close-btn" onclick="closeLoginModal()">&times;</button>

            <div class="jcl-logo">
                <h1>📋 Jericho Case Logs</h1>
                <p>Employer Login</p>
            </div>

            <div id="jcl-message" class="jcl-message"></div>

            <form id="jcl-login-form">
                <div class="jcl-form-group">
                    <label for="jcl-email">Email Address</label>
                    <input type="email" id="jcl-email" required>
                </div>

                <div class="jcl-form-group">
                    <label for="jcl-password">Password</label>
                    <input type="password" id="jcl-password" required>
                </div>

                <button type="submit" class="jcl-btn" id="jcl-submit-btn">Login</button>
            </form>

            <div class="jcl-forgot-password">
                <a href="#" onclick="alert('Please contact support to reset your password.'); return false;">Forgot Password?</a>
            </div>

            <div class="jcl-register-link">
                Don't have an account? <a href="#" onclick="closeLoginModal(); openRegistrationModal(); return false;">Register here</a>
            </div>
        </div>
    </div>

    <style>
        :root {
            --jcl-orange: #EE6C4D;
            --jcl-gray: #2B3241;
            --jcl-white: #E0FBFC;
            --jcl-taupe: #483C32;
        }

        #jcl-login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .jcl-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .jcl-modal-content {
            position: relative;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            padding: 40px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .jcl-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: transparent;
            border: none;
            font-size: 32px;
            color: var(--jcl-gray);
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }

        .jcl-close-btn:hover {
            color: var(--jcl-orange);
        }

        .jcl-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .jcl-logo h1 {
            color: var(--jcl-orange);
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 400;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-logo p {
            color: var(--jcl-gray);
            font-size: 16px;
            font-weight: 300;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-form-group {
            margin-bottom: 20px;
        }

        .jcl-form-group label {
            display: block;
            color: var(--jcl-taupe);
            font-weight: 400;
            margin-bottom: 8px;
            font-size: 14px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-weight: 300;
            transition: border-color 0.3s ease;
        }

        .jcl-form-group input:focus {
            outline: none;
            border-color: var(--jcl-orange);
        }

        .jcl-btn {
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

        .jcl-btn:hover {
            background: #d65b3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 108, 77, 0.3);
        }

        .jcl-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .jcl-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-weight: 300;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .jcl-forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .jcl-forgot-password a {
            color: var(--jcl-orange);
            text-decoration: none;
            font-size: 14px;
            font-weight: 300;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-register-link {
            text-align: center;
            margin-top: 0;
            color: var(--jcl-taupe);
            font-weight: 300;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-register-link a {
            color: var(--jcl-orange);
            font-weight: 400;
            text-decoration: none;
        }
    </style>

    <script src="https://npmcdn.com/parse@3.4.1/dist/parse.min.js"></script>
    <script>
        // Initialize Parse
        Parse.initialize("9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF", "xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI");
        Parse.serverURL = 'https://parseapi.back4app.com/';

        // Global function to open login modal
        window.openLoginModal = function() {
            document.getElementById('jcl-login-modal').style.display = 'flex';
        };

        // Global function to close login modal
        window.closeLoginModal = function() {
            document.getElementById('jcl-login-modal').style.display = 'none';
        };

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
            }
        });

        const form = document.getElementById('jcl-login-form');
        const messageDiv = document.getElementById('jcl-message');
        const submitBtn = document.getElementById('jcl-submit-btn');

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const email = document.getElementById('jcl-email').value;
                const password = document.getElementById('jcl-password').value;

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

                    // Redirect to poster dashboard
                    window.location.href = '<?php echo esc_url(home_url('/poster-dashboard')); ?>';

                } catch (error) {
                    console.error('Error:', error);
                    showMessage('Login failed: ' + error.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login';
                }
            });
        }

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.className = 'jcl-message ' + type;
        }
    </script>
    <?php
}
// Add registration modal to footer on all pages
add_action('wp_footer', 'jcl_employer_registration_modal');

function jcl_employer_registration_modal() {
    ?>
    <!-- Registration Modal -->
    <div id="jcl-registration-modal" style="display: none;">
        <div class="jcl-modal-overlay"></div>
        <div class="jcl-modal-content jcl-registration-modal-content">
            <button class="jcl-close-btn" onclick="closeRegistrationModal()">&times;</button>

            <div class="jcl-logo">
                <h1>📋 Jericho Case Logs</h1>
                <p>Employer Registration</p>
            </div>

            <div id="jcl-reg-message" class="jcl-message"></div>

            <form id="jcl-registration-form">
                <!-- Organization Details -->
                <div class="jcl-form-section">
                    <h3 class="jcl-section-title">Organization Details</h3>

                    <div class="jcl-form-group">
                        <label for="jcl-org-name" class="jcl-label">Organization Name <span class="required">*</span></label>
                        <input type="text" id="jcl-org-name" class="jcl-input" required>
                    </div>

                    <div class="jcl-form-group">
                        <label for="jcl-org-type" class="jcl-label">Organization Type <span class="required">*</span></label>
                        <select id="jcl-org-type" class="jcl-input" required>
                            <option value="">Select type</option>
                            <option value="hospital">Hospital</option>
                            <option value="clinic">Clinic</option>
                            <option value="surgery-center">Surgery Center</option>
                            <option value="staffing-agency">Staffing Agency</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Contact Person -->
                <div class="jcl-form-section">
                    <h3 class="jcl-section-title">Contact Person</h3>

                    <div class="jcl-form-row">
                        <div class="jcl-form-group">
                            <label for="jcl-first-name" class="jcl-label">First Name <span class="required">*</span></label>
                            <input type="text" id="jcl-first-name" class="jcl-input" required>
                        </div>

                        <div class="jcl-form-group">
                            <label for="jcl-last-name" class="jcl-label">Last Name <span class="required">*</span></label>
                            <input type="text" id="jcl-last-name" class="jcl-input" required>
                        </div>
                    </div>

                    <div class="jcl-form-group">
                        <label for="jcl-title" class="jcl-label">Title/Position <span class="required">*</span></label>
                        <input type="text" id="jcl-title" class="jcl-input" placeholder="e.g., HR Manager" required>
                    </div>

                    <div class="jcl-form-group">
                        <label for="jcl-phone" class="jcl-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="jcl-phone" class="jcl-input" placeholder="(555) 555-5555" required>
                    </div>
                </div>

                <!-- Account Details -->
                <div class="jcl-form-section">
                    <h3 class="jcl-section-title">Account Details</h3>

                    <div class="jcl-form-group">
                        <label for="jcl-reg-email" class="jcl-label">Email Address <span class="required">*</span></label>
                        <input type="email" id="jcl-reg-email" class="jcl-input" required>
                    </div>

                    <div class="jcl-form-group">
                        <label for="jcl-reg-password" class="jcl-label">Password <span class="required">*</span></label>
                        <input type="password" id="jcl-reg-password" class="jcl-input" minlength="8" required>
                        <small class="jcl-hint">At least 8 characters</small>
                    </div>

                    <div class="jcl-form-group">
                        <label for="jcl-confirm-password" class="jcl-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="jcl-confirm-password" class="jcl-input" required>
                    </div>
                </div>

                <button type="submit" class="jcl-btn" id="jcl-register-btn">Create Account</button>
            </form>

            <div class="jcl-login-link">
                Already have an account? <a href="#" onclick="closeRegistrationModal(); openLoginModal(); return false;">Login here</a>
            </div>
        </div>
    </div>

    <style>
        #jcl-registration-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .jcl-registration-modal-content {
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .jcl-form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .jcl-form-section:last-of-type {
            border-bottom: none;
        }

        .jcl-section-title {
            color: var(--jcl-orange);
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 600px) {
            .jcl-form-row {
                grid-template-columns: 1fr;
            }
        }

        .jcl-label {
            display: block;
            color: var(--jcl-taupe);
            font-weight: 400;
            margin-bottom: 8px;
            font-size: 14px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-label .required {
            color: var(--jcl-orange);
        }

        .jcl-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
            font-weight: 300;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .jcl-input:focus {
            outline: none;
            border-color: var(--jcl-orange);
        }

        .jcl-hint {
            display: block;
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-login-link {
            text-align: center;
            margin-top: 20px;
            color: var(--jcl-taupe);
            font-weight: 300;
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        }

        .jcl-login-link a {
            color: var(--jcl-orange);
            font-weight: 400;
            text-decoration: none;
        }

        .jcl-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
    </style>

    <script>
        // Global functions to open/close registration modal
        window.openRegistrationModal = function() {
            document.getElementById('jcl-registration-modal').style.display = 'flex';
        };

        window.closeRegistrationModal = function() {
            document.getElementById('jcl-registration-modal').style.display = 'none';
        };

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('jcl-registration-modal').style.display === 'flex') {
                closeRegistrationModal();
            }
        });

        // Handle registration form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jcl-registration-form');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('jcl-register-btn');
                const message = document.getElementById('jcl-reg-message');

                // Clear previous messages
                message.className = 'jcl-message';
                message.textContent = '';

                // Get form values
                const formData = {
                    organizationName: document.getElementById('jcl-org-name').value.trim(),
                    organizationType: document.getElementById('jcl-org-type').value,
                    firstName: document.getElementById('jcl-first-name').value.trim(),
                    lastName: document.getElementById('jcl-last-name').value.trim(),
                    title: document.getElementById('jcl-title').value.trim(),
                    phone: document.getElementById('jcl-phone').value.trim(),
                    email: document.getElementById('jcl-reg-email').value.trim().toLowerCase(),
                    password: document.getElementById('jcl-reg-password').value,
                    confirmPassword: document.getElementById('jcl-confirm-password').value
                };

                // Validate passwords match
                if (formData.password !== formData.confirmPassword) {
                    message.className = 'jcl-message error';
                    message.textContent = 'Passwords do not match.';
                    return;
                }

                // Validate password length
                if (formData.password.length < 8) {
                    message.className = 'jcl-message error';
                    message.textContent = 'Password must be at least 8 characters.';
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating Account...';

                try {
                    // Step 1: Create EmployerOrganization FIRST with all contact info
                    const EmployerOrganization = Parse.Object.extend('EmployerOrganization');
                    const organization = new EmployerOrganization();
                    organization.set('organizationName', formData.organizationName);
                    organization.set('orgType', formData.organizationType);
                    organization.set('firstName', formData.firstName);
                    organization.set('lastName', formData.lastName);
                    organization.set('title', formData.title);
                    organization.set('phone', formData.phone);
                    organization.set('email', formData.email);
                    organization.set('verificationStatus', 'pending');

                    const savedOrg = await organization.save();
                    console.log('EmployerOrganization created successfully, ID:', savedOrg.id);

                    // Step 2: Create User account (ABSOLUTE MINIMAL - no pointer fields)
                    const user = new Parse.User();
                    user.set('username', formData.email);
                    user.set('password', formData.password);
                    user.set('email', formData.email);

                    // Do NOT touch pointer fields at all - they'll remain undefined

                    await user.signUp();
                    console.log('User created successfully (minimal), ID:', user.id);

                    // Step 2b: Update user with additional fields AFTER signup succeeds
                    user.set('jclSilo', 'jclJobs');
                    user.set('userEmail', formData.email);
                    user.set('caseCount', 0);
                    user.set('userType', 'employer');
                    user.set('facName', formData.organizationName);
                    user.set('facContactName', formData.firstName + ' ' + formData.lastName);
                    user.set('facContactPhone', formData.phone);

                    await user.save();
                    console.log('User updated with employer fields');

                    // Step 3: Create PosterProfile linking User and Organization
                    const PosterProfile = Parse.Object.extend('PosterProfile');
                    const profile = new PosterProfile();
                    profile.set('user', user);
                    profile.set('organization', savedOrg);
                    profile.set('firstName', formData.firstName);
                    profile.set('lastName', formData.lastName);
                    profile.set('phone', formData.phone);
                    profile.set('title', formData.title);

                    const acl = new Parse.ACL();
                    acl.setPublicReadAccess(false);
                    acl.setReadAccess(user.id, true);
                    acl.setWriteAccess(user.id, true);
                    profile.setACL(acl);

                    await profile.save();
                    console.log('PosterProfile created successfully');

                    // Success!
                    message.className = 'jcl-message success';
                    message.textContent = 'Account created successfully! Redirecting...';

                    setTimeout(function() {
                        window.location.href = '/poster-dashboard';
                    }, 2000);

                } catch (error) {
                    console.error('Registration error:', error);
                    message.className = 'jcl-message error';
                    message.textContent = error.message || 'Registration failed. Please try again.';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                }
            });
        });
    </script>
    <?php
}

// Add signup modal to footer on all pages
add_action('wp_footer', 'jcl_job_poster_signup_modal');

function jcl_job_poster_signup_modal() {
    ?>
    <!-- Load Parse SDK if not already loaded -->
    <script src="https://npmcdn.com/parse@3.4.1/dist/parse.min.js"></script>

    <!-- Job Poster Signup Modal -->
    <script>
/**
 * JCL Job Poster Signup Modal - Client-Side Implementation
 * Complete vanilla JavaScript modal wizard for WordPress integration
 *
 * Integration: Add this script to your WordPress site
 * Trigger: window.openJobPosterSignup()
 */

(function() {
  'use strict';

  // Parse Configuration
  const PARSE_APP_ID = '9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF';
  const PARSE_JS_KEY = 'xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI';
  const PARSE_SERVER_URL = 'https://parseapi.back4app.com/';

  // Initialize Parse SDK
  if (typeof Parse !== 'undefined') {
    Parse.initialize(PARSE_APP_ID, PARSE_JS_KEY);
    Parse.serverURL = PARSE_SERVER_URL;
  }

  // State Machine
  const state = {
    currentStep: null,
    sessionId: null,
    accountType: null,
    facilityData: null,
    companyData: null,
    verifiedEmail: null,
    errors: {},
    loading: false
  };

  // CSS Styles
  const styles = `
    <style>
      #jcl-signup-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
      }

      #jcl-signup-modal.active {
        display: flex;
      }

      .jcl-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
      }

      .jcl-modal-content {
        position: relative;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 90%;
        padding: 40px;
        max-height: 90vh;
        overflow-y: auto;
        animation: jclModalSlideIn 0.3s ease-out;
      }

      @keyframes jclModalSlideIn {
        from {
          opacity: 0;
          transform: translateY(-50px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .jcl-close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #999;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
      }

      .jcl-close-btn:hover {
        background: #f0f0f0;
        color: #333;
      }

      .jcl-modal-header {
        margin-bottom: 30px;
      }

      .jcl-modal-title {
        font-size: 28px;
        font-weight: bold;
        color: #2B3241;
        margin: 0 0 10px 0;
      }

      .jcl-modal-subtitle {
        font-size: 16px;
        color: #666;
        margin: 0;
      }

      .jcl-form-group {
        margin-bottom: 20px;
      }

      .jcl-form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #2B3241;
        margin-bottom: 8px;
      }

      .jcl-form-label.required::after {
        content: ' *';
        color: #EE6C4D;
      }

      .jcl-form-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        font-family: inherit;
        transition: border-color 0.2s;
      }

      .jcl-form-input:focus {
        outline: none;
        border-color: #EE6C4D;
      }

      .jcl-form-input.error {
        border-color: #dc3545;
      }

      .jcl-form-hint {
        font-size: 13px;
        color: #666;
        margin-top: 5px;
      }

      .jcl-form-error {
        font-size: 13px;
        color: #dc3545;
        margin-top: 5px;
        display: none;
      }

      .jcl-form-error.visible {
        display: block;
      }

      .jcl-btn {
        padding: 14px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
      }

      .jcl-btn-primary {
        background: #EE6C4D;
        color: white;
      }

      .jcl-btn-primary:hover:not(:disabled) {
        background: #d85a3a;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(238, 108, 77, 0.3);
      }

      .jcl-btn-secondary {
        background: #f0f0f0;
        color: #333;
      }

      .jcl-btn-secondary:hover:not(:disabled) {
        background: #e0e0e0;
      }

      .jcl-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      .jcl-btn-group {
        display: flex;
        gap: 12px;
        margin-top: 30px;
      }

      .jcl-btn-group button {
        flex: 1;
      }

      .jcl-choice-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 0;
      }

      .jcl-choice-card {
        padding: 24px;
        border: 2px solid #ddd;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
      }

      .jcl-choice-card:hover {
        border-color: #EE6C4D;
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }

      .jcl-choice-icon {
        font-size: 48px;
        margin-bottom: 12px;
      }

      .jcl-choice-title {
        font-size: 18px;
        font-weight: 600;
        color: #2B3241;
        margin-bottom: 8px;
      }

      .jcl-choice-desc {
        font-size: 14px;
        color: #666;
      }

      .jcl-loading {
        display: none;
        text-align: center;
        padding: 20px;
      }

      .jcl-loading.active {
        display: block;
      }

      .jcl-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #EE6C4D;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: jclSpin 0.8s linear infinite;
        margin: 0 auto 16px;
      }

      @keyframes jclSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .jcl-success-icon {
        font-size: 64px;
        color: #28a745;
        margin-bottom: 16px;
      }

      .jcl-info-box {
        background: #f8f9fa;
        border-left: 4px solid #EE6C4D;
        padding: 16px;
        border-radius: 8px;
        margin: 16px 0;
      }

      .jcl-info-box strong {
        display: block;
        margin-bottom: 8px;
        color: #2B3241;
      }

      .jcl-step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        gap: 8px;
      }

      .jcl-step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ddd;
        transition: all 0.3s;
      }

      .jcl-step-dot.active {
        background: #EE6C4D;
        transform: scale(1.2);
      }

      .jcl-step-dot.completed {
        background: #28a745;
      }

      @media (max-width: 768px) {
        .jcl-modal-content {
          width: 100%;
          height: 100%;
          max-width: none;
          max-height: none;
          border-radius: 0;
          padding: 20px;
        }

        .jcl-choice-grid {
          grid-template-columns: 1fr;
        }

        .jcl-btn-group {
          flex-direction: column-reverse;
        }
      }
    </style>
  `;

  // HTML Template
  const modalHTML = `
    <div id="jcl-signup-modal">
      <div class="jcl-modal-overlay" onclick="window.closeJobPosterSignup()"></div>
      <div class="jcl-modal-content">
        <button class="jcl-close-btn" onclick="window.closeJobPosterSignup()" aria-label="Close">&times;</button>
        <div id="jcl-modal-body"></div>
      </div>
    </div>
  `;

  // Step Templates
  const templates = {
    step1_accountType: () => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Post Jobs on Jericho Case Logs</h2>
        <p class="jcl-modal-subtitle">Choose your account type to get started</p>
      </div>
      <div class="jcl-choice-grid">
        <div class="jcl-choice-card" onclick="JCLSignup.selectAccountType('facility')">
          <div class="jcl-choice-icon">🏥</div>
          <h3 class="jcl-choice-title">Medical Facility</h3>
          <p class="jcl-choice-desc">Hospitals, clinics, surgery centers</p>
        </div>
        <div class="jcl-choice-card" onclick="JCLSignup.selectAccountType('agency')">
          <div class="jcl-choice-icon">💼</div>
          <h3 class="jcl-choice-title">Staffing Agency</h3>
          <p class="jcl-choice-desc">Recruitment and staffing companies</p>
        </div>
      </div>
    `,

    step2a_facilityLookup: () => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Verify Your Facility</h2>
        <p class="jcl-modal-subtitle">Enter your facility's CCN or NPI number</p>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">CMS CCN (6 characters) or Organizational NPI (10 digits)</label>
        <input type="text" id="jcl-identifier" class="jcl-form-input" placeholder="e.g., 140001 or 1234567890" maxlength="10">
        <p class="jcl-form-hint">Find your CCN on your Medicare certification or NPI at nppes.cms.hhs.gov</p>
        <div id="jcl-identifier-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-secondary" onclick="JCLSignup.goBack()">Back</button>
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.lookupFacility()">Continue</button>
      </div>
      <div id="jcl-loading" class="jcl-loading">
        <div class="jcl-spinner"></div>
        <p>Looking up facility...</p>
      </div>
    `,

    step2b_agencyInfo: () => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Company Information</h2>
        <p class="jcl-modal-subtitle">Tell us about your staffing agency</p>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Company Name</label>
        <input type="text" id="jcl-company-name" class="jcl-form-input" placeholder="Your Company Name">
        <div id="jcl-company-name-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Company Website</label>
        <input type="url" id="jcl-company-website" class="jcl-form-input" placeholder="https://yourcompany.com">
        <div id="jcl-company-website-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-secondary" onclick="JCLSignup.goBack()">Back</button>
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.submitAgencyInfo()">Continue</button>
      </div>
    `,

    step3a_confirmFacility: (facility) => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Confirm Your Facility</h2>
        <p class="jcl-modal-subtitle">Is this your facility?</p>
      </div>
      <div class="jcl-info-box">
        <strong>${facility.facilityName}</strong>
        <p>${facility.address}<br>${facility.city}, ${facility.state} ${facility.zipCode}</p>
        ${facility.phone ? `<p>Phone: ${facility.phone}</p>` : ''}
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-secondary" onclick="JCLSignup.goBack()">No, go back</button>
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.confirmFacility(true)">Yes, this is my facility</button>
      </div>
    `,

    step3b_emailVerification: () => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Verify Your Work Email</h2>
        <p class="jcl-modal-subtitle">We'll send a verification code to your company email</p>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Work Email Address</label>
        <input type="email" id="jcl-work-email" class="jcl-form-input" placeholder="you@yourcompany.com">
        <p class="jcl-form-hint">Must match your company domain. Personal emails (gmail, yahoo) are not allowed.</p>
        <div id="jcl-work-email-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-secondary" onclick="JCLSignup.goBack()">Back</button>
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.requestOTP()">Send Verification Code</button>
      </div>
      <div id="jcl-loading" class="jcl-loading">
        <div class="jcl-spinner"></div>
        <p>Sending code...</p>
      </div>
    `,

    step4_otpVerification: (email) => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Enter Verification Code</h2>
        <p class="jcl-modal-subtitle">We sent a 6-digit code to ${email}</p>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Verification Code</label>
        <input type="text" id="jcl-otp-code" class="jcl-form-input" placeholder="000000" maxlength="6" inputmode="numeric">
        <p class="jcl-form-hint">Code expires in 10 minutes</p>
        <div id="jcl-otp-code-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-secondary" onclick="JCLSignup.goBack()">Back</button>
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.verifyOTP()">Verify Code</button>
      </div>
      <div id="jcl-loading" class="jcl-loading">
        <div class="jcl-spinner"></div>
        <p>Verifying...</p>
      </div>
    `,

    step5_userInfo: () => `
      <div class="jcl-modal-header">
        <h2 class="jcl-modal-title">Create Your Account</h2>
        <p class="jcl-modal-subtitle">One last step to complete your registration</p>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">First Name</label>
        <input type="text" id="jcl-first-name" class="jcl-form-input" placeholder="First Name">
        <div id="jcl-first-name-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Last Name</label>
        <input type="text" id="jcl-last-name" class="jcl-form-input" placeholder="Last Name">
        <div id="jcl-last-name-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Job Title</label>
        <input type="text" id="jcl-job-title" class="jcl-form-input" placeholder="e.g., HR Manager">
        <div id="jcl-job-title-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Phone Number</label>
        <input type="tel" id="jcl-phone" class="jcl-form-input" placeholder="(555) 123-4567">
        <div id="jcl-phone-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Email</label>
        <input type="email" id="jcl-email" class="jcl-form-input" placeholder="your@email.com" ${state.verifiedEmail ? `value="${state.verifiedEmail}" readonly` : ''}>
        <div id="jcl-email-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-form-group">
        <label class="jcl-form-label required">Password</label>
        <input type="password" id="jcl-password" class="jcl-form-input" placeholder="••••••••">
        <p class="jcl-form-hint">At least 8 characters, 1 uppercase, 1 number, 1 special character</p>
        <div id="jcl-password-error" class="jcl-form-error"></div>
      </div>
      <div class="jcl-btn-group">
        <button class="jcl-btn jcl-btn-primary" onclick="JCLSignup.completeSignup()" style="width: 100%">Create Account</button>
      </div>
      <div id="jcl-loading" class="jcl-loading">
        <div class="jcl-spinner"></div>
        <p>Creating your account...</p>
      </div>
    `,

    step6_success: () => `
      <div style="text-align: center; padding: 10px 20px;">
        <div class="jcl-success-icon">✓</div>
        <h2 class="jcl-modal-title">Account Created Successfully!</h2>
        <p class="jcl-modal-subtitle">You're all set to start posting jobs</p>
        <button class="jcl-btn jcl-btn-primary" onclick="window.location.href='/employer-dashboard'" style="margin-top: 30px;">Go to Dashboard</button>
      </div>
    `
  };

  // Main Signup Object
  window.JCLSignup = {
    init() {
      // Inject styles and modal HTML
      if (!document.getElementById('jcl-signup-modal')) {
        document.head.insertAdjacentHTML('beforeend', styles);
        document.body.insertAdjacentHTML('beforeend', modalHTML);
      }

      // ESC key to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('jcl-signup-modal').classList.contains('active')) {
          window.closeJobPosterSignup();
        }
      });
    },

    showStep(template) {
      const body = document.getElementById('jcl-modal-body');
      body.innerHTML = template;
    },

    showLoading(show) {
      const loading = document.getElementById('jcl-loading');
      if (loading) {
        loading.classList.toggle('active', show);
      }
    },

    showError(fieldId, message) {
      const errorEl = document.getElementById(`${fieldId}-error`);
      const inputEl = document.getElementById(fieldId);
      if (errorEl && inputEl) {
        errorEl.textContent = message;
        errorEl.classList.add('visible');
        inputEl.classList.add('error');
      }
    },

    clearErrors() {
      document.querySelectorAll('.jcl-form-error').forEach(el => el.classList.remove('visible'));
      document.querySelectorAll('.jcl-form-input').forEach(el => el.classList.remove('error'));
    },

    async selectAccountType(type) {
      state.accountType = type;
      this.showLoading(true);

      try {
        const result = await Parse.Cloud.run('startSignupSession', { accountType: type });
        state.sessionId = result.sessionId;

        if (type === 'facility') {
          this.showStep(templates.step2a_facilityLookup());
        } else {
          this.showStep(templates.step2b_agencyInfo());
        }
      } catch (error) {
        alert('Error starting signup: ' + error.message);
      }
    },

    async lookupFacility() {
      this.clearErrors();
      const identifier = document.getElementById('jcl-identifier').value.trim();

      if (!identifier) {
        this.showError('jcl-identifier', 'Please enter a CCN or NPI');
        return;
      }

      const identifierType = identifier.length === 6 ? 'ccn' : 'npi';
      this.showLoading(true);

      try {
        const result = await Parse.Cloud.run('lookupFacilityByCCNOrNPI', {
          sessionId: state.sessionId,
          identifier,
          identifierType
        });

        state.facilityData = result;
        this.showStep(templates.step3a_confirmFacility(result));
      } catch (error) {
        this.showLoading(false);
        this.showError('jcl-identifier', error.message);
      }
    },

    async confirmFacility(confirmed) {
      try {
        await Parse.Cloud.run('confirmFacilityMatch', {
          sessionId: state.sessionId,
          confirmed
        });

        this.showStep(templates.step5_userInfo());
      } catch (error) {
        alert('Error: ' + error.message);
      }
    },

    async submitAgencyInfo() {
      this.clearErrors();
      const companyName = document.getElementById('jcl-company-name').value.trim();
      const companyWebsite = document.getElementById('jcl-company-website').value.trim();

      if (!companyName) {
        this.showError('jcl-company-name', 'Company name is required');
        return;
      }

      if (!companyWebsite) {
        this.showError('jcl-company-website', 'Company website is required');
        return;
      }

      try {
        await Parse.Cloud.run('updateSignupSession', {
          sessionId: state.sessionId,
          companyName,
          companyWebsite
        });

        state.companyData = { companyName, companyWebsite };
        this.showStep(templates.step3b_emailVerification());
      } catch (error) {
        alert('Error: ' + error.message);
      }
    },

    async requestOTP() {
      this.clearErrors();
      const email = document.getElementById('jcl-work-email').value.trim();

      if (!email) {
        this.showError('jcl-work-email', 'Email is required');
        return;
      }

      this.showLoading(true);

      try {
        await Parse.Cloud.run('requestEmailOTP', {
          sessionId: state.sessionId,
          email
        });

        state.verifiedEmail = email;
        this.showStep(templates.step4_otpVerification(email));
      } catch (error) {
        this.showLoading(false);
        this.showError('jcl-work-email', error.message);
      }
    },

    async verifyOTP() {
      this.clearErrors();
      const otp = document.getElementById('jcl-otp-code').value.trim();

      if (!otp || otp.length !== 6) {
        this.showError('jcl-otp-code', 'Please enter the 6-digit code');
        return;
      }

      this.showLoading(true);

      try {
        await Parse.Cloud.run('verifyEmailOTP', {
          sessionId: state.sessionId,
          otp,
          email: state.verifiedEmail
        });

        this.showStep(templates.step5_userInfo());
      } catch (error) {
        this.showLoading(false);
        this.showError('jcl-otp-code', error.message);
      }
    },

    async completeSignup() {
      this.clearErrors();

      const firstName = document.getElementById('jcl-first-name').value.trim();
      const lastName = document.getElementById('jcl-last-name').value.trim();
      const title = document.getElementById('jcl-job-title').value.trim();
      const phone = document.getElementById('jcl-phone').value.trim();
      const email = document.getElementById('jcl-email').value.trim();
      const password = document.getElementById('jcl-password').value;

      let hasError = false;

      if (!firstName) {
        this.showError('jcl-first-name', 'First name is required');
        hasError = true;
      }
      if (!lastName) {
        this.showError('jcl-last-name', 'Last name is required');
        hasError = true;
      }
      if (!title) {
        this.showError('jcl-job-title', 'Job title is required');
        hasError = true;
      }
      if (!phone) {
        this.showError('jcl-phone', 'Phone is required');
        hasError = true;
      }
      if (!email) {
        this.showError('jcl-email', 'Email is required');
        hasError = true;
      }
      if (!password) {
        this.showError('jcl-password', 'Password is required');
        hasError = true;
      }

      if (hasError) return;

      this.showLoading(true);

      try {
        const result = await Parse.Cloud.run('completeSignup', {
          sessionId: state.sessionId,
          userFields: {
            firstName,
            lastName,
            title,
            phone,
            email
          },
          password
        });

        // Auto-login with session token
        await Parse.User.become(result.sessionToken);

        this.showStep(templates.step6_success());
      } catch (error) {
        this.showLoading(false);
        alert('Error creating account: ' + error.message);
      }
    },

    goBack() {
      if (state.currentStep === 'step2a') {
        this.showStep(templates.step1_accountType());
      } else if (state.currentStep === 'step2b') {
        this.showStep(templates.step1_accountType());
      }
      // Add more back navigation as needed
    }
  };

  // Global open/close functions
  window.openJobPosterSignup = function() {
    JCLSignup.init();
    const modal = document.getElementById('jcl-signup-modal');
    modal.classList.add('active');
    JCLSignup.showStep(templates.step1_accountType());
  };

  window.closeJobPosterSignup = function() {
    const modal = document.getElementById('jcl-signup-modal');
    modal.classList.remove('active');
    // Reset state
    state.currentStep = null;
    state.sessionId = null;
    state.accountType = null;
    state.facilityData = null;
    state.errors = {};
  };

  // Auto-initialize on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => JCLSignup.init());
  } else {
    JCLSignup.init();
  }

})();
    </script>
    <?php
}

// Add shortcode for Registration Page (V2 with forced override)
function jcl_employer_registration_page_v2() {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                align-items: flex-start;
                justify-content: center;
                padding: 10px 20px;
            }

            .jcl-container {
                background: rgba(43, 50, 65, 0.95);
                border-radius: 20px;
                box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
                max-width: 600px;
                width: 100%;
                padding: 40px;
                margin-top: 0;
            }

            .jcl-logo {
                text-align: center;
                margin-bottom: 20px;
            }

            .jcl-logo h1 {
                color: var(--jcl-orange);
                font-size: 32px;
                margin-bottom: 10px;
                font-weight: 400;
            }

            .jcl-logo p {
                color: #FFFFFF !important;
                font-size: 18px;
                font-weight: 300;
            }

            .jcl-form-group {
                margin-bottom: 25px;
            }

            .jcl-label {
                display: block;
                color: #FFFFFF !important;
                font-weight: 400;
                margin-bottom: 8px;
                font-size: 14px;
            }

            .jcl-label .required {
                color: var(--jcl-orange);
            }

            .jcl-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 16px;
                font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
                font-weight: 300;
                transition: border-color 0.3s ease;
            }

            .jcl-input:focus {
                outline: none;
                border-color: var(--jcl-orange);
            }

            .jcl-btn {
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

            .jcl-btn:hover {
                background: #d65b3e;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(238, 108, 77, 0.3);
            }

            .jcl-btn:disabled {
                background: #ccc;
                cursor: not-allowed;
                transform: none;
            }

            .jcl-message {
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: none;
                font-weight: 300;
            }

            .jcl-message.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
                display: block;
            }

            .jcl-message.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                display: block;
            }

            .jcl-info-text {
                background: rgba(224, 251, 252, 0.1);
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 25px;
                font-size: 14px;
                color: #FFFFFF !important;
                font-weight: 300;
                border: 1px solid rgba(224, 251, 252, 0.3);
            }

            .jcl-login-link {
                text-align: center;
                margin-top: 0;
                color: #FFFFFF !important;
                font-weight: 300;
            }

            .jcl-login-link a {
                color: #4A9EFF;
                font-weight: 400;
                text-decoration: none;
            }

            .jcl-login-link a:hover {
                text-decoration: underline;
            }

            @media (max-width: 600px) {
                .jcl-container {
                    padding: 30px 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="jcl-container">
            <div class="jcl-logo">
                <h1>📋 Jericho Case Logs</h1>
                <p>Employer Registration</p>
            </div>

            <div class="jcl-info-text">
                Register your healthcare facility to post job opportunities for anesthesia professionals.
            </div>

            <div id="jcl-message" class="jcl-message"></div>

            <form id="jcl-registration-form">
                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-facName">Facility Name <span class="required">*</span></label>
                    <input type="text" class="jcl-input" id="jcl-facName" name="facName" required>
                </div>

                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-facContactName">Contact Person Name <span class="required">*</span></label>
                    <input type="text" class="jcl-input" id="jcl-facContactName" name="facContactName" required>
                </div>

                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-facContactEmail">Contact Email <span class="required">*</span></label>
                    <input type="email" class="jcl-input" id="jcl-facContactEmail" name="facContactEmail" required>
                </div>

                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-facContactPhone">Contact Phone <span class="required">*</span></label>
                    <input type="tel" class="jcl-input" id="jcl-facContactPhone" name="facContactPhone" placeholder="(555) 555-5555" required>
                </div>

                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-password">Password <span class="required">*</span></label>
                    <input type="password" class="jcl-input" id="jcl-password" name="password" minlength="6" required>
                </div>

                <div class="jcl-form-group">
                    <label class="jcl-label" for="jcl-confirmPassword">Confirm Password <span class="required">*</span></label>
                    <input type="password" class="jcl-input" id="jcl-confirmPassword" name="confirmPassword" minlength="6" required>
                </div>

                <button type="submit" class="jcl-btn" id="jcl-submit-btn">Create Account</button>
            </form>

            <div class="jcl-login-link">
                Already have an account? <a href="#" onclick="if(window.opener){window.opener.openLoginModal();window.close();}else{window.location.href='<?php echo esc_url(home_url('/')); ?>';setTimeout(function(){window.openLoginModal();},500);}return false;">Login here</a>
            </div>
        </div>

        <script>
            // Initialize Parse
            Parse.initialize("9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF", "xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI");
            Parse.serverURL = 'https://parseapi.back4app.com/';

            const form = document.getElementById('jcl-registration-form');
            const messageDiv = document.getElementById('jcl-message');
            const submitBtn = document.getElementById('jcl-submit-btn');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const facName = document.getElementById('jcl-facName').value;
                const facContactName = document.getElementById('jcl-facContactName').value;
                const facContactEmail = document.getElementById('jcl-facContactEmail').value;
                const facContactPhone = document.getElementById('jcl-facContactPhone').value;
                const password = document.getElementById('jcl-password').value;
                const confirmPassword = document.getElementById('jcl-confirmPassword').value;

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
                        window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                        setTimeout(function() {
                            if (window.openLoginModal) {
                                window.openLoginModal();
                            }
                        }, 500);
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
                messageDiv.className = 'jcl-message ' + type;
            }
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// ============================================
// JCL Poster Dashboard Shortcode
// ============================================

/**
 * Enqueue dashboard scripts and styles
 */
function jcl_poster_dashboard_enqueue_assets() {
    // Only load on pages with the shortcode (checked dynamically)
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'jcl_poster_dashboard')) {
        
        // Enqueue Parse SDK (if not already loaded)
        if (!wp_script_is('parse-sdk', 'enqueued')) {
            wp_enqueue_script(
                'parse-sdk',
                'https://npmcdn.com/parse@3.4.1/dist/parse.min.js',
                array(),
                '3.4.1',
                false // Load in head
            );
        }

        // Enqueue dashboard CSS
        wp_enqueue_style(
            'jcl-poster-dashboard-css',
            plugin_dir_url(__FILE__) . 'jcl-poster-dashboard.css',
            array(),
            '1.0.0'
        );

        // Enqueue dashboard JS
        wp_enqueue_script(
            'jcl-poster-dashboard-js',
            plugin_dir_url(__FILE__) . 'jcl-poster-dashboard.js',
            array('parse-sdk'),
            '1.0.0',
            true // Load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'jcl_poster_dashboard_enqueue_assets');

/**
 * Dashboard shortcode
 * Usage: [jcl_poster_dashboard]
 */
function jcl_poster_dashboard_shortcode() {
    // Return the root element that JavaScript will populate
    return '<div id="jcl-dashboard-root"></div>';
}
add_shortcode('jcl_poster_dashboard', 'jcl_poster_dashboard_shortcode');

// ============================================
// JCL Job Posting Page Shortcode
// ============================================

/**
 * Enqueue job posting scripts and styles
 */
function jcl_job_posting_enqueue_assets() {
    // Only load on pages with the shortcode
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'jcl_job_post')) {

        // Enqueue Parse SDK (if not already loaded)
        if (!wp_script_is('parse-sdk', 'enqueued')) {
            wp_enqueue_script(
                'parse-sdk',
                'https://npmcdn.com/parse@3.4.1/dist/parse.min.js',
                array(),
                '3.4.1',
                false // Load in head
            );
        }

        // Enqueue job posting CSS
        wp_enqueue_style(
            'jcl-job-posting-css',
            plugin_dir_url(__FILE__) . 'jcl-job-posting.css',
            array(),
            '1.0.0'
        );

        // Enqueue job posting JS
        wp_enqueue_script(
            'jcl-job-posting-js',
            plugin_dir_url(__FILE__) . 'jcl-job-posting.js',
            array('parse-sdk'),
            '1.0.0',
            true // Load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'jcl_job_posting_enqueue_assets');

/**
 * Job posting page shortcode
 * Usage: [jcl_job_post]
 */
function jcl_job_posting_shortcode() {
    // Return the root element that JavaScript will populate
    return '<div id="jcl-job-post-root"></div>';
}
add_shortcode('jcl_job_post', 'jcl_job_posting_shortcode');
