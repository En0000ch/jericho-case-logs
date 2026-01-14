import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Parse service for managing user-specific custom nursing skills
///
/// Parse Class: nursingCustomSkills
/// Fields:
///   - userEmail (String): User's email address
///   - customSkills (Array): List of custom skills added by this user
class ParseNursingSkillsService {
  static const String _className = 'nursingCustomSkills';

  /// Fetch custom skills for a specific user
  /// Returns list of custom skill strings
  Future<List<String>> fetchCustomSkills(String userEmail) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        final List<dynamic>? skillsArray = parseObject.get<List<dynamic>>('customSkills');

        if (skillsArray != null) {
          return skillsArray.cast<String>();
        }
      }

      return [];
    } catch (e) {
      print('Error fetching custom skills: $e');
      return [];
    }
  }

  /// Add a new custom skill for a user
  /// Returns true if successful
  Future<bool> addCustomSkill(String userEmail, String skillName) async {
    try {
      // First, fetch existing custom skills
      final existingSkills = await fetchCustomSkills(userEmail);

      // Check if skill already exists (case-insensitive)
      if (existingSkills.any((s) => s.toLowerCase() == skillName.toLowerCase())) {
        print('Skill already exists');
        return false;
      }

      // Add new skill to list
      final updatedSkills = [...existingSkills, skillName];

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

      // Set updated skills array
      parseObject.set('customSkills', updatedSkills);

      final saveResponse = await parseObject.save();
      return saveResponse.success;
    } catch (e) {
      print('Error adding custom skill: $e');
      return false;
    }
  }

  /// Remove a custom skill for a user
  /// Returns true if successful
  Future<bool> removeCustomSkill(String userEmail, String skillName) async {
    try {
      final existingSkills = await fetchCustomSkills(userEmail);

      // Remove skill (case-insensitive match)
      final updatedSkills = existingSkills
          .where((s) => s.toLowerCase() != skillName.toLowerCase())
          .toList();

      if (updatedSkills.length == existingSkills.length) {
        print('Skill not found');
        return false;
      }

      // Find existing record
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        parseObject.set('customSkills', updatedSkills);

        final saveResponse = await parseObject.save();
        return saveResponse.success;
      }

      return false;
    } catch (e) {
      print('Error removing custom skill: $e');
      return false;
    }
  }

  /// Get all skills (default + custom) for a user, alphabetically sorted
  Future<List<String>> getAllSkillsForUser(String userEmail, List<String> defaultSkills) async {
    final customSkills = await fetchCustomSkills(userEmail);
    final allSkills = [...defaultSkills, ...customSkills];
    allSkills.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return allSkills;
  }
}
