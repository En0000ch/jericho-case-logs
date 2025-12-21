/// Helper utility to get the correct image asset for a surgery class
class SurgeryImageHelper {
  /// Get the image filename for a given surgery class
  static String getImageForSurgeryClass(String? surgeryClass) {
    if (surgeryClass == null || surgeryClass.isEmpty) {
      return 'genSurgery.png'; // Default fallback
    }

    switch (surgeryClass) {
      case 'Cardiovascular':
        return 'cardiology.png';
      case 'Dental':
        return 'dental.png';
      case 'General':
        return 'genSurgery.png';
      case 'Neurosurgery':
        return 'neurology.png';
      case 'Obstetric/Gynecologic':
        return 'obgyn.png';
      case 'Ophthalmic':
        return 'ophthalmology.png';
      case 'Orthopedic':
        return 'orthopedics.png';
      case 'Otolaryngology Head/Neck':
        return 'otolaryngology.png';
      case 'Out-of-Operating Room Procedures':
        return 'out-of-room procedures.png';
      case 'Pediatric':
        return 'pediatric.png';
      case 'Plastics & Reconstructive':
        return 'plastics.png';
      case 'Thoracic':
        return 'pulmonology.png';
      case 'Urology':
        return 'urology.png';
      default:
        return 'genSurgery.png'; // Default fallback
    }
  }

  /// Get the full asset path for a surgery class image
  static String getAssetPathForSurgeryClass(String? surgeryClass) {
    return 'assets/images/${getImageForSurgeryClass(surgeryClass)}';
  }

  /// Get the full asset path from an image name
  /// If imageName is null, falls back to using surgeryClass
  static String getAssetPath(String? imageName, {String? surgeryClass}) {
    if (imageName != null && imageName.isNotEmpty) {
      return 'assets/images/$imageName';
    }
    // Fallback to surgeryClass if imageName is not available
    return getAssetPathForSurgeryClass(surgeryClass);
  }
}
