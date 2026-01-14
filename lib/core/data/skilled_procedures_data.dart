/// Default skilled procedures list
/// These procedures are available to all anesthesia providers
class SkilledProceduresData {
  /// Default skilled procedures (alphabetically sorted)
  static const List<String> defaultProcedures = [
    'BIS - Bispectral Monitoring',
    'Blood Transfusion',
    'Brain Oximetry',
    'Cardioversion',
    'Cell Saver',
    'Central Line Placement',
    'Continous Hemodynamic Monitoring',
    'CPR/Defibrillator',
    'EEG - Electroencephalography',
    'Epidural Infusion Maintenance',
    'Evoke Potential Nerve Monitoring',
    'Femoral Arterial Line Placement',
    'IABP - Intraaortic Balloon Pump',
    'Intrathecal Infusion Maintenance',
    'Intravenous Access',
    'Intravenous Access -Neonate',
    'Intravenous Access -Pediatric',
    'Nerve Stimulator-Peripheral Nerve Block',
    'Perfusion Bypass',
    'Phlebotomy',
    'PICC - Peripheral Inserted Central Catheter Placement',
    'Radial Arterial Line Placement',
    'Swan ganz Catheter Placement',
    'Ultrasound/IV Access',
  ];

  /// Get all procedures including user's custom procedures (alphabetically sorted)
  static List<String> getAllProcedures(List<String> customProcedures) {
    final allProcedures = <String>{
      ...defaultProcedures,
      ...customProcedures,
    }.toList();
    allProcedures.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return allProcedures;
  }

  /// Filter procedures by search query
  static List<String> filterProcedures(List<String> procedures, String query) {
    if (query.isEmpty) return procedures;
    final lowerQuery = query.toLowerCase();
    return procedures
        .where((procedure) => procedure.toLowerCase().contains(lowerQuery))
        .toList();
  }
}
