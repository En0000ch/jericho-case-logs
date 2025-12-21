import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import 'core/themes/app_theme.dart';
import 'core/constants/app_constants.dart';
import 'data/datasources/remote/parse_api_service.dart';
import 'presentation/providers/auth_provider.dart';
import 'presentation/providers/facility_provider.dart';
import 'presentation/providers/surgeon_provider.dart';
import 'presentation/providers/skills_provider.dart';
import 'presentation/providers/surgery_provider.dart';
import 'presentation/screens/auth/login_screen.dart';
import 'presentation/screens/home/home_screen.dart';

void main() async {
  runZonedGuarded<Future<void>>(() async {
    WidgetsFlutterBinding.ensureInitialized();

    // Initialize Firebase (gracefully handle missing configuration)
    bool firebaseInitialized = false;
    try {
      await Firebase.initializeApp();
      firebaseInitialized = true;
      print('✅ Firebase initialized successfully');

      // Initialize Firebase Crashlytics
      // Pass all uncaught "fatal" errors from the framework to Crashlytics
      FlutterError.onError = (errorDetails) {
        FirebaseCrashlytics.instance.recordFlutterFatalError(errorDetails);
      };

      // Pass all uncaught asynchronous errors that aren't handled by the Flutter framework to Crashlytics
      PlatformDispatcher.instance.onError = (error, stack) {
        FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
        return true;
      };
    } catch (e) {
      print('⚠️ Firebase not configured: $e');
      print('📖 See FIREBASE_SETUP.md for setup instructions');

      // Set up default error handlers without Firebase
      FlutterError.onError = (errorDetails) {
        print('❌ Flutter Error: ${errorDetails.exception}');
        print(errorDetails.stack);
      };

      PlatformDispatcher.instance.onError = (error, stack) {
        print('❌ Dart Error: $error');
        print(stack);
        return true;
      };
    }

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
  }, (error, stack) {
    // Catch errors that occur outside of the Flutter framework
    try {
      FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
    } catch (e) {
      print('❌ Unhandled Error: $error');
      print(stack);
    }
  });
}

/// Firebase Analytics instance
final FirebaseAnalytics analytics = FirebaseAnalytics.instance;

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

    // Authenticated - Initialize data providers and show home screen
    // Watch the providers to trigger their initialization (fetching from server/cache)
    ref.watch(facilityProvider);
    ref.watch(surgeonProvider);
    ref.watch(skillsProvider);

    // Initialize surgery provider to load user's saved surgeries
    final surgeryState = ref.watch(surgeryProvider);
    if (surgeryState.surgeriesBySpecialty.isEmpty && !surgeryState.isLoading) {
      // Trigger surgery loading on first access
      Future.microtask(() {
        ref.read(surgeryProvider.notifier).loadSurgeries();
      });
    }

    // Disclaimer is now handled as a one-time dialog on first app launch in login screen
    return const HomeScreen();
  }
}
