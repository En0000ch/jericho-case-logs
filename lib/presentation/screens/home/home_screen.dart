import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../domain/entities/case.dart';
import '../../../core/themes/app_colors.dart';
import '../cases/case_creation_flow_screen.dart';
import '../cases/case_detail_screen.dart';
import '../calendar/calendar_screen.dart';
import '../settings/settings_screen.dart';

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
  /// Only show onboarding for truly new users (caseCount == 0)
  /// This prevents onboarding from showing on app reinstall for existing users
  Future<void> _checkOnboardingStatus() async {
    final user = ref.read(currentUserProvider);

    // Check if user has ever created a case on the server
    // If caseCount > 0, they're an existing user who has used the app before
    if (user != null && user.caseCount == 0 && mounted) {
      final prefs = await SharedPreferences.getInstance();
      final hasSeenOnboarding = prefs.getBool('jcl_hasSeenOnboarding') ?? false;

      // Only show onboarding if they haven't seen it yet in this session
      if (!hasSeenOnboarding) {
        _showOnboardingAlert();
      }
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
    print('DEBUG HOME: _filterByToday called with ${allCases.length} cases');
    if (allCases.isNotEmpty) {
      print('DEBUG HOME: First case - objectId=${allCases.first.objectId}, surgery=${allCases.first.procedureSurgery}, date=${allCases.first.date}');
    }

    final now = DateTime.now();
    final twentyFourHoursAgo = now.subtract(const Duration(hours: 24));
    print('DEBUG HOME: Filtering for cases after $twentyFourHoursAgo');

    // Find cases entered within the last 24 hours
    final recentCases = allCases.where((caseItem) {
      final isRecent = caseItem.date.isAfter(twentyFourHoursAgo);
      print('DEBUG HOME: Case ${caseItem.objectId} date ${caseItem.date}, isRecent: $isRecent');
      return isRecent;
    }).toList();

    print('DEBUG HOME: Found ${recentCases.length} recent cases');
    print('DEBUG HOME: Will display ${recentCases.isEmpty ? allCases.length : recentCases.length} cases');

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
    final date = await showDatePicker(
      context: context,
      initialDate: _startDate ?? DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (date != null) {
      setState(() {
        _startDate = date;
        _updateDateFields();
      });
    }
  }

  Future<void> _selectEndDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _endDate ?? DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (date != null) {
      setState(() {
        _endDate = date;
        _updateDateFields();
      });
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
                              return ListTile(
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
                                    child: caseItem.imageName != null
                                        ? Image.asset(
                                            'assets/images/${caseItem.imageName}',
                                            fit: BoxFit.contain,
                                            errorBuilder: (context, error, stackTrace) {
                                              // Last resort - show a generic icon
                                              return const Icon(
                                                Icons.medical_services,
                                                color: AppColors.jclOrange,
                                                size: 24,
                                              );
                                            },
                                          )
                                        : const Icon(
                                            Icons.medical_services,
                                            color: AppColors.jclOrange,
                                            size: 24,
                                          ),
                                  ),
                                ),
                                title: Text(
                                  caseItem.procedureSurgery,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    color: AppColors.jclGray,
                                    fontSize: 15,
                                  ),
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const SizedBox(height: 4),
                                    Text(
                                      caseItem.anestheticPlan,
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
                  onPressed: _showFeatureComingSoonDialog,
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
                    children: const [
                      Icon(
                        Icons.hotel,
                        size: 28,
                        color: AppColors.jclGray,
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Lodging',
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

  void _showFeatureComingSoonDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclGray,
        title: const Text(
          'Feature Coming Soon',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        content: const Text(
          'The Lodging feature is currently under development and will be available in a future update.',
          style: TextStyle(color: AppColors.jclWhite),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text(
              'OK',
              style: TextStyle(color: AppColors.jclOrange),
            ),
          ),
        ],
      ),
    );
  }
}
