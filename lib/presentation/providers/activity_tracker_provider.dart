import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/patient_timer_constants.dart';

/// State for activity tracking
class ActivityTrackerState {
  final DateTime? lastActivityTime;
  final bool isActive;

  ActivityTrackerState({
    this.lastActivityTime,
    this.isActive = true,
  });

  ActivityTrackerState copyWith({
    DateTime? lastActivityTime,
    bool? isActive,
  }) {
    return ActivityTrackerState(
      lastActivityTime: lastActivityTime ?? this.lastActivityTime,
      isActive: isActive ?? this.isActive,
    );
  }
}

/// Activity tracker notifier
class ActivityTrackerNotifier extends StateNotifier<ActivityTrackerState> {
  final SharedPreferences _prefs;

  ActivityTrackerNotifier(this._prefs) : super(ActivityTrackerState()) {
    _loadLastActivity();
  }

  /// Load last activity from SharedPreferences
  void _loadLastActivity() {
    final timestampString = _prefs.getString(PatientTimerConstants.keyLastActivity);
    if (timestampString != null) {
      try {
        final timestamp = DateTime.parse(timestampString);
        state = state.copyWith(lastActivityTime: timestamp);
      } catch (e) {
        print('Error parsing last activity timestamp: $e');
      }
    }
  }

  /// Record user activity and persist to SharedPreferences
  Future<void> recordActivity() async {
    final now = DateTime.now();
    state = state.copyWith(lastActivityTime: now, isActive: true);

    // Persist to SharedPreferences
    await _prefs.setString(
      PatientTimerConstants.keyLastActivity,
      now.toIso8601String(),
    );

    print('📱 Activity recorded: ${now.toIso8601String()}');
  }

  /// Check if user has been inactive for the specified duration
  bool isInactive(Duration threshold) {
    if (state.lastActivityTime == null) {
      return false;
    }

    final now = DateTime.now();
    final inactiveDuration = now.difference(state.lastActivityTime!);
    return inactiveDuration >= threshold;
  }

  /// Get the duration of inactivity
  Duration getInactiveDuration() {
    if (state.lastActivityTime == null) {
      return Duration.zero;
    }

    final now = DateTime.now();
    return now.difference(state.lastActivityTime!);
  }

  /// Set active state
  void setActive(bool active) {
    state = state.copyWith(isActive: active);
  }
}

/// Provider for activity tracker
final activityTrackerProvider =
    StateNotifierProvider<ActivityTrackerNotifier, ActivityTrackerState>((ref) {
  // Note: SharedPreferences should be provided via FutureProvider
  // For now, we'll handle async initialization in the notifier
  throw UnimplementedError('Use activityTrackerFutureProvider instead');
});

/// Async provider for activity tracker (handles SharedPreferences init)
final activityTrackerFutureProvider = FutureProvider<ActivityTrackerNotifier>((ref) async {
  final prefs = await SharedPreferences.getInstance();
  return ActivityTrackerNotifier(prefs);
});
