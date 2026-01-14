import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Parse service for managing user-specific custom nursing clinical scenarios
///
/// Parse Class: nursingCustomComorbidities
/// Fields:
///   - userEmail (String): User's email address
///   - customComorbidities (Array): List of custom clinical scenarios added by this user
class ParseNursingComorbiditiesService {
  static const String _className = 'nursingCustomComorbidities';

  /// Fetch custom clinical scenarios for a specific user
  /// Returns list of custom clinical scenario strings
  Future<List<String>> fetchCustomComorbidities(String userEmail) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        final List<dynamic>? comorbiditiesArray = parseObject.get<List<dynamic>>('customComorbidities');

        if (comorbiditiesArray != null) {
          return comorbiditiesArray.cast<String>();
        }
      }

      return [];
    } catch (e) {
      print('Error fetching custom comorbidities: $e');
      return [];
    }
  }

  /// Add a new custom clinical scenario for a user
  /// Returns true if successful
  Future<bool> addCustomComorbidity(String userEmail, String comorbidityName) async {
    try {
      // First, fetch existing custom clinical scenarios
      final existingComorbidities = await fetchCustomComorbidities(userEmail);

      // Check if clinical scenario already exists (case-insensitive)
      if (existingComorbidities.any((c) => c.toLowerCase() == comorbidityName.toLowerCase())) {
        print('Clinical scenario already exists');
        return false;
      }

      // Add new clinical scenario to list
      final updatedComorbidities = [...existingComorbidities, comorbidityName];

      // Try to find existing record
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail);

      final response = await query.query();

      ParseObject parseObject;
      if (response.success && response.results != null && response.results!.isNotEmpty) {
        // Update existing record
        parseObject = response.results!.first as ParseObject;
      } else {
        // Create new record
        parseObject = ParseObject(_className)
          ..set('userEmail', userEmail);
      }

      // Set updated comorbidities array
      parseObject.set('customComorbidities', updatedComorbidities);

      final saveResponse = await parseObject.save();
      return saveResponse.success;
    } catch (e) {
      print('Error adding custom comorbidity: $e');
      return false;
    }
  }

  /// Remove a custom clinical scenario for a user
  /// Returns true if successful
  Future<bool> removeCustomComorbidity(String userEmail, String comorbidityName) async {
    try {
      final existingComorbidities = await fetchCustomComorbidities(userEmail);

      // Remove clinical scenario (case-insensitive match)
      final updatedComorbidities = existingComorbidities
          .where((c) => c.toLowerCase() != comorbidityName.toLowerCase())
          .toList();

      if (updatedComorbidities.length == existingComorbidities.length) {
        print('Clinical scenario not found');
        return false;
      }

      // Find existing record
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        parseObject.set('customComorbidities', updatedComorbidities);

        final saveResponse = await parseObject.save();
        return saveResponse.success;
      }

      return false;
    } catch (e) {
      print('Error removing custom comorbidity: $e');
      return false;
    }
  }

  /// Get all clinical scenarios (default + custom) for a user, alphabetically sorted
  Future<List<String>> getAllComorbiditiesForUser(String userEmail, List<String> defaultComorbidities) async {
    final customComorbidities = await fetchCustomComorbidities(userEmail);
    final allComorbidities = [...defaultComorbidities, ...customComorbidities];
    allComorbidities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return allComorbidities;
  }
}
