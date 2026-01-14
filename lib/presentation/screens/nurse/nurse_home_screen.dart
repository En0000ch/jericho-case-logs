import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:intl/intl.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/medical_units_data.dart';
import '../../../data/datasources/remote/parse_medical_unit_service.dart';
import '../../../data/datasources/remote/parse_patient_service.dart';
import '../../providers/auth_provider.dart';
import '../../providers/patient_timer_provider.dart';
import '../../providers/activity_tracker_provider.dart';
import '../../widgets/patient_carry_forward_dialog.dart';
import '../settings/settings_screen.dart';
import '../job_search/job_search_screen.dart';
import '../reports/generate_report_screen.dart';
import 'patient_creation_screen.dart';

/// Nurse silo home screen
/// Displays patient list with Jobs tab on bottom nav bar
class NurseHomeScreen extends ConsumerStatefulWidget {
  const NurseHomeScreen({super.key});

  @override
  ConsumerState<NurseHomeScreen> createState() => _NurseHomeScreenState();
}

class _NurseHomeScreenState extends ConsumerState<NurseHomeScreen>
    with WidgetsBindingObserver {
  final ParseMedicalUnitService _medicalUnitService = ParseMedicalUnitService();
  final ParsePatientService _patientService = ParsePatientService();
  String? _selectedMedicalUnit;
  bool _isLoadingUnit = true;
  List<ParseObject> _patients = [];
  bool _isLoadingPatients = true;

  final List<String> _ageCategories = [
    '',
    'Neonate',
    'Pediatric',
    'Adolescent',
    'Adult',
    'Elderly',
  ];

  String? _selectedGender; // Store selected gender

  // Timer-related state
  ActivityTrackerNotifier? _activityTracker;
  PatientTimerNotifier? _patientTimer;
  bool _timerDialogShown = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadMedicalUnit();
      _loadPatients();
      _initializeTimers();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _patientTimer?.stopMonitoring();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    super.didChangeAppLifecycleState(state);

    // Record activity when app is resumed
    if (state == AppLifecycleState.resumed) {
      _activityTracker?.recordActivity();
      print('📱 App resumed - activity recorded');
    }
  }

  /// Initialize activity tracker and patient timer
  Future<void> _initializeTimers() async {
    try {
      // Get activity tracker
      final activityTrackerAsync = ref.read(activityTrackerFutureProvider.future);
      _activityTracker = await activityTrackerAsync;

      // Get patient timer
      final patientTimerAsync = ref.read(patientTimerFutureProvider.future);
      _patientTimer = await patientTimerAsync;

      // Record initial activity
      await _activityTracker?.recordActivity();

      // Start monitoring
      _patientTimer?.startMonitoring();

      // Listen for timer triggers
      _checkTimerStatus();

      print('⏱️ Patient timer initialized and monitoring started');
    } catch (e) {
      print('❌ Error initializing timers: $e');
    }
  }

  /// Check if timer should show dialog
  void _checkTimerStatus() {
    // Check every few seconds if timer has triggered
    Future.delayed(const Duration(seconds: 5), () {
      if (!mounted) return;

      final timerState = _patientTimer?.currentState;
      if (timerState?.shouldShowModal == true && !_timerDialogShown) {
        _showPatientCarryForwardDialog();
      }

      // Continue checking
      _checkTimerStatus();
    });
  }

  /// Show the patient carry forward dialog
  Future<void> _showPatientCarryForwardDialog() async {
    if (_timerDialogShown) return;

    setState(() => _timerDialogShown = true);

    final user = ref.read(currentUserProvider);
    if (user == null) {
      setState(() => _timerDialogShown = false);
      return;
    }

    // Fetch patients from last 24 hours
    final patients = await _patientService.fetchPatientsFromLast24Hours(user.email);

    if (!mounted) {
      setState(() => _timerDialogShown = false);
      return;
    }

    // Show dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => PatientCarryForwardDialog(
        patients: patients,
        onConfirm: (patientIdsToRemove) async {
          await _handlePatientRemoval(patientIdsToRemove);
        },
        onCancel: () {
          _patientTimer?.reset();
          setState(() => _timerDialogShown = false);
        },
      ),
    );
  }

  /// Handle patient removal after dialog confirmation
  Future<void> _handlePatientRemoval(List<String> patientIdsToRemove) async {
    if (patientIdsToRemove.isEmpty) {
      // No patients to remove - just reset timer
      await _patientTimer?.reset();
      setState(() => _timerDialogShown = false);
      return;
    }

    // Show loading indicator
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Removing ${patientIdsToRemove.length} patient(s)...'),
          duration: const Duration(seconds: 2),
        ),
      );
    }

    // Remove patients
    final success = await _patientService.removePatients(patientIdsToRemove);

    if (success) {
      // Refresh patient list
      await _loadPatients();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Successfully removed ${patientIdsToRemove.length} patient(s)'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Error removing some patients'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }

    // Reset timer
    await _patientTimer?.reset();
    setState(() => _timerDialogShown = false);
  }

  Future<void> _loadPatients() async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    setState(() => _isLoadingPatients = true);

    try {
      final patients = await _patientService.fetchPatients(user.email);
      setState(() {
        _patients = patients;
        _isLoadingPatients = false;
      });
    } catch (e) {
      print('Error loading patients: $e');
      setState(() => _isLoadingPatients = false);
    }
  }

  Future<void> _loadMedicalUnit() async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    setState(() => _isLoadingUnit = true);

    try {
      final unit = await _medicalUnitService.fetchMedicalUnit(user.email);
      setState(() {
        _selectedMedicalUnit = unit;
        _isLoadingUnit = false;
      });

      // If no unit is selected, show the selection dialog
      if (unit == null && mounted) {
        _showMedicalUnitRequiredDialog();
      }
    } catch (e) {
      print('Error loading medical unit: $e');
      setState(() => _isLoadingUnit = false);
    }
  }

  void _showMedicalUnitRequiredDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Medical Unit Required',
            style: TextStyle(
              color: AppColors.jclGray,
              fontWeight: FontWeight.w600,
            ),
          ),
          content: const Text(
            'Please select your current medical unit to proceed.',
            style: TextStyle(
              color: AppColors.jclGray,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                _showMedicalUnitPicker();
              },
              child: const Text(
                'Select Unit',
                style: TextStyle(
                  color: AppColors.jclOrange,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showMedicalUnitPicker() {
    int selectedIndex = _selectedMedicalUnit != null
        ? MedicalUnitsData.allUnits.indexOf(_selectedMedicalUnit!)
        : 0;
    if (selectedIndex < 0) selectedIndex = 0;

    showCupertinoModalPopup(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              // Header with Done button
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.jclWhite,
                  border: Border(
                    bottom: BorderSide(
                      color: AppColors.jclGray.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Select Medical Unit',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: AppColors.jclGray,
                      ),
                    ),
                    CupertinoButton(
                      padding: EdgeInsets.zero,
                      onPressed: () async {
                        final user = ref.read(currentUserProvider);
                        if (user != null) {
                          final selectedUnit = MedicalUnitsData.allUnits[selectedIndex];
                          await _medicalUnitService.saveMedicalUnit(
                            user.email,
                            selectedUnit,
                          );
                          setState(() {
                            _selectedMedicalUnit = selectedUnit;
                          });
                        }
                        Navigator.of(context).pop();
                      },
                      child: const Text(
                        'Done',
                        style: TextStyle(
                          color: AppColors.jclOrange,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              // Picker
              Expanded(
                child: CupertinoPicker(
                  scrollController: FixedExtentScrollController(
                    initialItem: selectedIndex,
                  ),
                  itemExtent: 40,
                  onSelectedItemChanged: (int index) {
                    selectedIndex = index;
                  },
                  children: MedicalUnitsData.allUnits.map((unit) {
                    return Center(
                      child: Text(
                        unit,
                        style: const TextStyle(
                          fontSize: 16,
                          color: AppColors.jclGray,
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  /// Show confirmation dialog before adding a new patient
  void _showAgeSelectionPicker() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Add New Patient?',
            style: TextStyle(
              color: AppColors.jclGray,
              fontWeight: FontWeight.w600,
            ),
          ),
          content: const Text(
            'Would you like to add a new patient?',
            style: TextStyle(
              color: AppColors.jclGray,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Dismiss dialog
              },
              child: const Text(
                'No',
                style: TextStyle(
                  color: AppColors.jclGray,
                  fontSize: 16,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Dismiss dialog
                _showGenderSelectionDialog(); // Show gender selection
              },
              child: const Text(
                'Yes',
                style: TextStyle(
                  color: AppColors.jclOrange,
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  /// Show gender selection dialog
  void _showGenderSelectionDialog() {
    String? tempGender; // Temporary storage for gender selection in dialog

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext dialogContext) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              backgroundColor: AppColors.jclWhite,
              title: const Text(
                'Select Patient Gender',
                style: TextStyle(
                  color: AppColors.jclGray,
                  fontWeight: FontWeight.w600,
                  fontSize: 18,
                ),
              ),
              content: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Male Button
                  GestureDetector(
                    onTap: () {
                      setState(() => tempGender = 'Male');
                    },
                    child: Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                        color: tempGender == 'Male' ? Colors.blue : Colors.blue.shade100,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: tempGender == 'Male' ? Colors.blue.shade700 : Colors.blue.shade300,
                          width: tempGender == 'Male' ? 2 : 1,
                        ),
                        boxShadow: tempGender == 'Male' ? [
                          BoxShadow(
                            color: Colors.blue.withOpacity(0.5),
                            blurRadius: 12,
                            spreadRadius: 3,
                          ),
                        ] : null,
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.male,
                            size: 40,
                            color: tempGender == 'Male' ? AppColors.jclWhite : Colors.blue.shade700,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Male',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: tempGender == 'Male' ? AppColors.jclWhite : Colors.blue.shade700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 20),
                  // Female Button
                  GestureDetector(
                    onTap: () {
                      setState(() => tempGender = 'Female');
                    },
                    child: Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                        color: tempGender == 'Female' ? Colors.pink : Colors.pink.shade100,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: tempGender == 'Female' ? Colors.pink.shade700 : Colors.pink.shade300,
                          width: tempGender == 'Female' ? 2 : 1,
                        ),
                        boxShadow: tempGender == 'Female' ? [
                          BoxShadow(
                            color: Colors.pink.withOpacity(0.5),
                            blurRadius: 12,
                            spreadRadius: 3,
                          ),
                        ] : null,
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.female,
                            size: 40,
                            color: tempGender == 'Female' ? AppColors.jclWhite : Colors.pink.shade700,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Female',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: tempGender == 'Female' ? AppColors.jclWhite : Colors.pink.shade700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(dialogContext).pop(); // Cancel - go back
                  },
                  child: const Text(
                    'Cancel',
                    style: TextStyle(
                      color: AppColors.jclGray,
                      fontSize: 16,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: () {
                    if (tempGender != null) {
                      this.setState(() {
                        _selectedGender = tempGender;
                      });
                      Navigator.of(dialogContext).pop();
                      _showAgeCategoryPicker(); // Proceed to age selection
                    } else {
                      // Show error if no gender selected
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Please select a gender'),
                          backgroundColor: AppColors.jclOrange,
                          duration: Duration(seconds: 2),
                        ),
                      );
                    }
                  },
                  child: const Text(
                    'Next',
                    style: TextStyle(
                      color: AppColors.jclOrange,
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  /// Show age category selection picker wheel
  void _showAgeCategoryPicker() {
    int selectedIndex = 0; // Default to blank

    showCupertinoModalPopup(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              // Header with Done button
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: AppColors.jclOrange,
                  border: Border(
                    bottom: BorderSide(
                      color: AppColors.jclGray.withOpacity(0.2),
                      width: 1,
                    ),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    CupertinoButton(
                      padding: EdgeInsets.zero,
                      child: const Icon(
                        Icons.close,
                        color: AppColors.jclWhite,
                        size: 28,
                      ),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                    const Text(
                      'Select Patient Age',
                      style: TextStyle(
                        color: AppColors.jclWhite,
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    CupertinoButton(
                      padding: EdgeInsets.zero,
                      child: const Icon(
                        Icons.check,
                        color: AppColors.jclWhite,
                        size: 28,
                      ),
                      onPressed: () {
                        final selectedCategory = _ageCategories[selectedIndex];

                        // Validate that user selected an actual age range
                        if (selectedCategory.isEmpty) {
                          // Show alert - don't dismiss picker
                          showDialog(
                            context: context,
                            builder: (BuildContext dialogContext) {
                              return AlertDialog(
                                backgroundColor: AppColors.jclWhite,
                                title: const Text(
                                  'Age Range Required',
                                  style: TextStyle(
                                    color: AppColors.jclGray,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                content: const Text(
                                  'Please select patient age range.',
                                  style: TextStyle(
                                    color: AppColors.jclGray,
                                  ),
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.of(dialogContext).pop(),
                                    child: const Text(
                                      'OK',
                                      style: TextStyle(
                                        color: AppColors.jclOrange,
                                        fontSize: 16,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              );
                            },
                          );
                        } else {
                          // Valid selection - proceed
                          Navigator.of(context).pop();
                          _navigateToPatientCreation(selectedCategory);
                        }
                      },
                    ),
                  ],
                ),
              ),
              // Picker
              Expanded(
                child: CupertinoPicker(
                  backgroundColor: AppColors.jclWhite,
                  itemExtent: 40,
                  scrollController: FixedExtentScrollController(initialItem: 0),
                  onSelectedItemChanged: (int index) {
                    selectedIndex = index;
                  },
                  children: _ageCategories.map((category) {
                    return Center(
                      child: Semantics(
                        label: category.isEmpty ? 'Not specified' : category,
                        child: Text(
                          category.isEmpty ? ' ' : category, // Use space for blank
                          style: const TextStyle(
                            fontSize: 18,
                            color: AppColors.jclGray,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  /// Navigate to patient creation screen with selected gender and age category
  void _navigateToPatientCreation(String ageCategory) async {
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => PatientCreationScreen(
          ageCategory: ageCategory,
          gender: _selectedGender ?? 'Not specified',
          medicalUnit: _selectedMedicalUnit ?? 'Not specified',
        ),
      ),
    );
    // Reload patients after returning from patient creation
    _loadPatients();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Jericho Patient Log',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclOrange,
        elevation: 0,
        leading: IconButton(
          icon: Image.asset(
            'assets/images/dashboard-30.png',
            width: 24,
            height: 24,
            color: AppColors.jclWhite,
          ),
          onPressed: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => const SettingsScreen(),
              ),
            );
          },
          tooltip: 'Dashboard / Settings',
        ),
        actions: [
          IconButton(
            icon: Image.asset(
              'assets/images/add-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            onPressed: _showAgeSelectionPicker,
            tooltip: 'Add New Patient',
          ),
        ],
      ),
      body: GestureDetector(
        onTap: () {
          // Record activity on tap
          _activityTracker?.recordActivity();
        },
        onPanUpdate: (_) {
          // Record activity on scroll/drag
          _activityTracker?.recordActivity();
        },
        child: Column(
          children: [
            // Patient list
            Expanded(
              child: _buildPatientList(),
            ),
            // Medical unit button above tab bar
            _buildMedicalUnitButton(),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomNavigationBar(),
    );
  }

  /// Build patient list view (home)
  Widget _buildPatientList() {
    if (_isLoadingPatients) {
      return const Center(
        child: CircularProgressIndicator(
          color: AppColors.jclOrange,
        ),
      );
    }

    if (_patients.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 80,
              color: AppColors.jclGray.withOpacity(0.3),
            ),
            const SizedBox(height: 16),
            const Text(
              'No patients added',
              style: TextStyle(
                fontSize: 18,
                color: AppColors.jclGray,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Tap + to add your first patient',
              style: TextStyle(
                fontSize: 14,
                color: AppColors.jclOrange,
              ),
            ),
          ],
        ),
      );
    }

    // Group patients by date
    final patientsByDate = _groupPatientsByDate(_patients);

    return ListView.builder(
      itemCount: patientsByDate.length,
      itemBuilder: (context, index) {
        final dateEntry = patientsByDate[index];
        final date = dateEntry['date'] as String;
        final patients = dateEntry['patients'] as List<ParseObject>;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Date section header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: AppColors.jclGray.withOpacity(0.1),
              child: Text(
                date,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.jclGray,
                ),
              ),
            ),
            // Patient cells
            ...patients.map((patient) => _buildPatientCell(patient)),
          ],
        );
      },
    );
  }

  /// Group patients by date
  List<Map<String, dynamic>> _groupPatientsByDate(List<ParseObject> patients) {
    final Map<String, List<ParseObject>> grouped = {};

    for (var patient in patients) {
      final createdAt = patient.get<DateTime>('createdAt');
      if (createdAt != null) {
        final dateKey = DateFormat('MMMM d, yyyy').format(createdAt);
        grouped.putIfAbsent(dateKey, () => []);
        grouped[dateKey]!.add(patient);
      }
    }

    // Convert to list and maintain order (most recent first)
    return grouped.entries.map((entry) {
      return {
        'date': entry.key,
        'patients': entry.value,
      };
    }).toList();
  }

  /// Build individual patient cell
  Widget _buildPatientCell(ParseObject patient) {
    final gender = patient.get<String>('gender') ?? 'Unknown';
    final ageRange = patient.get<String>('ageRange') ?? ''; // NEW: Get age range
    final medicalUnit = patient.get<String>('medicalUnit') ?? 'N/A';
    final skills = patient.get<List<dynamic>>('skills')?.cast<String>() ?? [];
    final skillsText = skills.join(', ');

    // Determine profile icon color based on gender
    Color iconColor;
    IconData iconData;
    if (gender.toLowerCase() == 'male') {
      iconColor = Colors.blue;
      iconData = Icons.account_circle;
    } else if (gender.toLowerCase() == 'female') {
      iconColor = const Color(0xFFFFB6D3); // Custom pink #ffb6d3
      iconData = Icons.account_circle;
    } else {
      iconColor = AppColors.jclGray;
      iconData = Icons.account_circle;
    }

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.jclWhite,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Profile icon with age range (far left)
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  iconData,
                  size: 40,
                  color: iconColor,
                ),
                if (ageRange.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    ageRange,
                    style: TextStyle(
                      fontSize: 9,
                      color: AppColors.jclGray.withOpacity(0.7),
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(width: 12),
            // Skills (center)
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    skillsText.isNotEmpty ? skillsText : 'No skills listed',
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.jclOrange,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            // Medical unit (far right)
            SizedBox(
              width: 80,
              child: Text(
                medicalUnit,
                style: TextStyle(
                  fontSize: 10,
                  color: AppColors.jclGray.withOpacity(0.8),
                  fontWeight: FontWeight.w400,
                ),
                textAlign: TextAlign.right,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Build medical unit button that sits above the tab bar
  Widget _buildMedicalUnitButton() {
    return InkWell(
      onTap: _isLoadingUnit ? null : _showMedicalUnitPicker,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.jclGray.withOpacity(0.5),
          border: Border(
            top: BorderSide(
              color: AppColors.jclGray.withOpacity(0.3),
              width: 1,
            ),
          ),
        ),
        child: _isLoadingUnit
            ? const Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(AppColors.jclWhite),
                  ),
                ),
              )
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.location_on_outlined,
                    color: AppColors.jclWhite,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _selectedMedicalUnit ?? 'Select Medical Unit',
                    style: const TextStyle(
                      color: AppColors.jclWhite,
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Icon(
                    Icons.edit_outlined,
                    color: AppColors.jclWhite,
                    size: 18,
                  ),
                ],
              ),
      ),
    );
  }

  /// Build bottom navigation bar with Reports (far left) and Jobs (far right)
  Widget _buildBottomNavigationBar() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.jclWhite,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Reports button on far left
              TextButton(
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => GenerateReportScreen(),
                    ),
                  );
                },
                style: TextButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  foregroundColor: AppColors.jclGray,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Image.asset(
                      'assets/images/report-30.png',
                      width: 28,
                      height: 28,
                      color: AppColors.jclGray,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(
                          Icons.assessment,
                          size: 28,
                          color: AppColors.jclGray,
                        );
                      },
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Reports',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.jclGray,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              // Jobs button on far right
              TextButton(
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const JobSearchScreen(),
                    ),
                  );
                },
                style: TextButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  foregroundColor: AppColors.jclGray,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Image.asset(
                      'assets/images/opportunity-30.png',
                      width: 28,
                      height: 28,
                      color: AppColors.jclGray,
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Jobs',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.jclGray,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
