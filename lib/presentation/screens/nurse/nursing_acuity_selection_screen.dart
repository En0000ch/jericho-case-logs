import 'package:flutter/material.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/nursing_acuity_data.dart';

/// Nursing Acuity Selection Screen
/// Allows nurses to select patient acuity level (1-5)
class NursingAcuitySelectionScreen extends StatefulWidget {
  final String? initiallySelectedAcuity;

  const NursingAcuitySelectionScreen({
    super.key,
    this.initiallySelectedAcuity,
  });

  @override
  State<NursingAcuitySelectionScreen> createState() =>
      _NursingAcuitySelectionScreenState();
}

class _NursingAcuitySelectionScreenState
    extends State<NursingAcuitySelectionScreen> {
  String? _selectedAcuity;

  @override
  void initState() {
    super.initState();
    _selectedAcuity = widget.initiallySelectedAcuity;
  }

  void _showAcuityInfoDialog(Map<String, String> acuity) {
    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: Text(
            'Level ${acuity['level']}: ${acuity['classification']}',
            style: const TextStyle(
              color: AppColors.jclGray,
              fontWeight: FontWeight.w600,
              fontSize: 16,
            ),
          ),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Clinical Description:',
                  style: TextStyle(
                    color: AppColors.jclOrange,
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  acuity['clinicalDescription'] ?? '',
                  style: const TextStyle(
                    color: AppColors.jclGray,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Representative Examples:',
                  style: TextStyle(
                    color: AppColors.jclOrange,
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  acuity['examples'] ?? '',
                  style: const TextStyle(
                    color: AppColors.jclGray,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: const Text(
                'Close',
                style: TextStyle(
                  color: AppColors.jclOrange,
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  void _confirmSelection() {
    Navigator.of(context).pop(_selectedAcuity);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Acuity Level',
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
        leading: const BackButton(),
        actions: [
          IconButton(
            icon: const Icon(Icons.check, color: AppColors.jclWhite),
            onPressed: _confirmSelection,
            tooltip: 'Confirm Selection',
          ),
        ],
      ),
      body: Column(
        children: [
          // Info header
          Container(
            padding: const EdgeInsets.all(16),
            color: AppColors.jclOrange.withOpacity(0.1),
            child: Row(
              children: [
                const Icon(
                  Icons.info_outline,
                  color: AppColors.jclOrange,
                  size: 20,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Acuity level reflects patient complexity, intensity of nursing intervention, and clinical judgment required.',
                    style: TextStyle(
                      color: AppColors.jclGray.withOpacity(0.8),
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Acuity levels list
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: NursingAcuityData.acuityLevels.length,
              separatorBuilder: (context, index) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final acuity = NursingAcuityData.acuityLevels[index];
                final level = acuity['level']!;
                final classification = acuity['classification']!;
                final isSelected = _selectedAcuity == level;

                return InkWell(
                  onTap: () {
                    setState(() {
                      _selectedAcuity = level;
                    });
                  },
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppColors.jclOrange
                          : AppColors.jclWhite,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected
                            ? AppColors.jclOrange
                            : AppColors.jclGray.withOpacity(0.3),
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        // Radio button
                        Icon(
                          isSelected
                              ? Icons.radio_button_checked
                              : Icons.radio_button_unchecked,
                          color: isSelected
                              ? AppColors.jclWhite
                              : AppColors.jclGray.withOpacity(0.5),
                          size: 24,
                        ),
                        const SizedBox(width: 16),
                        // Level and classification
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Level $level',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: isSelected
                                      ? AppColors.jclWhite
                                      : AppColors.jclOrange,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                classification,
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w500,
                                  color: isSelected
                                      ? AppColors.jclWhite
                                      : AppColors.jclGray,
                                ),
                              ),
                            ],
                          ),
                        ),
                        // Info button
                        IconButton(
                          icon: Icon(
                            Icons.info_outline,
                            color: isSelected
                                ? AppColors.jclWhite
                                : AppColors.jclOrange,
                            size: 22,
                          ),
                          onPressed: () => _showAcuityInfoDialog(acuity),
                          tooltip: 'View details',
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),

          // Selected acuity display
          if (_selectedAcuity != null)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.jclGray.withOpacity(0.05),
                border: Border(
                  top: BorderSide(
                    color: AppColors.jclGray.withOpacity(0.2),
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.check_circle,
                    color: AppColors.jclOrange,
                    size: 20,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Selected: ${NursingAcuityData.getDisplayText(_selectedAcuity!)}',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: AppColors.jclGray,
                      ),
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
