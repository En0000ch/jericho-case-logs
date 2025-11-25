import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/entities/case.dart';
import '../../domain/repositories/i_case_repository.dart';
import '../../data/repositories/case_repository.dart';
import '../../data/datasources/local/database_helper.dart';

/// Database Helper Provider
final databaseHelperProvider = Provider<DatabaseHelper>((ref) {
  return DatabaseHelper.instance;
});

/// Case Repository Provider
final caseRepositoryProvider = Provider<ICaseRepository>((ref) {
  final databaseHelper = ref.watch(databaseHelperProvider);
  return CaseRepository(databaseHelper);
});

/// Case List State
class CaseListState {
  final List<Case> cases;
  final bool isLoading;
  final String? error;

  CaseListState({
    this.cases = const [],
    this.isLoading = false,
    this.error,
  });

  CaseListState copyWith({
    List<Case>? cases,
    bool? isLoading,
    String? error,
  }) {
    return CaseListState(
      cases: cases ?? this.cases,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

/// Case List Notifier
class CaseListNotifier extends StateNotifier<CaseListState> {
  final ICaseRepository _caseRepository;
  final String _userEmail;

  CaseListNotifier(this._caseRepository, this._userEmail)
      : super(CaseListState()) {
    loadCases();
  }

  /// Load all cases for the user
  Future<void> loadCases() async {
    print('DEBUG PROVIDER: loadCases called for userEmail=$_userEmail');
    state = state.copyWith(isLoading: true, error: null);

    final result = await _caseRepository.getCases(_userEmail);

    print('DEBUG PROVIDER: Repository returned result');
    result.fold(
      (failure) {
        print('DEBUG PROVIDER: Got failure - ${failure.message}');
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (cases) {
        print('DEBUG PROVIDER: Got ${cases.length} cases from repository');
        if (cases.isNotEmpty) {
          print('DEBUG PROVIDER: First case - objectId=${cases.first.objectId}, surgery=${cases.first.procedureSurgery}, date=${cases.first.date}');
        }
        state = CaseListState(
          cases: cases,
          isLoading: false,
        );
        print('DEBUG PROVIDER: State updated, state.cases.length=${state.cases.length}');
      },
    );
  }

  /// Search cases with filters
  Future<void> searchCases({
    String? keyword,
    DateTime? startDate,
    DateTime? endDate,
    String? asaClassification,
    String? surgeryClass,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    final result = await _caseRepository.searchCases(
      userEmail: _userEmail,
      keyword: keyword,
      startDate: startDate,
      endDate: endDate,
      asaClassification: asaClassification,
      surgeryClass: surgeryClass,
    );

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (cases) {
        state = CaseListState(
          cases: cases,
          isLoading: false,
        );
      },
    );
  }

  /// Delete a case
  Future<bool> deleteCase(String caseId) async {
    final result = await _caseRepository.deleteCase(caseId);

    return result.fold(
      (failure) {
        state = state.copyWith(error: failure.message);
        return false;
      },
      (_) {
        // Remove from local list
        state = state.copyWith(
          cases: state.cases.where((c) => c.objectId != caseId).toList(),
        );
        return true;
      },
    );
  }

  /// Sync cases from remote
  Future<void> syncCases() async {
    state = state.copyWith(isLoading: true, error: null);

    final result = await _caseRepository.syncCases(_userEmail);

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (_) {
        // Reload cases after sync
        loadCases();
      },
    );
  }
}

/// Case List Provider (requires user ID)
final caseListProvider =
    StateNotifierProvider.family<CaseListNotifier, CaseListState, String>(
  (ref, userEmail) {
    final caseRepository = ref.watch(caseRepositoryProvider);
    return CaseListNotifier(caseRepository, userEmail);
  },
);

/// Case Detail State
class CaseDetailState {
  final Case? caseData;
  final bool isLoading;
  final bool isSaving;
  final String? error;

  CaseDetailState({
    this.caseData,
    this.isLoading = false,
    this.isSaving = false,
    this.error,
  });

  CaseDetailState copyWith({
    Case? caseData,
    bool? isLoading,
    bool? isSaving,
    String? error,
  }) {
    return CaseDetailState(
      caseData: caseData ?? this.caseData,
      isLoading: isLoading ?? this.isLoading,
      isSaving: isSaving ?? this.isSaving,
      error: error,
    );
  }
}

/// Case Detail Notifier
class CaseDetailNotifier extends StateNotifier<CaseDetailState> {
  final ICaseRepository _caseRepository;

  CaseDetailNotifier(this._caseRepository) : super(CaseDetailState());

  /// Load a specific case
  Future<void> loadCase(String caseId) async {
    state = state.copyWith(isLoading: true, error: null);

    final result = await _caseRepository.getCase(caseId);

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (caseData) {
        state = CaseDetailState(
          caseData: caseData,
          isLoading: false,
        );
      },
    );
  }

  /// Create a new case
  Future<bool> createCase({
    required String userEmail,
    required DateTime date,
    String? patientAge,
    String? gender,
    required String asaClassification,
    required String procedureSurgery,
    required String anestheticPlan,
    List<String>? anestheticsUsed,
    required String surgeryClass,
    String? location,
    String? airwayManagement,
    String? additionalComments,
    bool? complications,
  }) async {
    state = state.copyWith(isSaving: true, error: null);

    final result = await _caseRepository.createCase(
      userEmail: userEmail,
      date: date,
      patientAge: patientAge,
      gender: gender,
      asaClassification: asaClassification,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      anestheticsUsed: anestheticsUsed,
      surgeryClass: surgeryClass,
      location: location,
      airwayManagement: airwayManagement,
      additionalComments: additionalComments,
      complications: complications,
    );

    return result.fold(
      (failure) {
        state = state.copyWith(
          isSaving: false,
          error: failure.message,
        );
        return false;
      },
      (caseData) {
        state = CaseDetailState(
          caseData: caseData,
          isSaving: false,
        );
        return true;
      },
    );
  }

  /// Update an existing case
  Future<bool> updateCase({
    required String caseId,
    DateTime? date,
    String? patientAge,
    String? gender,
    String? asaClassification,
    String? procedureSurgery,
    String? anestheticPlan,
    List<String>? anestheticsUsed,
    String? surgeryClass,
    String? location,
    String? airwayManagement,
    String? additionalComments,
    bool? complications,
  }) async {
    state = state.copyWith(isSaving: true, error: null);

    final result = await _caseRepository.updateCase(
      caseId: caseId,
      date: date,
      patientAge: patientAge,
      gender: gender,
      asaClassification: asaClassification,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      anestheticsUsed: anestheticsUsed,
      surgeryClass: surgeryClass,
      location: location,
      airwayManagement: airwayManagement,
      additionalComments: additionalComments,
      complications: complications,
    );

    return result.fold(
      (failure) {
        state = state.copyWith(
          isSaving: false,
          error: failure.message,
        );
        return false;
      },
      (caseData) {
        state = CaseDetailState(
          caseData: caseData,
          isSaving: false,
        );
        return true;
      },
    );
  }
}

/// Case Detail Provider
final caseDetailProvider =
    StateNotifierProvider<CaseDetailNotifier, CaseDetailState>((ref) {
  final caseRepository = ref.watch(caseRepositoryProvider);
  return CaseDetailNotifier(caseRepository);
});
