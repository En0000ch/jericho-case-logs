import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../providers/case_form_provider.dart';
import '../../../../core/constants/app_constants.dart';
import '../../../../core/themes/app_colors.dart';

class Step3AnestheticPlan extends ConsumerWidget {
  const Step3AnestheticPlan({super.key});

  void _showAnestheticPlanPicker(BuildContext context, WidgetRef ref) {
    final formData = ref.read(caseFormProvider).formData;
    String? selectedPlan = formData.anestheticPlan;

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
                          'Select Anesthetic Plan',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            if (selectedPlan != null) {
                              ref
                                  .read(caseFormProvider.notifier)
                                  .updateFormData(
                                    formData.copyWith(
                                        anestheticPlan: selectedPlan),
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

                  // Anesthetic Plan List
                  Flexible(
                    child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: AppConstants.anestheticPlans.length,
                      itemBuilder: (context, index) {
                        final plan = AppConstants.anestheticPlans[index];
                        final isSelected = selectedPlan == plan;

                        return ListTile(
                          title: Text(plan),
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
                              selectedPlan = plan;
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

  void _showAnestheticsUsedDialog(BuildContext context, WidgetRef ref) {
    final formData = ref.read(caseFormProvider).formData;
    final tempSelected = List<String>.from(formData.anestheticsUsed);

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Select Anesthetics Used'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: AppConstants.anestheticPlans.map((anesthetic) {
                    final isSelected = tempSelected.contains(anesthetic);
                    return CheckboxListTile(
                      title: Text(anesthetic),
                      value: isSelected,
                      activeColor: AppColors.jclOrange,
                      onChanged: (checked) {
                        setDialogState(() {
                          if (checked == true) {
                            tempSelected.add(anesthetic);
                          } else {
                            tempSelected.remove(anesthetic);
                          }
                        });
                      },
                    );
                  }).toList(),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: () {
                    ref.read(caseFormProvider.notifier).updateFormData(
                          formData.copyWith(anestheticsUsed: tempSelected),
                        );
                    Navigator.of(context).pop();
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.jclOrange,
                    foregroundColor: AppColors.jclWhite,
                  ),
                  child: const Text('Done'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
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
                      Icons.healing,
                      color: AppColors.jclOrange,
                      size: 24,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Anesthetic Information',
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
                  'Select the primary anesthetic plan and any additional anesthetics used.',
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

        // Anesthetic Plan Picker
        Card(
          color: AppColors.jclWhite,
          child: ListTile(
            leading: const Icon(
              Icons.medication,
              color: AppColors.jclOrange,
            ),
            title: const Text(
              'Primary Anesthetic Plan *',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            subtitle: Text(
              formData.anestheticPlan ?? 'Tap to select',
              style: TextStyle(
                fontSize: 16,
                color: formData.anestheticPlan != null
                    ? AppColors.jclGray
                    : Colors.grey[600],
              ),
            ),
            trailing: const Icon(
              Icons.arrow_forward_ios,
              color: AppColors.jclOrange,
              size: 16,
            ),
            onTap: () => _showAnestheticPlanPicker(context, ref),
          ),
        ),
        const SizedBox(height: 16),

        // Anesthetics Used
        Card(
          color: AppColors.jclWhite,
          child: ListTile(
            leading: const Icon(
              Icons.science,
              color: AppColors.jclOrange,
            ),
            title: const Text(
              'Anesthetics Used (Optional)',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            subtitle: formData.anestheticsUsed.isEmpty
                ? Text(
                    'Tap to select',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  )
                : Padding(
                    padding: const EdgeInsets.only(top: 8.0),
                    child: Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: formData.anestheticsUsed
                          .map((a) => Chip(
                                label: Text(
                                  a,
                                  style: const TextStyle(fontSize: 12),
                                ),
                                backgroundColor:
                                    AppColors.jclOrange.withOpacity(0.1),
                                side: BorderSide.none,
                              ))
                          .toList(),
                    ),
                  ),
            trailing: const Icon(
              Icons.edit,
              color: AppColors.jclOrange,
              size: 16,
            ),
            onTap: () => _showAnestheticsUsedDialog(context, ref),
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
