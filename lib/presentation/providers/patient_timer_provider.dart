import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/patient_timer_constants.dart';
import 'activity_tracker_provider.dart';

/// Timer status states
enum PatientTimerStatus {
  idle, // Normal operation, user active
  monitoring, // Counting down after inactivity
  triggered, // Should show modal
  dismissed, // User handled modal
}

/// State for patient timer
class PatientTimerState {
  final PatientTimerStatus status;
  final DateTime? countdownStartTime;
  final DateTime? lastTriggerTime;
  final Duration? remainingTime;

  PatientTimerState({
    this.status = PatientTimerStatus.idle,
    this.countdownStartTime,
    this.lastTriggerTime,
    this.remainingTime,
  });

  bool get shouldShowModal => status == PatientTimerStatus.triggered;

  PatientTimerState copyWith({
    PatientTimerStatus? status,
    DateTime? countdownStartTime,
    DateTime? lastTriggerTime,
    Duration? remainingTime,
  }) {
    return PatientTimerState(
      status: status ?? this.status,
      countdownStartTime: countdownStartTime ?? this.countdownStartTime,
      lastTriggerTime: lastTriggerTime ?? this.lastTriggerTime,
      remainingTime: remainingTime ?? this.remainingTime,
    );
  }
}

/// Patient timer notifier
class PatientTimerNotifier extends StateNotifier<PatientTimerState> {
  final SharedPreferences _prefs;
  final ActivityTrackerNotifier _activityTracker;
  Timer? _checkTimer;

  PatientTimerNotifier(this._prefs, this._activityTracker)
      : super(PatientTimerState()) {
    _loadTimerState();
  }

  /// Get current timer state (public accessor)
  PatientTimerState get currentState => state;

  /// Load timer state from SharedPreferences
  void _loadTimerState() {
    final countdownStartString = _prefs.getString(PatientTimerConstants.keyCountdownStartTime);
    final lastTriggerString = _prefs.getString(PatientTimerConstants.keyLastTimerTrigger);

    DateTime? countdownStart;
    DateTime? lastTrigger;

    if (countdownStartString != null) {
      try {
        countdownStart = DateTime.parse(countdownStartString);
      } catch (e) {
        print('Error parsing countdown start time: $e');
      }
    }

    if (lastTriggerString != null) {
      try {
        lastTrigger = DateTime.parse(lastTriggerString);
      } catch (e) {
        print('Error parsing last trigger time: $e');
      }
    }

    // Check if countdown would have expired while app was closed
    if (countdownStart != null) {
      final now = DateTime.now();
      final elapsed = now.difference(countdownStart);

      if (elapsed >= PatientTimerConstants.countdownDuration) {
        // Countdown expired - trigger modal immediately
        state = PatientTimerState(
          status: PatientTimerStatus.triggered,
          countdownStartTime: countdownStart,
          lastTriggerTime: lastTrigger,
        );
      } else {
        // Still counting down
        final remaining = PatientTimerConstants.countdownDuration - elapsed;
        state = PatientTimerState(
          status: PatientTimerStatus.monitoring,
          countdownStartTime: countdownStart,
          lastTriggerTime: lastTrigger,
          remainingTime: remaining,
        );
      }
    }
  }

  /// Start monitoring for inactivity
  void startMonitoring() {
    if (_checkTimer != null) {
      return; // Already monitoring
    }

    print('⏱️ Patient timer monitoring started');

    _checkTimer = Timer.periodic(PatientTimerConstants.checkInterval, (_) {
      _checkInactivity();
    });

    // Check immediately on start
    _checkInactivity();
  }

  /// Stop monitoring
  void stopMonitoring() {
    _checkTimer?.cancel();
    _checkTimer = null;
    print('⏱️ Patient timer monitoring stopped');
  }

  /// Check for inactivity and update timer state
  void _checkInactivity() {
    final now = DateTime.now();

    switch (state.status) {
      case PatientTimerStatus.idle:
        // Check if user has been inactive for threshold duration
        if (_activityTracker.isInactive(PatientTimerConstants.inactivityThreshold)) {
          // Start countdown
          _startCountdown();
        }
        break;

      case PatientTimerStatus.monitoring:
        // Check if countdown has expired
        if (state.countdownStartTime != null) {
          final elapsed = now.difference(state.countdownStartTime!);

          if (elapsed >= PatientTimerConstants.countdownDuration) {
            // Trigger modal
            _triggerModal();
          } else {
            // Update remaining time
            final remaining = PatientTimerConstants.countdownDuration - elapsed;
            state = state.copyWith(remainingTime: remaining);
          }
        }
        break;

      case PatientTimerStatus.triggered:
      case PatientTimerStatus.dismissed:
        // Do nothing - waiting for user interaction
        break;
    }
  }

  /// Start countdown
  void _startCountdown() {
    final now = DateTime.now();
    state = PatientTimerState(
      status: PatientTimerStatus.monitoring,
      countdownStartTime: now,
      lastTriggerTime: state.lastTriggerTime,
      remainingTime: PatientTimerConstants.countdownDuration,
    );

    // Persist countdown start time
    _prefs.setString(
      PatientTimerConstants.keyCountdownStartTime,
      now.toIso8601String(),
    );

    print('⏱️ Countdown started: ${PatientTimerConstants.countdownDuration.inMinutes} minutes');
  }

  /// Trigger modal
  void _triggerModal() {
    final now = DateTime.now();
    state = state.copyWith(
      status: PatientTimerStatus.triggered,
      lastTriggerTime: now,
    );

    // Persist trigger time
    _prefs.setString(
      PatientTimerConstants.keyLastTimerTrigger,
      now.toIso8601String(),
    );

    print('⏱️ Timer triggered - show modal');
  }

  /// Reset timer (called when modal is handled)
  Future<void> reset() async {
    state = PatientTimerState(status: PatientTimerStatus.idle);

    // Clear persisted state
    await _prefs.remove(PatientTimerConstants.keyCountdownStartTime);
    await _prefs.remove(PatientTimerConstants.keyLastTimerTrigger);

    // Record activity to reset inactivity timer
    await _activityTracker.recordActivity();

    print('⏱️ Timer reset to idle');
  }

  /// Mark modal as dismissed (don't show again until next trigger)
  void dismiss() {
    state = state.copyWith(status: PatientTimerStatus.dismissed);
    print('⏱️ Modal dismissed');
  }

  @override
  void dispose() {
    stopMonitoring();
    super.dispose();
  }
}

/// Provider for patient timer (requires activity tracker)
final patientTimerProvider =
    StateNotifierProvider<PatientTimerNotifier, PatientTimerState>((ref) {
  throw UnimplementedError('Use patientTimerFutureProvider instead');
});

/// Async provider for patient timer
final patientTimerFutureProvider = FutureProvider<PatientTimerNotifier>((ref) async {
  final prefs = await SharedPreferences.getInstance();
  final activityTracker = await ref.watch(activityTrackerFutureProvider.future);
  return PatientTimerNotifier(prefs, activityTracker);
});
