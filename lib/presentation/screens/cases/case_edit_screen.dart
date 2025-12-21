import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../domain/entities/case.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/facility_provider.dart';
import '../../providers/surgeon_provider.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/constants/app_constants.dart';
import '../../../data/models/case_model.dart';
import 'general_anesthetic_selection_screen.dart';
import 'regional_anesthetic_selection_screen.dart';
import '../../widgets/confetti_widget.dart';
import '../surgeries/surgeries_selection_screen.dart';

class CaseEditScreen extends ConsumerStatefulWidget {
  final Case caseToEdit;

  const CaseEditScreen({
    super.key,
    required this.caseToEdit,
  });

  @override
  ConsumerState<CaseEditScreen> createState() => _CaseEditScreenState();
}

class _CaseEditScreenState extends ConsumerState<CaseEditScreen> {
  late final TextEditingController _locationController;
  late final TextEditingController _surgeonController;
  late final TextEditingController _surgeryController;
  late final TextEditingController _primaryAnestheticController;
  late final TextEditingController _secondaryAnestheticController;
  late final TextEditingController _dateController;
  late final TextEditingController _ageController;
  late final TextEditingController _airwayController;
  late final TextEditingController _notesController;

  late DateTime _selectedDate;
  String? _selectedGender;
  String? _selectedASA;
  bool _asaEmergency = false;
  bool _hasComplications = false;
  String? _selectedSurgeryClass;
  List<String> _selectedAnesthetics = [];

  bool _isSaving = false;
  double _sliderValue = 1.0;
  bool _showConfetti = false;

  @override
  void initState() {
    super.initState();

    // Initialize controllers with existing case data
    _locationController =
        TextEditingController(text: widget.caseToEdit.location ?? '');
    _surgeonController =
        TextEditingController(text: widget.caseToEdit.surgeonName ?? '');
    _surgeryController =
        TextEditingController(text: widget.caseToEdit.procedureSurgery ?? '');
    _primaryAnestheticController =
        TextEditingController(text: widget.caseToEdit.anestheticPlan ?? '');
    // Secondary anesthetic should always show a value, default to 'N/A' if empty
    _secondaryAnestheticController = TextEditingController(
        text: widget.caseToEdit.secondaryAnesthetic?.isNotEmpty == true
            ? widget.caseToEdit.secondaryAnesthetic!
            : 'N/A');
    _selectedDate = widget.caseToEdit.date;
    _dateController = TextEditingController(
        text: DateFormat('M/d/yyyy').format(_selectedDate));
    _ageController = TextEditingController(
        text: widget.caseToEdit.patientAge?.toString() ?? '');
    _airwayController =
        TextEditingController(text: widget.caseToEdit.airwayManagement ?? '');
    _notesController =
        TextEditingController(text: widget.caseToEdit.additionalComments ?? '');

    _selectedGender = widget.caseToEdit.gender;
    _selectedASA = widget.caseToEdit.asaClassification;
    _hasComplications = widget.caseToEdit.complications ?? false;
    _selectedSurgeryClass = widget.caseToEdit.surgeryClass;
    _selectedAnesthetics = List.from(widget.caseToEdit.anestheticsUsed);

    // Check if ASA has emergency designation
    if (_selectedASA?.endsWith('E') ?? false) {
      _asaEmergency = true;
      _selectedASA =
          _selectedASA!.replaceAll('E', '').replaceAll(',', '').trim();
    }
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
    _airwayController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    DateTime selectedDate = _selectedDate;

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
                border:
                    Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel',
                        style: TextStyle(color: AppColors.jclOrange)),
                  ),
                  const Text('Select Date',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 17,
                          color: Colors.black)),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _selectedDate = selectedDate;
                        _dateController.text =
                            DateFormat('M/d/yyyy').format(selectedDate);
                      });
                      Navigator.of(context).pop();
                    },
                    child: const Text('Done',
                        style: TextStyle(
                            color: AppColors.jclOrange,
                            fontWeight: FontWeight.bold)),
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
  }

  Future<void> _showAgePicker() async {
    // Parse current age or default to 30
    int selectedAge = int.tryParse(_ageController.text.trim()) ?? 30;

    // Ensure age is within valid range
    if (selectedAge < 0) selectedAge = 0;
    if (selectedAge > 120) selectedAge = 120;

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
                border:
                    Border(bottom: BorderSide(color: Colors.grey, width: 0.5)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel',
                        style: TextStyle(color: AppColors.jclOrange)),
                  ),
                  const Text('Select Age',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 17,
                          color: Colors.black)),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _ageController.text = selectedAge.toString();
                      });
                      Navigator.of(context).pop();
                    },
                    child: const Text('Done',
                        style: TextStyle(
                            color: AppColors.jclOrange,
                            fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
            // iOS Age Picker
            Expanded(
              child: CupertinoPicker(
                scrollController: FixedExtentScrollController(
                  initialItem: selectedAge,
                ),
                itemExtent: 40,
                onSelectedItemChanged: (int index) {
                  selectedAge = index;
                },
                children: List<Widget>.generate(121, (int index) {
                  return Center(
                    child: Text(
                      index.toString(),
                      style: const TextStyle(fontSize: 22),
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
  }

  /// iOS chooseMainPlan: equivalent for Edit screen
  /// Shows primary or secondary anesthesia plan selection dialog
  Future<void> _showAnestheticPlanPicker(bool isPrimary) async {
    final controller = isPrimary
        ? _primaryAnestheticController
        : _secondaryAnestheticController;

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
              const SizedBox(width: 8),
              Text(
                isPrimary ? 'Primary Anesthetic Plan' : 'Secondary Anesthetic Plan',
                style: const TextStyle(color: AppColors.jclGray, fontSize: 18),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: primeArray.map((plan) {
              final isSelected = selectedPlan == plan;
              return ListTile(
                tileColor: AppColors.jclWhite,
                title: Text(plan, style: const TextStyle(color: AppColors.jclGray)),
                trailing: isSelected
                    ? const Icon(Icons.check_circle, color: AppColors.jclOrange)
                    : null,
                selected: isSelected,
                selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                onTap: () {
                  setDialogState(() {
                    selectedPlan = plan;
                  });
                },
              );
            }).toList(),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(null),
              child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
            ),
            ElevatedButton(
              onPressed: selectedPlan == null
                  ? null
                  : () => Navigator.of(context).pop(selectedPlan),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: AppColors.jclWhite,
              ),
              child: const Text('Continue'),
            ),
          ],
        ),
      ),
    );

    // iOS chooseMainPlan: logic for each plan type
    if (result != null && mounted) {
      setState(() {
        controller.text = result;
      });

      // Navigate based on selection (iOS showGenAnes/showRegAnes logic)
      switch (result) {
        case 'General Anesthetic':
          // Show GA options
          await _showGeneralAnestheticOptions(isPrimary: isPrimary);
          break;
        case 'Regional Anesthetic':
          // Show regional options
          await _showRegionalAnestheticOptions(isPrimary: isPrimary);
          break;
        case 'MAC':
        case 'TIVA':
          // iOS checkAirwayAlert: logic
          final wantsAirway = await showDialog<bool>(
            context: context,
            barrierDismissible: false,
            builder: (context) => AlertDialog(
              backgroundColor: AppColors.jclWhite,
              title: const Text('Add Airway Management?', style: TextStyle(color: AppColors.jclGray)),
              content: const Text('Would you like to add airway management details?', style: TextStyle(color: AppColors.jclGray)),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('No', style: TextStyle(color: Colors.grey)),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.of(context).pop(true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: AppColors.jclWhite,
                  ),
                  child: const Text('Yes'),
                ),
              ],
            ),
          );

          if (wantsAirway == true && mounted) {
            // Show GA options with TIVA flag
            await _showGeneralAnestheticOptions(isPrimary: isPrimary, isTIVA: result == 'TIVA');
          }
          break;
      }
    }
  }

  /// iOS showGenAnes: equivalent - Show General Anesthetic options screen
  Future<void> _showGeneralAnestheticOptions({required bool isPrimary, bool isTIVA = false}) async {
    final controller = isPrimary
        ? _primaryAnestheticController
        : _secondaryAnestheticController;

    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => GeneralAnestheticSelectionScreen(isTIVA: isTIVA),
      ),
    );

    if (result != null && result.isNotEmpty) {
      setState(() {
        controller.text = result;
      });
    }
  }

  /// iOS showRegAnes: equivalent - Show Regional Anesthetic options screen
  Future<void> _showRegionalAnestheticOptions({required bool isPrimary}) async {
    final controller = isPrimary
        ? _primaryAnestheticController
        : _secondaryAnestheticController;

    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => const RegionalAnestheticSelectionScreen(),
      ),
    );

    if (result != null && result.isNotEmpty) {
      setState(() {
        controller.text = result;
      });
    }
  }

  /// iOS chooseFacilityList: equivalent
  Future<void> _showLocationPicker() async {
    final facilityAsync = ref.read(facilityProvider);
    List<String> facilities = facilityAsync.when(
      data: (data) => data.toList(),
      loading: () => <String>[],
      error: (_, __) => <String>[],
    );

    String? selected = _locationController.text.isNotEmpty ? _locationController.text : null;

    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
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
                      final newFacility = await _showAddItemDialog('Add New Facility', 'Enter facility name');
                      if (newFacility != null && newFacility.isNotEmpty) {
                        setDialogState(() {
                          if (!facilities.contains(newFacility)) {
                            facilities.add(newFacility);
                            facilities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
                          }
                          selected = newFacility;
                        });
                        // Add to provider
                        ref.read(facilityProvider.notifier).addFacility(newFacility);
                      }
                    },
                  ),
                  const Divider(),
                  // Facilities list
                  Flexible(
                    child: facilities.isEmpty
                        ? const Center(child: Text('No facilities found'))
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
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(null),
                child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
              ),
              ElevatedButton(
                onPressed: selected == null || selected!.isEmpty
                    ? null
                    : () => Navigator.of(context).pop(selected),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: AppColors.jclWhite,
                ),
                child: const Text('Select'),
              ),
            ],
          ),
        );
      },
    );

    if (result != null && result.isNotEmpty) {
      setState(() {
        _locationController.text = result;
      });
    }
  }

  /// iOS chooseSurgeonList: equivalent
  Future<void> _showSurgeonPicker() async {
    final surgeonAsync = ref.read(surgeonProvider);
    List<String> surgeons = surgeonAsync.when(
      data: (data) => data.toList(),
      loading: () => <String>[],
      error: (_, __) => <String>[],
    );

    String? selected = _surgeonController.text.isNotEmpty ? _surgeonController.text : null;

    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
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
                      final newSurgeon = await _showAddItemDialog('Add New Surgeon', 'Enter surgeon name');
                      if (newSurgeon != null && newSurgeon.isNotEmpty) {
                        setDialogState(() {
                          if (!surgeons.contains(newSurgeon)) {
                            surgeons.add(newSurgeon);
                            surgeons.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
                          }
                          selected = newSurgeon;
                        });
                        // Add to provider
                        ref.read(surgeonProvider.notifier).addSurgeon(newSurgeon);
                      }
                    },
                  ),
                  const Divider(),
                  // Surgeons list
                  Flexible(
                    child: surgeons.isEmpty
                        ? const Center(child: Text('No surgeons found'))
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
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(null),
                child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
              ),
              ElevatedButton(
                onPressed: selected == null || selected!.isEmpty
                    ? null
                    : () => Navigator.of(context).pop(selected),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: AppColors.jclWhite,
                ),
                child: const Text('Select'),
              ),
            ],
          ),
        );
      },
    );

    if (result != null && result.isNotEmpty) {
      setState(() {
        _surgeonController.text = result;
      });
    }
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

  Future<void> _showSurgeryPicker() async {
    final result = await Navigator.of(context).push<Map<String, dynamic>>(
      MaterialPageRoute(
        builder: (context) => const SurgeriesSelectionScreen(),
      ),
    );

    if (result != null && result['surgery'] != null) {
      setState(() {
        _surgeryController.text = result['surgery'] as String;
        _selectedSurgeryClass = result['surgeryCategory'] as String?;
      });

      // Also update the image name based on the surgery class
      if (_selectedSurgeryClass != null) {
        final imageName = CaseModel.getImageNameForSurgeryClass(_selectedSurgeryClass);
        // Store imageName if needed for the case update
      }
    }
  }

  void _showSurgeryClassPicker() {
    String? selected = _selectedSurgeryClass;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Cancel',
                              style: TextStyle(color: AppColors.jclOrange)),
                        ),
                        const Text('Select Surgery Class',
                            style: TextStyle(
                                fontSize: 18, fontWeight: FontWeight.bold)),
                        TextButton(
                          onPressed: () {
                            if (selected != null) {
                              setState(() {
                                _selectedSurgeryClass = selected;
                              });
                            }
                            Navigator.of(context).pop();
                          },
                          child: const Text('Done',
                              style: TextStyle(
                                  color: AppColors.jclOrange,
                                  fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                  ),
                  const Divider(),
                  Flexible(
                    child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: AppConstants.surgeryClasses.length,
                      itemBuilder: (context, index) {
                        final surgeryClass = AppConstants.surgeryClasses[index];
                        final isSelected = selected == surgeryClass;
                        return ListTile(
                          title: Text(surgeryClass),
                          trailing: isSelected
                              ? const Icon(Icons.check_circle,
                                  color: AppColors.jclOrange)
                              : null,
                          onTap: () {
                            setModalState(() {
                              selected = surgeryClass;
                            });
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }


  Future<void> _handleUpdate() async {
    final user = ref.read(currentUserProvider);

    if (user == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('User not logged in'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    // Validate required fields
    if (_locationController.text.trim().isEmpty ||
        _surgeryController.text.trim().isEmpty ||
        _selectedSurgeryClass == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please complete all required fields'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    final notifier = ref.read(caseDetailProvider.notifier);

    // Build ASA string with emergency designation if needed
    String? asaString = _selectedASA;
    if (_asaEmergency && asaString != null) {
      asaString = '$asaString, E';
    }

    final success = await notifier.updateCase(
      caseId: widget.caseToEdit.objectId,
      date: _selectedDate,
      patientAge: _ageController.text.trim().isEmpty
          ? null
          : _ageController.text.trim(),
      gender: _selectedGender,
      asaClassification: asaString,
      procedureSurgery: _surgeryController.text.trim(),
      anestheticPlan: _primaryAnestheticController.text.trim(),
      secondaryAnesthetic: _secondaryAnestheticController.text.trim().isEmpty
          ? null
          : _secondaryAnestheticController.text.trim(),
      anestheticsUsed: _selectedAnesthetics,
      surgeryClass: _selectedSurgeryClass,
      location: _locationController.text.trim(),
      airwayManagement: _airwayController.text.trim().isEmpty
          ? null
          : _airwayController.text.trim(),
      additionalComments: _notesController.text.trim().isEmpty
          ? null
          : _notesController.text.trim(),
      complications: _hasComplications,
      imageName: _selectedSurgeryClass != null
          ? CaseModel.getImageNameForSurgeryClass(_selectedSurgeryClass!)
          : null,
    );

    setState(() {
      _isSaving = false;
    });

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Case updated successfully'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.of(context).pop();
      } else {
        final state = ref.read(caseDetailProvider);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.error ?? 'Failed to update case'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _handleSliderEnd(double value) {
    if (value >= 9.5) {
      // Slider reached end - trigger confetti and save
      setState(() => _showConfetti = true);
      _handleUpdate();
    } else {
      // Reset slider if not fully slid
      setState(() => _sliderValue = 1.0);
    }
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String placeholder,
    VoidCallback? onTap,
    bool readOnly = false,
  }) {
    return Container(
      height: 40,
      decoration: BoxDecoration(
        color: AppColors.jclWhite,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.jclGray.withOpacity(0.3)),
      ),
      child: TextField(
        controller: controller,
        readOnly: readOnly,
        onTap: onTap,
        decoration: InputDecoration(
          hintText: placeholder,
          hintStyle: TextStyle(color: AppColors.jclGray.withOpacity(0.5)),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          border: InputBorder.none,
        ),
        style: const TextStyle(color: AppColors.jclGray),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final textFieldWidth = screenWidth * 0.8;

    return ConfettiWidget(
      showConfetti: _showConfetti,
      onAnimationComplete: () {
        setState(() => _showConfetti = false);
      },
      child: Scaffold(
        backgroundColor: AppColors.jclGray,
        appBar: AppBar(
          title: const Text(
            'Edit Case',
            style: TextStyle(color: AppColors.jclWhite),
          ),
          centerTitle: true,
          backgroundColor: AppColors.jclOrange,
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
                      onPressed: () => Navigator.of(context).pop(),
                      style: TextButton.styleFrom(
                        foregroundColor: Colors.green,
                      ),
                      child: const Text('Continue Case'),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.of(context).pop(); // Close dialog
                        Navigator.of(context).pop(); // Close edit screen
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
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Center(
            child: SizedBox(
              width: textFieldWidth,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Location
                  _buildTextField(
                    controller: _locationController,
                    placeholder: 'Location',
                    readOnly: true,
                    onTap: _showLocationPicker,
                  ),
                  const SizedBox(height: 8),

                  // Surgeon
                  _buildTextField(
                    controller: _surgeonController,
                    placeholder: 'Surgeon',
                    readOnly: true,
                    onTap: _showSurgeonPicker,
                  ),
                  const SizedBox(height: 8),

                  // Surgery
                  _buildTextField(
                    controller: _surgeryController,
                    placeholder: 'Surgery',
                    readOnly: true,
                    onTap: _showSurgeryPicker,
                  ),
                  const SizedBox(height: 8),

                  // Primary Anesthetic
                  _buildTextField(
                    controller: _primaryAnestheticController,
                    placeholder: 'Primary Anesthetic',
                    readOnly: true,
                    onTap: () => _showAnestheticPlanPicker(true),
                  ),
                  const SizedBox(height: 8),

                  // Secondary Anesthetic
                  _buildTextField(
                    controller: _secondaryAnestheticController,
                    placeholder: 'Secondary Anesthetic',
                    readOnly: true,
                    onTap: () => _showAnestheticPlanPicker(false),
                  ),
                  const SizedBox(height: 8),

                  // Surgery Date
                  _buildTextField(
                    controller: _dateController,
                    placeholder: 'Surgery Date',
                    readOnly: true,
                    onTap: _selectDate,
                  ),
                  const SizedBox(height: 20),

                  // ASA Stack View
                  Row(
                    children: [
                      TextButton(
                        onPressed: () {
                          showDialog(
                            context: context,
                            builder: (context) => AlertDialog(
                              title: const Text('ASA Classification'),
                              content: const Text(
                                  'American Society of Anesthesiologists Physical Status Classification System'),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context),
                                  child: const Text('OK'),
                                ),
                              ],
                            ),
                          );
                        },
                        child: const Text('ASA',
                            style: TextStyle(color: AppColors.jclOrange)),
                      ),
                      Expanded(
                        child: CupertinoSegmentedControl<String>(
                          padding: EdgeInsets.zero,
                          groupValue: _selectedASA,
                          children: const {
                            'I': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('I')),
                            'II': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('II')),
                            'III': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('III')),
                            'IV': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('IV')),
                            'V': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('V')),
                            'VI': Padding(
                                padding: EdgeInsets.symmetric(horizontal: 8),
                                child: Text('VI')),
                          },
                          onValueChanged: (value) {
                            setState(() => _selectedASA = value);
                          },
                        ),
                      ),
                      const SizedBox(width: 8),
                      GestureDetector(
                        onTap: _selectedASA != null
                            ? () {
                                setState(() => _asaEmergency = !_asaEmergency);
                              }
                            : null,
                        child: Opacity(
                          opacity: _selectedASA != null ? 1.0 : 0.5,
                          child: Container(
                            width: 40,
                            height: 32,
                            decoration: BoxDecoration(
                              color: _asaEmergency
                                  ? const Color.fromRGBO(238, 108, 97, 1.0)
                                  : AppColors.jclWhite,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                  color: Colors.grey.shade400, width: 1),
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
                                  color: _asaEmergency
                                      ? AppColors.jclWhite
                                      : AppColors.jclGray,
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
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppColors.jclOrange,
                      fontSize: 12,
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Patient Info Stack (Age + Gender)
                  Row(
                    children: [
                      // Age Field
                      Expanded(
                        child: Container(
                          height: 34,
                          decoration: BoxDecoration(
                            color: AppColors.jclWhite,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                                color: AppColors.jclGray.withOpacity(0.3)),
                          ),
                          child: TextField(
                            controller: _ageController,
                            readOnly: true,
                            onTap: _showAgePicker,
                            textAlign: TextAlign.center,
                            decoration: InputDecoration(
                              hintText: 'Age',
                              hintStyle: TextStyle(
                                  color: AppColors.jclGray.withOpacity(0.5)),
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 8),
                              border: InputBorder.none,
                            ),
                            style: const TextStyle(color: AppColors.jclGray),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),

                      // Male Button
                      GestureDetector(
                        onTap: () {
                          setState(() => _selectedGender = 'Male');
                        },
                        child: Container(
                          width: 50,
                          height: 40,
                          decoration: BoxDecoration(
                            color: _selectedGender == 'Male'
                                ? Colors.blue
                                : Colors.blue.shade100,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: _selectedGender == 'Male'
                                  ? Colors.blue.shade700
                                  : Colors.blue.shade300,
                              width: _selectedGender == 'Male' ? 2 : 1,
                            ),
                            boxShadow: _selectedGender == 'Male'
                                ? [
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
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Icon(
                              Icons.male,
                              size: 20,
                              color: _selectedGender == 'Male'
                                  ? AppColors.jclWhite
                                  : Colors.blue.shade700,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      // Female Button
                      GestureDetector(
                        onTap: () {
                          setState(() => _selectedGender = 'Female');
                        },
                        child: Container(
                          width: 50,
                          height: 40,
                          decoration: BoxDecoration(
                            color: _selectedGender == 'Female'
                                ? Colors.pink
                                : Colors.pink.shade100,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: _selectedGender == 'Female'
                                  ? Colors.pink.shade700
                                  : Colors.pink.shade300,
                              width: _selectedGender == 'Female' ? 2 : 1,
                            ),
                            boxShadow: _selectedGender == 'Female'
                                ? [
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
                                  ]
                                : null,
                          ),
                          child: Center(
                            child: Icon(
                              Icons.female,
                              size: 20,
                              color: _selectedGender == 'Female'
                                  ? AppColors.jclWhite
                                  : Colors.pink.shade700,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 30),

                  // Notes and Complications Buttons
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            showDialog(
                              context: context,
                              builder: (context) {
                                final tempController = TextEditingController(
                                    text: _notesController.text);
                                return AlertDialog(
                                  title: const Text('Add Patient Notes'),
                                  content: TextField(
                                    controller: tempController,
                                    maxLines: 5,
                                    decoration: const InputDecoration(
                                      hintText: 'Enter notes...',
                                      border: OutlineInputBorder(),
                                    ),
                                  ),
                                  actions: [
                                    TextButton(
                                      onPressed: () =>
                                          Navigator.of(context).pop(),
                                      child: const Text('Cancel'),
                                    ),
                                    TextButton(
                                      onPressed: () {
                                        setState(() {
                                          _notesController.text =
                                              tempController.text;
                                        });
                                        Navigator.of(context).pop();
                                      },
                                      child: const Text('Save'),
                                    ),
                                  ],
                                );
                              },
                            );
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.jclOrange,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          child: const Text(
                            'Notes',
                            style: TextStyle(color: AppColors.jclWhite),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            setState(() {
                              _hasComplications = !_hasComplications;
                            });
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _hasComplications
                                ? Colors.red
                                : AppColors.jclOrange,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          child: const Text(
                            'Complications',
                            style: TextStyle(color: AppColors.jclWhite),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),

                  // Comorbidities and Skills Buttons (Placeholder)
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            // Placeholder for comorbidities
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.jclOrange,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          child: const Text(
                            'Comorbidities',
                            style: TextStyle(color: AppColors.jclWhite),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () {
                            // Placeholder for skilled procedures
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.jclOrange,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          child: const Text(
                            'Skilled Procedures',
                            style: TextStyle(
                                color: AppColors.jclWhite, fontSize: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 40),

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
                      thumbShape:
                          const RoundSliderThumbShape(enabledThumbRadius: 16),
                      overlayShape:
                          const RoundSliderOverlayShape(overlayRadius: 24),
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
