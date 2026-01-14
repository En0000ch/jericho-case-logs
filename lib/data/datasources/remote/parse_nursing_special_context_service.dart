import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing custom nursing special contexts in Parse Server
class ParseNursingSpecialContextService {
  static const String _className = 'nursingCustomSpecialContexts';

  /// Fetch user's custom special contexts from Parse Server
  Future<List<String>> fetchCustomContexts(String userEmail) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        final customContextsList = parseObject.get<List<dynamic>>('customContexts');

        if (customContextsList != null) {
          return customContextsList.map((e) => e.toString()).toList();
        }
      }

      return [];
    } catch (e) {
      print('Error fetching custom special contexts: $e');
      return [];
    }
  }

  /// Add a custom special context for a user
  Future<bool> addCustomContext(String userEmail, String contextName) async {
    try {
      // Fetch existing contexts
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();
      ParseObject parseObject;

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        // Update existing record
        parseObject = response.results!.first as ParseObject;
        final existingContexts = parseObject.get<List<dynamic>>('customContexts') ?? [];

        // Check if context already exists
        if (existingContexts.contains(contextName)) {
          return false;
        }

        existingContexts.add(contextName);
        parseObject.set('customContexts', existingContexts);
      } else {
        // Create new record
        parseObject = ParseObject(_className)
          ..set('userEmail', userEmail)
          ..set('customContexts', [contextName]);
      }

      final saveResponse = await parseObject.save();
      return saveResponse.success;
    } catch (e) {
      print('Error adding custom special context: $e');
      return false;
    }
  }

  /// Remove a custom special context for a user
  Future<bool> removeCustomContext(String userEmail, String contextName) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(_className))
        ..whereEqualTo('userEmail', userEmail)
        ..orderByDescending('updatedAt')
        ..setLimit(1);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        final existingContexts = parseObject.get<List<dynamic>>('customContexts') ?? [];

        existingContexts.remove(contextName);
        parseObject.set('customContexts', existingContexts);

        final saveResponse = await parseObject.save();
        return saveResponse.success;
      }

      return false;
    } catch (e) {
      print('Error removing custom special context: $e');
      return false;
    }
  }
}
