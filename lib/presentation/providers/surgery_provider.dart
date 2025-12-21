import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../data/datasources/remote/parse_surgery_service.dart';
import '../../domain/entities/surgery_specialty.dart';
import '../../core/data/surgery_data.dart';

/// Surgery state
class SurgeryState {
  final Map<String, List<String>> surgeriesBySpecialty;
  final bool isLoading;
  final String? error;

  const SurgeryState({
    required this.surgeriesBySpecialty,
    this.isLoading = false,
    this.error,
  });

  SurgeryState copyWith({
    Map<String, List<String>>? surgeriesBySpecialty,
    bool? isLoading,
    String? error,
  }) {
    return SurgeryState(
      surgeriesBySpecialty: surgeriesBySpecialty ?? this.surgeriesBySpecialty,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }

  /// Get surgeries for a specific specialty
  List<String> getSurgeriesForSpecialty(String specialty) {
    return surgeriesBySpecialty[specialty] ?? [];
  }

  /// Get SurgerySpecialty objects with populated surgery lists
  List<SurgerySpecialty> getSurgerySpecialties() {
    return SurgerySpecialties.all.map((specialty) {
      return SurgerySpecialty(
        title: specialty.title,
        imageName: specialty.imageName,
        surgeries: getSurgeriesForSpecialty(specialty.title),
      );
    }).toList();
  }
}

/// Surgery notifier
class SurgeryNotifier extends StateNotifier<SurgeryState> {
  SurgeryNotifier() : super(const SurgeryState(surgeriesBySpecialty: {}));

  /// Load surgeries for the current user
  Future<void> loadSurgeries() async {
    try {
      state = state.copyWith(isLoading: true, error: null);

      // Get current user
      final currentUser = await ParseUser.currentUser() as ParseUser?;
      if (currentUser == null) {
        // User not logged in, use default surgeries only
        final defaultSurgeries = <String, List<String>>{};
        for (final specialty in SurgeryData.specialtyToKey.keys) {
          defaultSurgeries[specialty] =
              SurgeryData.getDefaultSurgeriesForSpecialty(specialty);
        }
        state = state.copyWith(
          surgeriesBySpecialty: defaultSurgeries,
          isLoading: false,
        );
        return;
      }

      final userEmail = currentUser.emailAddress;
      if (userEmail == null) {
        throw Exception('User email is null');
      }

      // Fetch all surgeries (default + saved) for all specialties
      final allSurgeries = await ParseSurgeryService.getAllSurgeries(userEmail);

      state = state.copyWith(
        surgeriesBySpecialty: allSurgeries,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      print('Error loading surgeries: $e');
    }
  }

  /// Reload surgeries for a specific specialty
  Future<void> reloadSpecialty(String specialty) async {
    try {
      final currentUser = await ParseUser.currentUser() as ParseUser?;
      if (currentUser == null) return;

      final userEmail = currentUser.emailAddress;
      if (userEmail == null) return;

      final surgeries = await ParseSurgeryService.getSurgeriesForSpecialty(
        specialty,
        userEmail,
      );

      final updatedMap = Map<String, List<String>>.from(state.surgeriesBySpecialty);
      updatedMap[specialty] = surgeries;

      state = state.copyWith(surgeriesBySpecialty: updatedMap);
    } catch (e) {
      print('Error reloading specialty $specialty: $e');
    }
  }

  /// Save a new surgery
  Future<bool> saveSurgery({
    required String surgeryClass,
    required String subSurgery,
  }) async {
    try {
      final currentUser = await ParseUser.currentUser() as ParseUser?;
      if (currentUser == null) return false;

      final userEmail = currentUser.emailAddress;
      if (userEmail == null) return false;

      // Check if surgery already exists (to avoid duplicates)
      final exists = await ParseSurgeryService.isSurgerySaved(
        userEmail: userEmail,
        surgeryClass: surgeryClass,
        subSurgery: subSurgery,
      );

      if (exists) {
        // Surgery already exists, no need to save
        return true;
      }

      // Save to Parse
      final response = await ParseSurgeryService.saveSurgery(
        userEmail: userEmail,
        surgeryClass: surgeryClass,
        subSurgery: subSurgery,
      );

      if (response.success) {
        // Reload the specialty to include the new surgery
        await reloadSpecialty(surgeryClass);
        return true;
      }

      return false;
    } catch (e) {
      print('Error saving surgery: $e');
      return false;
    }
  }
}

/// Provider for surgery state
final surgeryProvider = StateNotifierProvider<SurgeryNotifier, SurgeryState>(
  (ref) => SurgeryNotifier(),
);
