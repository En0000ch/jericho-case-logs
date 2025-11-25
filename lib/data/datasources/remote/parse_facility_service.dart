import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for managing facility data with Parse Server
/// Flutter equivalent of iOS facilityManager
class ParseFacilityService {
  /// Fetch facilities from Parse Server for the current user
  Future<List<String>> fetchFacilitiesFromServer() async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final query = QueryBuilder<ParseObject>(ParseObject('savedFacilities'))
      ..whereEqualTo('userEmail', currentUser.emailAddress)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    if (response.success && response.results != null && response.results!.isNotEmpty) {
      final facilityObject = response.results!.first as ParseObject;
      final facilityList = facilityObject.get<List<dynamic>>('userFacilities') ?? [];

      // Filter out empty strings and ensure only valid strings
      return facilityList
          .where((f) => f is String && f.isNotEmpty)
          .map((f) => f.toString())
          .toList();
    }

    return [];
  }

  /// Update facility list on Parse Server
  Future<bool> updateFacilities(List<String> facilities) async {
    final currentUser = await ParseUser.currentUser() as ParseUser?;
    if (currentUser == null) {
      throw Exception('No user logged in');
    }

    final userEmail = currentUser.emailAddress;
    if (userEmail == null) {
      throw Exception('User email not found');
    }

    // Filter out empty strings
    final filteredFacilities = facilities.where((f) => f.isNotEmpty).toList();

    // Check if object exists
    final query = QueryBuilder<ParseObject>(ParseObject('savedFacilities'))
      ..whereEqualTo('userEmail', userEmail)
      ..orderByDescending('updatedAt')
      ..setLimit(1);

    final response = await query.query();

    ParseObject facilityObject;
    if (response.success && response.results != null && response.results!.isNotEmpty) {
      // Update existing object
      facilityObject = response.results!.first as ParseObject;
      facilityObject.set('userFacilities', filteredFacilities);
    } else {
      // Create new object
      facilityObject = ParseObject('savedFacilities')
        ..set('userEmail', userEmail)
        ..set('userFacilities', filteredFacilities);
    }

    final saveResponse = await facilityObject.save();
    return saveResponse.success;
  }

  /// Add a single facility to the list
  Future<bool> addFacility(String facilityName, List<String> currentFacilities) async {
    if (facilityName.isEmpty || currentFacilities.contains(facilityName)) {
      return false;
    }

    final updatedList = [...currentFacilities, facilityName];
    return await updateFacilities(updatedList);
  }

  /// Fetch all facilities from all users (for autocomplete/suggestions)
  Future<List<String>> fetchAllFacilitiesFromServer() async {
    final query = QueryBuilder<ParseObject>(ParseObject('savedFacilities'));
    final response = await query.query();

    final allFacilities = <String>{};
    if (response.success && response.results != null) {
      for (var object in response.results!) {
        final facilities = (object as ParseObject).get<List<dynamic>>('userFacilities') ?? [];
        allFacilities.addAll(
          facilities
              .where((f) => f is String && f.isNotEmpty)
              .map((f) => f.toString())
        );
      }
    }

    return allFacilities.toList()..sort();
  }
}
