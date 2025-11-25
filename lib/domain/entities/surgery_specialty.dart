/// Surgery specialty entity
/// Represents a surgical specialty category with its associated surgeries
class SurgerySpecialty {
  final String title;
  final String imageName;
  final List<String> surgeries;

  const SurgerySpecialty({
    required this.title,
    required this.imageName,
    required this.surgeries,
  });
}

/// Predefined surgery specialties matching iOS implementation
class SurgerySpecialties {
  static const List<SurgerySpecialty> all = [
    SurgerySpecialty(
      title: 'Cardiovascular',
      imageName: 'cardiology.png',
      surgeries: [], // Will be populated from data files
    ),
    SurgerySpecialty(
      title: 'Dental',
      imageName: 'dental.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'General',
      imageName: 'genSurgery.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Neurosurgery',
      imageName: 'neurology.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Obstetric/Gynecologic',
      imageName: 'obgyn.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Ophthalmic',
      imageName: 'ophthalmology.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Orthopedic',
      imageName: 'orthopedics.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Otolaryngology Head/Neck',
      imageName: 'otolaryngology.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Out-of-Operating Room Procedures',
      imageName: 'out-of-room procedures.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Pediatric',
      imageName: 'pediatric.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Plastics & Reconstructive',
      imageName: 'plastics.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Thoracic',
      imageName: 'pulmonology.png',
      surgeries: [],
    ),
    SurgerySpecialty(
      title: 'Urology',
      imageName: 'urology.png',
      surgeries: [],
    ),
  ];
}
