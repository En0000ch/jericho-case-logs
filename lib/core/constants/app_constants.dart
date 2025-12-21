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

  // Skilled Procedures / Skills
  static const List<String> skilledProcedures = [
    'Arterial Line Placement',
    'Central Line Placement',
    'Epidural Placement',
    'Spinal Placement',
    'Nerve Block',
    'Difficult Airway Management',
    'Awake Fiberoptic Intubation',
    'Rapid Sequence Intubation',
    'Video Laryngoscopy',
    'LMA Placement',
    'TEE (Transesophageal Echocardiography)',
    'Ultrasound Guided IV Placement',
    'Pediatric Anesthesia',
    'Cardiac Anesthesia',
    'Neuroanesthesia',
    'OB Anesthesia',
    'Regional Anesthesia',
    'Pain Management Procedures',
  ];

  // Complications
  static const List<String> defaultComplications = [
    'Hypotension',
    'Hypertension',
    'Bradycardia',
    'Tachycardia',
    'Difficult Intubation',
    'Failed Intubation',
    'Aspiration',
    'Bronchospasm',
    'Laryngospasm',
    'Desaturation',
    'Hypoxia',
    'Hemorrhage',
    'Anaphylaxis',
    'Cardiac Arrest',
    'Arrhythmia',
    'Pneumothorax',
    'Nerve Injury',
    'Dental Injury',
    'Awareness',
    'PONV',
    'Hypothermia',
    'Hyperthermia',
  ];

  // Comorbidities
  static const List<String> defaultComorbidities = [
    'Hypertension',
    'Diabetes Type 1',
    'Diabetes Type 2',
    'COPD',
    'Asthma',
    'CAD (Coronary Artery Disease)',
    'CHF (Congestive Heart Failure)',
    'Atrial Fibrillation',
    'CKD (Chronic Kidney Disease)',
    'ESRD (End Stage Renal Disease)',
    'Obesity',
    'Sleep Apnea',
    'GERD',
    'Stroke/CVA',
    'Seizure Disorder',
    'Depression',
    'Anxiety',
    'Smoker',
    'Drug/Alcohol Use',
    'Coagulopathy',
    'Liver Disease',
    'Thyroid Disorder',
  ];
}
