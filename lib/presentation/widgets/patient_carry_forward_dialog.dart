import 'package:flutter/material.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../core/themes/app_colors.dart';

/// Dialog for patient carry forward selection
/// Shows patients from last 24 hours with checkboxes to remove them
class PatientCarryForwardDialog extends StatefulWidget {
  final List<ParseObject> patients;
  final Function(List<String> patientIdsToRemove) onConfirm;
  final VoidCallback onCancel;

  const PatientCarryForwardDialog({
    Key? key,
    required this.patients,
    required this.onConfirm,
    required this.onCancel,
  }) : super(key: key);

  @override
  State<PatientCarryForwardDialog> createState() => _PatientCarryForwardDialogState();
}

class _PatientCarryForwardDialogState extends State<PatientCarryForwardDialog> {
  // Track which patients are checked (to be removed)
  final Map<String, bool> _checkedPatients = {};

  @override
  void initState() {
    super.initState();
    // Initialize all patients as unchecked
    for (final patient in widget.patients) {
      _checkedPatients[patient.objectId!] = false;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: AppColors.jclWhiteBg,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Container(
        constraints: const BoxConstraints(maxHeight: 600, maxWidth: 400),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: AppColors.jclOrange,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(12),
                  topRight: Radius.circular(12),
                ),
              ),
              child: const Text(
                'Patient Care Status Update',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
            ),

            // Message
            Padding(
              padding: const EdgeInsets.all(16),
              child: Text(
                'Select all patients no longer in your care',
                style: TextStyle(
                  fontSize: 16,
                  color: AppColors.jclGray,
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
              ),
            ),

            // Subtitle
            if (widget.patients.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Text(
                  'Patients from last 24 hours (${widget.patients.length}):',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.jclGray.withOpacity(0.7),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),

            // Patient list
            Expanded(
              child: widget.patients.isEmpty
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Text(
                          'No patients from the last 24 hours',
                          style: TextStyle(
                            color: AppColors.jclGray.withOpacity(0.5),
                            fontSize: 14,
                          ),
                        ),
                      ),
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      itemCount: widget.patients.length,
                      separatorBuilder: (context, index) => const Divider(height: 1),
                      itemBuilder: (context, index) {
                        final patient = widget.patients[index];
                        final patientId = patient.objectId!;
                        final isChecked = _checkedPatients[patientId] ?? false;

                        return _buildPatientRow(patient, isChecked, patientId);
                      },
                    ),
            ),

            // Action buttons
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.jclWhite,
                border: Border(
                  top: BorderSide(color: AppColors.divider, width: 1),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () {
                      widget.onCancel();
                      Navigator.of(context).pop();
                    },
                    child: const Text(
                      'Cancel',
                      style: TextStyle(
                        color: AppColors.jclGray,
                        fontSize: 16,
                      ),
                    ),
                  ),
                  ElevatedButton(
                    onPressed: _onConfirm,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.jclOrange,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Confirm',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPatientRow(ParseObject patient, bool isChecked, String patientId) {
    // Extract patient data
    final gender = patient.get<String>('gender') ?? '';
    final ageRange = patient.get<String>('ageRange') ?? '';
    final medicalUnit = patient.get<String>('medicalUnit') ?? '';
    final skills = patient.get<List<dynamic>>('skills')?.cast<String>() ?? [];

    // Determine icon color based on gender
    final iconColor = gender.toLowerCase() == 'male'
        ? Colors.blue
        : const Color(0xFFFFB6D3); // Custom pink

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      elevation: 1,
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Checkbox
            Checkbox(
              value: isChecked,
              activeColor: AppColors.jclOrange,
              onChanged: (value) {
                setState(() {
                  _checkedPatients[patientId] = value ?? false;
                });
              },
            ),

            const SizedBox(width: 8),

            // Patient icon with age range
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  gender.toLowerCase() == 'male'
                      ? Icons.person
                      : Icons.person_outline,
                  size: 32,
                  color: iconColor,
                ),
                const SizedBox(height: 4),
                Text(
                  ageRange,
                  style: TextStyle(
                    fontSize: 9,
                    color: AppColors.jclGray.withOpacity(0.7),
                  ),
                ),
              ],
            ),

            const SizedBox(width: 12),

            // Skills and medical unit
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Skills
                  if (skills.isNotEmpty)
                    Text(
                      'Skills: ${skills.join(', ')}',
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppColors.jclGray,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),

                  // Medical unit
                  if (medicalUnit.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        medicalUnit,
                        style: TextStyle(
                          fontSize: 11,
                          color: AppColors.jclGray.withOpacity(0.6),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _onConfirm() {
    // Get list of patient IDs that are checked (to be removed)
    final patientIdsToRemove = _checkedPatients.entries
        .where((entry) => entry.value == true)
        .map((entry) => entry.key)
        .toList();

    widget.onConfirm(patientIdsToRemove);
    Navigator.of(context).pop();
  }
}
