import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
// Temporarily disabled - missing GoogleService-Info.plist
// import 'package:firebase_core/firebase_core.dart';
// import 'package:firebase_crashlytics/firebase_crashlytics.dart';
// import 'package:firebase_analytics/firebase_analytics.dart';
import 'core/themes/app_theme.dart';
import 'core/constants/app_constants.dart';
import 'core/constants/user_roles.dart';
import 'core/utils/silo_resolver.dart';
import 'data/datasources/remote/parse_api_service.dart';
import 'presentation/providers/auth_provider.dart';
import 'presentation/providers/facility_provider.dart';
import 'presentation/providers/surgeon_provider.dart';
import 'presentation/providers/skills_provider.dart';
import 'presentation/providers/surgery_provider.dart';
import 'presentation/screens/auth/login_screen.dart';
import 'presentation/screens/home/home_screen.dart';
import 'presentation/screens/nurse/nurse_home_screen.dart';
import 'presentation/widgets/silo_selection_modal.dart';

void main() async {
  runZonedGuarded<Future<void>>(() async {
    WidgetsFlutterBinding.ensureInitialized();

    // Initialize Firebase (temporarily disabled - missing GoogleService-Info.plist)
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
    print('❌ Unhandled Error: $error');
    print(stack);
  });
}

/// Firebase Analytics instance (temporarily disabled)
// final FirebaseAnalytics analytics = FirebaseAnalytics.instance;

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

/// Router that determines which screen to show based on authentication state and silo
class AuthRouter extends ConsumerStatefulWidget {
  const AuthRouter({super.key});

  @override
  ConsumerState<AuthRouter> createState() => _AuthRouterState();
}

class _AuthRouterState extends ConsumerState<AuthRouter> {
  String? _selectedSiloForAdmin;
  bool _hasShownAdminModal = false;

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    print('[AuthRouter] isAuthenticated=${authState.isAuthenticated}, user=${authState.user?.email}');

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
      // Reset admin state when logged out
      _selectedSiloForAdmin = null;
      _hasShownAdminModal = false;
      return const LoginScreen();
    }

    final user = authState.user!;

    // Resolve effective silo using strict precedence
    final effectiveSilo = SiloResolver.resolveEffectiveSilo(user);

    // If user is admin (effectiveSilo == jclAll), show selection modal
    if (effectiveSilo == SiloConfig.siloAll) {
      // If admin hasn't selected a silo yet, show modal
      if (_selectedSiloForAdmin == null) {
        if (!_hasShownAdminModal) {
          _hasShownAdminModal = true;
          // Show modal on next frame
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) {
              _showAdminSiloSelectionModal(context);
            }
          });
        }

        // Show loading while waiting for selection
        return const Scaffold(
          body: Center(
            child: CircularProgressIndicator(),
          ),
        );
      } else {
        // Admin has selected a silo, route to it
        return _buildSiloScreen(_selectedSiloForAdmin!);
      }
    }

    // Non-admin user: route directly to their silo
    return _buildSiloScreen(effectiveSilo);
  }

  /// Show admin silo selection modal
  void _showAdminSiloSelectionModal(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => SiloSelectionModal(
        onSiloSelected: (selectedSilo) {
          Navigator.of(dialogContext).pop();
          setState(() {
            _selectedSiloForAdmin = selectedSilo;
          });
          print('[AuthRouter] Admin selected silo: $selectedSilo');
        },
        onCancel: () {
          // Logout if admin cancels
          Navigator.of(dialogContext).pop();
          ref.read(authProvider.notifier).logout();
        },
      ),
    );
  }

  /// Build the appropriate silo screen based on silo value
  Widget _buildSiloScreen(String silo) {
    // Initialize common data providers
    ref.watch(facilityProvider);
    ref.watch(surgeonProvider);
    ref.watch(skillsProvider);

    // Initialize surgery provider for Anesthesia silo
    if (silo == SiloConfig.siloAnes) {
      final surgeryState = ref.watch(surgeryProvider);
      if (surgeryState.surgeriesBySpecialty.isEmpty && !surgeryState.isLoading) {
        Future.microtask(() {
          ref.read(surgeryProvider.notifier).loadSurgeries();
        });
      }
    }

    // Route to appropriate silo home screen
    switch (silo) {
      case SiloConfig.siloAnes:
        print('[AuthRouter] Routing to Anesthesia home');
        return const HomeScreen();

      case SiloConfig.siloNurse:
        print('[AuthRouter] Routing to Nurse home');
        return const NurseHomeScreen();

      case SiloConfig.siloJobs:
        // Jobs silo - for future implementation
        print('[AuthRouter] Jobs silo not yet implemented, defaulting to Anesthesia');
        return const HomeScreen();

      // Future silos
      case SiloConfig.siloTech:
      case SiloConfig.siloDoc:
        print('[AuthRouter] Silo $silo not yet implemented, defaulting to Anesthesia');
        return const HomeScreen();

      default:
        print('[AuthRouter] Unknown silo $silo, defaulting to Anesthesia');
        return const HomeScreen();
    }
  }
}
