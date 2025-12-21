import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Case Form Data - holds all the data being collected across steps
class CaseFormData {
  // Step 1: Basic Info
  DateTime date;
  String? location;
  String? surgeon;

  // Step 2: Surgery
  String? surgeryClass;
  String? procedureSurgery;
  String? imageName; // Image determined by surgeryClass

  // Step 3: Anesthetic Plans
  String? anestheticPlan;
  List<String> anestheticsUsed;

  // Step 4: Patient Info
  int? patientAge;
  String? gender;
  String asaClassification;

  // Step 5: Additional Details
  String? airwayManagement;
  bool hasComplications;
  String? additionalComments;

  CaseFormData({
    DateTime? date,
    this.location,
    this.surgeon,
    this.surgeryClass,
    this.procedureSurgery,
    this.imageName,
    this.anestheticPlan,
    List<String>? anestheticsUsed,
    this.patientAge,
    this.gender,
    String? asaClassification,
    this.airwayManagement,
    bool? hasComplications,
    this.additionalComments,
  })  : date = date ?? DateTime.now(),
        anestheticsUsed = anestheticsUsed ?? [],
        asaClassification = asaClassification ?? 'I',
        hasComplications = hasComplications ?? false;

  CaseFormData copyWith({
    DateTime? date,
    String? location,
    String? surgeon,
    String? surgeryClass,
    String? procedureSurgery,
    String? imageName,
    String? anestheticPlan,
    List<String>? anestheticsUsed,
    int? patientAge,
    String? gender,
    String? asaClassification,
    String? airwayManagement,
    bool? hasComplications,
    String? additionalComments,
  }) {
    return CaseFormData(
      date: date ?? this.date,
      location: location ?? this.location,
      surgeon: surgeon ?? this.surgeon,
      surgeryClass: surgeryClass ?? this.surgeryClass,
      procedureSurgery: procedureSurgery ?? this.procedureSurgery,
      imageName: imageName ?? this.imageName,
      anestheticPlan: anestheticPlan ?? this.anestheticPlan,
      anestheticsUsed: anestheticsUsed ?? this.anestheticsUsed,
      patientAge: patientAge ?? this.patientAge,
      gender: gender ?? this.gender,
      asaClassification: asaClassification ?? this.asaClassification,
      airwayManagement: airwayManagement ?? this.airwayManagement,
      hasComplications: hasComplications ?? this.hasComplications,
      additionalComments: additionalComments ?? this.additionalComments,
    );
  }

  /// Validate step 1 (Basic Info)
  bool isStep1Valid() {
    return location != null &&
           location!.trim().isNotEmpty &&
           surgeon != null &&
           surgeon!.trim().isNotEmpty;
  }

  /// Validate step 2 (Surgery)
  bool isStep2Valid() {
    return surgeryClass != null &&
           surgeryClass!.isNotEmpty &&
           procedureSurgery != null &&
           procedureSurgery!.trim().isNotEmpty;
  }

  /// Validate step 3 (Anesthetic Plan)
  bool isStep3Valid() {
    return anestheticPlan != null && anestheticPlan!.isNotEmpty;
  }

  /// Validate step 4 (Patient Info) - Age and Gender are optional
  bool isStep4Valid() {
    return true; // ASA is always set to a default value
  }

  /// All required fields are valid
  bool isValid() {
    return isStep1Valid() &&
           isStep2Valid() &&
           isStep3Valid() &&
           isStep4Valid();
  }
}

/// Case Form State
class CaseFormState {
  final CaseFormData formData;
  final int currentStep;
  final bool isSaving;
  final String? error;

  CaseFormState({
    CaseFormData? formData,
    this.currentStep = 0,
    this.isSaving = false,
    this.error,
  }) : formData = formData ?? CaseFormData();

  CaseFormState copyWith({
    CaseFormData? formData,
    int? currentStep,
    bool? isSaving,
    String? error,
  }) {
    return CaseFormState(
      formData: formData ?? this.formData,
      currentStep: currentStep ?? this.currentStep,
      isSaving: isSaving ?? this.isSaving,
      error: error,
    );
  }
}

/// Case Form Notifier
class CaseFormNotifier extends StateNotifier<CaseFormState> {
  CaseFormNotifier() : super(CaseFormState());

  /// Update form data
  void updateFormData(CaseFormData data) {
    state = state.copyWith(formData: data);
  }

  /// Go to next step
  bool nextStep() {
    // Validate current step before proceeding
    switch (state.currentStep) {
      case 0:
        if (!state.formData.isStep1Valid()) {
          state = state.copyWith(error: 'Please fill in Location and Surgeon');
          return false;
        }
        break;
      case 1:
        if (!state.formData.isStep2Valid()) {
          state = state.copyWith(error: 'Please select Surgery Class and Procedure');
          return false;
        }
        break;
      case 2:
        if (!state.formData.isStep3Valid()) {
          state = state.copyWith(error: 'Please select Anesthetic Plan');
          return false;
        }
        break;
      case 3:
        if (!state.formData.isStep4Valid()) {
          state = state.copyWith(error: 'Please complete Patient Info');
          return false;
        }
        break;
    }

    state = state.copyWith(
      currentStep: state.currentStep + 1,
      error: null,
    );
    return true;
  }

  /// Go to previous step
  void previousStep() {
    if (state.currentStep > 0) {
      state = state.copyWith(
        currentStep: state.currentStep - 1,
        error: null,
      );
    }
  }

  /// Go to specific step
  void goToStep(int step) {
    state = state.copyWith(currentStep: step, error: null);
  }

  /// Reset form
  void reset() {
    state = CaseFormState();
  }

  /// Set saving state
  void setSaving(bool isSaving) {
    state = state.copyWith(isSaving: isSaving);
  }

  /// Set error
  void setError(String error) {
    state = state.copyWith(error: error);
  }

  /// Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Case Form Provider
final caseFormProvider =
    StateNotifierProvider<CaseFormNotifier, CaseFormState>((ref) {
  return CaseFormNotifier();
});
