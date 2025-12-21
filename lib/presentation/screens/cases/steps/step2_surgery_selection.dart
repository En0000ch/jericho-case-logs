import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../providers/case_form_provider.dart';
import '../../../../core/constants/app_constants.dart';
import '../../../../core/themes/app_colors.dart';
import '../../../../data/models/case_model.dart';

class Step2SurgerySelection extends ConsumerStatefulWidget {
  const Step2SurgerySelection({super.key});

  @override
  ConsumerState<Step2SurgerySelection> createState() =>
      _Step2SurgerySelectionState();
}

class _Step2SurgerySelectionState extends ConsumerState<Step2SurgerySelection> {
  late final TextEditingController _procedureController;

  @override
  void initState() {
    super.initState();
    final formData = ref.read(caseFormProvider).formData;
    _procedureController =
        TextEditingController(text: formData.procedureSurgery ?? '');
  }

  @override
  void dispose() {
    _procedureController.dispose();
    super.dispose();
  }

  void _updateFormData() {
    final formData = ref.read(caseFormProvider).formData;
    ref.read(caseFormProvider.notifier).updateFormData(
          formData.copyWith(
            procedureSurgery: _procedureController.text.trim(),
          ),
        );
  }

  void _showSurgeryClassPicker() {
    final formData = ref.read(caseFormProvider).formData;
    String? selectedClass = formData.surgeryClass;

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
                          'Select Surgery Class',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            if (selectedClass != null) {
                              // Calculate image name based on selected surgery class
                              final imageName = CaseModel.getImageNameForSurgeryClass(selectedClass);

                              ref
                                  .read(caseFormProvider.notifier)
                                  .updateFormData(
                                    formData.copyWith(
                                      surgeryClass: selectedClass,
                                      imageName: imageName,
                                    ),
                                  );
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

                  // Surgery Class List
                  Flexible(
                    child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: AppConstants.surgeryClasses.length,
                      itemBuilder: (context, index) {
                        final surgeryClass =
                            AppConstants.surgeryClasses[index];
                        final isSelected = selectedClass == surgeryClass;

                        return ListTile(
                          title: Text(surgeryClass),
                          trailing: isSelected
                              ? const Icon(
                                  Icons.check_circle,
                                  color: AppColors.jclOrange,
                                )
                              : null,
                          selected: isSelected,
                          selectedTileColor:
                              AppColors.jclOrange.withOpacity(0.1),
                          onTap: () {
                            setModalState(() {
                              selectedClass = surgeryClass;
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

  @override
  Widget build(BuildContext context) {
    final formData = ref.watch(caseFormProvider).formData;

    return Container(
      color: AppColors.jclGray,
      child: Column(
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
                      Icons.local_hospital,
                      color: AppColors.jclOrange,
                      size: 24,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Surgery Information',
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
                  'Select the surgery class and enter the specific procedure.',
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

        // Surgery Class Picker
        Card(
          color: AppColors.jclWhite,
          child: ListTile(
            leading: const Icon(
              Icons.category,
              color: AppColors.jclOrange,
            ),
            title: const Text(
              'Surgery Class *',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            subtitle: Text(
              formData.surgeryClass ?? 'Tap to select',
              style: TextStyle(
                fontSize: 16,
                color: formData.surgeryClass != null
                    ? AppColors.jclGray
                    : Colors.grey[600],
              ),
            ),
            trailing: const Icon(Icons.arrow_forward_ios,
                color: AppColors.jclOrange, size: 16),
            onTap: _showSurgeryClassPicker,
          ),
        ),
        const SizedBox(height: 16),

        // Procedure Field
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
                      'Procedure/Surgery *',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _procedureController,
                  decoration: InputDecoration(
                    hintText: 'e.g., Appendectomy',
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
                  maxLines: 2,
                  onChanged: (_) => _updateFormData(),
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
      ),
    );
  }
}
