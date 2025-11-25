/// Complications data
/// Matches iOS app's complicationsTableViewController.m
class ComplicationsData {
  /// Default complications list (26 items)
  static const List<String> defaultComplications = [
    'Allergic Reaction',
    'Anaphylaxis ',
    'Aspiration',
    'Bradydysrhythmia',
    'Bronchospasm',
    'Cardiac Tamponade',
    'CAS - Central Anticholinergic Syndrome',
    'Cardiovascular Collapse, Cardiac Arrest',
    'Cerebrovascular Accident ',
    'Conversion to General Anesthetic',
    'Damage Dentition',
    'Death',
    'Dural Tap',
    'Embolic Event',
    'Fetal Demise ',
    'Hemorrhage >1500',
    'Hypoxic Brain Injury',
    'Laryngospasm',
    'Malignant Hyperthermia',
    'Myocardial Infarction',
    'Post Dural Puncture Headache',
    'Postpartum Hemmorhage',
    'Recurarization',
    'Supraventricular Dysrhythmia',
    'Tachydysrhythmia',
    'Ventricular Dysrhythmia',
  ];

  /// Get default complications (sorted alphabetically)
  static List<String> getDefaultComplications() {
    final complications = List<String>.from(defaultComplications);
    complications.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return complications;
  }
}
