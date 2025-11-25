import 'package:dartz/dartz.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../domain/entities/case.dart';
import '../../domain/repositories/i_case_repository.dart';
import '../../core/errors/failures.dart';
import '../models/case_model.dart';
import '../datasources/remote/parse_case_service.dart';
import '../datasources/local/database_helper.dart';

/// Case repository implementation
class CaseRepository implements ICaseRepository {
  final DatabaseHelper _databaseHelper;

  CaseRepository(this._databaseHelper);

  @override
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
  }) async {
    try {
      final response = await ParseCaseService.createCase(
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

      if (response.success && response.result != null) {
        final parseObject = response.result as ParseObject;
        final caseModel = CaseModel.fromParseObject(parseObject);

        // Save to local database
        await _databaseHelper.insertCase(caseModel);

        return Right(caseModel.toDomain());
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Failed to create case'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Error creating case: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, List<Case>>> getCases(String userEmail) async {
    try {
      // Try to get from remote first
      print('DEBUG REPO: Fetching cases for user: $userEmail');
      final response = await ParseCaseService.getCases(userEmail);

      print('DEBUG REPO: Parse response success=${response.success}, results count=${response.results?.length ?? 0}');

      if (response.success && response.results != null) {
        print('DEBUG REPO: Raw Parse results: ${response.results}');

        final cases = (response.results as List<dynamic>)
            .map((e) {
              final parseObject = e as ParseObject;
              print('DEBUG REPO: Parsing ParseObject with objectId=${parseObject.objectId}');
              print('DEBUG REPO: Fields - surgery=${parseObject.get<String>('surgery')}, primePlan=${parseObject.get<String>('primePlan')}, dateTime=${parseObject.get<DateTime>('dateTime')}');
              final caseModel = CaseModel.fromParseObject(parseObject);
              print('DEBUG REPO: Parsed to CaseModel - objectId=${caseModel.objectId}, procedureSurgery=${caseModel.procedureSurgery}, date=${caseModel.date}');
              return caseModel;
            })
            .toList();

        print('DEBUG REPO: Successfully parsed ${cases.length} cases');

        // Update local database
        for (final caseModel in cases) {
          await _databaseHelper.insertCase(caseModel);
        }

        final domainCases = cases.map((e) => e.toDomain()).toList();
        print('DEBUG REPO: Converted to ${domainCases.length} domain entities');
        return Right(domainCases);
      } else {
        print('DEBUG REPO: Parse failed or no results, trying local database');
        // If remote fails, get from local database
        final localCases = await _databaseHelper.getCases(userEmail);
        print('DEBUG REPO: Retrieved ${localCases.length} cases from local database');
        return Right(localCases.map((e) => e.toDomain()).toList());
      }
    } catch (e, stackTrace) {
      print('DEBUG REPO: Error in getCases: $e');
      print('DEBUG REPO: Stack trace: $stackTrace');
      // If any error, try to get from local database
      try {
        final localCases = await _databaseHelper.getCases(userEmail);
        print('DEBUG REPO: Fallback - Retrieved ${localCases.length} cases from local database');
        return Right(localCases.map((e) => e.toDomain()).toList());
      } catch (localError) {
        print('DEBUG REPO: Local database also failed: $localError');
        return Left(
          DatabaseFailure('Error getting cases: ${localError.toString()}'),
        );
      }
    }
  }

  @override
  Future<Either<Failure, Case>> getCase(String caseId) async {
    try {
      // Try remote first
      final response = await ParseCaseService.getCase(caseId);

      if (response.success &&
          response.results != null &&
          response.results!.isNotEmpty) {
        final caseModel =
            CaseModel.fromParseObject(response.results!.first as ParseObject);

        // Update local database
        await _databaseHelper.insertCase(caseModel);

        return Right(caseModel.toDomain());
      } else {
        // Try local database
        final localCase = await _databaseHelper.getCase(caseId);
        if (localCase != null) {
          return Right(localCase.toDomain());
        }
        return const Left(CacheFailure('Case not found'));
      }
    } catch (e) {
      // Try local database
      try {
        final localCase = await _databaseHelper.getCase(caseId);
        if (localCase != null) {
          return Right(localCase.toDomain());
        }
        return const Left(CacheFailure('Case not found'));
      } catch (localError) {
        return Left(
          DatabaseFailure('Error getting case: ${localError.toString()}'),
        );
      }
    }
  }

  @override
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
  }) async {
    try {
      final response = await ParseCaseService.updateCase(
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

      if (response.success && response.result != null) {
        final parseObject = response.result as ParseObject;
        final caseModel = CaseModel.fromParseObject(parseObject);

        // Update local database
        await _databaseHelper.updateCase(caseModel);

        return Right(caseModel.toDomain());
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Failed to update case'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Error updating case: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, void>> deleteCase(String caseId) async {
    try {
      final response = await ParseCaseService.deleteCase(caseId);

      if (response.success) {
        // Delete from local database
        await _databaseHelper.deleteCase(caseId);
        return const Right(null);
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Failed to delete case'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Error deleting case: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, List<Case>>> searchCases({
    required String userEmail,
    String? keyword,
    DateTime? startDate,
    DateTime? endDate,
    String? asaClassification,
    String? surgeryClass,
  }) async {
    try {
      // Try remote first
      final response = await ParseCaseService.searchCases(
        userEmail: userEmail,
        keyword: keyword,
        startDate: startDate,
        endDate: endDate,
        asaClassification: asaClassification,
        surgeryClass: surgeryClass,
      );

      if (response.success && response.results != null) {
        final cases = (response.results as List<dynamic>)
            .map((e) => CaseModel.fromParseObject(e as ParseObject))
            .toList();

        return Right(cases.map((e) => e.toDomain()).toList());
      } else {
        // If remote fails, search local database
        final localCases = await _databaseHelper.searchCases(
          userEmail: userEmail,
          keyword: keyword,
          startDate: startDate,
          endDate: endDate,
          asaClassification: asaClassification,
          surgeryClass: surgeryClass,
        );
        return Right(localCases.map((e) => e.toDomain()).toList());
      }
    } catch (e) {
      // If any error, try local database
      try {
        final localCases = await _databaseHelper.searchCases(
          userEmail: userEmail,
          keyword: keyword,
          startDate: startDate,
          endDate: endDate,
          asaClassification: asaClassification,
          surgeryClass: surgeryClass,
        );
        return Right(localCases.map((e) => e.toDomain()).toList());
      } catch (localError) {
        return Left(
          DatabaseFailure('Error searching cases: ${localError.toString()}'),
        );
      }
    }
  }

  @override
  Future<Either<Failure, int>> getCaseCount(String userEmail) async {
    try {
      // Try remote first
      final response = await ParseCaseService.getCaseCount(userEmail);

      if (response.success) {
        return Right(response.count);
      } else {
        // If remote fails, get from local database
        final localCount = await _databaseHelper.getCaseCount(userEmail);
        return Right(localCount);
      }
    } catch (e) {
      // If any error, try local database
      try {
        final localCount = await _databaseHelper.getCaseCount(userEmail);
        return Right(localCount);
      } catch (localError) {
        return Left(
          DatabaseFailure(
              'Error getting case count: ${localError.toString()}'),
        );
      }
    }
  }

  @override
  Future<Either<Failure, void>> syncCases(String userEmail) async {
    try {
      // Get all cases from remote
      final response = await ParseCaseService.getCases(userEmail);

      if (response.success && response.results != null) {
        // Clear local database for this user
        await _databaseHelper.deleteAllCases(userEmail);

        // Insert all cases from remote
        final cases = (response.results as List<dynamic>)
            .map((e) => CaseModel.fromParseObject(e as ParseObject))
            .toList();

        for (final caseModel in cases) {
          await _databaseHelper.insertCase(caseModel);
        }

        return const Right(null);
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Failed to sync cases'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Error syncing cases: ${e.toString()}'));
    }
  }
}
