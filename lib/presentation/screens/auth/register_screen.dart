import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/themes/app_colors.dart';
import '../../widgets/glow_button.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _titleController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  String _selectedSilo = AppConstants.siloAnes; // Default to CRNA

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _firstNameController.dispose();
    _lastNameController.dispose();
    _titleController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    await ref.read(authProvider.notifier).register(
          email: _emailController.text.trim(),
          password: _passwordController.text,
          silo: _selectedSilo,
          firstName: _firstNameController.text.trim().isEmpty
              ? null
              : _firstNameController.text.trim(),
          lastName: _lastNameController.text.trim().isEmpty
              ? null
              : _lastNameController.text.trim(),
          title: _titleController.text.trim().isEmpty
              ? null
              : _titleController.text.trim(),
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
      appBar: AppBar(
        backgroundColor: AppColors.jclGray,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.jclOrange),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 21.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // App Logo
                  Image.asset(
                    'assets/images/1024Logo.png',
                    width: 100,
                    height: 100,
                  ),
                  const SizedBox(height: 16),

                  // Title
                  const Text(
                    'Create Account',
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w600,
                      color: AppColors.jclWhite,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 32),

                  // Email Field
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: const InputDecoration(
                      hintText: 'Email',
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
                  const SizedBox(height: 16),

                  // Password Field
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Password',
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
                        return 'Please enter a password';
                      }
                      if (value.length < 6) {
                        return 'Password must be at least 6 characters';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),

                  // Confirm Password Field
                  TextFormField(
                    controller: _confirmPasswordController,
                    obscureText: _obscureConfirmPassword,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: InputDecoration(
                      hintText: 'Confirm Password',
                      suffixIcon: IconButton(
                        icon: Image.asset(
                          _obscureConfirmPassword
                              ? 'assets/images/hidePW-20.png'
                              : 'assets/images/showPW-20.png',
                          width: 20,
                          height: 20,
                        ),
                        onPressed: () {
                          setState(() {
                            _obscureConfirmPassword = !_obscureConfirmPassword;
                          });
                        },
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Please confirm your password';
                      }
                      if (value != _passwordController.text) {
                        return 'Passwords do not match';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),

                  // First Name Field (Optional)
                  TextFormField(
                    controller: _firstNameController,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: const InputDecoration(
                      hintText: 'First Name (Optional)',
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Last Name Field (Optional)
                  TextFormField(
                    controller: _lastNameController,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: const InputDecoration(
                      hintText: 'Last Name (Optional)',
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Title Field (Optional)
                  TextFormField(
                    controller: _titleController,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray, fontSize: 14),
                    decoration: const InputDecoration(
                      hintText: 'Title (e.g., CRNA, MD) (Optional)',
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Account Type Selection
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      border: Border.all(color: AppColors.jclWhite.withAlpha((255 * 0.3).round())),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Account Type',
                          style: TextStyle(
                            color: AppColors.jclWhite,
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 12),
                        RadioListTile<String>(
                          title: const Text(
                            'Healthcare Provider (Anesthesia)',
                            style: TextStyle(color: AppColors.jclWhite, fontSize: 13),
                          ),
                          value: AppConstants.siloAnes,
                          groupValue: _selectedSilo,
                          activeColor: AppColors.jclOrange,
                          onChanged: (value) {
                            setState(() {
                              _selectedSilo = value!;
                            });
                          },
                          contentPadding: EdgeInsets.zero,
                        ),
                        RadioListTile<String>(
                          title: const Text(
                            'Job Provider',
                            style: TextStyle(color: AppColors.jclWhite, fontSize: 13),
                          ),
                          value: AppConstants.siloJobs,
                          groupValue: _selectedSilo,
                          activeColor: AppColors.jclOrange,
                          onChanged: (value) {
                            setState(() {
                              _selectedSilo = value!;
                            });
                          },
                          contentPadding: EdgeInsets.zero,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 32),

                  // Register Button with Glow Effect
                  if (authState.isLoading)
                    const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: AppColors.jclOrange,
                      ),
                    )
                  else
                    GlowButton(
                      text: 'Create Account',
                      onPressed: _handleRegister,
                      isPrimary: true,
                      icon: Icons.person_add,
                    ),
                  const SizedBox(height: 24),

                  // Already have account
                  Center(
                    child: TextButton(
                      onPressed: authState.isLoading
                          ? null
                          : () => Navigator.of(context).pop(),
                      child: const Text(
                        'Already have an account? Sign In',
                        style: TextStyle(
                          color: AppColors.jclOrange,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
