import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../providers/auth_provider.dart';
import '../../../core/themes/app_colors.dart';
import '../reports/generate_report_screen.dart';
import '../job_search/job_search_screen.dart';
import '../sort_surgeries/sort_surgeries_screen.dart';
import 'settings_update_screen.dart';

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  late TextEditingController _firstNameController;
  late TextEditingController _lastNameController;
  late TextEditingController _titleController;

  @override
  void initState() {
    super.initState();
    // Initialize controllers with empty values first
    _firstNameController = TextEditingController();
    _lastNameController = TextEditingController();
    _titleController = TextEditingController();

    // Add listeners to save data when text changes
    _firstNameController.addListener(_saveUserDataDebounced);
    _lastNameController.addListener(_saveUserDataDebounced);
    _titleController.addListener(_saveUserDataDebounced);

    // Load data asynchronously
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final user = ref.read(currentUserProvider);

    // Load from SharedPreferences first, fall back to user object
    final firstName = prefs.getString('jclFirstName') ?? user?.firstName ?? '';
    final lastName = prefs.getString('jclLastName') ?? user?.lastName ?? '';
    final title = prefs.getString('jclUserTitle') ?? user?.title ?? '';

    // Update controller text
    _firstNameController.text = firstName;
    _lastNameController.text = lastName;
    _titleController.text = title;
  }

  void _saveUserDataDebounced() {
    // Save immediately when user types
    _saveUserData();
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _titleController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(currentUserProvider);

    if (user == null) {
      return const Scaffold(
        body: Center(
          child: Text('Please log in to view settings'),
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Dashboard',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclOrange,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.jclWhite),
        actions: [
          PopupMenuButton<String>(
            icon: Image.asset(
              'assets/images/menu-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            color: AppColors.jclWhite,
            offset: const Offset(0, 50),
            onSelected: (value) {
              switch (value) {
                case 'job_search':
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const JobSearchScreen(),
                    ),
                  );
                  break;
                case 'search_surgeries':
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const SortSurgeriesScreen(),
                    ),
                  );
                  break;
                case 'logout':
                  _showLogoutAlert();
                  break;
                case 'delete_account':
                  _showDeleteAccountAlert();
                  break;
              }
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'job_search',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/opportunity-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Job Search',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'search_surgeries',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/spyglass-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Search Past Surgeries',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/logout-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Logout',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'delete_account',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/delete-user-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Delete JCL Account',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: GestureDetector(
        onTap: () {
          // Dismiss keyboard when tapping outside text fields
          FocusScope.of(context).unfocus();
        },
        child: Stack(
          children: [
            // Background Logo (faded)
            Positioned(
              top: 0,
              left: 16,
              right: 16,
              child: Opacity(
                opacity: 0.25,
                child: Image.asset(
                  'assets/images/1024Logo.png',
                  height: 360,
                  fit: BoxFit.contain,
                ),
              ),
            ),

            // Content
            SingleChildScrollView(
            child: Column(
              children: [
                const SizedBox(height: 39),

                // User Info Text Fields
                Center(
                  child: SizedBox(
                    width: 300,
                    child: Column(
                      children: [
                        // First Name Field
                        SizedBox(
                          height: 34,
                          child: TextField(
                            controller: _firstNameController,
                            autocorrect: false,
                            enableSuggestions: false,
                            textCapitalization: TextCapitalization.words,
                            style: const TextStyle(
                              color: AppColors.jclGray,
                              fontSize: 14,
                            ),
                            decoration: InputDecoration(
                              hintText: 'User First Name',
                              hintStyle: TextStyle(
                                color: AppColors.jclGray.withAlpha(128),
                                fontSize: 14,
                              ),
                              filled: true,
                              fillColor: AppColors.jclWhite,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Last Name Field
                        SizedBox(
                          height: 34,
                          child: TextField(
                            controller: _lastNameController,
                            autocorrect: false,
                            enableSuggestions: false,
                            textCapitalization: TextCapitalization.words,
                            style: const TextStyle(
                              color: AppColors.jclGray,
                              fontSize: 14,
                            ),
                            decoration: InputDecoration(
                              hintText: 'User Last Name',
                              hintStyle: TextStyle(
                                color: AppColors.jclGray.withAlpha(128),
                                fontSize: 14,
                              ),
                              filled: true,
                              fillColor: AppColors.jclWhite,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Title Field
                        SizedBox(
                          height: 34,
                          child: TextField(
                            controller: _titleController,
                            autocorrect: false,
                            enableSuggestions: false,
                            textCapitalization: TextCapitalization.characters,
                            style: const TextStyle(
                              color: AppColors.jclGray,
                              fontSize: 14,
                            ),
                            decoration: InputDecoration(
                              hintText: 'Title',
                              hintStyle: TextStyle(
                                color: AppColors.jclGray.withAlpha(128),
                                fontSize: 14,
                              ),
                              filled: true,
                              fillColor: AppColors.jclWhite,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(4),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 88),

                // Action Buttons Section
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 65),
                  child: Column(
                    children: [
                      // Update Facilities
                      _buildActionRow(
                        context,
                        label: 'Update Facilites',
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const SettingsUpdateScreen(
                                updateType: 'facilities',
                              ),
                            ),
                          );
                        },
                      ),
                      const SizedBox(height: 14),

                      // Update Surgeons/Doctors
                      _buildActionRow(
                        context,
                        label: 'Update Surgeons/Doctors',
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const SettingsUpdateScreen(
                                updateType: 'surgeons',
                              ),
                            ),
                          );
                        },
                      ),
                      const SizedBox(height: 14),

                      // Update Skill Set
                      _buildActionRow(
                        context,
                        label: 'Update Skill Set',
                        onPressed: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const SettingsUpdateScreen(
                                updateType: 'skills',
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 33),

                // Generate Report Button
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 65),
                  child: _buildActionRow(
                    context,
                    label: 'Generate Report',
                    onPressed: _generateReport,
                  ),
                ),

                const SizedBox(height: 100),
              ],
            ),
            ),
          ],
        ),
      ),
    );
  }

  void _generateReport() {
    // Dismiss keyboard
    FocusScope.of(context).unfocus();

    // Validate that all required fields are filled
    if (_firstNameController.text.trim().isEmpty ||
        _lastNameController.text.trim().isEmpty ||
        _titleController.text.trim().isEmpty) {
      _showNameRequiredAlert();
      return;
    }

    // Save user data to shared preferences (or user defaults)
    _saveUserData();

    // Navigate to generate report screen
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const GenerateReportScreen(),
      ),
    );
  }

  Future<void> _saveUserData() async {
    final firstName = _firstNameController.text.trim();
    final lastName = _lastNameController.text.trim();
    final title = _titleController.text.trim();

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('jclFirstName', firstName);
    await prefs.setString('jclLastName', lastName);
    await prefs.setString('jclUserTitle', title);
  }

  void _showNameRequiredAlert() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Missing Information'),
        content: const Text(
            'In order to use this feature, your full name & title are required.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  Widget _buildActionRow(BuildContext context,
      {required String label, required VoidCallback onPressed}) {
    return SizedBox(
      width: 263,
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: const TextStyle(
                color: AppColors.jclWhite,
                fontSize: 16,
              ),
              textAlign: TextAlign.right,
            ),
          ),
          const SizedBox(width: 8),
          SizedBox(
            width: 60,
            height: 35,
            child: ElevatedButton(
              onPressed: onPressed,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.jclOrange,
                foregroundColor: AppColors.jclGray,
                elevation: 0,
                padding: EdgeInsets.zero,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                  side: const BorderSide(
                    color: Colors.black,
                    width: 1.0,
                  ),
                ),
              ),
              child: const Text(''),
            ),
          ),
        ],
      ),
    );
  }

  void _showLogoutAlert() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to log out?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _performLogout();
            },
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }

  void _performLogout() async {
    final authNotifier = ref.read(authProvider.notifier);
    await authNotifier.logout();

    if (mounted) {
      // Navigate to login screen
      Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
    }
  }

  void _showDeleteAccountAlert() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sorry to see you go.'),
        content: const Text(
            'Click \'OK\' to start the account deletion process.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _openAccountDeletionUrl();
            },
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _openAccountDeletionUrl() async {
    // In a real implementation, you would use url_launcher package
    // For now, show a message
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
            'Account deletion: Please visit https://www.jerichocreations.com/contact-us/'),
        duration: Duration(seconds: 5),
      ),
    );
  }
}
