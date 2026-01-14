import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing patient records in Parse Server
class ParsePatientService {
  static const String _className = 'nursePatients';

  /// Save a new patient record
  Future<bool> savePatient({
    required String userEmail,
    required String ageRange,
    required String gender,
    required String medicalUnit,
    required List<String> skills,
    required List<String> scenarios,
    required String acuity,
    required List<String> specialContext,
  }) async {
    try {
      final patient = ParseObject(_className)
        ..set('userEmail', userEmail)
        ..set('ageRange', ageRange)
        ..set('gender', gender)
        ..set('medicalUnit', medicalUnit)
        ..set('skills', skills)
        ..set('scenarios', scenarios)
        ..set('acuity', acuity)
        ..set('specialContext', specialContext)
        ..set('createdAt', DateTime.now());

      final response = await patient.save();

      if (response.success) {
        print('✅ Patient saved successfully: ${response.result}');
        return true;
      } else {
        print('❌ Failed to save patient: ${response.error?.message}');
        return false;
      }
    } catch (e) {
      print('❌ Error saving patient: $e');
      return false;
    }
  }

  /// Fetch all patients for a user
  Future<List<ParseObject>> fetchPatients(String userEmail) async {
    try {
      print('🔍 Fetching patients for user: $userEmail');

      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('createdAt');

      final response = await query.query();

      print('📦 Query response - success: ${response.success}, results count: ${response.results?.length ?? 0}');

      if (response.success && response.results != null) {
        print('✅ Found ${response.results!.length} patients');
        return response.results!.cast<ParseObject>();
      }

      print('⚠️ No patients found or query failed');
      return [];
    } catch (e) {
      print('❌ Error fetching patients: $e');
      return [];
    }
  }

  /// Delete a patient record
  Future<bool> deletePatient(String objectId) async {
    try {
      final patient = ParseObject(_className)..objectId = objectId;
      final response = await patient.delete();
      return response.success;
    } catch (e) {
      print('Error deleting patient: $e');
      return false;
    }
  }

  /// Fetch patients from last 24 hours (or since last app use)
  /// Returns patients created within the specified lookback period
  Future<List<ParseObject>> fetchPatientsFromLast24Hours(
    String userEmail, {
    DateTime? lastAppUse,
    Duration lookbackPeriod = const Duration(hours: 24),
  }) async {
    try {
      final now = DateTime.now();

      // Calculate cutoff time: use the later of (lastAppUse or 24 hours ago)
      DateTime cutoffTime;
      if (lastAppUse != null) {
        final lookbackTime = now.subtract(lookbackPeriod);
        cutoffTime = lastAppUse.isAfter(lookbackTime) ? lastAppUse : lookbackTime;
      } else {
        cutoffTime = now.subtract(lookbackPeriod);
      }

      print('🔍 Fetching patients from last 24 hours for: $userEmail');
      print('   Cutoff time: ${cutoffTime.toIso8601String()}');

      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..whereGreaterThanOrEqualsTo('createdAt', cutoffTime)
        ..orderByDescending('createdAt');

      final response = await query.query();

      if (response.success && response.results != null) {
        print('✅ Found ${response.results!.length} patients from last 24 hours');
        return response.results!.cast<ParseObject>();
      }

      print('⚠️ No patients found from last 24 hours');
      return [];
    } catch (e) {
      print('❌ Error fetching patients from last 24 hours: $e');
      return [];
    }
  }

  /// Batch delete multiple patients
  /// Takes a list of patient object IDs and deletes them all
  /// Returns true if all deletions succeeded
  Future<bool> removePatients(List<String> patientIds) async {
    if (patientIds.isEmpty) {
      print('⚠️ No patients to remove');
      return true;
    }

    try {
      print('🗑️ Removing ${patientIds.length} patients...');

      int successCount = 0;
      int failCount = 0;

      // Delete each patient
      for (final patientId in patientIds) {
        final patient = ParseObject(_className)..objectId = patientId;
        final response = await patient.delete();

        if (response.success) {
          successCount++;
        } else {
          failCount++;
          print('❌ Failed to delete patient $patientId: ${response.error?.message}');
        }
      }

      print('✅ Batch delete complete: $successCount succeeded, $failCount failed');

      // Return true if all deletions succeeded
      return failCount == 0;
    } catch (e) {
      print('❌ Error during batch delete: $e');
      return false;
    }
  }
}
