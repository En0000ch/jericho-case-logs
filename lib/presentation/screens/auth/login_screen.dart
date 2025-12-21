import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../../core/themes/app_colors.dart';
import '../../widgets/glow_button.dart';
import 'register_screen.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _savePassword = false;

  @override
  void initState() {
    super.initState();
    _checkFirstLaunch();
  }

  Future<void> _checkFirstLaunch() async {
    final prefs = await SharedPreferences.getInstance();
    final hasSeenDisclaimer = prefs.getBool('has_seen_disclaimer') ?? false;

    if (!hasSeenDisclaimer && mounted) {
      // Show disclaimer dialog on first launch
      Future.microtask(() => _showDisclaimerDialog());
    }
  }

  void _showDisclaimerDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text(
          '***DISCLAIMER***',
          textAlign: TextAlign.center,
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: const [
              Text(
                'Before you use the Jericho Case Logs application, please read and agree to the following terms and conditions:',
                style: TextStyle(fontSize: 13),
              ),
              SizedBox(height: 16),
              Text(
                '1. HIPAA Compliance:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              SizedBox(height: 4),
              Text(
                '- Jericho Creations is committed to maintaining the highest standards of privacy and security. However, users of the Jericho Case Logs app are solely responsible for ensuring HIPAA (Health Insurance Portability and Accountability Act) compliance in their use of the application.',
                style: TextStyle(fontSize: 12),
              ),
              SizedBox(height: 12),
              Text(
                '2. Data Security:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              SizedBox(height: 4),
              Text(
                '- You are responsible for the security of your login credentials, including your username and password. Please take all necessary measures to safeguard this information to prevent unauthorized access to your account.',
                style: TextStyle(fontSize: 12),
              ),
              SizedBox(height: 12),
              Text(
                '3. Confidentiality:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              SizedBox(height: 4),
              Text(
                '- Ensure that you do not share sensitive patient information or any other data that may be protected under HIPAA without the appropriate consent and safeguards. Jericho Creations is not liable for any breaches of confidentiality due to user actions.',
                style: TextStyle(fontSize: 12),
              ),
              SizedBox(height: 12),
              Text(
                '4. Liability:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              SizedBox(height: 4),
              Text(
                '- Jericho Creations shall not be held responsible for any HIPAA violations, data breaches, or unauthorized disclosures that may occur as a result of your use of the Jericho Case Logs app.',
                style: TextStyle(fontSize: 12),
              ),
              SizedBox(height: 12),
              Text(
                '5. Legal Compliance:',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              SizedBox(height: 4),
              Text(
                '- You must comply with all applicable laws, regulations, and guidelines, including but not limited to HIPAA, when using this application.',
                style: TextStyle(fontSize: 12),
              ),
              SizedBox(height: 16),
              Text(
                'By continuing to use the Jericho Case Logs app, you acknowledge that you have read, understood, and agreed to the terms and conditions outlined in this disclaimer. Jericho Creations reserves the right to modify these terms at any time.',
                style: TextStyle(fontSize: 12, fontStyle: FontStyle.italic),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () async {
              final prefs = await SharedPreferences.getInstance();
              await prefs.setBool('has_seen_disclaimer', true);
              if (context.mounted) {
                Navigator.of(context).pop();
                // After disclaimer, check if we should show new user question alert
                _checkForNewUserAlert();
              }
            },
            child: const Text('I Accept'),
          ),
        ],
      ),
    );
  }

  /// Check if we should show the new user question alert
  /// iOS: newUserQuestionAlert - shows if email/password fields are empty after disclaimer
  Future<void> _checkForNewUserAlert() async {
    final prefs = await SharedPreferences.getInstance();
    final savedEmail = prefs.getString('jcl_email') ?? '';
    final savedPassword = prefs.getString('jcl_password') ?? '';

    // If both fields are empty (no saved credentials), ask if they're a new user
    if (savedEmail.isEmpty && savedPassword.isEmpty && mounted) {
      _showNewUserQuestionAlert();
    }
  }

  /// iOS: newUserQuestionAlert method
  /// Shows dialog asking "Are you a new user?"
  void _showNewUserQuestionAlert() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclGray,
        title: const Text(
          'Welcome!',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        content: const Text(
          'There is no email address saved.\nAre you a new user?',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        actions: [
          TextButton(
            onPressed: () async {
              Navigator.of(context).pop();
              // User is new, navigate to register screen
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const RegisterScreen(),
                ),
              );
            },
            child: const Text(
              'Yes',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
          TextButton(
            onPressed: () async {
              Navigator.of(context).pop();
              // User is existing, just mark as registered
              final prefs = await SharedPreferences.getInstance();
              await prefs.setBool('jcl_isRegistered', true);
              // Focus on email field to let them log in
              FocusScope.of(context).requestFocus(FocusNode());
            },
            child: const Text(
              'No',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    print('LoginScreen: Attempting login with email: ${_emailController.text.trim()}');

    await ref.read(authProvider.notifier).login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
          savePassword: _savePassword,
        );

    print('LoginScreen: Login completed');
  }

  /// Show forgot password dialog and send reset email
  void _showForgotPasswordDialog() {
    final emailController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: const Text(
          'Forgot Password?',
          style: TextStyle(color: AppColors.jclGray),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Enter your email address and we\'ll send you a link to reset your password.',
              style: TextStyle(color: AppColors.jclGray, fontSize: 14),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: emailController,
              keyboardType: TextInputType.emailAddress,
              autocorrect: false,
              enableSuggestions: false,
              decoration: InputDecoration(
                hintText: 'Email',
                filled: true,
                fillColor: Colors.grey.shade100,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide.none,
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: AppColors.jclOrange, width: 1),
                ),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text(
              'Cancel',
              style: TextStyle(color: Colors.grey),
            ),
          ),
          ElevatedButton(
            onPressed: () async {
              final email = emailController.text.trim();
              if (email.isEmpty || !email.contains('@')) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Please enter a valid email address'),
                    backgroundColor: Colors.red,
                  ),
                );
                return;
              }

              Navigator.of(context).pop();

              // Show loading
              showDialog(
                context: context,
                barrierDismissible: false,
                builder: (context) => const Center(
                  child: CircularProgressIndicator(color: AppColors.jclOrange),
                ),
              );

              // Request password reset
              final success = await ref.read(authProvider.notifier).requestPasswordReset(email);

              if (context.mounted) {
                Navigator.of(context).pop(); // Close loading

                if (success) {
                  showDialog(
                    context: context,
                    builder: (context) => AlertDialog(
                      backgroundColor: AppColors.jclWhite,
                      title: const Text(
                        'Email Sent',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                      content: Text(
                        'A password reset link has been sent to $email. Please check your inbox.',
                        style: const TextStyle(color: AppColors.jclGray),
                      ),
                      actions: [
                        ElevatedButton(
                          onPressed: () => Navigator.of(context).pop(),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.jclOrange,
                            foregroundColor: AppColors.jclWhite,
                          ),
                          child: const Text('OK'),
                        ),
                      ],
                    ),
                  );
                } else {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Failed to send reset email. Please try again.'),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('Send Reset Link'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    // Show error if any
    ref.listen<AuthState>(authProvider, (previous, next) {
      if (next.error != null && !next.isLoading) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(next.error!),
            backgroundColor: Colors.red,
          ),
        );
      }
    });

    return Scaffold(
      backgroundColor: AppColors.jclGray,
      body: SafeArea(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 21.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // App Logo - large at top
                  Padding(
                    padding: const EdgeInsets.only(top: 0),
                    child: Image.asset(
                      'assets/images/1024Logo.png',
                      height: 260,
                      fit: BoxFit.contain,
                    ),
                  ),

                  // Version number
                  const Align(
                    alignment: Alignment.centerRight,
                    child: Padding(
                      padding: EdgeInsets.only(right: 15, bottom: 8),
                      child: Text(
                        'v2.1',
                        style: TextStyle(
                          fontSize: 10,
                          color: AppColors.jclOrange,
                        ),
                      ),
                    ),
                  ),

                  // Email Field
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Email',
                      filled: true,
                      fillColor: AppColors.jclWhite,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: AppColors.jclOrange, width: 1),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Please enter your email';
                      }
                      if (!value.contains('@')) {
                        return 'Please enter a valid email';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 8),

                  // Password Field
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Password',
                      filled: true,
                      fillColor: AppColors.jclWhite,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: AppColors.jclOrange, width: 1),
                      ),
                      suffixIcon: IconButton(
                        icon: Image.asset(
                          _obscurePassword
                              ? 'assets/images/hidePW-20.png'
                              : 'assets/images/showPW-20.png',
                          width: 20,
                          height: 20,
                        ),
                        onPressed: () {
                          setState(() {
                            _obscurePassword = !_obscurePassword;
                          });
                        },
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Please enter your password';
                      }
                      // No length requirement on login to allow existing admin accounts with short passwords
                      return null;
                    },
                  ),
                  const SizedBox(height: 8),

                  // Sign In Button with Glow Effect
                  if (authState.isLoading)
                    const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: AppColors.jclOrange,
                      ),
                    )
                  else
                    GlowButton(
                      text: 'Sign In',
                      onPressed: _handleLogin,
                      isPrimary: false,
                      icon: Icons.login,
                    ),
                  const SizedBox(height: 8),

                  // Save Password Switch
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      const Text(
                        'Save Password',
                        style: TextStyle(
                          fontSize: 10,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Transform.scale(
                        scale: 0.8,
                        child: Switch(
                          value: _savePassword,
                          onChanged: (value) {
                            setState(() {
                              _savePassword = value;
                            });
                          },
                          activeColor: AppColors.jclWhite,
                          activeTrackColor: AppColors.jclOrange,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Create Account Button
                  Center(
                    child: TextButton(
                      onPressed: authState.isLoading
                          ? null
                          : () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => const RegisterScreen(),
                                ),
                              );
                            },
                      child: const Text(
                        'Create Account',
                        style: TextStyle(
                          color: AppColors.jclOrange,
                          fontSize: 15,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Forgot Password Button
                  Center(
                    child: TextButton(
                      onPressed: authState.isLoading ? null : _showForgotPasswordDialog,
                      child: const Text(
                        'Forgot Password?',
                        style: TextStyle(
                          color: AppColors.jclOrange,
                          fontSize: 15,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 80),

                  // Info button and Copyright
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Info button (disclaimer)
                      Opacity(
                        opacity: 0.5,
                        child: IconButton(
                          icon: const Icon(
                            Icons.info_outline,
                            color: AppColors.jclOrange,
                            size: 24,
                          ),
                          onPressed: () {
                            _showDisclaimerDialog();
                          },
                        ),
                      ),

                      // Copyright Text
                      Expanded(
                        child: GestureDetector(
                          onTap: () async {
                            final url = Uri.parse('https://www.jerichocreations.com');
                            if (await canLaunchUrl(url)) {
                              await launchUrl(url, mode: LaunchMode.externalApplication);
                            }
                          },
                          child: const Text(
                            '© 2023 Jericho Creations, LLC.\nAll rights reserved.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                              color: Colors.blue,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ),
                      ),

                      // Spacer to balance layout
                      const SizedBox(width: 48),
                    ],
                  ),
                  const SizedBox(height: 8),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
