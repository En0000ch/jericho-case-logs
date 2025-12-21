import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../domain/entities/case.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/utils/surgery_image_helper.dart';
import '../cases/case_creation_flow_screen.dart';
import '../cases/case_form_wizard.dart';
import '../cases/case_detail_screen.dart';
import '../cases/case_edit_screen.dart';
import '../calendar/calendar_screen.dart';
import '../settings/settings_screen.dart';
import '../job_search/job_search_screen.dart';
import '../../widgets/marquee_text.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final _startDateController = TextEditingController();
  final _endDateController = TextEditingController();
  DateTime? _startDate;
  DateTime? _endDate;
  List<Case> _displayedCases = [];
  bool _isFiltering = false;
  bool _hasInitialized = false;

  @override
  void initState() {
    super.initState();
    // Cases are loaded automatically by CaseListNotifier constructor
    // Check onboarding status after first frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkOnboardingStatus();
    });
  }

  /// Check if user needs onboarding
  /// Only show onboarding for truly new users (users with 0 cases in database)
  /// This prevents onboarding from showing for existing users even after reinstall
  Future<void> _checkOnboardingStatus() async {
    final user = ref.read(currentUserProvider);
    if (user == null || !mounted) return;

    try {
      // Query Parse Server to count actual cases for this user
      final query = QueryBuilder<ParseObject>(ParseObject('jclCases'))
        ..whereEqualTo('userEmail', user.email)
        ..setLimit(1); // We only need to know if ANY cases exist

      final response = await query.count();

      // Only show onboarding if user has NO cases in the database
      if (response.success && response.count == 0 && mounted) {
        final prefs = await SharedPreferences.getInstance();
        final hasSeenOnboarding = prefs.getBool('jcl_hasSeenOnboarding') ?? false;

        // Only show onboarding if they haven't seen it yet in this session
        if (!hasSeenOnboarding) {
          _showOnboardingAlert();
        }
      }
    } catch (e) {
      print('Error checking onboarding status: $e');
      // On error, don't show onboarding (fail gracefully)
    }
  }

  /// iOS onBoardingAlert equivalent
  void _showOnboardingAlert() {
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
          'In order to make your experience more engaging, please answer the following questions.',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _showAddFacilityAlert();
            },
            child: const Text(
              'OK',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }

  /// iOS addLocationAlert equivalent
  void _showAddFacilityAlert([String? existingText]) {
    final controller = TextEditingController(text: existingText);

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclGray,
        title: const Text(
          'Facility/Office Name',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Please enter the name of the facility you work in most often or most recently',
              style: TextStyle(color: AppColors.jclWhite),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              autofocus: true,
              style: const TextStyle(color: AppColors.jclGray),
              decoration: const InputDecoration(
                hintText: 'Facility Name',
                filled: true,
                fillColor: AppColors.jclWhite,
              ),
              textCapitalization: TextCapitalization.words,
              autocorrect: false,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () async {
              if (controller.text.trim().isNotEmpty) {
                await _saveFacility(controller.text.trim());
                if (mounted) {
                  Navigator.of(context).pop();
                  _showAddFacilityAlert(); // Show again to add another
                }
              }
            },
            child: const Text(
              'Add Another Facility',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
          TextButton(
            onPressed: () async {
              if (controller.text.trim().isNotEmpty) {
                await _saveFacility(controller.text.trim());
              }
              if (mounted) {
                Navigator.of(context).pop();
                _showAddSurgeonAlert();
              }
            },
            child: const Text(
              'Next Step',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }

  /// iOS addDoctorAlert equivalent
  void _showAddSurgeonAlert([String? existingText]) {
    final controller = TextEditingController(text: existingText);

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclGray,
        title: const Text(
          'Add Surgeon',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Please enter the name of the surgeon you work with most often or most recently.',
              style: TextStyle(color: AppColors.jclWhite),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              autofocus: true,
              style: const TextStyle(color: AppColors.jclGray),
              decoration: const InputDecoration(
                hintText: 'Surgeon Name',
                filled: true,
                fillColor: AppColors.jclWhite,
              ),
              textCapitalization: TextCapitalization.words,
              autocorrect: false,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () async {
              if (controller.text.trim().isNotEmpty) {
                await _saveSurgeon(controller.text.trim());
                if (mounted) {
                  Navigator.of(context).pop();
                  _showAddSurgeonAlert(); // Show again to add another
                }
              }
            },
            child: const Text(
              'Add Another Surgeon',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
          TextButton(
            onPressed: () async {
              if (controller.text.trim().isNotEmpty) {
                await _saveSurgeon(controller.text.trim());
              }
              if (mounted) {
                Navigator.of(context).pop();
                _showNewUserTourAlert();
              }
            },
            child: const Text(
              'Next Step',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }

  /// iOS newUserTourAlert equivalent
  void _showNewUserTourAlert() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclGray,
        title: const Text(
          "Let's get started!",
          style: TextStyle(color: AppColors.jclWhite),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              "To enter your first case, click on the 'OK' button.",
              style: TextStyle(color: AppColors.jclWhite),
            ),
            const SizedBox(height: 16),
            Image.asset(
              'assets/images/addItem-96.png',
              width: 30,
              height: 30,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () async {
              // Mark that user has seen onboarding in this session
              final prefs = await SharedPreferences.getInstance();
              await prefs.setBool('jcl_hasSeenOnboarding', true);

              if (mounted) {
                Navigator.of(context).pop();
                // Open case creation screen and reload cases when returning
                await Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => const CaseCreationFlowScreen(),
                  ),
                );
                // Reload cases after returning from case creation
                if (mounted) {
                  _loadCases();
                }
              }
            },
            child: const Text(
              'OK',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }

  /// Save facility to SharedPreferences and Parse
  Future<void> _saveFacility(String facilityName) async {
    final prefs = await SharedPreferences.getInstance();
    final facilities = prefs.getStringList('jclFacilityArray') ?? [];
    if (!facilities.contains(facilityName)) {
      facilities.add(facilityName);
      await prefs.setStringList('jclFacilityArray', facilities);
    }
  }

  /// Save surgeon to SharedPreferences and Parse
  Future<void> _saveSurgeon(String surgeonName) async {
    final prefs = await SharedPreferences.getInstance();
    final surgeons = prefs.getStringList('jclSurgeonArray') ?? [];
    if (!surgeons.contains(surgeonName)) {
      surgeons.add(surgeonName);
      await prefs.setStringList('jclSurgeonArray', surgeons);
    }
  }

  @override
  void dispose() {
    _startDateController.dispose();
    _endDateController.dispose();
    super.dispose();
  }

  void _loadCases() {
    final user = ref.read(currentUserProvider);
    if (user != null) {
      setState(() {
        _hasInitialized = false;
      });
      ref.read(caseListProvider(user.email).notifier).loadCases();
    }
  }

  void _updateDateFields() {
    if (_startDate != null) {
      _startDateController.text =
          '${_startDate!.month}/${_startDate!.day}/${_startDate!.year}';
    }
    if (_endDate != null) {
      _endDateController.text =
          '${_endDate!.month}/${_endDate!.day}/${_endDate!.year}';
    }
  }

  void _filterByToday(List<Case> allCases) {
    print('');
    print('═══════════════════════════════════════════════════════════');
    print('DEBUG HOME: _filterByToday called');
    print('DEBUG HOME: Total cases received: ${allCases.length}');
    print('═══════════════════════════════════════════════════════════');

    if (allCases.isEmpty) {
      print('⚠️  WARNING: No cases available to display!');
      setState(() {
        _displayedCases = [];
        _isFiltering = false;
      });
      return;
    }

    if (allCases.isNotEmpty) {
      print('DEBUG HOME: First case details:');
      print('  - ObjectId: ${allCases.first.objectId}');
      print('  - Surgery: ${allCases.first.procedureSurgery}');
      print('  - Surgery Date: ${allCases.first.date}');
      print('  - Created At: ${allCases.first.createdAt}');
    }

    final now = DateTime.now();
    final twentyFourHoursAgo = now.subtract(const Duration(hours: 24));
    print('');
    print('Time Reference:');
    print('  - Current time: $now');
    print('  - 24 hours ago: $twentyFourHoursAgo');
    print('');
    print('Filtering Logic: Looking for cases where createdAt > $twentyFourHoursAgo');
    print('───────────────────────────────────────────────────────────');

    // Find cases entered within the last 24 hours
    final recentCases = allCases.where((caseItem) {
      final isRecent = caseItem.createdAt.isAfter(twentyFourHoursAgo);
      print('Case Check:');
      print('  - Surgery: ${caseItem.procedureSurgery}');
      print('  - Surgery Date: ${caseItem.date}');
      print('  - Created At: ${caseItem.createdAt}');
      print('  - Is Recent? $isRecent');
      print('  ---');
      return isRecent;
    }).toList();

    print('───────────────────────────────────────────────────────────');
    print('Filter Results:');
    print('  - Recent cases (last 24h): ${recentCases.length}');
    print('  - Total cases available: ${allCases.length}');
    print('  - Will display: ${recentCases.isEmpty ? "ALL ${allCases.length} CASES" : "ONLY ${recentCases.length} RECENT CASES"}');
    print('═══════════════════════════════════════════════════════════');
    print('');

    // If any cases in last 24 hours, show only those. Otherwise show ALL cases.
    // Sort by date descending (newest first)
    final casesToDisplay = recentCases.isEmpty ? allCases : recentCases;
    casesToDisplay.sort((a, b) => b.date.compareTo(a.date));

    setState(() {
      _displayedCases = casesToDisplay;
      _isFiltering = false;
      print('DEBUG HOME: _displayedCases set to ${_displayedCases.length} cases');
      if (_displayedCases.isNotEmpty) {
        print('DEBUG HOME: First displayed case - ${_displayedCases.first.procedureSurgery}, date=${_displayedCases.first.date}');
      }
    });
  }

  void _filterByDateRange(List<Case> allCases) {
    if (_startDate == null || _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select both start and end dates'),
        ),
      );
      return;
    }

    final start = DateTime(_startDate!.year, _startDate!.month, _startDate!.day);
    final end = DateTime(_endDate!.year, _endDate!.month, _endDate!.day, 23, 59, 59);

    final filteredCases = allCases.where((caseItem) {
      return caseItem.date.isAfter(start.subtract(const Duration(seconds: 1))) &&
          caseItem.date.isBefore(end.add(const Duration(seconds: 1)));
    }).toList();

    // Sort by date descending (newest first)
    filteredCases.sort((a, b) => b.date.compareTo(a.date));

    setState(() {
      _displayedCases = filteredCases;
      _isFiltering = true;
    });
  }

  Future<void> _selectStartDate() async {
    DateTime? selectedDate = _startDate ?? DateTime.now();

    await showCupertinoModalPopup(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  CupertinoButton(
                    child: const Text('Cancel'),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  CupertinoButton(
                    child: const Text('Done'),
                    onPressed: () {
                      setState(() {
                        _startDate = selectedDate;
                        _updateDateFields();
                      });
                      Navigator.of(context).pop();
                    },
                  ),
                ],
              ),
              Expanded(
                child: CupertinoDatePicker(
                  mode: CupertinoDatePickerMode.date,
                  initialDateTime: selectedDate,
                  minimumDate: DateTime(2000),
                  maximumDate: DateTime(2100),
                  onDateTimeChanged: (DateTime newDate) {
                    selectedDate = newDate;
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _selectEndDate() async {
    DateTime? selectedDate = _endDate ?? DateTime.now();

    await showCupertinoModalPopup(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  CupertinoButton(
                    child: const Text('Cancel'),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  CupertinoButton(
                    child: const Text('Done'),
                    onPressed: () {
                      setState(() {
                        _endDate = selectedDate;
                        _updateDateFields();
                      });
                      Navigator.of(context).pop();
                    },
                  ),
                ],
              ),
              Expanded(
                child: CupertinoDatePicker(
                  mode: CupertinoDatePickerMode.date,
                  initialDateTime: selectedDate,
                  minimumDate: DateTime(2000),
                  maximumDate: DateTime(2100),
                  onDateTimeChanged: (DateTime newDate) {
                    selectedDate = newDate;
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  /// Handle duplicate case action
  Future<void> _handleDuplicate(Case caseItem) async {
    // Show confirmation dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Duplicate Case?'),
        content: Text(
          'Are you sure you want to duplicate this case?\n\n${caseItem.procedureSurgery}',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(
              foregroundColor: Colors.blue,
            ),
            child: const Text('Duplicate'),
          ),
        ],
      ),
    );

    // Only proceed if confirmed
    if (confirmed != true || !mounted) return;

    final user = ref.read(currentUserProvider);
    if (user == null) return;

    final notifier = ref.read(caseDetailProvider.notifier);

    final success = await notifier.createCase(
      userEmail: user.email,
      date: caseItem.date,
      patientAge: caseItem.patientAge?.toString(),
      gender: caseItem.gender,
      asaClassification: caseItem.asaClassification,
      procedureSurgery: caseItem.procedureSurgery,
      anestheticPlan: caseItem.anestheticPlan,
      anestheticsUsed: caseItem.anestheticsUsed,
      surgeryClass: caseItem.surgeryClass,
      location: caseItem.location,
      airwayManagement: caseItem.airwayManagement,
      additionalComments: caseItem.additionalComments,
      complications: caseItem.complications,
      imageName: caseItem.imageName,
    );

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Case duplicated successfully'),
            backgroundColor: Colors.green,
          ),
        );
        _loadCases(); // Refresh the list
      } else {
        final state = ref.read(caseDetailProvider);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.error ?? 'Failed to duplicate case'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  /// Handle edit case action
  void _handleEdit(Case caseItem) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => CaseEditScreen(caseToEdit: caseItem),
      ),
    ).then((_) => _loadCases());
  }

  /// Handle delete case action
  Future<void> _handleDelete(Case caseItem) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Case?'),
        content: Text(
          'Are you sure you want to delete this case?\n\n${caseItem.procedureSurgery}',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final user = ref.read(currentUserProvider);
      if (user == null) return;

      final listNotifier = ref.read(caseListProvider(user.email).notifier);
      final success = await listNotifier.deleteCase(caseItem.objectId);

      if (mounted) {
        if (success) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Case deleted successfully'),
              backgroundColor: Colors.green,
            ),
          );
          _loadCases(); // Refresh the list
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Failed to delete case'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(currentUserProvider);

    if (user == null) {
      return const Scaffold(
        body: Center(
          child: Text('Please log in'),
        ),
      );
    }

    print('DEBUG BUILD: Watching provider for email=${user.email}');
    final caseListState = ref.watch(caseListProvider(user.email));

    print('DEBUG BUILD: isLoading=${caseListState.isLoading}, _isFiltering=$_isFiltering, _hasInitialized=$_hasInitialized, cases.length=${caseListState.cases.length}, _displayedCases.length=${_displayedCases.length}, user.email=${user.email}');

    // Update displayed cases when cases are first loaded
    if (!caseListState.isLoading && !_isFiltering && !_hasInitialized && caseListState.cases.isNotEmpty) {
      print('DEBUG: Scheduling _filterByToday');
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!_hasInitialized) {
          print('DEBUG: Calling _filterByToday from callback');
          _hasInitialized = true;
          _filterByToday(caseListState.cases);
        }
      });
    }

    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Jericho Case Logs',
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
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const CaseCreationFlowScreen(),
                ),
              ).then((_) => _loadCases());
            },
            tooltip: 'Add New Case',
          ),
        ],
      ),
      body: Column(
        children: [
          // Date Filter Section
          Container(
            color: AppColors.jclWhite,
            padding: const EdgeInsets.fromLTRB(29, 34, 29, 8),
            child: Column(
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Start Date Field
                    Expanded(
                      child: SizedBox(
                        height: 34,
                        child: TextField(
                          controller: _startDateController,
                          readOnly: true,
                          onTap: _selectStartDate,
                          style: const TextStyle(
                            color: AppColors.jclGray,
                            fontSize: 14,
                          ),
                          decoration: InputDecoration(
                            hintText: 'Start Date',
                            filled: true,
                            fillColor: AppColors.jclWhite,
                            isDense: true,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: BorderSide(color: AppColors.jclGray.withAlpha(128), width: 1),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: BorderSide(color: AppColors.jclGray.withAlpha(128), width: 1),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: const BorderSide(color: AppColors.jclGray, width: 1.5),
                            ),
                            suffixIcon: IconButton(
                              icon: const Icon(
                                Icons.clear,
                                color: AppColors.textSecondary,
                                size: 20,
                              ),
                              onPressed: () {
                                setState(() {
                                  _startDate = null;
                                  _startDateController.clear();
                                });
                              },
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 26),
                    // Calendar Button
                    Padding(
                      padding: const EdgeInsets.only(top: 14),
                      child: Container(
                        width: 74,
                        height: 50,
                        decoration: BoxDecoration(
                          color: AppColors.jclWhite.withAlpha((255 * 0.6).round()),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: IconButton(
                          icon: Image.asset(
                            'assets/images/calendar-50.png',
                            width: 40,
                            height: 40,
                          ),
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => const CalendarScreen(),
                              ),
                            ).then((_) => _loadCases());
                          },
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 3),
                // End Date Field
                Row(
                  children: [
                    Expanded(
                      child: SizedBox(
                        height: 34,
                        child: TextField(
                          controller: _endDateController,
                          readOnly: true,
                          onTap: _selectEndDate,
                          style: const TextStyle(
                            color: AppColors.jclGray,
                            fontSize: 14,
                          ),
                          decoration: InputDecoration(
                            hintText: 'End Date',
                            filled: true,
                            fillColor: AppColors.jclWhite,
                            isDense: true,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: BorderSide(color: AppColors.jclGray.withAlpha(128), width: 1),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: BorderSide(color: AppColors.jclGray.withAlpha(128), width: 1),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(4),
                              borderSide: const BorderSide(color: AppColors.jclGray, width: 1.5),
                            ),
                            suffixIcon: IconButton(
                              icon: const Icon(
                                Icons.clear,
                                color: AppColors.textSecondary,
                                size: 20,
                              ),
                              onPressed: () {
                                setState(() {
                                  _endDate = null;
                                  _endDateController.clear();
                                });
                              },
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 100), // Space for calendar button
                  ],
                ),
                const SizedBox(height: 8),
                // Search Button
                Center(
                  child: SizedBox(
                    height: 25,
                    width: 100,
                    child: ElevatedButton(
                      onPressed: () => _filterByDateRange(caseListState.cases),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        foregroundColor: AppColors.jclOrange,
                        padding: EdgeInsets.zero,
                        side: BorderSide.none,
                        elevation: 0,
                      ),
                      child: const Text(
                        'Search',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 25),
              ],
            ),
          ),

          // Cases List
          Expanded(
            child: Container(
              color: AppColors.jclWhite,
              child: caseListState.isLoading
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: AppColors.jclOrange,
                      ),
                    )
                  : _displayedCases.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.folder_open,
                                size: 64,
                                color: AppColors.jclGray.withAlpha((255 * 0.3).round()),
                              ),
                              const SizedBox(height: 16),
                              const Text(
                                'No cases found',
                                style: TextStyle(
                                  color: AppColors.jclGray,
                                  fontSize: 18,
                                ),
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'Tap + to add your first case',
                                style: TextStyle(
                                  color: AppColors.jclOrange,
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: () async => _loadCases(),
                          color: AppColors.jclOrange,
                          child: ListView.separated(
                            padding: const EdgeInsets.all(0),
                            itemCount: _displayedCases.length,
                            separatorBuilder: (context, index) => const Divider(
                              height: 1,
                              color: AppColors.jclGray,
                              thickness: 0.5,
                            ),
                            itemBuilder: (context, index) {
                              final caseItem = _displayedCases[index];
                              return Slidable(
                                key: ValueKey(caseItem.objectId),
                                endActionPane: ActionPane(
                                  motion: const StretchMotion(),
                                  children: [
                                    SlidableAction(
                                      onPressed: (_) => _handleDuplicate(caseItem),
                                      backgroundColor: Colors.blue,
                                      foregroundColor: Colors.white,
                                      icon: Icons.content_copy,
                                      label: 'Duplicate',
                                    ),
                                    SlidableAction(
                                      onPressed: (_) => _handleEdit(caseItem),
                                      backgroundColor: AppColors.jclOrange,
                                      foregroundColor: Colors.white,
                                      icon: Icons.edit,
                                      label: 'Edit',
                                    ),
                                    SlidableAction(
                                      onPressed: (_) => _handleDelete(caseItem),
                                      backgroundColor: Colors.red,
                                      foregroundColor: Colors.white,
                                      icon: Icons.delete,
                                      label: 'Delete',
                                    ),
                                  ],
                                ),
                                child: ListTile(
                                  tileColor: AppColors.jclWhite,
                                  contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 16,
                                    vertical: 8,
                                  ),
                                  leading: Container(
                                    width: 40,
                                    height: 40,
                                    decoration: const BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                    ),
                                    child: Padding(
                                      padding: const EdgeInsets.all(6.0),
                                      child: Image.asset(
                                        SurgeryImageHelper.getAssetPath(caseItem.imageName, surgeryClass: caseItem.surgeryClass),
                                        fit: BoxFit.contain,
                                        errorBuilder: (context, error, stackTrace) {
                                          return const Icon(
                                            Icons.medical_services,
                                            color: AppColors.jclOrange,
                                            size: 24,
                                          );
                                        },
                                      ),
                                    ),
                                  ),
                                  title: MarqueeText(
                                    caseItem.procedureSurgery,
                                    maxLines: 1,
                                    scrollSpeed: 30,
                                    pauseInterval: 1.5,
                                    labelSpacing: 30,
                                    style: const TextStyle(
                                      color: AppColors.jclGray,
                                      fontSize: 15,
                                    ),
                                  ),
                                  subtitle: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const SizedBox(height: 4),
                                      MarqueeText(
                                        caseItem.anestheticPlan,
                                        maxLines: 1,
                                        scrollSpeed: 24,
                                        pauseInterval: 1.8,
                                        labelSpacing: 30,
                                        style: TextStyle(
                                          color: AppColors.jclGray.withAlpha((255 * 0.7).round()),
                                          fontSize: 13,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        DateFormat.yMMMd().format(caseItem.date),
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: AppColors.jclGray.withAlpha((255 * 0.5).round()),
                                        ),
                                      ),
                                    ],
                                  ),
                                  trailing: const Icon(
                                    Icons.chevron_right,
                                    color: AppColors.jclOrange,
                                  ),
                                  onTap: () {
                                    Navigator.of(context).push(
                                      MaterialPageRoute(
                                        builder: (_) => CaseDetailScreen(
                                          caseId: caseItem.objectId,
                                        ),
                                      ),
                                    ).then((_) => _loadCases());
                                  },
                                ),
                              );
                            },
                          ),
                        ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
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
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
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
                        'assets/images/nurse-32.png',
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
      ),
    );
  }

}
