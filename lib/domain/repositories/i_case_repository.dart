import 'package:dartz/dartz.dart';
import '../entities/case.dart';
import '../../core/errors/failures.dart';

/// Case repository interface
abstract class ICaseRepository {
  /// Create a new case
  Future<Either<Failure, Case>> createCase({
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
  });

  /// Get all cases for a user
  Future<Either<Failure, List<Case>>> getCases(String userEmail);

  /// Get a single case by ID
  Future<Either<Failure, Case>> getCase(String caseId);

  /// Update an existing case
  Future<Either<Failure, Case>> updateCase({
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
  });

  /// Delete a case
  Future<Either<Failure, void>> deleteCase(String caseId);

  /// Search cases by keyword
  Future<Either<Failure, List<Case>>> searchCases({
    required String userEmail,
    String? keyword,
    DateTime? startDate,
    DateTime? endDate,
    String? asaClassification,
    String? surgeryClass,
  });

  /// Get case count for a user
  Future<Either<Failure, int>> getCaseCount(String userEmail);

  /// Sync local cases to remote
  Future<Either<Failure, void>> syncCases(String userEmail);
}
