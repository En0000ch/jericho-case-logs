import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../data/datasources/remote/parse_surgeon_service.dart';
import '../../data/datasources/local/shared_prefs_service.dart';

/// Provider for surgeon service
final surgeonServiceProvider = Provider<ParseSurgeonService>((ref) {
  return ParseSurgeonService();
});

/// Provider for SharedPreferences instance
final sharedPreferencesProvider = FutureProvider<SharedPreferences>((ref) async {
  return await SharedPreferences.getInstance();
});

/// Provider for shared preferences service
final sharedPrefsServiceProvider = Provider<SharedPrefsService>((ref) {
  final prefsAsync = ref.watch(sharedPreferencesProvider);
  return prefsAsync.when(
    data: (prefs) => SharedPrefsService(prefs),
    loading: () => throw Exception('SharedPreferences not initialized'),
    error: (err, stack) => throw err,
  );
});

/// State notifier for managing surgeon list
class SurgeonNotifier extends StateNotifier<AsyncValue<List<String>>> {
  SurgeonNotifier(this._surgeonService, this._sharedPrefs)
      : super(const AsyncValue.loading()) {
    _init();
  }

  final ParseSurgeonService _surgeonService;
  final SharedPrefsService _sharedPrefs;

  /// Initialize by loading from local cache first, then syncing with server
  Future<void> _init() async {
    try {
      // Load from local cache first for immediate UI
      final cachedSurgeons = await _sharedPrefs.getSurgeons();
      if (cachedSurgeons.isNotEmpty) {
        state = AsyncValue.data(cachedSurgeons);
      }

      // Then fetch from server to get latest data
      await refresh();
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Fetch surgeons from server and update local cache
  Future<void> refresh() async {
    try {
      final surgeons = await _surgeonService.fetchSurgeonsFromServer();
      await _sharedPrefs.saveSurgeons(surgeons);
      state = AsyncValue.data(surgeons);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Add a new surgeon
  Future<bool> addSurgeon(String surgeonName) async {
    final currentSurgeons = state.value ?? [];
    if (surgeonName.isEmpty || currentSurgeons.contains(surgeonName)) {
      return false;
    }

    try {
      final success = await _surgeonService.addSurgeon(surgeonName, currentSurgeons);
      if (success) {
        final updatedList = [...currentSurgeons, surgeonName];
        await _sharedPrefs.saveSurgeons(updatedList);
        state = AsyncValue.data(updatedList);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Update the entire surgeon list
  Future<bool> updateSurgeons(List<String> surgeons) async {
    try {
      final success = await _surgeonService.updateSurgeons(surgeons);
      if (success) {
        await _sharedPrefs.saveSurgeons(surgeons);
        state = AsyncValue.data(surgeons);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Get all surgeons from all users for autocomplete
  Future<List<String>> getAllSurgeons() async {
    try {
      return await _surgeonService.fetchAllSurgeonsFromServer();
    } catch (e) {
      return [];
    }
  }
}

/// Provider for surgeon notifier
final surgeonProvider = StateNotifierProvider<SurgeonNotifier, AsyncValue<List<String>>>((ref) {
  final surgeonService = ref.watch(surgeonServiceProvider);
  final sharedPrefs = ref.watch(sharedPrefsServiceProvider);
  return SurgeonNotifier(surgeonService, sharedPrefs);
});
