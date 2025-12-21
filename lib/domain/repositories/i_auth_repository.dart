import 'package:dartz/dartz.dart';
import '../entities/user.dart';
import '../../core/errors/failures.dart';

/// Authentication repository interface
abstract class IAuthRepository {
  /// Login with email and password
  Future<Either<Failure, User>> login({
    required String email,
    required String password,
  });

  /// Register a new user
  Future<Either<Failure, User>> register({
    required String email,
    required String password,
    required String silo,
    String? firstName,
    String? lastName,
    String? title,
  });

  /// Logout current user
  Future<Either<Failure, void>> logout();

  /// Get current logged-in user
  Future<Either<Failure, User?>> getCurrentUser();

  /// Check if user is logged in
  Future<bool> isLoggedIn();

  /// Accept disclaimer
  Future<Either<Failure, User>> acceptDisclaimer(String userId);

  /// Update user profile
  Future<Either<Failure, User>> updateUserProfile({
    required String userId,
    String? firstName,
    String? lastName,
    String? title,
  });

  /// Save password locally (optional for auto-login)
  Future<Either<Failure, void>> savePassword({
    required String email,
    required String password,
  });

  /// Get saved credentials
  Future<Either<Failure, Map<String, String>?>> getSavedCredentials();

  /// Clear saved credentials
  Future<Either<Failure, void>> clearSavedCredentials();

  /// Request password reset email
  Future<Either<Failure, bool>> requestPasswordReset(String email);
}
