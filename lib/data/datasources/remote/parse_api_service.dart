import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/constants/api_constants.dart';

/// Parse Server API Service
class ParseApiService {
  static bool _initialized = false;

  /// Initialize Parse Server connection
  static Future<void> initialize() async {
    if (_initialized) return;

    await Parse().initialize(
      ApiConstants.parseApplicationId,
      ApiConstants.parseServerUrl,
      clientKey: ApiConstants.parseClientKey,
      autoSendSessionId: true,
      debug: true, // Set to false in production
    );

    _initialized = true;
  }

  /// Login user
  static Future<ParseResponse> login({
    required String email,
    required String password,
  }) async {
    final user = ParseUser(email, password, email);
    return await user.login();
  }

  /// Register new user
  static Future<ParseResponse> register({
    required String email,
    required String password,
    required String silo,
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    final user = ParseUser(email, password, email);

    // Set custom fields
    user.set('firstName', firstName);
    user.set('lastName', lastName);
    user.set('title', title);
    user.set('jclSilo', silo);
    user.set('hasPurchased', false);
    user.set('caseCount', 0);

    return await user.signUp();
  }

  /// Get current user
  static Future<ParseUser?> getCurrentUser() async {
    return await ParseUser.currentUser() as ParseUser?;
  }

  /// Logout user
  static Future<ParseResponse> logout() async {
    final user = await getCurrentUser();
    if (user != null) {
      return await user.logout();
    }
    return ParseResponse()
      ..success = false
      ..statusCode = 400
      ..error = ParseError(message: 'No user is logged in');
  }

  /// Check if user is logged in
  static Future<bool> isLoggedIn() async {
    final user = await getCurrentUser();
    return user != null;
  }

  /// Update user profile
  static Future<ParseResponse> updateUserProfile({
    required String userId,
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    final user = await getCurrentUser();
    if (user == null) {
      return ParseResponse()
        ..success = false
        ..statusCode = 401
        ..error = ParseError(message: 'User not logged in');
    }

    if (firstName != null) user.set('firstName', firstName);
    if (lastName != null) user.set('lastName', lastName);
    if (title != null) user.set('title', title);

    return await user.save();
  }

  /// Increment case count
  static Future<ParseResponse> incrementCaseCount() async {
    final user = await getCurrentUser();
    if (user == null) {
      return ParseResponse()
        ..success = false
        ..statusCode = 401
        ..error = ParseError(message: 'User not logged in');
    }

    final currentCount = user.get<int>('caseCount') ?? 0;
    user.set('caseCount', currentCount + 1);

    return await user.save();
  }

  /// Update purchase status
  static Future<ParseResponse> updatePurchaseStatus(bool purchased) async {
    final user = await getCurrentUser();
    if (user == null) {
      return ParseResponse()
        ..success = false
        ..statusCode = 401
        ..error = ParseError(message: 'User not logged in');
    }

    user.set('hasPurchased', purchased);
    return await user.save();
  }
}
