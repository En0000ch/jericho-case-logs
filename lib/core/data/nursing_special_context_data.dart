/// Nursing Special Context data
/// These contexts represent unique clinical circumstances in critical care nursing
class NursingSpecialContext {
  /// Default special contexts (alphabetically sorted)
  static const List<String> defaultContexts = [
    'Actively dying patient',
    'Code blue involvement',
    'Comfort-focused care',
    'Conflict or high-emotion family situation',
    'Continuous EEG monitoring',
    'CRRT patient',
    'ECMO patient',
    'End-of-life care context',
    'Ethics consultation involvement',
    'Family goals-of-care discussions',
    'Fresh ICU admission',
    'Hemodynamic instability',
    'High sedation requirements',
    'High-acuity patient',
    'High-risk infection exposure',
    'ICP-directed care',
    'Immediate post-op ICU care',
    'Isolation precautions',
    'Massive transfusion case',
    'Mechanical circulatory support patient',
    'Multiple vasopressors',
    'Neurologic critical care context',
    'Open chest or open abdomen patient',
    'Organ donation case',
    'Post–cardiac arrest care',
    'Prolonged mechanical ventilation',
    'Prone ventilation context',
    'Rapid clinical deterioration',
    'Rapid response involvement',
    'Stroke activation',
    'Targeted temperature management case',
    'Terminal extubation',
    'Unstable patient',
    'Withdrawal of life-sustaining treatment',
  ];

  /// Get all contexts including user's custom contexts (alphabetically sorted)
  static List<String> getAllContexts(List<String> customContexts) {
    final allContexts = [...defaultContexts, ...customContexts];
    allContexts.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return allContexts;
  }

  /// Filter contexts by search query
  static List<String> filterContexts(List<String> contexts, String query) {
    if (query.isEmpty) return contexts;
    final lowerQuery = query.toLowerCase();
    return contexts
        .where((context) => context.toLowerCase().contains(lowerQuery))
        .toList();
  }
}
