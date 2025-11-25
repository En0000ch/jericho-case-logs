import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing surgeon data with Parse Server
/// Flutter equivalent of iOS surgeonManager
class ParseSurgeonService {
  /// Fetch surgeons from Parse Server for the current user
  Future<List<String>> fetchSurgeonsFromServer() async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final query = QueryBuilder<ParseObject>(ParseObject('savedSurgeons'))
      ..whereEqualTo('userEmail', currentUser.emailAddress)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    if (response.success && response.results != null && response.results!.isNotEmpty) {
      final surgeonObject = response.results!.first as ParseObject;
      final surgeonList = surgeonObject.get<List<dynamic>>('userSurgeons') ?? [];
      return surgeonList.map((e) => e.toString()).where((s) => s.isNotEmpty).toList();
    }

    return [];
  }

  /// Update surgeon list on Parse Server
  Future<bool> updateSurgeons(List<String> surgeons) async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final userEmail = currentUser.emailAddress;
    if (userEmail == null) {
      throw Exception('User email not found');
    }

    // Check if object exists
    final query = QueryBuilder<ParseObject>(ParseObject('savedSurgeons'))
      ..whereEqualTo('userEmail', userEmail)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    ParseObject surgeonObject;
    if (response.success && response.results != null && response.results!.isNotEmpty) {
      // Update existing object
      surgeonObject = response.results!.first as ParseObject;
      surgeonObject.set('userSurgeons', surgeons);
    } else {
      // Create new object
      surgeonObject = ParseObject('savedSurgeons')
        ..set('userEmail', userEmail)
        ..set('userSurgeons', surgeons);
    }

    final saveResponse = await surgeonObject.save();
    return saveResponse.success;
  }

  /// Add a single surgeon to the list
  Future<bool> addSurgeon(String surgeonName, List<String> currentSurgeons) async {
    if (surgeonName.isEmpty || currentSurgeons.contains(surgeonName)) {
      return false;
    }

    final updatedList = [...currentSurgeons, surgeonName];
    return await updateSurgeons(updatedList);
  }

  /// Fetch all surgeons from all users (for autocomplete/suggestions)
  Future<List<String>> fetchAllSurgeonsFromServer() async {
    final query = QueryBuilder<ParseObject>(ParseObject('savedSurgeons'));
    final response = await query.query();

    final allSurgeons = <String>{};
    if (response.success && response.results != null) {
      for (var object in response.results!) {
        final surgeons = (object as ParseObject).get<List<dynamic>>('userSurgeons') ?? [];
        allSurgeons.addAll(surgeons.map((e) => e.toString()).where((s) => s.isNotEmpty));
      }
    }

    return allSurgeons.toList()..sort();
  }
}
