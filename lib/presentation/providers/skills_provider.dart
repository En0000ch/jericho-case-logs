import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/datasources/remote/parse_skills_service.dart';
import '../../data/datasources/local/shared_prefs_service.dart';
import 'auth_provider.dart';

/// Provider for skills service
final skillsServiceProvider = Provider<ParseSkillsService>((ref) {
  return ParseSkillsService();
});

/// State notifier for managing skills list
class SkillsNotifier extends StateNotifier<AsyncValue<List<String>>> {
  SkillsNotifier(this._skillsService, this._sharedPrefs)
      : super(const AsyncValue.loading()) {
    _init();
  }

  final ParseSkillsService _skillsService;
  final SharedPrefsService _sharedPrefs;

  /// Initialize by loading from local cache first, then syncing with server
  Future<void> _init() async {
    try {
      // Load from local cache first for immediate UI
      final cachedSkills = await _sharedPrefs.getSkills();
      if (cachedSkills.isNotEmpty) {
        state = AsyncValue.data(cachedSkills);
      }

      // Then fetch from server to get latest data
      await refresh();
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Fetch skills from server and update local cache
  Future<void> refresh() async {
    try {
      final skills = await _skillsService.fetchSkillsFromServer();
      await _sharedPrefs.saveSkills(skills);
      state = AsyncValue.data(skills);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  /// Add a new skill
  Future<bool> addSkill(String skillName) async {
    final currentSkills = state.value ?? [];
    if (skillName.isEmpty || currentSkills.contains(skillName)) {
      return false;
    }

    try {
      final success = await _skillsService.addSkill(skillName, currentSkills);
      if (success) {
        final updatedList = [...currentSkills, skillName];
        await _sharedPrefs.saveSkills(updatedList);
        state = AsyncValue.data(updatedList);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Update the entire skills list
  Future<bool> updateSkills(List<String> skills) async {
    try {
      final success = await _skillsService.updateSkills(skills);
      if (success) {
        await _sharedPrefs.saveSkills(skills);
        state = AsyncValue.data(skills);
      }
      return success;
    } catch (e) {
      return false;
    }
  }

  /// Get all skills from all users for autocomplete
  Future<List<String>> getAllSkills() async {
    try {
      return await _skillsService.fetchAllSkillsFromServer();
    } catch (e) {
      return [];
    }
  }
}

/// Provider for skills notifier
final skillsProvider = StateNotifierProvider<SkillsNotifier, AsyncValue<List<String>>>((ref) {
  final skillsService = ref.watch(skillsServiceProvider);
  final sharedPrefs = ref.watch(sharedPrefsServiceProvider);
  return SkillsNotifier(skillsService, sharedPrefs);
});
