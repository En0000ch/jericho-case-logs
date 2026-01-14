import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing user's selected medical unit in Parse Server
class ParseMedicalUnitService {
  static const String _className = 'nurseMedicalUnit';

  /// Fetch user's selected medical unit from Parse Server
  Future<String?> fetchMedicalUnit(String userEmail) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        return parseObject.get<String>('medicalUnit');
      }

      return null;
    } catch (e) {
      print('Error fetching medical unit: $e');
      return null;
    }
  }

  /// Save user's selected medical unit
  Future<bool> saveMedicalUnit(String userEmail, String medicalUnit) async {
    try {
      // Check if record exists
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();
      ParseObject parseObject;

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        // Update existing record
        parseObject = response.results!.first as ParseObject;
        parseObject.set('medicalUnit', medicalUnit);
      } else {
        // Create new record
        parseObject = ParseObject(_className)
          ..set('userEmail', userEmail)
          ..set('medicalUnit', medicalUnit);
      }

      final saveResponse = await parseObject.save();
      return saveResponse.success;
    } catch (e) {
      print('Error saving medical unit: $e');
      return false;
    }
  }
}
