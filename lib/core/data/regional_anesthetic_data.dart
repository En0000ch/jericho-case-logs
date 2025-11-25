/// Regional Anesthetic data
/// Matches iOS app's regTableViewController.m
class RegionalAnestheticData {
  /// Default Regional Anesthetic options (25 items)
  static const List<String> defaultOptions = [
    'Adductor Canal Block',
    'Ankle Block',
    'Axillary Brachial Plexus Block',
    'Caudal Epidural',
    'Cervical Epidural',
    'Digital Block',
    'Epidural',
    'Femoral Nerve Block',
    'Intercostal Nerve Block',
    'Interscalene Brachial Plexus Block',
    'Intra-articular Block',
    'Lumbar Epidural',
    'Occipital Nerve Block',
    'Paravertebral Block',
    'Popliteal Block',
    'Retrobulbar Block',
    'Saphenous Nerve Block',
    'Sciatic Nerve Block',
    'Spinal',
    'Stellate Ganglion Block',
    'Supraclavicular Brachial Plexus Block',
    'TAP Block (Transversus Abdominis Plane)',
    'Thoracic Epidural',
    'Wrist Block',
    'Other Regional Block',
  ];

  /// Get default Regional options (sorted alphabetically)
  static List<String> getDefaultOptions() {
    final options = List<String>.from(defaultOptions);
    options.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return options;
  }
}
