/// Constants for patient timer feature
class PatientTimerConstants {
  // Inactivity threshold before starting countdown
  static const Duration inactivityThreshold = Duration(minutes: 10);

  // Countdown duration (use 5 min for testing, 24 hours for production)
  static const Duration countdownDuration = Duration(minutes: 5); // Testing
  // static const Duration countdownDuration = Duration(hours: 24); // Production

  // How often to check for inactivity
  static const Duration checkInterval = Duration(seconds: 30);

  // Lookback period for patient fetch
  static const Duration patientLookbackPeriod = Duration(hours: 24);

  // SharedPreferences keys
  static const String keyLastActivity = 'nurse_last_activity_timestamp';
  static const String keyLastTimerTrigger = 'nurse_last_timer_trigger';
  static const String keyCountdownStartTime = 'nurse_countdown_start_time';
  static const String keyDismissedPatients = 'nurse_timer_dismissed_patients';
}
