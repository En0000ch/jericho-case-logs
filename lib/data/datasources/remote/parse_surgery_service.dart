import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/constants/api_constants.dart';
import '../../../core/data/surgery_data.dart';

/// Parse Server Surgery Service
/// Handles fetching and merging user-specific saved surgeries with default surgery lists
class ParseSurgeryService {
  /// Get all surgeries for a specialty, merging default and user-saved surgeries
  /// Returns a sorted list of unique surgery names
  static Future<List<String>> getSurgeriesForSpecialty(
    String specialty,
    String userEmail,
  ) async {
    // Get default surgeries for this specialty
    final defaultSurgeries = SurgeryData.getDefaultSurgeriesForSpecialty(specialty);

    // Create a set to avoid duplicates
    final surgeriesSet = Set<String>.from(defaultSurgeries);

    try {
      // Fetch user's saved surgeries from Parse
      final query = QueryBuilder<ParseObject>(
        ParseObject(ApiConstants.savedSurgeriesClass),
      )
        ..whereEqualTo('userEmail', userEmail)
        ..whereEqualTo('surgeryClass', specialty);

      final response = await query.query();

      if (response.success && response.results != null) {
        // Add saved surgeries to the set
        for (var obj in response.results!) {
          final parseObj = obj as ParseObject;
          final subSurgery = parseObj.get<String>('subSurgery');

          if (subSurgery != null && subSurgery.trim().isNotEmpty) {
            surgeriesSet.add(subSurgery.trim());
          }
        }
      }
    } catch (e) {
      // If there's an error fetching saved surgeries, just use defaults
      print('Error fetching saved surgeries: $e');
    }

    // Convert set to list and sort alphabetically (case-insensitive)
    final sortedList = surgeriesSet.toList();
    sortedList.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));

    return sortedList;
  }

  /// Get all surgeries for all specialties, merging default and user-saved surgeries
  /// Returns a map of specialty name to sorted surgery list
  static Future<Map<String, List<String>>> getAllSurgeries(String userEmail) async {
    final result = <String, List<String>>{};

    // Process each specialty
    for (final specialty in SurgeryData.specialtyToKey.keys) {
      result[specialty] = await getSurgeriesForSpecialty(specialty, userEmail);
    }

    return result;
  }

  /// Save a new surgery to Parse
  static Future<ParseResponse> saveSurgery({
    required String userEmail,
    required String surgeryClass,
    required String subSurgery,
  }) async {
    final surgeryObject = ParseObject(ApiConstants.savedSurgeriesClass)
      ..set('userEmail', userEmail)
      ..set('surgeryClass', surgeryClass)
      ..set('subSurgery', subSurgery);

    return await surgeryObject.save();
  }

  /// Delete a saved surgery from Parse
  static Future<ParseResponse> deleteSavedSurgery(String objectId) async {
    final surgeryObject = ParseObject(ApiConstants.savedSurgeriesClass)
      ..objectId = objectId;

    return await surgeryObject.delete();
  }

  /// Check if a surgery exists in user's saved surgeries
  static Future<bool> isSurgerySaved({
    required String userEmail,
    required String surgeryClass,
    required String subSurgery,
  }) async {
    final query = QueryBuilder<ParseObject>(
      ParseObject(ApiConstants.savedSurgeriesClass),
    )
      ..whereEqualTo('userEmail', userEmail)
      ..whereEqualTo('surgeryClass', surgeryClass)
      ..whereEqualTo('subSurgery', subSurgery);

    final response = await query.count();

    return response.success && response.count > 0;
  }
}
