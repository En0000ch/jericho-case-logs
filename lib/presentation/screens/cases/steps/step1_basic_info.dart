import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../providers/case_form_provider.dart';
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
                  decoration: InputDecoration(
                    hintText: 'e.g., Main OR 2',
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
                  onChanged: (_) => _updateFormData(),
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
                  decoration: InputDecoration(
                    hintText: 'e.g., Dr. Smith',
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
              color: AppColors.jclWhite.withOpacity(0.7),
              fontStyle: FontStyle.italic,
            ),
          ),
        ),
      ],
    );
  }
}
