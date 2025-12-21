import 'package:equatable/equatable.dart';

/// Anesthesia Case entity
class Case extends Equatable {
  final String objectId;
  final String userEmail;
  final DateTime date;
  final int? patientAge;
  final String? gender;
  final String? surgeonName;
  final String asaClassification;
  final String procedureSurgery;
  final String anestheticPlan;
  final String? secondaryAnesthetic;
  final List<String> anestheticsUsed;
  final String surgeryClass;
  final String? location;
  final String? airwayManagement;
  final String? additionalComments;
  final bool? complications;
  final List<String> complicationsList;
  final List<String> comorbidities;
  final List<String> skills;
  final String? imageName;
  final DateTime createdAt;
  final DateTime updatedAt;

  const Case({
    required this.objectId,
    required this.userEmail,
    required this.date,
    this.patientAge,
    this.gender,
    this.surgeonName,
    required this.asaClassification,
    required this.procedureSurgery,
    required this.anestheticPlan,
    this.secondaryAnesthetic,
    this.anestheticsUsed = const [],
    required this.surgeryClass,
    this.location,
    this.airwayManagement,
    this.additionalComments,
    this.complications,
    this.complicationsList = const [],
    this.comorbidities = const [],
    this.skills = const [],
    this.imageName,
    required this.createdAt,
    required this.updatedAt,
  });

  @override
  List<Object?> get props => [
        objectId,
        userEmail,
        date,
        patientAge,
        gender,
        surgeonName,
        asaClassification,
        procedureSurgery,
        anestheticPlan,
        secondaryAnesthetic,
        anestheticsUsed,
        surgeryClass,
        location,
        airwayManagement,
        additionalComments,
        complications,
        complicationsList,
        comorbidities,
        skills,
        imageName,
        createdAt,
        updatedAt,
      ];

  Case copyWith({
    String? objectId,
    String? userEmail,
    DateTime? date,
    int? patientAge,
    String? gender,
    String? surgeonName,
    String? asaClassification,
    String? procedureSurgery,
    String? anestheticPlan,
    String? secondaryAnesthetic,
    List<String>? anestheticsUsed,
    String? surgeryClass,
    String? location,
    String? airwayManagement,
    String? additionalComments,
    bool? complications,
    List<String>? complicationsList,
    List<String>? comorbidities,
    List<String>? skills,
    String? imageName,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Case(
      objectId: objectId ?? this.objectId,
      userEmail: userEmail ?? this.userEmail,
      date: date ?? this.date,
      patientAge: patientAge ?? this.patientAge,
      gender: gender ?? this.gender,
      surgeonName: surgeonName ?? this.surgeonName,
      asaClassification: asaClassification ?? this.asaClassification,
      procedureSurgery: procedureSurgery ?? this.procedureSurgery,
      anestheticPlan: anestheticPlan ?? this.anestheticPlan,
      secondaryAnesthetic: secondaryAnesthetic ?? this.secondaryAnesthetic,
      anestheticsUsed: anestheticsUsed ?? this.anestheticsUsed,
      surgeryClass: surgeryClass ?? this.surgeryClass,
      location: location ?? this.location,
      airwayManagement: airwayManagement ?? this.airwayManagement,
      additionalComments: additionalComments ?? this.additionalComments,
      complications: complications ?? this.complications,
      complicationsList: complicationsList ?? this.complicationsList,
      comorbidities: comorbidities ?? this.comorbidities,
      skills: skills ?? this.skills,
      imageName: imageName ?? this.imageName,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
