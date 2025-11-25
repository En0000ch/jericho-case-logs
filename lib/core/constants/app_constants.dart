/// Application-wide constants
class AppConstants {
  // App Info
  static const String appName = 'JerichoCaseLogs';
  static const String appVersion = '1.0.0';

  // Free tier limit
  static const int freeCaseLimit = 5;

  // Silo types (user categories)
  static const String siloAnes = 'jclAnes';  // CRNA users
  static const String siloJobs = 'jclJobs';  // Job providers
  static const String siloAll = 'jclAll';    // Administrators
  static const String siloNurse = 'jclNurse';
  static const String siloScrub = 'jclScrub';
  static const String siloPhy = 'jclPhy';

  // ASA Classification
  static const List<String> asaClassifications = [
    'I',
    'II',
    'III',
    'IV',
    'V',
    'VI',
  ];

  // Anesthetic Plans
  static const List<String> anestheticPlans = [
    'General',
    'Regional',
    'MAC',
    'TIVA',
    'Spinal',
    'Epidural',
    'Combined Spinal-Epidural',
  ];

  // Gender Options
  static const List<String> genderOptions = [
    'Male',
    'Female',
    'Other',
  ];

  // Surgery Classes
  static const List<String> surgeryClasses = [
    'Cardiology',
    'Orthopedics',
    'Neurosurgery',
    'General Surgery',
    'Obstetrics & Gynecology',
    'Pediatrics',
    'ENT',
    'Ophthalmology',
    'Urology',
    'Vascular Surgery',
    'Plastic Surgery',
    'Thoracic Surgery',
    'Trauma',
    'Emergency',
  ];
}
