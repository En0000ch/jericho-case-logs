import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../providers/case_form_provider.dart';
import '../../../../core/themes/app_colors.dart';

class Step6Review extends ConsumerWidget {
  const Step6Review({super.key});

  Widget _buildInfoRow(String label, String? value, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: AppColors.jclOrange,
            size: 20,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[700],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value ?? 'Not specified',
                  style: TextStyle(
                    fontSize: 16,
                    color: value != null
                        ? AppColors.jclGray
                        : Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
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
                      Icons.checklist,
                      color: AppColors.jclOrange,
                      size: 24,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Review Case',
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
                  'Review all case information before saving. Tap "Save Case" when ready.',
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

        // Basic Information
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'BASIC INFORMATION',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclOrange,
                    letterSpacing: 1.2,
                  ),
                ),
                const Divider(),
                _buildInfoRow(
                  'Date',
                  DateFormat.yMMMd().format(formData.date),
                  Icons.calendar_today,
                ),
                _buildInfoRow(
                  'Location',
                  formData.location,
                  Icons.location_on,
                ),
                _buildInfoRow(
                  'Surgeon',
                  formData.surgeon,
                  Icons.medical_services,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Surgery Information
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'SURGERY INFORMATION',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclOrange,
                    letterSpacing: 1.2,
                  ),
                ),
                const Divider(),
                _buildInfoRow(
                  'Surgery Class',
                  formData.surgeryClass,
                  Icons.category,
                ),
                _buildInfoRow(
                  'Procedure/Surgery',
                  formData.procedureSurgery,
                  Icons.local_hospital,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Anesthetic Information
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'ANESTHETIC INFORMATION',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclOrange,
                    letterSpacing: 1.2,
                  ),
                ),
                const Divider(),
                _buildInfoRow(
                  'Primary Plan',
                  formData.anestheticPlan,
                  Icons.medication,
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8.0),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(
                        Icons.science,
                        color: AppColors.jclOrange,
                        size: 20,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Anesthetics Used',
                              style: TextStyle(
                                fontSize: 12,
                                color: AppColors.jclGray.withOpacity(0.7),
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 8),
                            formData.anestheticsUsed.isEmpty
                                ? Text(
                                    'None specified',
                                    style: TextStyle(
                                      fontSize: 16,
                                      color:
                                          AppColors.jclGray.withOpacity(0.5),
                                    ),
                                  )
                                : Wrap(
                                    spacing: 8,
                                    runSpacing: 8,
                                    children: formData.anestheticsUsed
                                        .map((a) => Chip(
                                              label: Text(
                                                a,
                                                style: const TextStyle(
                                                    fontSize: 12),
                                              ),
                                              backgroundColor:
                                                  AppColors.jclOrange
                                                      .withOpacity(0.1),
                                              side: BorderSide.none,
                                            ))
                                        .toList(),
                                  ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Patient Information
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'PATIENT INFORMATION',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclOrange,
                    letterSpacing: 1.2,
                  ),
                ),
                const Divider(),
                _buildInfoRow(
                  'Age',
                  formData.patientAge != null
                      ? '${formData.patientAge} years'
                      : null,
                  Icons.cake,
                ),
                _buildInfoRow(
                  'Gender',
                  formData.gender,
                  Icons.wc,
                ),
                _buildInfoRow(
                  'ASA Classification',
                  'ASA ${formData.asaClassification}',
                  Icons.assessment,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Additional Details
        Card(
          color: AppColors.jclWhite,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'ADDITIONAL DETAILS',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclOrange,
                    letterSpacing: 1.2,
                  ),
                ),
                const Divider(),
                _buildInfoRow(
                  'Airway Management',
                  formData.airwayManagement,
                  Icons.air,
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8.0),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        formData.hasComplications
                            ? Icons.warning
                            : Icons.check_circle,
                        color: formData.hasComplications
                            ? Colors.red
                            : Colors.green,
                        size: 20,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Complications',
                              style: TextStyle(
                                fontSize: 12,
                                color: AppColors.jclGray.withOpacity(0.7),
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              formData.hasComplications ? 'Yes' : 'No',
                              style: TextStyle(
                                fontSize: 16,
                                color: formData.hasComplications
                                    ? Colors.red
                                    : Colors.green,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                _buildInfoRow(
                  'Additional Comments',
                  formData.additionalComments,
                  Icons.notes,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Edit Warning
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.jclOrange.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: AppColors.jclOrange.withOpacity(0.3),
            ),
          ),
          child: Row(
            children: [
              Icon(
                Icons.info_outline,
                color: AppColors.jclOrange,
                size: 20,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Use the Back button to edit any information before saving.',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.jclGray,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
