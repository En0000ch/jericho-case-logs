import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../providers/auth_provider.dart';
import '../../providers/case_provider.dart';
import '../../providers/facility_provider.dart';
import '../../providers/surgeon_provider.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/surgery_data.dart';
import '../../../core/data/complications_data.dart';
import '../../../core/data/comorbidities_data.dart';
import '../../../data/models/case_model.dart';
import '../settings/settings_update_screen.dart';
import 'multi_selection_screen.dart';
import 'general_anesthetic_selection_screen.dart';
import 'regional_anesthetic_selection_screen.dart';
import '../../widgets/confetti_widget.dart';
import '../../widgets/glow_button.dart';
import '../../widgets/single_select_dialog.dart';

/// iOS-style Case Creation Flow with Sequential Popups
/// Flow: Date → Facility → Surgeon → Specialty → Surgery → Anesthesia → Secondary? → Age → ASA
class CaseCreationFlowScreen extends ConsumerStatefulWidget {
  const CaseCreationFlowScreen({super.key});

  @override
  ConsumerState<CaseCreationFlowScreen> createState() => _CaseCreationFlowScreenState();
}

class _CaseCreationFlowScreenState extends ConsumerState<CaseCreationFlowScreen> {
  // Case data
  DateTime? _selectedDate;
  String? _facility;
  String? _surgeon;
  String? _specialty;
  String? _surgery;
  String? _primaryAnesthesia;
  String? _secondaryAnesthesia;
  int? _age;
  String? _asaClassification;
  String? _gender;
  bool _asaEmergency = false;
  String _patientNotes = '';
  String _complications = '';
  String _comorbidities = '';
  String _skilledProcedures = '';
  String? _airwayManagement;

  // Lists from database
  List<String> _facilities = [];
  List<String> _surgeons = [];

  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    try {
      print('DEBUG: Starting _loadData...');

      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      // Load facilities DIRECTLY from Parse Server (same as dashboard update screen)
      final facilityQuery = QueryBuilder<ParseObject>(ParseObject('savedFacilities'))
        ..whereEqualTo('userEmail', user.email);
      final facilityResponse = await facilityQuery.query();

      if (facilityResponse.success && facilityResponse.results != null && facilityResponse.results!.isNotEmpty) {
        final parseObject = facilityResponse.results!.first as ParseObject;
        final arrayData = parseObject.get<List<dynamic>>('userFacilities');
        if (arrayData != null) {
          _facilities = arrayData
              .map((e) => e.toString())
              .where((item) => item.trim().isNotEmpty)
              .map((item) => item.trim())
              .toSet()
              .toList();
          _facilities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
        }
      }

      print('DEBUG: Loaded ${_facilities.length} facilities from Parse: $_facilities');

      // Load surgeons DIRECTLY from Parse Server (same as dashboard update screen)
      final surgeonQuery = QueryBuilder<ParseObject>(ParseObject('savedSurgeons'))
        ..whereEqualTo('userEmail', user.email);
      final surgeonResponse = await surgeonQuery.query();

      if (surgeonResponse.success && surgeonResponse.results != null && surgeonResponse.results!.isNotEmpty) {
        final parseObject = surgeonResponse.results!.first as ParseObject;
        final arrayData = parseObject.get<List<dynamic>>('userSurgeons');
        if (arrayData != null) {
          _surgeons = arrayData
              .map((e) => e.toString())
              .where((item) => item.trim().isNotEmpty)
              .map((item) => item.trim())
              .toSet()
              .toList();
          _surgeons.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
        }
      }

      print('DEBUG: Loaded ${_surgeons.length} surgeons from Parse: $_surgeons');

      // Note: surgeries will be loaded per-specialty in _SurgeryListScreen

      if (mounted) {
        setState(() => _isLoading = false);
        // Start the flow with new case alert
        _showNewCaseAlert();
      }
    } catch (e, stackTrace) {
      print('DEBUG: Error loading data: $e');
      print('DEBUG: Stack trace: $stackTrace');
      if (mounted) {
        setState(() => _isLoading = false);
        _showError('Failed to load data: $e');
      }
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  Future<void> _saveFacilities() async {
    // Alphabetize before saving
    _facilities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    // Save through facility provider to sync with Parse server
    await ref.read(facilityProvider.notifier).updateFacilities(_facilities);
  }

  Future<void> _saveSurgeons() async {
    // Alphabetize before saving
    _surgeons.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    // Save through surgeon provider to sync with Parse server
    await ref.read(surgeonProvider.notifier).updateSurgeons(_surgeons);
  }

  // 24-hour facility caching
  Future<void> _saveCachedFacility(String facility) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('cached_facility', facility);
    await prefs.setInt('cached_facility_timestamp', DateTime.now().millisecondsSinceEpoch);
    print('DEBUG: Saved cached facility: $facility at ${DateTime.now()}');
  }

  Future<String?> _getCachedFacility() async {
    final prefs = await SharedPreferences.getInstance();
    final facility = prefs.getString('cached_facility');
    final timestamp = prefs.getInt('cached_facility_timestamp');

    if (facility == null || timestamp == null) {
      print('DEBUG: No cached facility found');
      return null;
    }

    final cachedTime = DateTime.fromMillisecondsSinceEpoch(timestamp);
    final now = DateTime.now();
    final difference = now.difference(cachedTime);

    print('DEBUG: Cached facility: $facility, age: ${difference.inHours} hours');

    // Check if less than 24 hours old
    if (difference.inHours < 24) {
      return facility;
    } else {
      print('DEBUG: Cached facility expired');
      return null;
    }
  }

  // Step 0: New Case Alert (iOS addNewCaseAlert)
  Future<void> _showNewCaseAlert() async {
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: const Text(
          'New Case',
          style: TextStyle(color: AppColors.jclGray),
        ),
        content: const Text(
          'Are you ready to enter a new case?',
          style: TextStyle(color: AppColors.jclGray),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop(); // Close dialog
              Navigator.of(context).pop(); // Go back to previous screen
            },
            child: const Text(
              'No',
              style: TextStyle(color: AppColors.jclGray),
            ),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(context).pop(); // Close dialog

              // Set date to current date
              setState(() => _selectedDate = DateTime.now());

              // Check for cached facility
              final cachedFacility = await _getCachedFacility();
              if (cachedFacility != null) {
                print('DEBUG: Using cached facility: $cachedFacility');
                setState(() => _facility = cachedFacility);
                // Navigate to background screen, then show surgeon picker
                await Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => _CaseBuilderBackgroundScreen(
                      date: _selectedDate!,
                      facility: _facility,
                      onReady: (ctx) => _showSurgeonPickerOnBackground(ctx),
                    ),
                  ),
                );
              } else {
                // Navigate to background screen, then show facility picker
                await Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => _CaseBuilderBackgroundScreen(
                      date: _selectedDate!,
                      facility: null,
                      onReady: (ctx) => _showFacilityPickerOnBackground(ctx),
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: AppColors.jclGray,
            ),
            child: const Text('Yes'),
          ),
        ],
      ),
    );
  }

  // Step 1: Date Picker (iOS scroll wheel style)
  Future<void> _showDatePicker() async {
    DateTime selectedDate = DateTime.now();

    await showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.jclWhite,
      builder: (context) => Container(
        height: 300,
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel', style: TextStyle(color: AppColors.jclOrange)),
                  ),
                  const Text('Select Date', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: Colors.black)),
                  TextButton(
                    onPressed: () async {
                      setState(() => _selectedDate = selectedDate);
                      Navigator.of(context).pop();

                      // Check for cached facility
                      final cachedFacility = await _getCachedFacility();
                      if (cachedFacility != null) {
                        print('DEBUG: Using cached facility: $cachedFacility');
                        setState(() => _facility = cachedFacility);
                        // Navigate to background screen, then show surgeon picker
                        await Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => _CaseBuilderBackgroundScreen(
                              date: _selectedDate!,
                              facility: _facility,
                              onReady: (ctx) => _showSurgeonPickerOnBackground(ctx),
                            ),
                          ),
                        );
                      } else {
                        // Navigate to background screen, then show facility picker
                        await Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => _CaseBuilderBackgroundScreen(
                              date: _selectedDate!,
                              facility: null,
                              onReady: (ctx) => _showFacilityPickerOnBackground(ctx),
                            ),
                          ),
                        );
                      }
                    },
                    child: const Text('Done', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
            // iOS Date Picker
            Expanded(
              child: CupertinoDatePicker(
                mode: CupertinoDatePickerMode.date,
                initialDateTime: selectedDate,
                minimumDate: DateTime(2000),
                maximumDate: DateTime.now(),
                onDateTimeChanged: (DateTime newDate) {
                  selectedDate = newDate;
                },
              ),
            ),
          ],
        ),
      ),
      isDismissible: false,
    );

    // If user cancelled, close the screen
    if (_selectedDate == null && mounted) {
      Navigator.of(context).pop();
    }
  }

  // Step 2: Facility Picker - matches Edit Case page exactly
  Future<void> _showFacilityPicker() async {
    final result = await _showFacilityListPicker();

    if (result != null) {
      setState(() => _facility = result);
      // Save facility to 24-hour cache
      await _saveCachedFacility(result);
      _showSurgeonPicker();
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  // Facility List Picker - iOS chooseFacilityList: equivalent
  /// Matches iOS SCLAlertView implementation exactly
  Future<String?> _showFacilityListPicker() async {
    final facilityAsync = ref.read(facilityProvider);
    List<String> facilities = facilityAsync.when(
      data: (data) => data.toList(),
      loading: () => <String>[],
      error: (_, __) => <String>[],
    );

    String? selected;

    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) => AlertDialog(
            backgroundColor: AppColors.jclWhite,
            title: Row(
              children: [
                Image.asset(
                  'assets/images/hospital-sign-40.png',
                  width: 24,
                  height: 24,
                  errorBuilder: (context, error, stackTrace) {
                    return const Icon(Icons.local_hospital, color: AppColors.jclGray, size: 24);
                  },
                ),
                const SizedBox(width: 8),
                const Text(
                  'Select Facility',
                  style: TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
                ),
              ],
            ),
            content: SizedBox(
              width: double.maxFinite,
              height: 200,
              child: facilities.isEmpty
                  ? const Center(child: Text('No facilities found', style: TextStyle(color: AppColors.jclGray)))
                  : ListView.builder(
                      shrinkWrap: true,
                      itemCount: facilities.length,
                      itemBuilder: (context, index) {
                        final facility = facilities[index];
                        final isSelected = selected == facility;
                        return ListTile(
                          title: Text(facility, style: const TextStyle(color: AppColors.jclGray)),
                          trailing: isSelected
                              ? const Icon(Icons.check_circle, color: AppColors.jclOrange)
                              : null,
                          selected: isSelected,
                          selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                          tileColor: AppColors.jclWhite,
                          onTap: () {
                            setDialogState(() {
                              selected = facility;
                            });
                          },
                        );
                      },
                    ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(null),
                child: const Text('Back', style: TextStyle(color: Colors.grey)),
              ),
              ElevatedButton(
                onPressed: selected == null || selected!.isEmpty
                    ? null
                    : () => Navigator.of(context).pop(selected),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: AppColors.jclGray,
                ),
                child: const Text('Select'),
              ),
            ],
          ),
        );
      },
    );

    return result;
  }

  // Step 3: Surgeon Picker - matches Edit Case page exactly
  Future<void> _showSurgeonPicker() async {
    final result = await _showSurgeonListPicker();

    if (result != null) {
      setState(() => _surgeon = result);
      _showSurgerySelection();
    } else if (mounted) {
      // User pressed back, show facility picker
      _showFacilityPicker();
    }
  }

  // Surgeon List Picker - iOS chooseSurgeonList: equivalent
  /// Matches iOS SCLAlertView implementation exactly
  Future<String?> _showSurgeonListPicker() async {
    final surgeonAsync = ref.read(surgeonProvider);
    List<String> surgeons = surgeonAsync.when(
      data: (data) => data.toList(),
      loading: () => <String>[],
      error: (_, __) => <String>[],
    );

    String? selected;

    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) => AlertDialog(
            backgroundColor: AppColors.jclWhite,
            title: Row(
              children: [
                Image.asset(
                  'assets/images/doctor-40.png',
                  width: 24,
                  height: 24,
                  errorBuilder: (context, error, stackTrace) {
                    return const Icon(Icons.medical_services, color: AppColors.jclGray, size: 24);
                  },
                ),
                const SizedBox(width: 8),
                const Text(
                  'Select Surgeon',
                  style: TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
                ),
              ],
            ),
            content: SizedBox(
              width: double.maxFinite,
              height: 200,
              child: surgeons.isEmpty
                  ? const Center(child: Text('No surgeons found', style: TextStyle(color: AppColors.jclGray)))
                  : ListView.builder(
                      shrinkWrap: true,
                      itemCount: surgeons.length,
                      itemBuilder: (context, index) {
                        final surgeon = surgeons[index];
                        final isSelected = selected == surgeon;
                        return ListTile(
                          title: Text(surgeon, style: const TextStyle(color: AppColors.jclGray)),
                          trailing: isSelected
                              ? const Icon(Icons.check_circle, color: AppColors.jclOrange)
                              : null,
                          selected: isSelected,
                          selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                          tileColor: AppColors.jclWhite,
                          onTap: () {
                            setDialogState(() {
                              selected = surgeon;
                            });
                          },
                        );
                      },
                    ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(null),
                child: const Text('Back', style: TextStyle(color: Colors.grey)),
              ),
              ElevatedButton(
                onPressed: selected == null || selected!.isEmpty
                    ? null
                    : () => Navigator.of(context).pop(selected),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: AppColors.jclGray,
                ),
                child: const Text('Select'),
              ),
            ],
          ),
        );
      },
    );

    return result;
  }

  /// Helper method to show dialog for adding new facility/surgeon
  Future<String?> _showAddItemDialog(String title, String hint) async {
    final TextEditingController controller = TextEditingController();

    return showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: Text(
          title,
          style: const TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
        ),
        content: TextField(
          controller: controller,
          autofocus: true,
          decoration: InputDecoration(
            hintText: hint,
            border: const OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(null),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              final value = controller.text.trim();
              if (value.isNotEmpty) {
                Navigator.of(context).pop(value);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('Add'),
          ),
        ],
      ),
    );
  }

  /// Helper method to show dialog for adding new facility/surgeon on background screen
  Future<String?> _showAddItemDialogOnBackground(BuildContext backgroundContext, String title, String hint) async {
    final TextEditingController controller = TextEditingController();

    return showDialog<String>(
      context: backgroundContext,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: Text(
          title,
          style: const TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
        ),
        content: TextField(
          controller: controller,
          autofocus: true,
          decoration: InputDecoration(
            hintText: hint,
            border: const OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(null),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              final value = controller.text.trim();
              if (value.isNotEmpty) {
                Navigator.of(context).pop(value);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('Add'),
          ),
        ],
      ),
    );
  }

  // Facility Picker on Background Screen
  Future<void> _showFacilityPickerOnBackground(BuildContext backgroundContext) async{
    final controller = TextEditingController();
    String? result;

    result = await showModalBottomSheet<String>(
      context: backgroundContext,
      backgroundColor: AppColors.jclWhite,
      isScrollControlled: true,
      isDismissible: false,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
          left: 16,
          right: 16,
          top: 16,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            const Text(
              'Facility',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),

            // Text field
            TextField(
              controller: controller,
              autocorrect: false,
              enableSuggestions: false,
              decoration: InputDecoration(
                hintText: 'Enter facility name',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: AppColors.jclOrange, width: 2),
                ),
              ),
              style: const TextStyle(color: AppColors.jclGray),
            ),
            const SizedBox(height: 16),

            // Buttons
            Row(
              children: [
                // Pick List Button
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final selected = await _showFacilityListPickerOnBackground(backgroundContext);
                      if (selected != null) {
                        Navigator.of(context).pop(selected);
                      }
                    },
                    icon: const Icon(Icons.list, color: AppColors.jclOrange),
                    label: const Text('Pick List', style: TextStyle(color: AppColors.jclOrange)),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: const BorderSide(color: AppColors.jclOrange),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Add Button
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      if (controller.text.trim().isNotEmpty) {
                        final newFacility = controller.text.trim();
                        // Add to facilities list if not already there
                        if (!_facilities.contains(newFacility)) {
                          _facilities.add(newFacility);
                          _saveFacilities();
                        }
                        Navigator.of(context).pop(newFacility);
                      }
                    },
                    icon: const Icon(Icons.add, color: AppColors.jclWhite),
                    label: const Text('Add', style: TextStyle(color: AppColors.jclWhite)),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: AppColors.jclOrange,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );

    if (result != null) {
      setState(() => _facility = result);
      await _saveCachedFacility(result);
      // Update background to show facility, then show surgeon picker
      if (backgroundContext.mounted) {
        final backgroundState = backgroundContext.findAncestorStateOfType<_CaseBuilderBackgroundScreenState>();
        backgroundState?.updateFacility(result);
        await _showSurgeonPickerOnBackground(backgroundContext);
      }
    } else if (backgroundContext.mounted) {
      // User cancelled, go back
      Navigator.of(backgroundContext).pop();
    }
  }

  // Facility List Picker on Background Screen
  Future<String?> _showFacilityListPickerOnBackground(BuildContext backgroundContext) async {
    String? selectedItem;

    return await showDialog<String>(
      context: backgroundContext,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Select Location',
            style: TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Add New Facility button
                ListTile(
                  leading: const Icon(Icons.add, color: AppColors.jclOrange),
                  title: const Text('Add New Facility', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
                  tileColor: AppColors.jclWhite,
                  onTap: () async {
                    final newFacility = await _showAddItemDialogOnBackground(backgroundContext, 'Add New Facility', 'Enter facility name');
                    if (newFacility != null && newFacility.isNotEmpty) {
                      setDialogState(() {
                        if (!_facilities.contains(newFacility)) {
                          _facilities.add(newFacility);
                          _facilities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
                        }
                        selectedItem = newFacility;
                      });
                      // Add to Parse
                      await ref.read(facilityProvider.notifier).addFacility(newFacility);
                    }
                  },
                ),
                const Divider(),
                // Facilities list
                Flexible(
                  child: _facilities.isEmpty
                      ? const Center(
                          child: Text(
                            'No facilities saved yet',
                            style: TextStyle(color: AppColors.jclGray, fontSize: 16),
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          itemCount: _facilities.length,
                          itemBuilder: (context, index) {
                            final facility = _facilities[index];
                            final isSelected = selectedItem == facility;
                            return ListTile(
                              title: Text(facility, style: const TextStyle(color: AppColors.jclGray)),
                              trailing: isSelected
                                  ? const Icon(Icons.check_circle, color: AppColors.jclOrange)
                                  : null,
                              selected: isSelected,
                              selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                              tileColor: AppColors.jclWhite,
                              onTap: () {
                                setDialogState(() {
                                  selectedItem = facility;
                                });
                              },
                            );
                          },
                        ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(null),
              child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
            ),
            ElevatedButton(
              onPressed: selectedItem == null || selectedItem!.isEmpty
                  ? null
                  : () => Navigator.of(context).pop(selectedItem),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: AppColors.jclWhite,
              ),
              child: const Text('Select'),
            ),
          ],
        ),
      ),
    );
  }

  // Surgeon Picker on Background Screen
  Future<void> _showSurgeonPickerOnBackground(BuildContext backgroundContext) async {
    final controller = TextEditingController();
    String? result;

    result = await showModalBottomSheet<String>(
      context: backgroundContext,
      backgroundColor: AppColors.jclWhite,
      isScrollControlled: true,
      isDismissible: false,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
          left: 16,
          right: 16,
          top: 16,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            const Text(
              'Surgeon',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),

            // Text field
            TextField(
              controller: controller,
              autocorrect: false,
              enableSuggestions: false,
              decoration: InputDecoration(
                hintText: 'Enter surgeon name',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: const BorderSide(color: AppColors.jclOrange, width: 2),
                ),
              ),
              style: const TextStyle(color: AppColors.jclGray),
            ),
            const SizedBox(height: 16),

            // Buttons
            Row(
              children: [
                // Pick List Button
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final selected = await _showSurgeonListPickerOnBackground(backgroundContext);
                      if (selected != null) {
                        Navigator.of(context).pop(selected);
                      }
                    },
                    icon: const Icon(Icons.list, color: AppColors.jclOrange),
                    label: const Text('Pick List', style: TextStyle(color: AppColors.jclOrange)),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: const BorderSide(color: AppColors.jclOrange),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Add Button
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      if (controller.text.trim().isNotEmpty) {
                        final newSurgeon = controller.text.trim();
                        // Add to surgeons list if not already there
                        if (!_surgeons.contains(newSurgeon)) {
                          _surgeons.add(newSurgeon);
                          _saveSurgeons();
                        }
                        Navigator.of(context).pop(newSurgeon);
                      }
                    },
                    icon: const Icon(Icons.add, color: AppColors.jclWhite),
                    label: const Text('Add', style: TextStyle(color: AppColors.jclWhite)),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: AppColors.jclOrange,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );

    if (result != null) {
      setState(() => _surgeon = result);
      // Close background screen and continue with surgery selection
      if (backgroundContext.mounted) {
        Navigator.of(backgroundContext).pop();
        _showSurgerySelection();
      }
    } else if (backgroundContext.mounted) {
      // User pressed back, update background to hide facility and show facility picker again
      final backgroundState = backgroundContext.findAncestorStateOfType<_CaseBuilderBackgroundScreenState>();
      backgroundState?.updateFacility(null);
      await _showFacilityPickerOnBackground(backgroundContext);
    }
  }

  // Surgeon List Picker on Background Screen - iOS chooseSurgeonList: equivalent
  Future<String?> _showSurgeonListPickerOnBackground(BuildContext backgroundContext) async {
    String? selectedItem;

    return await showDialog<String>(
      context: backgroundContext,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Select Surgeon',
            style: TextStyle(color: AppColors.jclGray, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Add New Surgeon button
                ListTile(
                  leading: const Icon(Icons.add, color: AppColors.jclOrange),
                  title: const Text('Add New Surgeon', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
                  tileColor: AppColors.jclWhite,
                  onTap: () async {
                    final newSurgeon = await _showAddItemDialogOnBackground(backgroundContext, 'Add New Surgeon', 'Enter surgeon name');
                    if (newSurgeon != null && newSurgeon.isNotEmpty) {
                      setDialogState(() {
                        if (!_surgeons.contains(newSurgeon)) {
                          _surgeons.add(newSurgeon);
                          _surgeons.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
                        }
                        selectedItem = newSurgeon;
                      });
                      // Add to Parse
                      await ref.read(surgeonProvider.notifier).addSurgeon(newSurgeon);
                    }
                  },
                ),
                const Divider(),
                // Surgeons list
                Flexible(
                  child: _surgeons.isEmpty
                      ? const Center(
                          child: Text(
                            'No surgeons saved yet',
                            style: TextStyle(color: AppColors.jclGray, fontSize: 16),
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          itemCount: _surgeons.length,
                          itemBuilder: (context, index) {
                            final surgeon = _surgeons[index];
                            final isSelected = selectedItem == surgeon;
                            return ListTile(
                              title: Text(surgeon, style: const TextStyle(color: AppColors.jclGray)),
                              trailing: isSelected
                                  ? const Icon(Icons.check_circle, color: AppColors.jclOrange)
                                  : null,
                              selected: isSelected,
                              selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                              tileColor: AppColors.jclWhite,
                              onTap: () {
                                setDialogState(() {
                                  selectedItem = surgeon;
                                });
                              },
                            );
                          },
                        ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(null),
              child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
            ),
            TextButton(
              onPressed: () => Navigator.of(context).pop('not designated'),
              child: const Text('Skip', style: TextStyle(color: AppColors.jclOrange)),
            ),
            ElevatedButton(
              onPressed: selectedItem == null || selectedItem!.isEmpty
                  ? null
                  : () => Navigator.of(context).pop(selectedItem),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: AppColors.jclWhite,
              ),
              child: const Text('Select'),
            ),
          ],
        ),
      ),
    );
  }

  // Step 4: Surgery Selection (Full screen with specialty images → surgery list)
  Future<void> _showSurgerySelection() async {
    // Navigate to full screen specialty/surgery selector
    final result = await Navigator.of(context).push<Map<String, String>>(
      MaterialPageRoute(
        builder: (context) => const _SurgerySelectionScreen(),
        fullscreenDialog: true,
      ),
    );

    if (result != null) {
      setState(() {
        _specialty = result['specialty'];
        _surgery = result['surgery'];
      });
      // Note: New surgeries are saved automatically in _SurgeryListScreen
      // Trigger chooseMainPlan after surgery selection (iOS flow)
      await _chooseMainPlan();
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  /// iOS chooseMainPlan: method equivalent
  /// Shows primary anesthesia plan selection dialog
  Future<void> _chooseMainPlan() async {
    final primeArray = ['General Anesthetic', 'Regional Anesthetic', 'MAC', 'TIVA'];
    String? selectedPlan;

    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: Row(
            children: [
              Image.asset(
                'assets/images/syringe-40.png',
                width: 24,
                height: 24,
                errorBuilder: (context, error, stackTrace) {
                  return const Icon(Icons.medical_services, color: AppColors.jclOrange, size: 24);
                },
              ),
              const SizedBox(width: 12),
              const Expanded(child: Text('Primary Anesthesia Plan')),
            ],
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: primeArray.map((plan) {
                final isSelected = selectedPlan == plan;
                return ListTile(
                  tileColor: AppColors.jclWhite,
                  title: Text(
                    plan,
                    style: const TextStyle(color: AppColors.jclGray),
                  ),
                  trailing: isSelected
                      ? const Icon(Icons.check, color: AppColors.jclOrange)
                      : null,
                  onTap: () {
                    setDialogState(() {
                      selectedPlan = plan;
                    });
                  },
                );
              }).toList(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                // Back button - return null to indicate back was pressed
                Navigator.of(context).pop('__BACK__');
              },
              child: const Text('Back', style: TextStyle(color: Colors.grey)),
            ),
            ElevatedButton(
              onPressed: selectedPlan != null
                  ? () => Navigator.of(context).pop(selectedPlan)
                  : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: const Color(0xFF483930),
                disabledBackgroundColor: Colors.grey.withOpacity(0.3),
              ),
              child: const Text('Select'),
            ),
          ],
        ),
      ),
    );

    // Handle selection based on chosen plan (iOS chooseMainPlan: logic)
    if (result == '__BACK__' && mounted) {
      // User hit back, return to surgery specialty selection
      Navigator.of(context).pop();
    } else if (result != null && mounted) {
      setState(() {
        _primaryAnesthesia = result;

        // iOS chooseMainPlan: logic for each plan type
        if (result == 'General Anesthetic') {
          // Enable secondary plan (can be Regional, MAC, or TIVA)
          // Secondary plan will be set later if user chooses
        } else if (result == 'Regional Anesthetic') {
          // Regional doesn't allow secondary plan
          _secondaryAnesthesia = 'N/A';
        } else if (result == 'MAC') {
          // Enable secondary plan (can only be Regional)
        } else if (result == 'TIVA') {
          // Enable secondary plan (can only be Regional)
        }
      });

      switch (result) {
        case 'General Anesthetic':
          // Enable secondary plan, then show GA options
          await _showGeneralAnestheticOptions();
          break;
        case 'Regional Anesthetic':
          // No secondary plan (set to N/A), show regional options
          await _showRegionalAnestheticOptions();
          break;
        case 'MAC':
          // Ask if user wants secondary plan
          await _checkSecondaryPlanAlert();
          break;
        case 'TIVA':
          // Ask about airway management
          await _checkAirwayAlert();
          break;
      }
    } else if (mounted) {
      // Dialog dismissed without selection, go back
      Navigator.of(context).pop();
    }
  }

  /// iOS showGenAnes: equivalent - Show General Anesthetic options screen
  /// Matches iOS gaTableViewController
  Future<void> _showGeneralAnestheticOptions({bool isTIVA = false, bool isSecondary = false}) async {
    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => GeneralAnestheticSelectionScreen(isTIVA: isTIVA),
      ),
    );

    if (result != null && mounted) {
      setState(() {
        if (isSecondary) {
          _secondaryAnesthesia = result;
        } else {
          _primaryAnesthesia = result;
        }
      });
      // If this is primary anesthesia, ask about secondary plan
      if (!isSecondary) {
        await _checkSecondaryPlanAlert();
      } else {
        await _showAgePicker();
      }
    } else if (mounted) {
      // User exited without confirming, go back
      Navigator.of(context).pop();
    }
  }

  /// iOS showRegAnes: equivalent - Show Regional Anesthetic options screen
  /// Matches iOS regTableViewController
  Future<void> _showRegionalAnestheticOptions({bool isSecondary = false}) async {
    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => const RegionalAnestheticSelectionScreen(),
      ),
    );

    if (result != null && mounted) {
      setState(() {
        if (isSecondary) {
          _secondaryAnesthesia = result;
        } else {
          _primaryAnesthesia = result;
        }
      });
      await _showAgePicker();
    } else if (mounted) {
      // User exited without confirming, go back
      Navigator.of(context).pop();
    }
  }

  /// iOS checkSecPlanAlert: equivalent
  Future<void> _checkSecondaryPlanAlert() async {
    final wantsSecondary = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: const Text(
          'Secondary Anesthesia',
          style: TextStyle(color: AppColors.jclGray),
        ),
        content: const Text(
          'Do you need to enter a secondary anesthesia plan?',
          style: TextStyle(color: AppColors.jclGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('No', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: AppColors.jclGray,
            ),
            child: const Text('Yes'),
          ),
        ],
      ),
    );

    if (wantsSecondary == true && mounted) {
      // Show regional anesthetic options for secondary plan
      await _showRegionalAnestheticOptions(isSecondary: true);
    } else if (mounted) {
      // User doesn't want secondary plan, set to N/A (iOS logic)
      setState(() {
        _secondaryAnesthesia = 'N/A';
      });
      await _showAgePicker();
    }
  }

  /// iOS checkAirwayAlert: equivalent
  Future<void> _checkAirwayAlert() async {
    final wantsAirway = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Add Airway Management?'),
        content: const Text('Would you like to add airway management details?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('No', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: const Color(0xFF483930),
            ),
            child: const Text('Yes'),
          ),
        ],
      ),
    );

    if (wantsAirway == true && mounted) {
      // If yes, show GA options with TIVA flag (which will call age picker)
      await _showGeneralAnestheticOptions(isTIVA: true);
    } else if (mounted) {
      // If no, check for secondary plan (which will call age picker)
      await _checkSecondaryPlanAlert();
    }
  }

  // Show comprehensive form view (iOS buildLogCaseView equivalent)
  Future<void> _showLogCaseView() async {
    final result = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => _LogCaseViewScreen(
          initialDate: _selectedDate!,
          initialFacility: _facility,
          initialSurgeon: _surgeon,
          initialSurgery: _surgery,
          initialSpecialty: _specialty,
          initialPrimaryAnesthesia: _primaryAnesthesia,
          initialSecondaryAnesthesia: _secondaryAnesthesia,
          initialAge: _age,
          facilities: _facilities,
          surgeons: _surgeons,
          onSave: (caseData) async {
            setState(() {
              _selectedDate = caseData['date'];
              _facility = caseData['location'];
              _surgeon = caseData['surgeon'];
              _surgery = caseData['surgery'];
              _primaryAnesthesia = caseData['primaryAnesthetic'];
              _secondaryAnesthesia = caseData['secondaryAnesthetic'];
              // Parse age from String to int if needed
              final ageData = caseData['age'];
              _age = ageData is String
                  ? (int.tryParse(ageData) ?? 0)
                  : ageData as int?;
              _gender = caseData['gender'];
              _asaClassification = caseData['asaClassification'];
              _asaEmergency = caseData['asaEmergency'] ?? false;
              _patientNotes = caseData['patientNotes'] ?? '';
              _complications = caseData['complications'] ?? '';
              _comorbidities = caseData['comorbidities'] ?? '';
              _skilledProcedures = caseData['skilledProcedures'] ?? '';
              _airwayManagement = caseData['airwayManagement'];
            });
            await _saveCase();
          },
        ),
        fullscreenDialog: true,
      ),
    );

    // PatientInfoForm handles the 30-second delay and navigation
    // When it pops, we return to home screen
    if (mounted && result == true) {
      Navigator.of(context).pop(true);
    }
  }

  // Step 5: Primary Anesthesia Picker
  Future<void> _showPrimaryAnesthesiaPicker() async {
    final plans = ['General Anesthetic', 'Regional Anesthetic', 'MAC', 'TIVA'];

    String? selected = await _showPickerDialog(
      title: 'Primary Anesthetic Plan',
      items: plans,
      canAddNew: false,
    );

    if (selected != null) {
      setState(() => _primaryAnesthesia = selected);
      _showSecondaryAnesthesiaQuery();
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  // Step 6: Secondary Anesthesia Query
  Future<void> _showSecondaryAnesthesiaQuery() async {
    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Secondary Anesthetic?'),
        content: const Text('Did you use a secondary anesthetic plan?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('No', style: TextStyle(color: AppColors.jclOrange)),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Yes', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (result == true) {
      _showSecondaryAnesthesiaPicker();
    } else if (result == false) {
      _showAgePicker();
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  // Step 7: Secondary Anesthesia Picker (if yes)
  Future<void> _showSecondaryAnesthesiaPicker() async {
    final plans = ['General Anesthetic', 'Regional Anesthetic', 'MAC', 'TIVA'];

    String? selected = await _showPickerDialog(
      title: 'Secondary Anesthetic Plan',
      items: plans,
      canAddNew: false,
    );

    if (selected != null) {
      setState(() => _secondaryAnesthesia = selected);
      _showAgePicker();
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  // Step 8: Age Picker (iOS rolling picker style)
  // Called after anesthesia plan logic completes
  Future<void> _showAgePicker() async {
    int selectedAge = 30; // Default age

    await showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.jclWhite,
      builder: (context) => Container(
        height: 300,
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const SizedBox(width: 60),
                  const Text('Patient Age', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: Colors.black)),
                  TextButton(
                    onPressed: () {
                      setState(() => _age = selectedAge);
                      Navigator.of(context).pop();
                    },
                    child: const Text('Done', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
            // iOS Age Picker
            Expanded(
              child: CupertinoPicker(
                itemExtent: 40,
                scrollController: FixedExtentScrollController(initialItem: selectedAge),
                onSelectedItemChanged: (int index) {
                  selectedAge = index;
                },
                children: List<Widget>.generate(121, (int index) {
                  return Center(
                    child: Text(
                      '$index years',
                      style: const TextStyle(fontSize: 20, color: Colors.black),
                    ),
                  );
                }),
              ),
            ),
          ],
        ),
      ),
      isDismissible: false,
    );

    // After age is set, immediately show ASA classification alert
    if (mounted) {
      await _checkASAAlert();
    }
  }

  /// iOS checkASAAlert: equivalent
  /// Shows reminder alert to select ASA in the main screen
  Future<void> _checkASAAlert() async {
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('ASA Classification'),
        content: const Text('Please remember to select an ASA classification in the case form.'),
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

    // After user acknowledges, proceed to log case view
    if (mounted) {
      await _showLogCaseView();
    }
  }

  // Generic picker dialog
  Future<String?> _showPickerDialog({
    required String title,
    required List<String> items,
    required bool canAddNew,
    String? addNewHint,
  }) async {
    return await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppColors.jclWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
        ),
        child: DraggableScrollableSheet(
        initialChildSize: 0.7,
        minChildSize: 0.5,
        maxChildSize: 0.9,
        expand: false,
        builder: (context, scrollController) => Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel', style: TextStyle(color: AppColors.jclOrange)),
                  ),
                  Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Colors.black)),
                  if (canAddNew)
                    TextButton(
                      onPressed: () async {
                        final result = await _showAddNewDialog(addNewHint ?? 'Enter name');
                        if (result != null && context.mounted) {
                          Navigator.of(context).pop(result);
                        }
                      },
                      child: const Text('Add New', style: TextStyle(color: AppColors.jclOrange)),
                    )
                  else
                    const SizedBox(width: 60),
                ],
              ),
            ),
            // List
            Expanded(
              child: items.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.list, size: 64, color: Colors.grey),
                          const SizedBox(height: 16),
                          const Text('No items yet', style: TextStyle(color: Colors.grey)),
                          if (canAddNew) ...[
                            const SizedBox(height: 8),
                            ElevatedButton(
                              onPressed: () async {
                                final result = await _showAddNewDialog(addNewHint ?? 'Enter name');
                                if (result != null && context.mounted) {
                                  Navigator.of(context).pop(result);
                                }
                              },
                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.jclOrange),
                              child: const Text('Add First Item'),
                            ),
                          ],
                        ],
                      ),
                    )
                  : ListView.builder(
                      controller: scrollController,
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        return ListTile(
                          tileColor: AppColors.jclWhite,
                          title: Text(items[index], style: const TextStyle(color: AppColors.jclGray)),
                          trailing: const Icon(Icons.chevron_right, color: AppColors.jclOrange),
                          onTap: () => Navigator.of(context).pop(items[index]),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
        ),
      isDismissible: false,
    );
  }

  // Add new item dialog
  Future<String?> _showAddNewDialog(String hint) async {
    final controller = TextEditingController();

    return await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Add $hint'),
        content: TextField(
          controller: controller,
          style: const TextStyle(color: AppColors.jclGray),
          autofocus: true,
          autocorrect: false,
          enableSuggestions: false,
          textCapitalization: TextCapitalization.words,
          decoration: InputDecoration(
            hintText: hint,
            border: const OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          TextButton(
            onPressed: () {
              final text = controller.text.trim();
              if (text.isNotEmpty) {
                Navigator.of(context).pop(text);
              }
            },
            child: const Text('Add', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  // Save the case
  Future<void> _saveCase() async {
    final user = ref.read(currentUserProvider);
    if (user == null) {
      _showError('User not logged in');
      Navigator.of(context).pop();
      return;
    }

    setState(() => _isLoading = true);

    // Build ASA string with E suffix if emergency
    String asaString = _asaClassification ?? '';
    if (_asaEmergency && asaString.isNotEmpty) {
      asaString += ', E';
    }

    // Combine notes, complications, comorbidities, skilled procedures
    List<String> commentParts = [];
    if (_surgeon != null && _surgeon!.isNotEmpty) {
      commentParts.add('Surgeon: $_surgeon');
    }
    if (_patientNotes.isNotEmpty) {
      commentParts.add('Notes: $_patientNotes');
    }
    if (_comorbidities.isNotEmpty) {
      commentParts.add('Comorbidities: $_comorbidities');
    }
    if (_skilledProcedures.isNotEmpty) {
      commentParts.add('Skilled Procedures: $_skilledProcedures');
    }

    final anestheticsUsed = <String>[];
    if (_primaryAnesthesia != null) anestheticsUsed.add(_primaryAnesthesia!);
    if (_secondaryAnesthesia != null) anestheticsUsed.add(_secondaryAnesthesia!);

    // Calculate image name based on surgery class
    final imageName = CaseModel.getImageNameForSurgeryClass(_specialty);

    final notifier = ref.read(caseDetailProvider.notifier);
    final success = await notifier.createCase(
      userEmail: user.email,
      date: _selectedDate!,
      patientAge: _age?.toString(),
      gender: _gender,
      asaClassification: asaString,
      procedureSurgery: _surgery ?? '',
      anestheticPlan: _primaryAnesthesia ?? '',
      anestheticsUsed: anestheticsUsed,
      surgeryClass: _specialty ?? '',
      location: _facility,
      airwayManagement: _airwayManagement,
      additionalComments: commentParts.join('\n'),
      complications: _complications.isNotEmpty,
      imageName: imageName,
    );

    if (mounted) {
      setState(() => _isLoading = false);

      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Case created successfully'), backgroundColor: Colors.green),
        );
        // Navigation is handled by PatientInfoForm after 30 second delay
      } else {
        final state = ref.read(caseDetailProvider);
        _showError(state.error ?? 'Failed to save case');
      }
    }
  }

  Future<bool> _confirmCancellation() async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Case Creation?'),
        content: const Text('All entered data will be lost.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Continue Editing', style: TextStyle(color: AppColors.jclOrange)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Cancel Case'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) async {
        if (didPop) return;
        final shouldPop = await _confirmCancellation();
        if (shouldPop && context.mounted) {
          Navigator.of(context).pop();
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.jclWhite,
        appBar: AppBar(
          title: const Text(
            'Create New Case',
            style: TextStyle(color: AppColors.jclWhite, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          centerTitle: true,
          backgroundColor: AppColors.jclOrange,
          elevation: 0,
          iconTheme: const IconThemeData(color: AppColors.jclWhite),
        ),
        body: Container(
          decoration: BoxDecoration(
            color: AppColors.jclWhite,
            image: DecorationImage(
              image: const AssetImage('assets/images/jcl_logo_dark.png'),
              fit: BoxFit.contain,
              opacity: 0.5,
            ),
          ),
          child: _isLoading
              ? const Center(child: CircularProgressIndicator(color: AppColors.jclOrange))
              : const SizedBox.shrink(),
        ),
      ),
    );
  }
}

/// Full-screen Surgery Selection Screen matching iOS surgeriesViewController
/// Shows specialty images in a grid, then navigates to surgery list
class _SurgerySelectionScreen extends StatefulWidget {
  const _SurgerySelectionScreen();

  @override
  State<_SurgerySelectionScreen> createState() => _SurgerySelectionScreenState();
}

class _SurgerySelectionScreenState extends State<_SurgerySelectionScreen> {
  // Specialty data matching iOS groupedData and surgery_data.dart keys
  final List<Map<String, dynamic>> _specialties = [
    {
      'title': 'Cardiovascular',
      'image': 'assets/images/cardiology.png',
      'color': const Color(0xFFE74C3C),
    },
    {
      'title': 'Dental',
      'image': 'assets/images/dental.png',
      'color': const Color(0xFF3498DB),
    },
    {
      'title': 'General',
      'image': 'assets/images/genSurgery.png',
      'color': const Color(0xFF2ECC71),
    },
    {
      'title': 'Neurosurgery',
      'image': 'assets/images/neurology.png',
      'color': const Color(0xFF9B59B6),
    },
    {
      'title': 'Obstetric/Gynecologic',
      'image': 'assets/images/obgyn.png',
      'color': const Color(0xFFFF6B9D),
    },
    {
      'title': 'Ophthalmic',
      'image': 'assets/images/ophthalmology.png',
      'color': const Color(0xFFE67E22),
    },
    {
      'title': 'Orthopedic',
      'image': 'assets/images/orthopedics.png',
      'color': const Color(0xFF1ABC9C),
    },
    {
      'title': 'Otolaryngology Head/Neck',
      'image': 'assets/images/otolaryngology.png',
      'color': const Color(0xFFF39C12),
    },
    {
      'title': 'Out-of-Operating Room Procedures',
      'image': 'assets/images/out-of-room procedures.png',
      'color': const Color(0xFF95A5A6),
    },
    {
      'title': 'Pediatric',
      'image': 'assets/images/pediatric.png',
      'color': const Color(0xFFE91E63),
    },
    {
      'title': 'Plastics & Reconstructive',
      'image': 'assets/images/plastics.png',
      'color': const Color(0xFF00BCD4),
    },
    {
      'title': 'Thoracic',
      'image': 'assets/images/pulmonology.png',
      'color': const Color(0xFF34495E),
    },
    {
      'title': 'Urology',
      'image': 'assets/images/urology.png',
      'color': const Color(0xFF607D8B),
    },
  ];

  Future<bool> _confirmCancellation() async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Case Creation?'),
        content: const Text('All entered data will be lost.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Continue', style: TextStyle(color: AppColors.jclOrange)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Cancel Case'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) async {
        if (didPop) return;
        final shouldPop = await _confirmCancellation();
        if (shouldPop && context.mounted) {
          Navigator.of(context).pop();
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.jclWhite,
        appBar: AppBar(
          title: const Text(
            'Select Surgery Specialty',
            style: TextStyle(color: AppColors.jclWhite, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          centerTitle: true,
          backgroundColor: AppColors.jclOrange,
          elevation: 0,
          iconTheme: const IconThemeData(color: AppColors.jclWhite),
          leading: IconButton(
            icon: const Icon(Icons.close),
            onPressed: () async {
              final shouldPop = await _confirmCancellation();
              if (shouldPop && context.mounted) {
                Navigator.of(context).pop();
              }
            },
          ),
        ),
        body: Container(
          decoration: BoxDecoration(
            color: AppColors.jclGray.withOpacity(0.9),
            image: DecorationImage(
              image: const AssetImage('assets/images/jcl_logo_dark.png'),
              fit: BoxFit.contain,
              opacity: 0.15,
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: GridView.builder(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
              childAspectRatio: 1.0,
            ),
            itemCount: _specialties.length,
            itemBuilder: (context, index) {
              final specialty = _specialties[index];
              return _SpecialtyCard(
                title: specialty['title'],
                imagePath: specialty['image'],
                color: specialty['color'],
                onTap: () async {
                  // Capture navigator before async gap
                  final navigator = Navigator.of(context);

                  // Navigate to surgery list for this specialty
                  final result = await navigator.push<Map<String, String>>(
                    MaterialPageRoute(
                      builder: (context) => _SurgeryListScreen(
                        specialty: specialty['title'],
                      ),
                    ),
                  );

                  if (result != null && mounted) {
                    // Surgery was selected, return the data
                    navigator.pop(result);
                  }
                },
              );
            },
          ),
        ),
        ),
      ),
    );
  }
}

/// Specialty card widget matching iOS collection view cells
class _SpecialtyCard extends StatelessWidget {
  final String title;
  final String imagePath;
  final Color color;
  final VoidCallback onTap;

  const _SpecialtyCard({
    required this.title,
    required this.imagePath,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.jclWhite,
      borderRadius: BorderRadius.circular(12),
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Expanded(
                child: Image.asset(
                  imagePath,
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) {
                    return Icon(
                      Icons.medical_services,
                      size: 64,
                      color: color,
                    );
                  },
                ),
              ),
              const SizedBox(height: 12),
              Text(
                title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.jclGray,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Surgery list screen for a specific specialty
class _SurgeryListScreen extends StatefulWidget {
  final String specialty;

  const _SurgeryListScreen({
    required this.specialty,
  });

  @override
  State<_SurgeryListScreen> createState() => _SurgeryListScreenState();
}

class _SurgeryListScreenState extends State<_SurgeryListScreen> {
  late List<String> _surgeries;
  final TextEditingController _searchController = TextEditingController();
  List<String> _filteredSurgeries = [];

  @override
  void initState() {
    super.initState();
    _loadSurgeriesForSpecialty();
  }

  Future<void> _loadSurgeriesForSpecialty() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final key = SurgeryData.getKeyForSpecialty(widget.specialty);

      // Load custom surgeries from SharedPreferences
      List<String> customSurgeries = prefs.getStringList(key) ?? [];

      // If no custom surgeries exist, initialize with default data
      if (customSurgeries.isEmpty) {
        customSurgeries = SurgeryData.getDefaultSurgeriesForSpecialty(widget.specialty);
        await prefs.setStringList(key, customSurgeries);
      }

      // Alphabetize
      customSurgeries.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));

      setState(() {
        _surgeries = customSurgeries;
        _filteredSurgeries = List.from(_surgeries);
      });
    } catch (e) {
      setState(() {
        _surgeries = [];
        _filteredSurgeries = [];
      });
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filterSurgeries(String query) {
    setState(() {
      if (query.isEmpty) {
        _filteredSurgeries = List.from(_surgeries);
      } else {
        _filteredSurgeries = _surgeries
            .where((s) => s.toLowerCase().contains(query.toLowerCase()))
            .toList();
      }
    });
  }

  Future<void> _showAddSurgeryDialog() async {
    final controller = TextEditingController();

    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Surgery'),
        content: TextField(
          controller: controller,
          style: const TextStyle(color: AppColors.jclGray),
          autofocus: true,
          autocorrect: false,
          enableSuggestions: false,
          textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(
            hintText: 'Enter surgery name',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          TextButton(
            onPressed: () {
              final text = controller.text.trim();
              if (text.isNotEmpty) {
                Navigator.of(context).pop(text);
              }
            },
            child: const Text('Add', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (result != null && result.isNotEmpty) {
      setState(() {
        _surgeries.add(result);
        // Alphabetize after adding
        _surgeries.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
        _filterSurgeries(_searchController.text);
      });
      // Save to specialty-specific SharedPreferences key
      final prefs = await SharedPreferences.getInstance();
      final key = SurgeryData.getKeyForSpecialty(widget.specialty);
      await prefs.setStringList(key, _surgeries);
    }
  }

  String? _selectedSurgery;

  /// iOS showSelected: method equivalent
  /// Shows confirmation dialog for selected surgery
  Future<void> _showSelectedDialog(String surgery) async {
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Image.asset(
              'assets/images/scissors.png',
              width: 24,
              height: 24,
              errorBuilder: (context, error, stackTrace) {
                return const Icon(Icons.content_cut, color: AppColors.jclOrange, size: 24);
              },
            ),
            const SizedBox(width: 12),
            const Expanded(child: Text('Selected Surgery')),
          ],
        ),
        content: Text(
          surgery,
          style: const TextStyle(fontSize: 16),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Edit', style: TextStyle(color: AppColors.jclOrange)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop(); // Close dialog
              // Return surgery selection to previous screen
              Navigator.of(context).pop({
                'specialty': widget.specialty,
                'surgery': surgery,
              });
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: const Color(0xFF483930), // jclTaupe
            ),
            child: const Text('Select'),
          ),
        ],
      ),
    );
  }

  Future<bool> _confirmCancellation() async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Case Creation?'),
        content: const Text('All entered data will be lost.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Continue', style: TextStyle(color: AppColors.jclOrange)),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Cancel Case'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvoked: (didPop) async {
        if (didPop) return;
        final shouldPop = await _confirmCancellation();
        if (shouldPop && context.mounted) {
          Navigator.of(context).pop();
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.jclWhite,
        appBar: AppBar(
          title: Text(
            widget.specialty,
            style: const TextStyle(color: AppColors.jclWhite, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          centerTitle: true,
          backgroundColor: AppColors.jclOrange,
          elevation: 0,
          iconTheme: const IconThemeData(color: AppColors.jclWhite),
          actions: [
            IconButton(
              icon: const Icon(Icons.add),
              onPressed: _showAddSurgeryDialog,
            ),
          ],
        ),
      body: Container(
        decoration: BoxDecoration(
          color: AppColors.jclWhite,
          image: DecorationImage(
            image: const AssetImage('assets/images/jcl_logo_dark.png'),
            fit: BoxFit.contain,
            opacity: 0.5,
          ),
        ),
        child: Column(
          children: [
            // Search bar
            Container(
              color: AppColors.jclWhite,
              padding: const EdgeInsets.all(16),
              child: TextField(
                controller: _searchController,
                style: const TextStyle(color: AppColors.jclGray),
                autocorrect: false,
                enableSuggestions: false,
                decoration: InputDecoration(
                  hintText: 'Search surgeries...',
                  prefixIcon: const Icon(Icons.search, color: AppColors.jclOrange),
                  filled: true,
                  fillColor: AppColors.jclGray.withOpacity(0.05),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide.none,
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide(color: AppColors.jclGray.withOpacity(0.2)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: const BorderSide(color: AppColors.jclOrange, width: 2),
                  ),
                ),
                onChanged: _filterSurgeries,
              ),
            ),

            // Surgery list
            Expanded(
              child: _filteredSurgeries.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.list, size: 64, color: Colors.grey),
                          const SizedBox(height: 16),
                          const Text('No surgeries found', style: TextStyle(color: Colors.grey)),
                          const SizedBox(height: 8),
                          ElevatedButton.icon(
                            onPressed: _showAddSurgeryDialog,
                            icon: const Icon(Icons.add),
                            label: const Text('Add Surgery'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.jclOrange,
                              foregroundColor: AppColors.jclWhite,
                            ),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      itemCount: _filteredSurgeries.length,
                      itemBuilder: (context, index) {
                        final surgery = _filteredSurgeries[index];
                        final isSelected = _selectedSurgery == surgery;
                        return Container(
                          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.jclWhite,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: ListTile(
                            title: Text(
                              surgery,
                              style: const TextStyle(color: AppColors.jclGray),
                            ),
                            trailing: isSelected
                                ? const Icon(Icons.check, color: AppColors.jclOrange)
                                : null,
                            onTap: () {
                              setState(() {
                                _selectedSurgery = surgery;
                              });
                              _showSelectedDialog(surgery);
                            },
                          ),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
      ),
    );
  }
}

/// Comprehensive Log Case View Screen (iOS buildLogCaseView equivalent)
/// Displays all case fields in a scrollable form with "Slide to Save" functionality
class _LogCaseViewScreen extends StatefulWidget {
  final DateTime initialDate;
  final String? initialFacility;
  final String? initialSurgeon;
  final String? initialSurgery;
  final String? initialSpecialty;
  final String? initialPrimaryAnesthesia;
  final String? initialSecondaryAnesthesia;
  final int? initialAge;
  final Function(Map<String, dynamic>) onSave;
  final List<String> facilities;
  final List<String> surgeons;

  const _LogCaseViewScreen({
    required this.initialDate,
    required this.initialFacility,
    required this.initialSurgeon,
    required this.initialSurgery,
    required this.initialSpecialty,
    this.initialPrimaryAnesthesia,
    this.initialSecondaryAnesthesia,
    this.initialAge,
    required this.onSave,
    required this.facilities,
    required this.surgeons,
  });

  @override
  State<_LogCaseViewScreen> createState() => _LogCaseViewScreenState();
}

class _LogCaseViewScreenState extends State<_LogCaseViewScreen> {
  late final TextEditingController _locationController;
  late final TextEditingController _surgeonController;
  late final TextEditingController _surgeryController;
  late final TextEditingController _primaryAnestheticController;
  late final TextEditingController _secondaryAnestheticController;
  late final TextEditingController _dateController;
  late final TextEditingController _ageController;

  String? _asaClassification;
  bool _asaEmergency = false;
  String? _gender;
  String _patientNotes = '';
  String _complications = '';
  String _comorbidities = '';
  String _skilledProcedures = '';
  double _sliderValue = 1.0;
  bool _showConfetti = false;

  @override
  void initState() {
    super.initState();
    _locationController = TextEditingController(text: widget.initialFacility);
    _surgeonController = TextEditingController(text: widget.initialSurgeon);
    _surgeryController = TextEditingController(text: widget.initialSurgery);
    _primaryAnestheticController = TextEditingController(text: widget.initialPrimaryAnesthesia ?? '');
    _secondaryAnestheticController = TextEditingController(text: widget.initialSecondaryAnesthesia ?? 'N/A');
    _dateController = TextEditingController(
      text: '${widget.initialDate.month}/${widget.initialDate.day}/${widget.initialDate.year}',
    );
    _ageController = TextEditingController(text: widget.initialAge?.toString() ?? '');
  }

  @override
  void dispose() {
    _locationController.dispose();
    _surgeonController.dispose();
    _surgeryController.dispose();
    _primaryAnestheticController.dispose();
    _secondaryAnestheticController.dispose();
    _dateController.dispose();
    _ageController.dispose();
    super.dispose();
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String placeholder,
    VoidCallback? onTap,
  }) {
    return Container(
      height: 40,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.jclWhite,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: TextField(
        controller: controller,
        autocorrect: false,
        enableSuggestions: false,
        readOnly: onTap != null,
        onTap: onTap,
        style: const TextStyle(color: AppColors.jclGray, fontSize: 16),
        decoration: InputDecoration(
          hintText: placeholder,
          hintStyle: TextStyle(color: Colors.grey.shade500),
          border: InputBorder.none,
        ),
      ),
    );
  }

  Future<void> _showAgePicker() async {
    int selectedAge = int.tryParse(_ageController.text) ?? 30; // Use current age or default to 30

    await showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.jclWhite,
      builder: (context) => Container(
        height: 300,
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const SizedBox(width: 60),
                  const Text('Patient Age', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: Colors.black)),
                  TextButton(
                    onPressed: () {
                      setState(() => _ageController.text = selectedAge.toString());
                      Navigator.of(context).pop();
                    },
                    child: const Text('Done', style: TextStyle(color: AppColors.jclOrange, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
            // iOS Age Picker
            Expanded(
              child: CupertinoPicker(
                itemExtent: 40,
                scrollController: FixedExtentScrollController(initialItem: selectedAge),
                onSelectedItemChanged: (int index) {
                  selectedAge = index;
                },
                children: List<Widget>.generate(121, (int index) {
                  return Center(
                    child: Text(
                      '$index',
                      style: const TextStyle(fontSize: 20, color: Colors.black),
                    ),
                  );
                }),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showFacilityPicker() async {
    String? selectedItem;

    final result = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: AppColors.jclWhite,
      isScrollControlled: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          height: MediaQuery.of(context).size.height * 0.7,
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              const Text('Select Facility', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.jclGray)),
              const SizedBox(height: 16),
              // Add New Facility Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final newFacility = await _showAddNewDialog(
                      context: context,
                      title: 'Add New Facility',
                      hint: 'Enter facility name',
                    );
                    if (newFacility != null && newFacility.isNotEmpty && context.mounted) {
                      Navigator.pop(context, newFacility);
                    }
                  },
                  icon: const Icon(Icons.add, color: AppColors.jclWhite),
                  label: const Text('Add New Facility', style: TextStyle(color: AppColors.jclWhite)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.jclOrange,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Divider(color: AppColors.jclGray),
              const SizedBox(height: 8),
              Expanded(
                child: widget.facilities.isEmpty
                    ? const Center(child: Text('No facilities saved yet', style: TextStyle(color: AppColors.jclGray)))
                    : ListView.builder(
                        itemCount: widget.facilities.length,
                        itemBuilder: (context, index) {
                          final facility = widget.facilities[index];
                          final isSelected = selectedItem == facility;
                          return ListTile(
                            title: Text(facility, style: const TextStyle(color: AppColors.jclGray)),
                            trailing: isSelected ? const Icon(Icons.check, color: AppColors.jclOrange) : null,
                            onTap: () {
                              setModalState(() => selectedItem = facility);
                            },
                          );
                        },
                      ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancel', style: TextStyle(color: AppColors.jclGray)),
                    ),
                  ),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(context, selectedItem),
                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.jclOrange),
                      child: const Text('Done', style: TextStyle(color: AppColors.jclWhite)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (result != null) {
      setState(() => _locationController.text = result);
    }
  }

  Future<void> _showSurgeonPicker() async {
    String? selectedItem;

    final result = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: AppColors.jclWhite,
      isScrollControlled: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          height: MediaQuery.of(context).size.height * 0.7,
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              const Text('Select Surgeon', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.jclGray)),
              const SizedBox(height: 16),
              // Add New Surgeon Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final newSurgeon = await _showAddNewDialog(
                      context: context,
                      title: 'Add New Surgeon',
                      hint: 'Enter surgeon name',
                    );
                    if (newSurgeon != null && newSurgeon.isNotEmpty && context.mounted) {
                      Navigator.pop(context, newSurgeon);
                    }
                  },
                  icon: const Icon(Icons.add, color: AppColors.jclWhite),
                  label: const Text('Add New Surgeon', style: TextStyle(color: AppColors.jclWhite)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.jclOrange,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Divider(color: AppColors.jclGray),
              const SizedBox(height: 8),
              Expanded(
                child: widget.surgeons.isEmpty
                    ? const Center(child: Text('No surgeons saved yet', style: TextStyle(color: AppColors.jclGray)))
                    : ListView.builder(
                        itemCount: widget.surgeons.length,
                        itemBuilder: (context, index) {
                          final surgeon = widget.surgeons[index];
                          final isSelected = selectedItem == surgeon;
                          return ListTile(
                            title: Text(surgeon, style: const TextStyle(color: AppColors.jclGray)),
                            trailing: isSelected ? const Icon(Icons.check, color: AppColors.jclOrange) : null,
                            onTap: () {
                              setModalState(() => selectedItem = surgeon);
                            },
                          );
                        },
                      ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancel', style: TextStyle(color: AppColors.jclGray)),
                    ),
                  ),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(context, selectedItem),
                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.jclOrange),
                      child: const Text('Done', style: TextStyle(color: AppColors.jclWhite)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (result != null) {
      setState(() => _surgeonController.text = result);
    }
  }

  Future<void> _showSurgeryPicker() async {
    final result = await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const _SurgerySelectionScreen(),
      ),
    );

    if (result != null && result is Map<String, String>) {
      setState(() {
        _surgeryController.text = result['surgery'] ?? '';
      });
    }
  }

  Future<void> _showDatePicker() async {
    final now = DateTime.now();
    DateTime selectedDate = now;

    await showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.jclWhite,
      builder: (context) => Container(
        height: 300,
        color: AppColors.jclWhite,
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.jclWhite,
                border: Border(
                  bottom: BorderSide(
                    color: AppColors.jclGray.withOpacity(0.2),
                  ),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text(
                      'Cancel',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ),
                  const Text(
                    'Select Date',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.jclGray,
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _dateController.text = '${selectedDate.month}/${selectedDate.day}/${selectedDate.year}';
                      });
                      Navigator.pop(context);
                    },
                    child: const Text(
                      'Done',
                      style: TextStyle(
                        color: AppColors.jclOrange,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: CupertinoDatePicker(
                mode: CupertinoDatePickerMode.date,
                initialDateTime: now,
                minimumDate: DateTime(now.year - 5),
                maximumDate: now,
                onDateTimeChanged: (DateTime newDate) {
                  selectedDate = newDate;
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showPrimaryAnestheticPicker() async {
    final result = await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const GeneralAnestheticSelectionScreen(isTIVA: false),
      ),
    );

    if (result != null && result is String) {
      setState(() {
        _primaryAnestheticController.text = result;
      });
    }
  }

  Future<void> _showSecondaryAnestheticPicker() async {
    final result = await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const RegionalAnestheticSelectionScreen(),
      ),
    );

    if (result != null && result is String) {
      setState(() {
        _secondaryAnestheticController.text = result.isEmpty ? 'N/A' : result;
      });
    }
  }

  void _showNotesDialog() {
    showDialog(
      context: context,
      builder: (context) {
        final controller = TextEditingController(text: _patientNotes);
        return AlertDialog(
          title: const Text('Patient Notes'),
          content: TextField(
            controller: controller,
            style: const TextStyle(color: AppColors.jclGray),
            maxLines: 10,
            autocorrect: false,
            enableSuggestions: false,
            decoration: const InputDecoration(
              hintText: 'Enter patient notes...',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                setState(() => _patientNotes = controller.text);
                Navigator.pop(context);
              },
              child: const Text('Save'),
            ),
          ],
        );
      },
    );
  }

  Future<String?> _showAddNewDialog({
    required BuildContext context,
    required String title,
    required String hint,
  }) async {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: Text(
            title,
            style: const TextStyle(color: AppColors.jclGray),
          ),
          content: TextField(
            controller: controller,
            style: const TextStyle(color: AppColors.jclGray),
            autocorrect: false,
            enableSuggestions: false,
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: TextStyle(color: AppColors.jclGray.withOpacity(0.5)),
              filled: true,
              fillColor: AppColors.jclGray.withOpacity(0.05),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 12,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide.none,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide(
                  color: AppColors.jclGray.withOpacity(0.2),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(
                  color: AppColors.jclOrange,
                  width: 2,
                ),
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text(
                'Cancel',
                style: TextStyle(color: AppColors.jclGray),
              ),
            ),
            ElevatedButton(
              onPressed: () {
                final text = controller.text.trim();
                Navigator.pop(context, text);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.jclOrange,
              ),
              child: const Text(
                'Add',
                style: TextStyle(color: AppColors.jclWhite),
              ),
            ),
          ],
        );
      },
    );
  }

  void _showComplicationsDialog() async {
    final result = await Navigator.of(context).push<List<String>>(
      MaterialPageRoute(
        builder: (_) => MultiSelectionScreen(
          title: 'Surgical Complications',
          defaultItems: ComplicationsData.defaultComplications,
          sharedPrefsKey: 'jclComplicationsArray',
          parseClassName: 'addedComplications',
          parseArrayKey: 'addedComplications',
        ),
      ),
    );

    if (result != null && result.isNotEmpty && mounted) {
      setState(() {
        _complications = result.join(', ');
      });
    }
  }

  void _showComorbiditiesDialog() async {
    final result = await Navigator.of(context).push<List<String>>(
      MaterialPageRoute(
        builder: (_) => MultiSelectionScreen(
          title: 'Comorbidities',
          defaultItems: ComorbiditiesData.defaultComorbidities,
          sharedPrefsKey: 'jclComoArray',
          parseClassName: 'savedComorbidities',
          parseArrayKey: 'userComorbid',
        ),
      ),
    );

    if (result != null && result.isNotEmpty && mounted) {
      setState(() {
        _comorbidities = result.join(', ');
      });
    }
  }

  /// Navigate to same skilled procedures list as Settings "Update Skill Set"
  void _showSkilledProceduresDialog() async {
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const SettingsUpdateScreen(updateType: 'skills'),
      ),
    );

    // Reload skilled procedures list after returning
    final prefs = await SharedPreferences.getInstance();
    final skills = prefs.getStringList('jclSkillsArray') ?? [];
    if (mounted && skills.isNotEmpty) {
      setState(() {
        _skilledProcedures = skills.join(', ');
      });
    }
  }

  /// iOS checkForBlanks: equivalent - validates required fields
  void _handleSliderEnd(double value) {
    if (value >= 9.5) {
      // Slider reached end - check for blanks before saving
      _checkForBlanks();
    } else {
      // Reset slider if not fully slid
      setState(() => _sliderValue = 1.0);
    }
  }

  /// iOS checkForBlanks: equivalent
  void _checkForBlanks() {
    if (_asaClassification == null || _asaClassification!.isEmpty) {
      _showASAAlert();
    } else if (_gender == null || _gender!.isEmpty) {
      _showGenderAlert();
    } else {
      // All required fields present - save the case with confetti
      _saveWithConfetti();
    }
  }

  /// Show alert for missing ASA classification
  void _showASAAlert() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: const Text(
          'Missing ASA Classification',
          style: TextStyle(color: AppColors.jclGray),
        ),
        content: const Text(
          'Please select an ASA classification before saving.',
          style: TextStyle(color: AppColors.jclGray),
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() => _sliderValue = 1.0);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  /// Show alert for missing gender
  void _showGenderAlert() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: const Text(
          'Missing Gender',
          style: TextStyle(color: AppColors.jclGray),
        ),
        content: const Text(
          'Please select a gender before saving.',
          style: TextStyle(color: AppColors.jclGray),
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() => _sliderValue = 1.0);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  /// iOS chooseFacilityList: equivalent - Show facility selection dialog
  void chooseFacilityList() {
    final asyncValue = ProviderScope.containerOf(context).read(facilityProvider);
    final facilities = asyncValue.asData?.value ?? [];

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return SingleSelectDialog(
          title: 'Select Facility',
          iconPath: 'assets/images/hospital-sign-40.png',
          items: facilities,
          onSelect: (selectedItem) {
            if (selectedItem == null) {
              Navigator.pop(context);
              // No selection made - could show location alert again if needed
            } else {
              setState(() {
                _locationController.text = selectedItem;
              });
              Navigator.pop(context);
              // Proceed to surgeon selection
              chooseSurgeonList();
            }
          },
          onBack: () {
            Navigator.pop(context);
            // Could show location alert here if needed
          },
        );
      },
    );
  }

  /// iOS chooseSurgeonList: equivalent - Show surgeon selection dialog
  void chooseSurgeonList() {
    final asyncValue = ProviderScope.containerOf(context).read(surgeonProvider);
    final surgeons = asyncValue.asData?.value ?? [];

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return SingleSelectDialog(
          title: 'Select Surgeon',
          iconPath: 'assets/images/doctor-40.png',
          items: surgeons,
          onSelect: (selectedItem) {
            if (selectedItem == null) {
              Navigator.pop(context);
              // No selection made - could show surgeon alert again if needed
            } else {
              setState(() {
                _surgeonController.text = selectedItem;
              });
              Navigator.pop(context);
              // Proceed to surgery selection
              // _showSurgerySelection(); // Uncomment when implementing surgery selection
            }
          },
          onBack: () {
            Navigator.pop(context);
            // Could show facility selection again
            chooseFacilityList();
          },
        );
      },
    );
  }

  /// iOS saveRecord: + triggerSnowfall equivalent
  Future<void> _saveWithConfetti() async {
    // Show confetti animation
    setState(() => _showConfetti = true);
    print('DEBUG: Confetti started');

    // Wait for confetti to start, then save
    await Future.delayed(const Duration(milliseconds: 500));

    // Save the case and wait for it to complete
    print('DEBUG: Starting database save...');
    await widget.onSave({
      'date': widget.initialDate,
      'location': _locationController.text,
      'surgeon': _surgeonController.text,
      'surgery': _surgeryController.text,
      'primaryAnesthetic': _primaryAnestheticController.text,
      'secondaryAnesthetic': _secondaryAnestheticController.text.trim().isEmpty
          ? 'n/a'
          : _secondaryAnestheticController.text,
      'age': _ageController.text, // Keep as string to match Parse schema
      'gender': _gender,
      'asaClassification': _asaClassification,
      'asaEmergency': _asaEmergency,
      'patientNotes': _patientNotes,
      'complications': _complications,
      'comorbidities': _comorbidities,
      'skilledProcedures': _skilledProcedures,
      'airwayManagement': null,
    });

    print('DEBUG: Database save completed successfully');
    // Database save completed successfully - wait 5 seconds before returning to homescreen
    print('DEBUG: Waiting 5 seconds before returning to homescreen...');
    await Future.delayed(const Duration(seconds: 5));
    print('DEBUG: 5 seconds elapsed, navigating back to homescreen');
    if (mounted) {
      Navigator.pop(context, true);
    }
  }

  /// Called when snowfall animation completes
  /// Matches iOS executeOrder66: dispatch_after delay
  void _onSnowfallComplete() {
    // Navigation is now handled in _saveWithConfetti after database save completes
    // This callback is kept for animation completion event
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final textFieldWidth = screenWidth * 0.85;

    return ConfettiWidget(
      showConfetti: _showConfetti,
      onAnimationComplete: _onSnowfallComplete,
      child: Scaffold(
        backgroundColor: AppColors.jclGray,
        appBar: AppBar(
        title: const Text(
          'Create New Case',
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
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () {
            showDialog(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Are you sure?'),
                content: const Text(
                  'If you \'cancel\' now, all the info you\'ve entered for this case record will be deleted.',
                ),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    style: TextButton.styleFrom(foregroundColor: Colors.green),
                    child: const Text('Continue Case'),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.pop(context); // Close dialog
                      Navigator.pop(context, false); // Close form
                    },
                    child: const Text('Cancel Case'),
                  ),
                ],
              ),
            );
          },
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Center(
          child: SizedBox(
            width: textFieldWidth,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Location
                _buildTextField(controller: _locationController, placeholder: 'Location', onTap: _showFacilityPicker),
                const SizedBox(height: 8),

                // Surgeon
                _buildTextField(controller: _surgeonController, placeholder: 'Surgeon', onTap: _showSurgeonPicker),
                const SizedBox(height: 8),

                // Surgery
                _buildTextField(controller: _surgeryController, placeholder: 'Surgery', onTap: _showSurgeryPicker),
                const SizedBox(height: 8),

                // Primary Anesthetic
                _buildTextField(controller: _primaryAnestheticController, placeholder: 'Primary Anesthetic', onTap: _showPrimaryAnestheticPicker),
                const SizedBox(height: 8),

                // Secondary Anesthetic
                _buildTextField(controller: _secondaryAnestheticController, placeholder: 'Secondary Anesthetic', onTap: _showPrimaryAnestheticPicker),
                const SizedBox(height: 8),

                // Surgery Date
                _buildTextField(controller: _dateController, placeholder: 'Surgery Date', onTap: _showDatePicker),
                const SizedBox(height: 20),

                // ASA Classification
                Row(
                  children: [
                    TextButton(
                      onPressed: () {
                        showDialog(
                          context: context,
                          builder: (context) => AlertDialog(
                            title: const Text('ASA Classification'),
                            content: const Text('American Society of Anesthesiologists Physical Status Classification System'),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(context),
                                child: const Text('OK'),
                              ),
                            ],
                          ),
                        );
                      },
                      child: const Text('ASA', style: TextStyle(color: AppColors.jclOrange)),
                    ),
                    Expanded(
                      child: CupertinoSegmentedControl<String>(
                        padding: EdgeInsets.zero,
                        groupValue: _asaClassification,
                        children: const {
                          'I': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('I')),
                          'II': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('II')),
                          'III': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('III')),
                          'IV': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('IV')),
                          'V': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('V')),
                          'VI': Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('VI')),
                        },
                        onValueChanged: (value) {
                          setState(() => _asaClassification = value);
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: _asaClassification != null
                          ? () {
                              setState(() => _asaEmergency = !_asaEmergency);
                            }
                          : null,
                      child: Opacity(
                        opacity: _asaClassification != null ? 1.0 : 0.5,
                        child: Container(
                          width: 40,
                          height: 32,
                          decoration: BoxDecoration(
                            color: _asaEmergency ? const Color.fromRGBO(238, 108, 97, 1.0) : AppColors.jclWhite,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.grey.shade400, width: 1),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.grey.shade600,
                                offset: const Offset(0, 3),
                                blurRadius: 3.0,
                                spreadRadius: 0,
                              ),
                            ],
                          ),
                          child: Center(
                            child: Text(
                              'E',
                              style: TextStyle(
                                color: _asaEmergency ? AppColors.jclWhite : AppColors.jclGray,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // Patient Info Label
                const Text(
                  'Patient Info',
                  style: TextStyle(color: AppColors.jclOrange, fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),

                // Age + Gender
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        height: 40,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: AppColors.jclWhite,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: TextField(
                          controller: _ageController,
                          keyboardType: TextInputType.number,
                          autocorrect: false,
                          enableSuggestions: false,
                          readOnly: true,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: AppColors.jclGray, fontSize: 16),
                          decoration: InputDecoration(
                            hintText: 'Age',
                            hintStyle: TextStyle(color: Colors.grey.shade500),
                            border: InputBorder.none,
                          ),
                          onTap: _showAgePicker,
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    // Male Button
                    GestureDetector(
                      onTap: () {
                        setState(() => _gender = 'Male');
                      },
                      child: Container(
                        width: 50,
                        height: 40,
                        decoration: BoxDecoration(
                          color: _gender == 'Male' ? Colors.blue : Colors.blue.shade100,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: _gender == 'Male' ? Colors.blue.shade700 : Colors.blue.shade300,
                            width: _gender == 'Male' ? 2 : 1,
                          ),
                          boxShadow: _gender == 'Male' ? [
                            BoxShadow(
                              color: Colors.blue.withOpacity(0.5),
                              blurRadius: 12,
                              spreadRadius: 3,
                            ),
                            BoxShadow(
                              color: Colors.blue.withOpacity(0.3),
                              blurRadius: 20,
                              spreadRadius: 5,
                            ),
                          ] : null,
                        ),
                        child: Center(
                          child: Icon(
                            Icons.male,
                            size: 20,
                            color: _gender == 'Male' ? AppColors.jclWhite : Colors.blue.shade700,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Female Button
                    GestureDetector(
                      onTap: () {
                        setState(() => _gender = 'Female');
                      },
                      child: Container(
                        width: 50,
                        height: 40,
                        decoration: BoxDecoration(
                          color: _gender == 'Female' ? Colors.pink : Colors.pink.shade100,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: _gender == 'Female' ? Colors.pink.shade700 : Colors.pink.shade300,
                            width: _gender == 'Female' ? 2 : 1,
                          ),
                          boxShadow: _gender == 'Female' ? [
                            BoxShadow(
                              color: Colors.pink.withOpacity(0.5),
                              blurRadius: 12,
                              spreadRadius: 3,
                            ),
                            BoxShadow(
                              color: Colors.pink.withOpacity(0.3),
                              blurRadius: 20,
                              spreadRadius: 5,
                            ),
                          ] : null,
                        ),
                        child: Center(
                          child: Icon(
                            Icons.female,
                            size: 20,
                            color: _gender == 'Female' ? AppColors.jclWhite : Colors.pink.shade700,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 30),

                // Notes + Complications Row
                Row(
                  children: [
                    Expanded(
                      child: GlowButton(
                        text: 'Notes',
                        onPressed: _showNotesDialog,
                        isPrimary: true,
                        isFullWidth: true,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: GlowButton(
                        text: 'Complications',
                        onPressed: _showComplicationsDialog,
                        isPrimary: true,
                        isFullWidth: true,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Comorbidities + Skilled Procedures Row
                Row(
                  children: [
                    Expanded(
                      child: GlowButton(
                        text: 'Comorbidities',
                        onPressed: _showComorbiditiesDialog,
                        isPrimary: true,
                        isFullWidth: true,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: GlowButton(
                        text: 'Skilled Procedures',
                        onPressed: _showSkilledProceduresDialog,
                        isPrimary: true,
                        isFullWidth: true,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 60),

                // Slide to Save Label
                const Text(
                  'Slide to Save',
                  style: TextStyle(color: AppColors.jclOrange, fontSize: 10),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),

                // Slide to Save Slider
                SliderTheme(
                  data: SliderTheme.of(context).copyWith(
                    trackHeight: 16,
                    thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 16),
                    overlayShape: const RoundSliderOverlayShape(overlayRadius: 24),
                    activeTrackColor: AppColors.jclWhite,
                    inactiveTrackColor: AppColors.jclWhite,
                    thumbColor: AppColors.jclOrange,
                  ),
                  child: Slider(
                    value: _sliderValue,
                    min: 1.0,
                    max: 10.0,
                    onChanged: (value) {
                      setState(() => _sliderValue = value);
                    },
                    onChangeEnd: _handleSliderEnd,
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

/// Background Screen for Case Builder Wizard
/// Shows "Create New Case" title with context information (date/facility) visible behind modal pickers
class _CaseBuilderBackgroundScreen extends StatefulWidget {
  final DateTime date;
  final String? facility;
  final Function(BuildContext) onReady;

  const _CaseBuilderBackgroundScreen({
    required this.date,
    required this.facility,
    required this.onReady,
  });

  @override
  State<_CaseBuilderBackgroundScreen> createState() => _CaseBuilderBackgroundScreenState();
}

class _CaseBuilderBackgroundScreenState extends State<_CaseBuilderBackgroundScreen> {
  String? _currentFacility;

  @override
  void initState() {
    super.initState();
    _currentFacility = widget.facility;

    // Show the picker after the screen is built
    WidgetsBinding.instance.addPostFrameCallback((_) {
      widget.onReady(context);
    });
  }

  void updateFacility(String? facility) {
    setState(() {
      _currentFacility = facility;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Create New Case',
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
        automaticallyImplyLeading: false,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              // Case date display
              Text(
                '${widget.date.month}/${widget.date.day}/${widget.date.year}',
                style: const TextStyle(
                  color: AppColors.jclOrange,
                  fontSize: 20,
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
              ),

              // Facility display (shown when facility is selected)
              if (_currentFacility != null) ...[
                const SizedBox(height: 8),
                Text(
                  _currentFacility!,
                  style: const TextStyle(
                    color: AppColors.jclOrange,
                    fontSize: 20,
                    fontWeight: FontWeight.w500,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
