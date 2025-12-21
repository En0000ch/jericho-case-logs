import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../providers/case_form_provider.dart';
import '../../../../core/constants/app_constants.dart';
import '../../../../core/themes/app_colors.dart';

class Step4PatientInfo extends ConsumerStatefulWidget {
  const Step4PatientInfo({super.key});

  @override
  ConsumerState<Step4PatientInfo> createState() => _Step4PatientInfoState();
}

class _Step4PatientInfoState extends ConsumerState<Step4PatientInfo> {
  late final TextEditingController _ageController;
  bool _asaSelected = false; // Track if user has selected an ASA class

  @override
  void initState() {
    super.initState();
    final formData = ref.read(caseFormProvider).formData;
    _ageController = TextEditingController(
      text: formData.patientAge != null ? formData.patientAge.toString() : '',
    );
    // E button should be disabled initially for new cases
    // Only enable if editing an existing case with non-default ASA
    _asaSelected = false;
    print('DEBUG INIT: asaClassification="${formData.asaClassification}", _asaSelected=$_asaSelected (E button disabled)');
  }

  @override
  void dispose() {
    _ageController.dispose();
    super.dispose();
  }

  void _updateAge() {
    final formData = ref.read(caseFormProvider).formData;
    final ageText = _ageController.text.trim();
    final age = ageText.isEmpty ? null : int.tryParse(ageText);

    ref.read(caseFormProvider.notifier).updateFormData(
          formData.copyWith(patientAge: age),
        );
  }

  void _showGenderPicker() {
    final formData = ref.read(caseFormProvider).formData;
    String? selectedGender = formData.gender;

    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.jclWhite,
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
                          'Select Gender',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            ref.read(caseFormProvider.notifier).updateFormData(
                                  formData.copyWith(gender: selectedGender),
                                );
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

                  // Gender List (including "Not specified")
                  ListTile(
                    title: const Text('Not specified'),
                    trailing: selectedGender == null
                        ? const Icon(
                            Icons.check_circle,
                            color: AppColors.jclOrange,
                          )
                        : null,
                    selected: selectedGender == null,
                    selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                    onTap: () {
                      setModalState(() {
                        selectedGender = null;
                      });
                    },
                  ),
                  ...AppConstants.genderOptions.map((gender) {
                    final isSelected = selectedGender == gender;
                    return ListTile(
                      title: Text(gender),
                      trailing: isSelected
                          ? const Icon(
                              Icons.check_circle,
                              color: AppColors.jclOrange,
                            )
                          : null,
                      selected: isSelected,
                      selectedTileColor: AppColors.jclOrange.withOpacity(0.1),
                      onTap: () {
                        setModalState(() {
                          selectedGender = gender;
                        });
                      },
                    );
                  }).toList(),
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
                      Icons.person,
                      color: AppColors.jclOrange,
                      size: 24,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Patient Information',
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
                  'Enter patient demographics and ASA classification.',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.jclGray,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Age Field
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
                      Icons.cake,
                      color: AppColors.jclOrange,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'Patient Age (Optional)',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _ageController,
                  keyboardType: TextInputType.number,
                  autocorrect: false,
                  enableSuggestions: false,
                  decoration: InputDecoration(
                    hintText: 'Enter age in years',
                    suffixText: 'years',
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
                  onChanged: (_) => _updateAge(),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Gender Picker
        Card(
          color: AppColors.jclWhite,
          child: ListTile(
            leading: const Icon(
              Icons.wc,
              color: AppColors.jclOrange,
            ),
            title: const Text(
              'Gender (Optional)',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            subtitle: Text(
              formData.gender ?? 'Not specified',
              style: TextStyle(
                fontSize: 16,
                color: formData.gender != null
                    ? AppColors.jclGray
                    : Colors.grey[600],
              ),
            ),
            trailing: const Icon(
              Icons.arrow_forward_ios,
              color: AppColors.jclOrange,
              size: 16,
            ),
            onTap: _showGenderPicker,
          ),
        ),
        const SizedBox(height: 16),

        // ASA Classification
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
                      Icons.assessment,
                      color: AppColors.jclOrange,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    const Text(
                      'ASA Classification *',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    ...AppConstants.asaClassifications.map((asa) {
                      // Extract base ASA without E modifier
                      final baseAsa = formData.asaClassification.split(',').first.trim();
                      final isSelected = baseAsa == asa;
                      return ChoiceChip(
                        label: Text('ASA $asa'),
                        selected: isSelected,
                        selectedColor: AppColors.jclOrange,
                        backgroundColor: AppColors.jclGray.withOpacity(0.1),
                        labelStyle: TextStyle(
                          color: isSelected
                              ? AppColors.jclWhite
                              : AppColors.jclGray,
                          fontWeight: FontWeight.w500,
                        ),
                        onSelected: (selected) {
                          if (selected) {
                            print('DEBUG ASA SELECTED: ASA $asa selected, setting _asaSelected to true');
                            // Mark that user has selected an ASA class
                            setState(() {
                              _asaSelected = true;
                            });
                            // Check if E is currently selected
                            final hasEmergency = formData.asaClassification.contains(', E');
                            final newAsa = hasEmergency ? '$asa, E' : asa;
                            ref
                                .read(caseFormProvider.notifier)
                                .updateFormData(
                                  formData.copyWith(asaClassification: newAsa),
                                );
                          }
                        },
                      );
                    }).toList(),
                    // Emergency modifier chip
                    if (_asaSelected)
                      Builder(
                        builder: (context) {
                          final baseAsa = formData.asaClassification.split(',').first.trim();
                          final hasEmergency = formData.asaClassification.contains(', E');
                          final isSelected = hasEmergency;
                          print('DEBUG E CHIP BUILD: _asaSelected=$_asaSelected, isSelected=$isSelected');

                          return GestureDetector(
                            onTap: () {
                              print('DEBUG E CHIP TAPPED: selected=${!isSelected}');
                              final newAsa = !isSelected ? '$baseAsa, E' : baseAsa;
                              ref
                                  .read(caseFormProvider.notifier)
                                  .updateFormData(
                                    formData.copyWith(asaClassification: newAsa),
                                  );
                            },
                            child: Container(
                              decoration: BoxDecoration(
                                color: isSelected ? const Color.fromRGBO(238, 108, 97, 1.0) : AppColors.jclWhite,
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
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              child: Text(
                                'E',
                                style: TextStyle(
                                  color: isSelected ? AppColors.jclWhite : AppColors.jclGray,
                                  fontWeight: FontWeight.w500,
                                  fontSize: 14,
                                ),
                              ),
                            ),
                          );
                        },
                      )
                    else
                      Builder(
                        builder: (context) {
                          print('DEBUG E CHIP: DISABLED - showing grayed out version with shadow');
                          return Container(
                            decoration: BoxDecoration(
                              color: AppColors.jclWhite,
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
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            child: Text(
                              'E',
                              style: TextStyle(
                                color: AppColors.jclGray.withOpacity(0.5),
                                fontWeight: FontWeight.w500,
                                fontSize: 14,
                              ),
                            ),
                          );
                        },
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'E = Emergency',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.jclGray.withOpacity(0.7),
                    fontStyle: FontStyle.italic,
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
              color: AppColors.jclWhite,
              fontStyle: FontStyle.italic,
            ),
          ),
        ),
      ],
    );
  }
}
