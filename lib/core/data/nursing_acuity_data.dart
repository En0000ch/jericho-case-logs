/// Jericho Nursing Acuity Scale (Levels 1-5)
/// Acuity level reflects patient complexity, intensity of nursing intervention,
/// and clinical judgment required, independent of unit designation.
class NursingAcuityData {
  static const List<Map<String, String>> acuityLevels = [
    {
      'level': '1',
      'classification': 'Minimal / Stable Care',
      'clinicalDescription':
          'Patients are physiologically stable and require routine nursing care with minimal clinical decision-making. No continuous infusions or advanced monitoring are required.',
      'examples':
          'Med-Surg patients, rehabilitation, observation status, routine post-operative care',
    },
    {
      'level': '2',
      'classification': 'Moderate / Increased Monitoring',
      'clinicalDescription':
          'Patients have predictable conditions requiring scheduled interventions and increased monitoring. Potential for escalation exists but remains manageable.',
      'examples':
          'Telemetry, step-down units, IV antibiotics, PCA therapy',
    },
    {
      'level': '3',
      'classification': 'High / Unstable Potential',
      'clinicalDescription':
          'Patients require frequent assessment and advanced nursing judgment. Continuous non-titrated infusions or respiratory support may be present.',
      'examples':
          'Insulin or heparin infusions, BiPAP, chest tubes, high-acuity ICU care',
    },
    {
      'level': '4',
      'classification': 'Critical / Life-Threatening',
      'clinicalDescription':
          'Patients are actively unstable or at high risk of rapid deterioration. Care includes titratable vasoactive medications and invasive monitoring.',
      'examples':
          'Mechanical ventilation, vasopressors, CRRT, ICP monitoring',
    },
    {
      'level': '5',
      'classification': 'Maximal / Resuscitative',
      'clinicalDescription':
          'Patients require immediate life-sustaining interventions with continuous bedside nursing presence. Often involves multi-organ failure or resuscitation.',
      'examples':
          'Active codes, ECMO, massive transfusion protocols, post-ROSC management',
    },
  ];

  /// Get acuity level by level number
  static Map<String, String>? getAcuityByLevel(String level) {
    try {
      return acuityLevels.firstWhere((acuity) => acuity['level'] == level);
    } catch (e) {
      return null;
    }
  }

  /// Get display text for acuity level
  static String getDisplayText(String level) {
    final acuity = getAcuityByLevel(level);
    if (acuity == null) return 'Not specified';
    return 'Level ${acuity['level']}: ${acuity['classification']}';
  }
}
