import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/nursing_acuity_data.dart';
import '../../../data/datasources/remote/parse_patient_service.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/confetti_widget.dart';
import '../../widgets/orange_outline_button.dart';
import 'nursing_skills_selection_screen.dart';
import 'nursing_comorbidities_selection_screen.dart';
import 'nursing_special_context_selection_screen.dart';
import 'nursing_acuity_selection_screen.dart';

/// Patient Creation Screen - Nurse patient case creation
/// Displays selected age category, gender, and collects patient data
class PatientCreationScreen extends ConsumerStatefulWidget {
  final String ageCategory;
  final String gender;
  final String medicalUnit;

  const PatientCreationScreen({
    super.key,
    required this.ageCategory,
    required this.gender,
    required this.medicalUnit,
  });

  @override
  ConsumerState<PatientCreationScreen> createState() =>
      _PatientCreationScreenState();
}

class _PatientCreationScreenState extends ConsumerState<PatientCreationScreen> {
  final ParsePatientService _patientService = ParsePatientService();

  List<String> _selectedSkills = [];
  List<String> _selectedComorbidities = [];
  List<String> _selectedSpecialContexts = [];
  String? _selectedAcuity;

  double _sliderValue = 1.0;
  bool _showConfetti = false;
  bool _isSaving = false;

  void _handleSliderEnd(double value) {
    if (value >= 9.5) {
      // Slider reached end - save patient
      _savePatient();
    } else {
      // Reset slider if not fully slid
      setState(() => _sliderValue = 1.0);
    }
  }

  Future<void> _savePatient() async {
    if (_isSaving) return;

    final user = ref.read(currentUserProvider);
    if (user == null) {
      _showMessage('Error: User not logged in');
      setState(() => _sliderValue = 1.0);
      return;
    }

    setState(() {
      _isSaving = true;
      _showConfetti = true;
    });

    // Wait for confetti to start
    await Future.delayed(const Duration(milliseconds: 500));

    // Save the patient
    final success = await _patientService.savePatient(
      userEmail: user.email,
      ageRange: widget.ageCategory,
      gender: widget.gender,
      medicalUnit: widget.medicalUnit,
      skills: _selectedSkills,
      scenarios: _selectedComorbidities,
      acuity: _selectedAcuity ?? 'Not specified',
      specialContext: _selectedSpecialContexts,
    );

    if (success) {
      // Wait for confetti animation, then pop
      await Future.delayed(const Duration(milliseconds: 5000));
      if (mounted) {
        Navigator.of(context).pop();
      }
    } else {
      setState(() {
        _showConfetti = false;
        _isSaving = false;
        _sliderValue = 1.0;
      });
      _showMessage('Failed to save patient. Please try again.');
    }
  }

  void _onSnowfallComplete() {
    print('Confetti animation completed');
  }

  Future<String?> _showAcuityDialog() async {
    String? selectedAcuity = _selectedAcuity;

    return await showDialog<String>(
      context: context,
      barrierDismissible: true,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return Dialog(
              backgroundColor: AppColors.jclWhite,
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
                        'Select Acuity Level',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),

                    // Info header
                    Container(
                      padding: const EdgeInsets.all(12),
                      color: AppColors.jclOrange.withOpacity(0.1),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.info_outline,
                            color: AppColors.jclOrange,
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Reflects patient complexity and intensity of nursing intervention',
                              style: TextStyle(
                                color: AppColors.jclGray.withOpacity(0.8),
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Acuity levels list
                    Expanded(
                      child: ListView.separated(
                        padding: const EdgeInsets.all(12),
                        itemCount: NursingAcuityData.acuityLevels.length,
                        separatorBuilder: (context, index) => const SizedBox(height: 8),
                        itemBuilder: (context, index) {
                          final acuity = NursingAcuityData.acuityLevels[index];
                          final level = acuity['level']!;
                          final classification = acuity['classification']!;
                          final isSelected = selectedAcuity == level;

                          return InkWell(
                            onTap: () {
                              setState(() {
                                selectedAcuity = level;
                              });
                            },
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? AppColors.jclOrange
                                    : Colors.white,
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
                                  Icon(
                                    isSelected
                                        ? Icons.radio_button_checked
                                        : Icons.radio_button_unchecked,
                                    color: isSelected
                                        ? Colors.white
                                        : AppColors.jclGray.withOpacity(0.5),
                                    size: 22,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'Level $level',
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w600,
                                            color: isSelected
                                                ? Colors.white
                                                : AppColors.jclOrange,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          classification,
                                          style: TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w500,
                                            color: isSelected
                                                ? Colors.white
                                                : AppColors.jclGray,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),

                    // Action buttons
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border(
                          top: BorderSide(color: AppColors.jclGray.withOpacity(0.2), width: 1),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          TextButton(
                            onPressed: () => Navigator.of(context).pop(),
                            child: const Text(
                              'Cancel',
                              style: TextStyle(
                                color: AppColors.jclGray,
                                fontSize: 16,
                              ),
                            ),
                          ),
                          ElevatedButton(
                            onPressed: () {
                              Navigator.of(context).pop(selectedAcuity);
                            },
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
          },
        );
      },
    );
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.jclOrange,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ConfettiWidget(
      showConfetti: _showConfetti,
      onAnimationComplete: _onSnowfallComplete,
      child: Scaffold(
        backgroundColor: AppColors.jclGray,
        appBar: AppBar(
          title: const Text(
            'New Patient',
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
        ),
        body: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Age category icon
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: AppColors.jclOrange.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.person_add,
                    size: 50,
                    color: AppColors.jclOrange,
                  ),
                ),
                const SizedBox(height: 32),

                // Patient Info: Age, Gender, and Medical Unit
                const Text(
                  'Patient Information:',
                  style: TextStyle(
                    fontSize: 16,
                    color: AppColors.jclWhite,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 12),

                // Age Category
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Age Category',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppColors.jclWhite,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: AppColors.jclWhite.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: AppColors.jclOrange,
                                width: 1,
                              ),
                            ),
                            child: Center(
                              child: Text(
                                widget.ageCategory.isEmpty
                                    ? 'Not specified'
                                    : widget.ageCategory,
                                style: const TextStyle(
                                  fontSize: 16,
                                  color: AppColors.jclWhite,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Gender
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Gender',
                            style: TextStyle(
                              fontSize: 12,
                              color: AppColors.jclWhite,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: widget.gender == 'Male'
                                  ? Colors.blue
                                  : widget.gender == 'Female'
                                      ? Colors.pink
                                      : AppColors.jclWhite.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: AppColors.jclOrange,
                                width: 1,
                              ),
                            ),
                            child: Center(
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    widget.gender == 'Male'
                                        ? Icons.male
                                        : widget.gender == 'Female'
                                            ? Icons.female
                                            : Icons.help_outline,
                                    size: 20,
                                    color: AppColors.jclWhite,
                                  ),
                                  const SizedBox(width: 6),
                                  Flexible(
                                    child: Text(
                                      widget.gender,
                                      style: const TextStyle(
                                        fontSize: 16,
                                        color: AppColors.jclWhite,
                                        fontWeight: FontWeight.w600,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // Medical Unit
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Medical Unit',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.jclWhite,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: AppColors.jclWhite.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: AppColors.jclOrange,
                          width: 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.location_on_outlined,
                            color: AppColors.jclOrange,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              widget.medicalUnit,
                              style: const TextStyle(
                                fontSize: 16,
                                color: AppColors.jclWhite,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 32),

                // Select Skills Button
                SizedBox(
                  width: double.infinity,
                  child: OrangeOutlineButton(
                    onPressed: () async {
                      final result =
                          await Navigator.of(context).push<List<String>>(
                        MaterialPageRoute(
                          builder: (_) => NursingSkillsSelectionScreen(
                            initiallySelectedSkills: _selectedSkills,
                          ),
                        ),
                      );

                      if (result != null) {
                        setState(() {
                          _selectedSkills = result;
                        });
                      }
                    },
                    icon: const Icon(Icons.medical_services,
                        color: AppColors.jclWhite),
                    label: Text(
                      _selectedSkills.isEmpty
                          ? 'Select Nursing Skills'
                          : 'Skills Selected (${_selectedSkills.length})',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    backgroundColor: _selectedSkills.isEmpty
                        ? AppColors.jclOrange
                        : AppColors.jclGray.withOpacity(0.75),
                  ),
                ),

                // Display selected skills summary
                if (_selectedSkills.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.jclWhite.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: AppColors.jclOrange.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Selected Skills:',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclWhite,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children: _selectedSkills.take(5).map((skill) {
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.jclOrange.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                skill,
                                style: const TextStyle(
                                  fontSize: 10,
                                  color: AppColors.jclWhite,
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        if (_selectedSkills.length > 5)
                          Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: Text(
                              '+ ${_selectedSkills.length - 5} more',
                              style: TextStyle(
                                fontSize: 10,
                                color: AppColors.jclWhite.withOpacity(0.7),
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 16),

                // Select Clinical Scenarios Button
                SizedBox(
                  width: double.infinity,
                  child: OrangeOutlineButton(
                    onPressed: () async {
                      final result =
                          await Navigator.of(context).push<List<String>>(
                        MaterialPageRoute(
                          builder: (_) => NursingComorbiditiesSelectionScreen(
                            initiallySelectedComorbidities:
                                _selectedComorbidities,
                          ),
                        ),
                      );

                      if (result != null) {
                        setState(() {
                          _selectedComorbidities = result;
                        });
                      }
                    },
                    icon: const Icon(Icons.health_and_safety,
                        color: AppColors.jclWhite),
                    label: Text(
                      _selectedComorbidities.isEmpty
                          ? 'Select Clinical Scenarios'
                          : 'Clinical Scenarios Selected (${_selectedComorbidities.length})',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    backgroundColor: _selectedComorbidities.isEmpty
                        ? AppColors.jclOrange
                        : AppColors.jclGray.withOpacity(0.75),
                  ),
                ),

                // Display selected clinical scenarios summary
                if (_selectedComorbidities.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.jclWhite.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: AppColors.jclOrange.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Selected Clinical Scenarios:',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclWhite,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children:
                              _selectedComorbidities.take(5).map((comorbidity) {
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.jclOrange.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                comorbidity,
                                style: const TextStyle(
                                  fontSize: 10,
                                  color: AppColors.jclWhite,
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        if (_selectedComorbidities.length > 5)
                          Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: Text(
                              '+ ${_selectedComorbidities.length - 5} more',
                              style: TextStyle(
                                fontSize: 10,
                                color: AppColors.jclWhite.withOpacity(0.7),
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 16),

                // Select Special Context Button
                SizedBox(
                  width: double.infinity,
                  child: OrangeOutlineButton(
                    onPressed: () async {
                      final result =
                          await Navigator.of(context).push<List<String>>(
                        MaterialPageRoute(
                          builder: (_) => NursingSpecialContextSelectionScreen(
                            initiallySelectedContexts: _selectedSpecialContexts,
                          ),
                        ),
                      );

                      if (result != null) {
                        setState(() {
                          _selectedSpecialContexts = result;
                        });
                      }
                    },
                    icon: const Icon(Icons.info_outline,
                        color: AppColors.jclWhite),
                    label: Text(
                      _selectedSpecialContexts.isEmpty
                          ? 'Select Special Context'
                          : 'Special Context (${_selectedSpecialContexts.length})',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    backgroundColor: _selectedSpecialContexts.isEmpty
                        ? AppColors.jclOrange
                        : AppColors.jclGray.withOpacity(0.75),
                  ),
                ),

                // Display selected special contexts summary
                if (_selectedSpecialContexts.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.jclWhite.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: AppColors.jclOrange.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Selected Special Contexts:',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclWhite,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children:
                              _selectedSpecialContexts.take(5).map((context) {
                            return Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppColors.jclOrange.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                context,
                                style: const TextStyle(
                                  fontSize: 10,
                                  color: AppColors.jclWhite,
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        if (_selectedSpecialContexts.length > 5)
                          Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: Text(
                              '+ ${_selectedSpecialContexts.length - 5} more',
                              style: TextStyle(
                                fontSize: 10,
                                color: AppColors.jclWhite.withOpacity(0.7),
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 16),

                // Select Acuity Level Button
                SizedBox(
                  width: double.infinity,
                  child: OrangeOutlineButton(
                    onPressed: () async {
                      final result = await _showAcuityDialog();

                      if (result != null) {
                        setState(() {
                          _selectedAcuity = result;
                        });
                      }
                    },
                    icon: const Icon(Icons.speed, color: AppColors.jclWhite),
                    label: Text(
                      _selectedAcuity == null
                          ? 'Select Acuity Level'
                          : 'Acuity: ${NursingAcuityData.getDisplayText(_selectedAcuity!)}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    backgroundColor: _selectedAcuity == null
                        ? AppColors.jclOrange
                        : AppColors.jclGray.withOpacity(0.75),
                  ),
                ),

                const SizedBox(height: 60),

                // Slide to Save Section
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
                    onChanged: _isSaving
                        ? null
                        : (value) {
                            setState(() => _sliderValue = value);
                          },
                    onChangeEnd: _isSaving ? null : _handleSliderEnd,
                  ),
                ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
