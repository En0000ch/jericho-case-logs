import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/case_form_provider.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/themes/app_colors.dart';
import '../../../domain/entities/case.dart';
import '../../widgets/glow_button.dart';
import 'steps/step1_basic_info.dart';
import 'steps/step2_surgery_selection.dart';
import 'steps/step3_anesthetic_plan.dart';
import 'steps/step4_patient_info.dart';
import 'steps/step5_additional_details.dart';
import 'steps/step6_review.dart';

class CaseFormWizard extends ConsumerStatefulWidget {
  final Case? caseToEdit;

  const CaseFormWizard({
    super.key,
    this.caseToEdit,
  });

  @override
  ConsumerState<CaseFormWizard> createState() => _CaseFormWizardState();
}

class _CaseFormWizardState extends ConsumerState<CaseFormWizard> {
  final List<String> _stepTitles = [
    'Basic Info',
    'Surgery',
    'Anesthetic Plan',
    'Patient Info',
    'Additional Details',
    'Review',
  ];

  bool get _isEditMode => widget.caseToEdit != null;

  @override
  void initState() {
    super.initState();
    // Initialize form with existing data or reset
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_isEditMode) {
        final caseData = widget.caseToEdit!;
        ref.read(caseFormProvider.notifier).updateFormData(
              CaseFormData(
                date: caseData.date,
                location: caseData.location,
                surgeon: null, // Surgeon not stored in Case entity
                surgeryClass: caseData.surgeryClass,
                procedureSurgery: caseData.procedureSurgery,
                anestheticPlan: caseData.anestheticPlan,
                anestheticsUsed: List.from(caseData.anestheticsUsed),
                patientAge: caseData.patientAge,
                gender: caseData.gender,
                asaClassification: caseData.asaClassification,
                airwayManagement: caseData.airwayManagement,
                hasComplications: caseData.complications ?? false,
                additionalComments: caseData.additionalComments,
              ),
            );
      } else {
        ref.read(caseFormProvider.notifier).reset();
        // Date selection now handled in Step 1 (Basic Info)
      }
    });
  }

  Future<void> _showDatePicker() async {
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
    } else {
      // If user cancels date selection, close the wizard
      if (mounted) {
        Navigator.of(context).pop();
      }
    }
  }

  Widget _buildStepContent(int step) {
    switch (step) {
      case 0:
        return const Step1BasicInfo();
      case 1:
        return const Step2SurgerySelection();
      case 2:
        return const Step3AnestheticPlan();
      case 3:
        return const Step4PatientInfo();
      case 4:
        return const Step5AdditionalDetails();
      case 5:
        return const Step6Review();
      default:
        return const Step1BasicInfo();
    }
  }

  Future<void> _handleSave() async {
    final formState = ref.read(caseFormProvider);
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

    if (!formState.formData.isValid()) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please complete all required fields'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    ref.read(caseFormProvider.notifier).setSaving(true);

    final notifier = ref.read(caseDetailProvider.notifier);
    final data = formState.formData;

    bool success;
    if (_isEditMode) {
      success = await notifier.updateCase(
        caseId: widget.caseToEdit!.objectId,
        date: data.date,
        patientAge: data.patientAge?.toString(),
        gender: data.gender,
        asaClassification: data.asaClassification,
        procedureSurgery: data.procedureSurgery,
        anestheticPlan: data.anestheticPlan,
        anestheticsUsed: data.anestheticsUsed,
        surgeryClass: data.surgeryClass,
        location: data.location,
        airwayManagement: data.airwayManagement,
        additionalComments: data.additionalComments,
        complications: data.hasComplications,
        imageName: data.imageName,
      );
    } else {
      success = await notifier.createCase(
        userEmail: user.email,
        date: data.date,
        patientAge: data.patientAge?.toString(),
        gender: data.gender,
        asaClassification: data.asaClassification,
        procedureSurgery: data.procedureSurgery ?? '',
        anestheticPlan: data.anestheticPlan ?? '',
        anestheticsUsed: data.anestheticsUsed,
        surgeryClass: data.surgeryClass ?? '',
        location: data.location,
        airwayManagement: data.airwayManagement,
        additionalComments: data.additionalComments,
        complications: data.hasComplications,
        imageName: data.imageName,
      );
    }

    ref.read(caseFormProvider.notifier).setSaving(false);

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              _isEditMode
                  ? 'Case updated successfully'
                  : 'Case created successfully',
            ),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.of(context).pop();
      } else {
        final state = ref.read(caseDetailProvider);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.error ?? 'Failed to save case'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _handleCancel() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Case Creation?'),
        content: const Text(
          'Are you sure you want to cancel? All entered data will be lost.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Continue Editing'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop(); // Close dialog
              // Pop all routes until we're back at the home screen
              Navigator.of(context).popUntil((route) => route.isFirst);
            },
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('Cancel Case'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final formState = ref.watch(caseFormProvider);
    final currentStep = formState.currentStep;
    final isLastStep = currentStep == _stepTitles.length - 1;

    return WillPopScope(
      onWillPop: () async {
        if (currentStep > 0) {
          ref.read(caseFormProvider.notifier).previousStep();
          return false;
        }
        return true;
      },
      child: Scaffold(
        backgroundColor: AppColors.jclGray,
        appBar: AppBar(
          title: Text(
            '${_isEditMode ? "Edit" : "New"} Case - ${_stepTitles[currentStep]}',
            style: const TextStyle(
              color: AppColors.jclWhite,
              fontSize: 18,
              fontWeight: FontWeight.w600,
            ),
          ),
          centerTitle: true,
          backgroundColor: AppColors.jclOrange,
          elevation: 0,
          iconTheme: const IconThemeData(color: AppColors.jclWhite),
          leading: IconButton(
            icon: const Icon(Icons.close),
            onPressed: _handleCancel,
          ),
        ),
        body: Column(
          children: [
            // Progress Indicator
            Container(
              color: AppColors.jclGray,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: List.generate(
                  _stepTitles.length,
                  (index) => Expanded(
                    child: Container(
                      height: 4,
                      margin: EdgeInsets.only(
                        right: index < _stepTitles.length - 1 ? 4 : 0,
                      ),
                      decoration: BoxDecoration(
                        color: index <= currentStep
                            ? AppColors.jclOrange
                            : AppColors.jclWhite.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                ),
              ),
            ),

            // Step Content
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: _buildStepContent(currentStep),
              ),
            ),

            // Error Message
            if (formState.error != null)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                color: Colors.red.withOpacity(0.1),
                child: Text(
                  formState.error!,
                  style: const TextStyle(color: Colors.red),
                  textAlign: TextAlign.center,
                ),
              ),

            // Navigation Buttons
            Container(
              color: AppColors.jclGray,
              padding: const EdgeInsets.all(16),
              child: formState.isSaving
                  ? const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: AppColors.jclOrange,
                      ),
                    )
                  : Row(
                      children: [
                        // Back Button with Glow
                        if (currentStep > 0)
                          Expanded(
                            child: GlowButton(
                              text: 'Back',
                              onPressed: () {
                                ref.read(caseFormProvider.notifier).previousStep();
                              },
                              isPrimary: false,
                              icon: Icons.arrow_back,
                            ),
                          ),

                        if (currentStep > 0) const SizedBox(width: 16),

                        // Next/Save Button with Glow
                        Expanded(
                          flex: currentStep == 0 ? 1 : 1,
                          child: GlowButton(
                            text: isLastStep
                                ? (_isEditMode ? 'Update Case' : 'Save Case')
                                : 'Next',
                            onPressed: () {
                              if (isLastStep) {
                                _handleSave();
                              } else {
                                ref.read(caseFormProvider.notifier).nextStep();
                              }
                            },
                            isPrimary: true,
                            icon: isLastStep ? Icons.save : Icons.arrow_forward,
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
}
