import 'package:dartz/dartz.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../domain/entities/user.dart';
import '../../domain/repositories/i_auth_repository.dart';
import '../../core/errors/failures.dart';
import '../models/user_model.dart';
import '../datasources/remote/parse_api_service.dart';
import '../datasources/local/shared_prefs_service.dart';

/// Authentication repository implementation
class AuthRepository implements IAuthRepository {
  final SharedPrefsService _prefsService;

  AuthRepository(this._prefsService);

  @override
  Future<Either<Failure, User>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await ParseApiService.login(
        email: email,
        password: password,
      );

      if (response.success && response.result != null) {
        final parseUser = response.result as ParseUser;
        final userModel = UserModel.fromParseUser(parseUser);

        // Check disclaimer acceptance from local storage
        final acceptedDisclaimer =
            await _prefsService.hasAcceptedDisclaimer(userModel.objectId);

        final completeUserModel = UserModel(
          objectId: userModel.objectId,
          email: userModel.email,
          firstName: userModel.firstName,
          lastName: userModel.lastName,
          title: userModel.title,
          silo: userModel.silo,
          jclRole: userModel.jclRole,
          hasPurchased: userModel.hasPurchased,
          caseCount: userModel.caseCount,
          acceptedDisclaimer: acceptedDisclaimer,
          createdAt: userModel.createdAt,
          updatedAt: userModel.updatedAt,
        );

        // Save to local storage
        await _prefsService.saveCurrentUser(completeUserModel);

        return Right(completeUserModel.toDomain());
      } else {
        return Left(
          AuthFailure(response.error?.message ?? 'Login failed'),
        );
      }
    } catch (e) {
      return Left(AuthFailure('Login error: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, User>> register({
    required String email,
    required String password,
    required String silo,
    String? role,
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    try {
      final response = await ParseApiService.register(
        email: email,
        password: password,
        silo: silo,
        role: role,
        firstName: firstName,
        lastName: lastName,
        title: title,
      );

      if (response.success && response.result != null) {
        final parseUser = response.result as ParseUser;
        final userModel = UserModel.fromParseUser(parseUser);

        // Save to local storage
        await _prefsService.saveCurrentUser(userModel);

        return Right(userModel.toDomain());
      } else {
        return Left(
          AuthFailure(response.error?.message ?? 'Registration failed'),
        );
      }
    } catch (e) {
      return Left(AuthFailure('Registration error: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, void>> logout() async {
    try {
      final response = await ParseApiService.logout();

      if (response.success) {
        // Clear local storage
        await _prefsService.clearCurrentUser();
        return const Right(null);
      } else {
        return Left(
          AuthFailure(response.error?.message ?? 'Logout failed'),
        );
      }
    } catch (e) {
      return Left(AuthFailure('Logout error: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, User?>> getCurrentUser() async {
    try {
      // Try to get from local storage first
      final localUser = await _prefsService.getCurrentUser();
      if (localUser != null) {
        return Right(localUser.toDomain());
      }

      // If not in local storage, check Parse Server
      final parseUser = await ParseApiService.getCurrentUser();
      if (parseUser != null) {
        final userModel = UserModel.fromParseUser(parseUser);

        // Check disclaimer acceptance
        final acceptedDisclaimer =
            await _prefsService.hasAcceptedDisclaimer(userModel.objectId);

        final completeUserModel = UserModel(
          objectId: userModel.objectId,
          email: userModel.email,
          firstName: userModel.firstName,
          lastName: userModel.lastName,
          title: userModel.title,
          silo: userModel.silo,
          jclRole: userModel.jclRole,
          hasPurchased: userModel.hasPurchased,
          caseCount: userModel.caseCount,
          acceptedDisclaimer: acceptedDisclaimer,
          createdAt: userModel.createdAt,
          updatedAt: userModel.updatedAt,
        );

        // Save to local storage
        await _prefsService.saveCurrentUser(completeUserModel);

        return Right(completeUserModel.toDomain());
      }

      return const Right(null);
    } catch (e) {
      return Left(CacheFailure('Error getting current user: ${e.toString()}'));
    }
  }

  @override
  Future<bool> isLoggedIn() async {
    final result = await getCurrentUser();
    return result.fold(
      (_) => false,
      (user) => user != null,
    );
  }

  @override
  Future<Either<Failure, User>> acceptDisclaimer(String userId) async {
    try {
      // Save disclaimer acceptance locally
      await _prefsService.setDisclaimerAccepted(userId);

      // Get current user and update
      final userResult = await getCurrentUser();
      return userResult.fold(
        (failure) => Left(failure),
        (user) {
          if (user == null) {
            return const Left(AuthFailure('No user logged in'));
          }
          final updatedUser = user.copyWith(acceptedDisclaimer: true);
          return Right(updatedUser);
        },
      );
    } catch (e) {
      return Left(
        CacheFailure('Error accepting disclaimer: ${e.toString()}'),
      );
    }
  }

  @override
  Future<Either<Failure, User>> updateUserProfile({
    required String userId,
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    try {
      final response = await ParseApiService.updateUserProfile(
        userId: userId,
        firstName: firstName,
        lastName: lastName,
        title: title,
      );

      if (response.success && response.result != null) {
        final parseUser = response.result as ParseUser;
        final userModel = UserModel.fromParseUser(parseUser);

        // Update local storage
        await _prefsService.saveCurrentUser(userModel);

        return Right(userModel.toDomain());
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Update failed'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Update error: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, void>> savePassword({
    required String email,
    required String password,
  }) async {
    try {
      await _prefsService.saveCredentials(
        email: email,
        password: password,
      );
      return const Right(null);
    } catch (e) {
      return Left(CacheFailure('Error saving password: ${e.toString()}'));
    }
  }

  @override
  Future<Either<Failure, Map<String, String>?>> getSavedCredentials() async {
    try {
      final credentials = await _prefsService.getSavedCredentials();
      return Right(credentials);
    } catch (e) {
      return Left(
        CacheFailure('Error getting saved credentials: ${e.toString()}'),
      );
    }
  }

  @override
  Future<Either<Failure, void>> clearSavedCredentials() async {
    try {
      await _prefsService.clearSavedCredentials();
      return const Right(null);
    } catch (e) {
      return Left(
        CacheFailure('Error clearing credentials: ${e.toString()}'),
      );
    }
  }

  @override
  Future<Either<Failure, bool>> requestPasswordReset(String email) async {
    try {
      final user = ParseUser(null, null, email);
      final response = await user.requestPasswordReset();

      if (response.success) {
        return const Right(true);
      } else {
        return Left(
          ServerFailure(response.error?.message ?? 'Failed to send reset email'),
        );
      }
    } catch (e) {
      return Left(ServerFailure('Password reset error: ${e.toString()}'));
    }
  }
}
