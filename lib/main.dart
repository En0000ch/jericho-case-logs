import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'core/themes/app_theme.dart';
import 'core/constants/app_constants.dart';
import 'data/datasources/remote/parse_api_service.dart';
import 'presentation/providers/auth_provider.dart';
import 'presentation/screens/auth/login_screen.dart';
import 'presentation/screens/home/home_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Parse Server
  await ParseApiService.initialize();

  // Initialize SharedPreferences
  final sharedPreferences = await SharedPreferences.getInstance();

  runApp(
    ProviderScope(
      overrides: [
        sharedPreferencesProvider.overrideWith(
          (ref) => sharedPreferences,
        ),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp(
      title: AppConstants.appName,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      debugShowCheckedModeBanner: false,
      home: const AuthRouter(),
    );
  }
}

/// Router that determines which screen to show based on authentication state
class AuthRouter extends ConsumerWidget {
  const AuthRouter({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);

    print('AuthRouter: isAuthenticated=${authState.isAuthenticated}, user=${authState.user?.email}, disclaimer=${authState.user?.acceptedDisclaimer}');

    // Show loading screen while checking auth status
    if (authState.isLoading && authState.user == null) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    // Not authenticated - show login screen
    if (!authState.isAuthenticated || authState.user == null) {
      return const LoginScreen();
    }

    // Authenticated - show home screen
    // Disclaimer is now handled as a one-time dialog on first app launch in login screen
    return const HomeScreen();
  }
}
