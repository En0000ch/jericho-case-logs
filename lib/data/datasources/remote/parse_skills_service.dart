import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing skills data with Parse Server
/// Flutter equivalent of iOS skillsManager
class ParseSkillsService {
  /// Fetch skills from Parse Server for the current user
  Future<List<String>> fetchSkillsFromServer() async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final query = QueryBuilder<ParseObject>(ParseObject('savedSkills'))
      ..whereEqualTo('userEmail', currentUser.emailAddress)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    if (response.success && response.results != null && response.results!.isNotEmpty) {
      final skillsObject = response.results!.first as ParseObject;
      final skillsList = skillsObject.get<List<dynamic>>('userSkills') ?? [];

      // Filter to ensure only valid strings or numbers
      return skillsList
          .where((s) => (s is String || s is num) && s.toString().isNotEmpty)
          .map((s) => s.toString())
          .toList();
    }

    return [];
  }

  /// Update skills list on Parse Server
  Future<bool> updateSkills(List<String> skills) async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final userEmail = currentUser.emailAddress;
    if (userEmail == null) {
      throw Exception('User email not found');
    }

    // Check if object exists
    final query = QueryBuilder<ParseObject>(ParseObject('savedSkills'))
      ..whereEqualTo('userEmail', userEmail)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    ParseObject skillsObject;
    if (response.success && response.results != null && response.results!.isNotEmpty) {
      // Update existing object
      skillsObject = response.results!.first as ParseObject;
      skillsObject.set('userSkills', skills);
    } else {
      // Create new object
      skillsObject = ParseObject('savedSkills')
        ..set('userEmail', userEmail)
        ..set('userSkills', skills);
    }

    final saveResponse = await skillsObject.save();
    return saveResponse.success;
  }

  /// Add a single skill to the list
  Future<bool> addSkill(String skillName, List<String> currentSkills) async {
    if (skillName.isEmpty || currentSkills.contains(skillName)) {
      return false;
    }

    final updatedList = [...currentSkills, skillName];
    return await updateSkills(updatedList);
  }

  /// Fetch all skills from all users (for autocomplete/suggestions)
  Future<List<String>> fetchAllSkillsFromServer() async {
    final query = QueryBuilder<ParseObject>(ParseObject('savedSkills'));
    final response = await query.query();

    final allSkills = <String>{};
    if (response.success && response.results != null) {
      for (var object in response.results!) {
        final skills = (object as ParseObject).get<List<dynamic>>('userSkills') ?? [];
        allSkills.addAll(
          skills
              .where((s) => (s is String || s is num) && s.toString().isNotEmpty)
              .map((s) => s.toString())
        );
      }
    }

    return allSkills.toList()..sort();
  }
}
