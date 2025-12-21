import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../providers/case_form_provider.dart';
import '../../../providers/facility_provider.dart';
import '../../../providers/surgeon_provider.dart';
import '../../../../core/themes/app_colors.dart';

class Step1BasicInfo extends ConsumerStatefulWidget {
  const Step1BasicInfo({super.key});

  @override
  ConsumerState<Step1BasicInfo> createState() => _Step1BasicInfoState();
}

class _Step1BasicInfoState extends ConsumerState<Step1BasicInfo> {
  late final TextEditingController _locationController;
  late final TextEditingController _surgeonController;

  @override
  void initState() {
    super.initState();
    final formData = ref.read(caseFormProvider).formData;
    _locationController = TextEditingController(text: formData.location ?? '');
    _surgeonController = TextEditingController(text: formData.surgeon ?? '');
  }

  @override
  void dispose() {
    _locationController.dispose();
    _surgeonController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final formData = ref.read(caseFormProvider).formData;

    final picked = await showDatePicker(
      context: context,
      initialDate: formData.date,
      firstDate: DateTime(2000),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.jclOrange,
              onPrimary: AppColors.jclWhite,
              onSurface: AppColors.jclGray,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      ref.read(caseFormProvider.notifier).updateFormData(
            formData.copyWith(date: picked),
          );
    }
  }

  void _updateFormData() {
    final formData = ref.read(caseFormProvider).formData;
    ref.read(caseFormProvider.notifier).updateFormData(
          formData.copyWith(
            location: _locationController.text.trim(),
            surgeon: _surgeonController.text.trim(),
          ),
        );
  }

  void _showFacilityPicker() {
    final facilityAsync = ref.read(facilityProvider);
    print('DEBUG FACILITY PICKER: AsyncValue state: ${facilityAsync.toString()}');

    // Extract facilities from AsyncValue
    final facilities = facilityAsync.when(
      data: (data) {
        print('DEBUG FACILITY PICKER: Got ${data.length} facilities: $data');
        return data;
      },
      loading: () {
        print('DEBUG FACILITY PICKER: Still loading...');
        return <String>[];
      },
      error: (error, stack) {
        print('DEBUG FACILITY PICKER: Error: $error');
        return <String>[];
      },
    );

    final formData = ref.read(caseFormProvider).formData;
    String? selected = formData.location;

    print('DEBUG MODAL: Opening facility picker with white background');
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
              color: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text(
                            'Cancel',
                            style: TextStyle(color: AppColors.jclOrange),
                          ),
                        ),
                        const Text(
                          'Select Facility',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            if (selected != null && selected!.isNotEmpty) {
                              _locationController.text = selected!;
                              _updateFormData();
                            }
                            Navigator.of(context).pop();
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
                  const Divider(),

                  // Facility List
                  Flexible(
                    child: Container(
                      decoration: const BoxDecoration(
                        color: Colors.white,
                      ),
                      child: facilities.isEmpty
                          ? const Padding(
                              padding: EdgeInsets.all(32.0),
                              child: Center(
                                child: Text(
                                  'No facilities found',
                                  style: TextStyle(color: AppColors.jclGray),
                                ),
                              ),
                            )
                          : ListView.builder(
                              shrinkWrap: true,
                              itemCount: facilities.length,
                              itemBuilder: (context, index) {
                                final facility = facilities[index];
                                final isSelected = selected == facility;

                                return Material(
                                  color: Colors.white,
                                  child: InkWell(
                                    onTap: () {
                                      setModalState(() {
                                        selected = facility;
                                      });
                                    },
                                    child: Container(
                                      color: Colors.white,
                                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            facility,
                                            style: const TextStyle(
                                              color: AppColors.jclGray,
                                              fontSize: 16,
                                            ),
                                          ),
                                          if (isSelected)
                                            const Icon(
                                              Icons.check,
                                              color: AppColors.jclOrange,
                                            ),
                                        ],
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
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

  void _showSurgeonPicker() {
    final surgeonAsync = ref.read(surgeonProvider);
    print('DEBUG SURGEON PICKER: AsyncValue state: ${surgeonAsync.toString()}');

    // Extract surgeons from AsyncValue
    final surgeons = surgeonAsync.when(
      data: (data) {
        print('DEBUG SURGEON PICKER: Got ${data.length} surgeons: $data');
        return data;
      },
      loading: () {
        print('DEBUG SURGEON PICKER: Still loading...');
        return <String>[];
      },
      error: (error, stack) {
        print('DEBUG SURGEON PICKER: Error: $error');
        return <String>[];
      },
    );

    final formData = ref.read(caseFormProvider).formData;
    String? selected = formData.surgeon;

    print('DEBUG MODAL: Opening surgeon picker with white background');
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
              color: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text(
                            'Cancel',
                            style: TextStyle(color: AppColors.jclOrange),
                          ),
                        ),
                        const Text(
                          'Select Surgeon',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            if (selected != null && selected!.isNotEmpty) {
                              _surgeonController.text = selected!;
                              _updateFormData();
                            }
                            Navigator.of(context).pop();
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
                  const Divider(),

                  // Surgeon List
                  Flexible(
                    child: Container(
                      decoration: const BoxDecoration(
                        color: Colors.white,
                      ),
                      child: surgeons.isEmpty
                          ? const Padding(
                              padding: EdgeInsets.all(32.0),
                              child: Center(
                                child: Text(
                                  'No surgeons found',
                                  style: TextStyle(color: AppColors.jclGray),
                                ),
                              ),
                            )
                          : ListView.builder(
                              shrinkWrap: true,
                              itemCount: surgeons.length,
                              itemBuilder: (context, index) {
                                final surgeon = surgeons[index];
                                final isSelected = selected == surgeon;

                                return Material(
                                  color: Colors.white,
                                  child: InkWell(
                                    onTap: () {
                                      setModalState(() {
                                        selected = surgeon;
                                      });
                                    },
                                    child: Container(
                                      color: Colors.white,
                                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            surgeon,
                                            style: const TextStyle(
                                              color: AppColors.jclGray,
                                              fontSize: 16,
                                            ),
                                          ),
                                          if (isSelected)
                                            const Icon(
                                              Icons.check,
                                              color: AppColors.jclOrange,
                                            ),
                                        ],
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
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

  @override
  Widget build(BuildContext context) {
    final formData = ref.watch(caseFormProvider).formData;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Instructions
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: AppColors.jclOrange,
                      size: 24,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Basic Case Information',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppColors.jclGray,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Enter the date, location, and surgeon for this case.',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.jclGray.withOpacity(0.7),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Date Picker
        Card(
          color: AppColors.jclWhite,
          child: ListTile(
            leading: const Icon(
              Icons.calendar_today,
              color: AppColors.jclOrange,
            ),
            title: const Text(
              'Date *',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            subtitle: Text(
              DateFormat.yMMMd().format(formData.date),
              style: const TextStyle(
                fontSize: 16,
                color: AppColors.jclGray,
              ),
            ),
            trailing: const Icon(Icons.edit, color: AppColors.jclOrange),
            onTap: _selectDate,
          ),
        ),
        const SizedBox(height: 16),

        // Location Field
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.location_on,
                      color: AppColors.jclOrange,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Location *',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _locationController,
                  readOnly: true,
                  onTap: _showFacilityPicker,
                  decoration: InputDecoration(
                    hintText: 'Tap to select facility',
                    filled: true,
                    fillColor: AppColors.jclGray.withOpacity(0.05),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    suffixIcon: const Icon(
                      Icons.arrow_drop_down,
                      color: AppColors.jclOrange,
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
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Surgeon Field
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.medical_services,
                      color: AppColors.jclOrange,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Surgeon *',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _surgeonController,
                  readOnly: true,
                  onTap: _showSurgeonPicker,
                  decoration: InputDecoration(
                    hintText: 'Tap to select surgeon',
                    filled: true,
                    fillColor: AppColors.jclGray.withOpacity(0.05),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    suffixIcon: const Icon(
                      Icons.arrow_drop_down,
                      color: AppColors.jclOrange,
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
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Helper Text
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0),
          child: Text(
            '* Required fields',
            style: TextStyle(
              fontSize: 12,
              color: AppColors.jclWhite.withOpacity(0.7),
              fontStyle: FontStyle.italic,
            ),
          ),
        ),
      ],
    );
  }
}
