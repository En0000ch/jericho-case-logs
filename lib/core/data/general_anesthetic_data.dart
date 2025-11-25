/// General Anesthetic data
/// Matches iOS app's gaTableViewController.m
class GeneralAnestheticData {
  /// Default General Anesthetic options (10 items)
  static const List<String> defaultOptions = [
    'Bronchoscopy',
    'Conversion to ETT',
    'Conversion to LMA',
    'Direct Laryngoscopy',
    'Double Lumen ETT',
    'Endotracheal Tube',
    'Glidescope',
    'LMA',
    'Mechanically Ventilated',
    'Video Laryngoscope',
  ];

  /// Get default GA options (sorted alphabetically)
  static List<String> getDefaultOptions() {
    final options = List<String>.from(defaultOptions);
    options.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return options;
  }
}
