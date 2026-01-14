import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../domain/entities/user.dart';
import '../../domain/repositories/i_auth_repository.dart';
import '../../data/repositories/auth_repository.dart';
import '../../data/datasources/local/shared_prefs_service.dart';

/// Shared Preferences Provider
final sharedPreferencesProvider = FutureProvider<SharedPreferences>((ref) async {
  return await SharedPreferences.getInstance();
});

/// Shared Prefs Service Provider
final sharedPrefsServiceProvider = Provider<SharedPrefsService>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider).value;
  if (prefs == null) {
    throw Exception('SharedPreferences not initialized');
  }
  return SharedPrefsService(prefs);
});

/// Auth Repository Provider
final authRepositoryProvider = Provider<IAuthRepository>((ref) {
  final prefsService = ref.watch(sharedPrefsServiceProvider);
  return AuthRepository(prefsService);
});

/// Auth State
class AuthState {
  final User? user;
  final bool isLoading;
  final String? error;
  final bool isAuthenticated;

  AuthState({
    this.user,
    this.isLoading = false,
    this.error,
    this.isAuthenticated = false,
  });

  AuthState copyWith({
    User? user,
    bool? isLoading,
    String? error,
    bool? isAuthenticated,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
    );
  }
}

/// Auth State Notifier
class AuthNotifier extends StateNotifier<AuthState> {
  final IAuthRepository _authRepository;

  AuthNotifier(this._authRepository) : super(AuthState()) {
    _checkAuthStatus();
  }

  /// Check authentication status on init
  Future<void> _checkAuthStatus() async {
    state = state.copyWith(isLoading: true);

    final result = await _authRepository.getCurrentUser();
    result.fold(
      (failure) {
        state = AuthState(
          isLoading: false,
          error: failure.message,
          isAuthenticated: false,
        );
      },
      (user) {
        state = AuthState(
          user: user,
          isLoading: false,
          isAuthenticated: user != null,
        );
      },
    );
  }

  /// Login
  Future<void> login({
    required String email,
    required String password,
    bool savePassword = false,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    final result = await _authRepository.login(
      email: email,
      password: password,
    );

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
          isAuthenticated: false,
        );
      },
      (user) async {
        // Save credentials if requested
        if (savePassword) {
          await _authRepository.savePassword(
            email: email,
            password: password,
          );
        }

        state = AuthState(
          user: user,
          isLoading: false,
          isAuthenticated: true,
        );
      },
    );
  }

  /// Register
  Future<void> register({
    required String email,
    required String password,
    required String silo,
    String? role,
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    final result = await _authRepository.register(
      email: email,
      password: password,
      silo: silo,
      role: role,
      firstName: firstName,
      lastName: lastName,
      title: title,
    );

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
          isAuthenticated: false,
        );
      },
      (user) {
        state = AuthState(
          user: user,
          isLoading: false,
          isAuthenticated: true,
        );
      },
    );
  }

  /// Logout
  Future<void> logout() async {
    state = state.copyWith(isLoading: true);

    final result = await _authRepository.logout();

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (_) {
        state = AuthState(isLoading: false, isAuthenticated: false);
      },
    );
  }

  /// Accept disclaimer
  Future<void> acceptDisclaimer() async {
    if (state.user == null) return;

    final result = await _authRepository.acceptDisclaimer(state.user!.objectId);

    result.fold(
      (failure) {
        state = state.copyWith(error: failure.message);
      },
      (user) {
        state = state.copyWith(user: user);
      },
    );
  }

  /// Update user profile
  Future<void> updateProfile({
    String? firstName,
    String? lastName,
    String? title,
  }) async {
    if (state.user == null) return;

    state = state.copyWith(isLoading: true);

    final result = await _authRepository.updateUserProfile(
      userId: state.user!.objectId,
      firstName: firstName,
      lastName: lastName,
      title: title,
    );

    result.fold(
      (failure) {
        state = state.copyWith(
          isLoading: false,
          error: failure.message,
        );
      },
      (user) {
        state = state.copyWith(
          user: user,
          isLoading: false,
        );
      },
    );
  }

  /// Check for saved credentials and auto-login
  Future<void> attemptAutoLogin() async {
    final credentialsResult = await _authRepository.getSavedCredentials();

    credentialsResult.fold(
      (failure) => null,
      (credentials) async {
        if (credentials != null) {
          await login(
            email: credentials['email']!,
            password: credentials['password']!,
          );
        }
      },
    );
  }

  /// Request password reset email
  Future<bool> requestPasswordReset(String email) async {
    try {
      final result = await _authRepository.requestPasswordReset(email);
      return result.fold(
        (failure) => false,
        (success) => success,
      );
    } catch (e) {
      return false;
    }
  }
}

/// Auth State Provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final authRepository = ref.watch(authRepositoryProvider);
  return AuthNotifier(authRepository);
});

/// Current User Provider (convenience)
final currentUserProvider = Provider<User?>((ref) {
  return ref.watch(authProvider).user;
});

/// Is Authenticated Provider (convenience)
final isAuthenticatedProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isAuthenticated;
});
