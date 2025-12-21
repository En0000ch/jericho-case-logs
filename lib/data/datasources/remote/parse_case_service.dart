import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/constants/api_constants.dart';

/// Parse Server Case Service
class ParseCaseService {
  static const String _className = ApiConstants.casesClass;

  /// Create a new case
  static Future<ParseResponse> createCase({
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
    String? imageName,
  }) async {
    final caseObject = ParseObject(_className)
      ..set('userEmail', userEmail)
      ..set('date', date)
      ..set('asaClassification', asaClassification)
      ..set('procSurgery', procedureSurgery)
      ..set('anestheticPlan', anestheticPlan)
      ..set('anestheticsUsed', anestheticsUsed ?? [])
      ..set('surgeryClass', surgeryClass);

    if (patientAge != null) caseObject.set('patientAge', patientAge);
    if (gender != null) caseObject.set('gender', gender);
    if (location != null) caseObject.set('location', location);
    if (airwayManagement != null) {
      caseObject.set('airwayManagement', airwayManagement);
    }
    if (additionalComments != null) {
      caseObject.set('additionalComments', additionalComments);
    }
    if (complications != null) caseObject.set('complications', complications);
    if (imageName != null) caseObject.set('imageName', imageName);

    return await caseObject.save();
  }

  /// Get all cases for a user
  static Future<ParseResponse> getCases(String userEmail) async {
    final query = QueryBuilder<ParseObject>(ParseObject(_className))
      ..whereEqualTo('userEmail', userEmail)
      ..orderByDescending('date');

    return await query.query();
  }

  /// Get a single case by ID
  static Future<ParseResponse> getCase(String caseId) async {
    final query = QueryBuilder<ParseObject>(ParseObject(_className))
      ..whereEqualTo('objectId', caseId);

    return await query.query();
  }

  /// Update an existing case
  static Future<ParseResponse> updateCase({
    required String caseId,
    DateTime? date,
    String? patientAge,
    String? gender,
    String? asaClassification,
    String? procedureSurgery,
    String? anestheticPlan,
    String? secondaryAnesthetic,
    List<String>? anestheticsUsed,
    String? surgeryClass,
    String? location,
    String? airwayManagement,
    String? additionalComments,
    bool? complications,
    String? imageName,
  }) async {
    final caseObject = ParseObject(_className)..objectId = caseId;

    if (date != null) caseObject.set('date', date);
    if (patientAge != null) caseObject.set('patientAge', patientAge);
    if (gender != null) caseObject.set('gender', gender);
    if (asaClassification != null) {
      caseObject.set('asaClassification', asaClassification);
    }
    if (procedureSurgery != null) {
      caseObject.set('procSurgery', procedureSurgery);
    }
    if (anestheticPlan != null) {
      caseObject.set('anestheticPlan', anestheticPlan);
    }
    // iOS app uses 'secPlan' field for secondary anesthetic
    if (secondaryAnesthetic != null) {
      caseObject.set('secPlan', secondaryAnesthetic);
      caseObject.set('secondaryAnesthetic', secondaryAnesthetic); // Keep for Flutter compatibility
    }
    if (anestheticsUsed != null) {
      caseObject.set('anestheticsUsed', anestheticsUsed);
    }
    if (surgeryClass != null) caseObject.set('surgeryClass', surgeryClass);
    if (location != null) caseObject.set('location', location);
    if (airwayManagement != null) {
      caseObject.set('airwayManagement', airwayManagement);
    }
    if (additionalComments != null) {
      caseObject.set('additionalComments', additionalComments);
    }
    if (complications != null) caseObject.set('complications', complications);
    if (imageName != null) caseObject.set('imageName', imageName);

    return await caseObject.save();
  }

  /// Delete a case
  static Future<ParseResponse> deleteCase(String caseId) async {
    final caseObject = ParseObject(_className)..objectId = caseId;
    return await caseObject.delete();
  }

  /// Search cases with filters
  static Future<ParseResponse> searchCases({
    required String userEmail,
    String? keyword,
    DateTime? startDate,
    DateTime? endDate,
    String? asaClassification,
    String? surgeryClass,
  }) async {
    final query = QueryBuilder<ParseObject>(ParseObject(_className))
      ..whereEqualTo('userEmail', userEmail)
      ..orderByDescending('date');

    // Apply filters
    if (keyword != null && keyword.isNotEmpty) {
      // Search in procedure/surgery field
      query.whereContains('procSurgery', keyword, caseSensitive: false);
    }

    if (startDate != null) {
      query.whereGreaterThanOrEqualsTo('date', startDate);
    }

    if (endDate != null) {
      query.whereLessThanOrEqualTo('date', endDate);
    }

    if (asaClassification != null && asaClassification.isNotEmpty) {
      query.whereEqualTo('asaClassification', asaClassification);
    }

    if (surgeryClass != null && surgeryClass.isNotEmpty) {
      query.whereEqualTo('surgeryClass', surgeryClass);
    }

    return await query.query();
  }

  /// Get case count for a user
  static Future<ParseResponse> getCaseCount(String userEmail) async {
    final query = QueryBuilder<ParseObject>(ParseObject(_className))
      ..whereEqualTo('userEmail', userEmail);

    return await query.count();
  }

  /// Get saved surgeries for a user
  /// Returns a list of custom surgeries saved by the user
  static Future<ParseResponse> getSavedSurgeries(String userEmail) async {
    final query = QueryBuilder<ParseObject>(
      ParseObject(ApiConstants.savedSurgeriesClass),
    )
      ..whereEqualTo('userEmail', userEmail)
      ..orderByAscending('subSurgery');

    return await query.query();
  }
}
