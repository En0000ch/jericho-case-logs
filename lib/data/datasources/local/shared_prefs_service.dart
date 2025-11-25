import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../../models/user_model.dart';

/// SharedPreferences service for local storage
class SharedPrefsService {
  static const String _keyCurrentUser = 'current_user';
  static const String _keySavedEmail = 'saved_email';
  static const String _keySavedPassword = 'saved_password';
  static const String _keyAcceptedDisclaimer = 'accepted_disclaimer';
  static const String _keySavePassword = 'save_password_enabled';
  static const String _keySurgeonArray = 'jclSurgeonArray';
  static const String _keyFacilityArray = 'jclFacilityArray';
  static const String _keySkillsArray = 'jclSkillsArray';

  final SharedPreferences _prefs;

  SharedPrefsService(this._prefs);

  /// Get current user from local storage
  Future<UserModel?> getCurrentUser() async {
    final userJson = _prefs.getString(_keyCurrentUser);
    if (userJson == null) return null;

    try {
      final Map<String, dynamic> json = jsonDecode(userJson);
      return UserModel.fromJson(json);
    } catch (e) {
      return null;
    }
  }

  /// Save current user to local storage
  Future<bool> saveCurrentUser(UserModel user) async {
    final userJson = jsonEncode(user.toJson());
    return await _prefs.setString(_keyCurrentUser, userJson);
  }

  /// Clear current user from local storage
  Future<bool> clearCurrentUser() async {
    return await _prefs.remove(_keyCurrentUser);
  }

  /// Save credentials (email and password)
  Future<bool> saveCredentials({
    required String email,
    required String password,
  }) async {
    final emailSaved = await _prefs.setString(_keySavedEmail, email);
    final passwordSaved = await _prefs.setString(_keySavedPassword, password);
    final enabledSaved = await _prefs.setBool(_keySavePassword, true);
    return emailSaved && passwordSaved && enabledSaved;
  }

  /// Get saved credentials
  Future<Map<String, String>?> getSavedCredentials() async {
    final savePasswordEnabled = _prefs.getBool(_keySavePassword) ?? false;
    if (!savePasswordEnabled) return null;

    final email = _prefs.getString(_keySavedEmail);
    final password = _prefs.getString(_keySavedPassword);

    if (email == null || password == null) return null;

    return {
      'email': email,
      'password': password,
    };
  }

  /// Clear saved credentials
  Future<bool> clearSavedCredentials() async {
    final emailCleared = await _prefs.remove(_keySavedEmail);
    final passwordCleared = await _prefs.remove(_keySavedPassword);
    final enabledCleared = await _prefs.remove(_keySavePassword);
    return emailCleared && passwordCleared && enabledCleared;
  }

  /// Check if user accepted disclaimer
  Future<bool> hasAcceptedDisclaimer(String userId) async {
    return _prefs.getBool('${_keyAcceptedDisclaimer}_$userId') ?? false;
  }

  /// Set disclaimer acceptance
  Future<bool> setDisclaimerAccepted(String userId) async {
    return await _prefs.setBool('${_keyAcceptedDisclaimer}_$userId', true);
  }

  /// Clear all data
  Future<bool> clearAll() async {
    return await _prefs.clear();
  }

  /// Get surgeons from local storage
  Future<List<String>> getSurgeons() async {
    final surgeonsJson = _prefs.getStringList(_keySurgeonArray);
    return surgeonsJson ?? [];
  }

  /// Save surgeons to local storage
  Future<bool> saveSurgeons(List<String> surgeons) async {
    return await _prefs.setStringList(_keySurgeonArray, surgeons);
  }

  /// Get facilities from local storage
  Future<List<String>> getFacilities() async {
    final facilitiesJson = _prefs.getStringList(_keyFacilityArray);
    return facilitiesJson ?? [];
  }

  /// Save facilities to local storage
  Future<bool> saveFacilities(List<String> facilities) async {
    return await _prefs.setStringList(_keyFacilityArray, facilities);
  }

  /// Get skills from local storage
  Future<List<String>> getSkills() async {
    final skillsJson = _prefs.getStringList(_keySkillsArray);
    return skillsJson ?? [];
  }

  /// Save skills to local storage
  Future<bool> saveSkills(List<String> skills) async {
    return await _prefs.setStringList(_keySkillsArray, skills);
  }
}
