import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/datasources/remote/parse_facility_service.dart';
import '../../data/datasources/local/shared_prefs_service.dart';
import 'surgeon_provider.dart';

/// Provider for facility service
final facilityServiceProvider = Provider<ParseFacilityService>((ref) {
  return ParseFacilityService();
});

/// State notifier for managing facility list
class FacilityNotifier extends StateNotifier<AsyncValue<List<String>>> {
  FacilityNotifier(this._facilityService, this._sharedPrefs)
      : super(const AsyncValue.loading()) {
    _init();
  }

  final ParseFacilityService _facilityService;
  final SharedPrefsService _sharedPrefs;

  /// Initialize by loading from local cache first, then syncing with server
  Future<void> _init() async {
    try {
      // Load from local cache first for immediate UI
      final cachedFacilities = await _sharedPrefs.getFacilities();
      if (cachedFacilities.isNotEmpty) {
        state = AsyncValue.data(cachedFacilities);
      }

      // Then fetch from server to get latest data
      await refresh();
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Fetch facilities from server and update local cache
  Future<void> refresh() async {
    try {
      final facilities = await _facilityService.fetchFacilitiesFromServer();
      await _sharedPrefs.saveFacilities(facilities);
      state = AsyncValue.data(facilities);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Add a new facility
  Future<bool> addFacility(String facilityName) async {
    final currentFacilities = state.value ?? [];
    if (facilityName.isEmpty || currentFacilities.contains(facilityName)) {
      return false;
    }

    try {
      final success = await _facilityService.addFacility(facilityName, currentFacilities);
      if (success) {
        final updatedList = [...currentFacilities, facilityName];
        await _sharedPrefs.saveFacilities(updatedList);
        state = AsyncValue.data(updatedList);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Update the entire facility list
  Future<bool> updateFacilities(List<String> facilities) async {
    try {
      final success = await _facilityService.updateFacilities(facilities);
      if (success) {
        await _sharedPrefs.saveFacilities(facilities);
        state = AsyncValue.data(facilities);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Get all facilities from all users for autocomplete
  Future<List<String>> getAllFacilities() async {
    try {
      return await _facilityService.fetchAllFacilitiesFromServer();
    } catch (e) {
      return [];
    }
  }
}

/// Provider for facility notifier
final facilityProvider = StateNotifierProvider<FacilityNotifier, AsyncValue<List<String>>>((ref) {
  final facilityService = ref.watch(facilityServiceProvider);
  final sharedPrefs = ref.watch(sharedPrefsServiceProvider);
  return FacilityNotifier(facilityService, sharedPrefs);
});
