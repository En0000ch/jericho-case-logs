import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../domain/entities/case.dart';
import '../../domain/entities/surgery_specialty.dart';
import '../../core/constants/api_constants.dart';

/// Case model for data layer
class CaseModel extends Case {
  const CaseModel({
    required super.objectId,
    required super.userEmail,
    required super.date,
    super.patientAge,
    super.gender,
    super.surgeonName,
    required super.asaClassification,
    required super.procedureSurgery,
    required super.anestheticPlan,
    super.secondaryAnesthetic,
    super.anestheticsUsed,
    required super.surgeryClass,
    super.location,
    super.airwayManagement,
    super.additionalComments,
    super.complications,
    super.complicationsList,
    super.comorbidities,
    super.skills,
    super.imageName,
    required super.createdAt,
    required super.updatedAt,
  });

  /// Get image name based on surgery class
  /// Matches iOS implementation where surgeryClass determines the icon
  static String? getImageNameForSurgeryClass(String? surgeryClass) {
    if (surgeryClass == null || surgeryClass.isEmpty) {
      return 'genSurgery.png'; // Default fallback
    }

    // Find matching specialty by title
    final specialty = SurgerySpecialties.all.firstWhere(
      (s) => s.title == surgeryClass,
      orElse: () => const SurgerySpecialty(
        title: 'General',
        imageName: 'genSurgery.png',
        surgeries: [],
      ),
    );

    return specialty.imageName;
  }

  /// Create from ParseObject
  factory CaseModel.fromParseObject(ParseObject parseObject) {
    print('DEBUG MODEL: fromParseObject called for objectId=${parseObject.objectId}');

    // iOS app uses different field names
    final dateTimeFromIOS = parseObject.get<DateTime>('dateTime');
    final dateFromFlutter = parseObject.get<DateTime>('date');
    final date = dateTimeFromIOS ?? dateFromFlutter ?? DateTime.now();
    print('DEBUG MODEL: Date - iOS dateTime=$dateTimeFromIOS, Flutter date=$dateFromFlutter, final=$date');

    final asaPlanFromIOS = parseObject.get<String>('asaPlan');
    final asaFromFlutter = parseObject.get<String>('asaClassification');
    // iOS stores ASA as "I, E" or "II, E" etc. - extract just the first part
    var asaPlan = asaPlanFromIOS ?? asaFromFlutter ?? 'I';
    if (asaPlan.contains(',')) {
      asaPlan = asaPlan.split(',').first.trim();
    }
    print('DEBUG MODEL: ASA - iOS asaPlan=$asaPlanFromIOS, Flutter asaClassification=$asaFromFlutter, final=$asaPlan');

    final surgeryFromIOS = parseObject.get<String>('surgery');
    final procSurgeryFromFlutter = parseObject.get<String>('procSurgery');
    final procedureSurgery = surgeryFromIOS ?? procSurgeryFromFlutter ?? '';
    print('DEBUG MODEL: Surgery - iOS surgery=$surgeryFromIOS, Flutter procSurgery=$procSurgeryFromFlutter, final=$procedureSurgery');

    final primePlanFromIOS = parseObject.get<String>('primePlan');
    final anestheticPlanFromFlutter = parseObject.get<String>('anestheticPlan');
    final anestheticPlan = primePlanFromIOS ?? anestheticPlanFromFlutter ?? '';
    print('DEBUG MODEL: Anesthetic - iOS primePlan=$primePlanFromIOS, Flutter anestheticPlan=$anestheticPlanFromFlutter, final=$anestheticPlan');

    // iOS app uses 'secPlan' field for secondary anesthetic
    final secPlanFromIOS = parseObject.get<String>('secPlan');
    final secondaryPlanFromIOS = parseObject.get<String>('secondaryPlan');
    final secondaryAnestheticFromFlutter = parseObject.get<String>('secondaryAnesthetic');
    // Default to 'N/A' if no secondary anesthetic is specified
    final secondaryAnesthetic = secPlanFromIOS ?? secondaryPlanFromIOS ?? secondaryAnestheticFromFlutter ?? 'N/A';
    print('DEBUG MODEL: Secondary Anesthetic - iOS secPlan=$secPlanFromIOS, iOS secondaryPlan=$secondaryPlanFromIOS, Flutter secondaryAnesthetic=$secondaryAnestheticFromFlutter, final=$secondaryAnesthetic');

    final caseNotesFromIOS = parseObject.get<String>('caseNotes');
    final additionalCommentsFromFlutter = parseObject.get<String>('additionalComments');
    final additionalComments = caseNotesFromIOS ?? additionalCommentsFromFlutter;
    print('DEBUG MODEL: Comments - iOS caseNotes=$caseNotesFromIOS, Flutter additionalComments=$additionalCommentsFromFlutter, final=$additionalComments');

    final userEmail = parseObject.get<String>('userEmail') ?? '';
    print('DEBUG MODEL: userEmail=$userEmail');

    // Read surgeon name from Parse
    // iOS doesn't have a separate surgeonName field - it's embedded in additionalComments as "Surgeon: Name"
    String? surgeonName = parseObject.get<String>('surgeonName');

    // If no surgeonName field, try to extract from additionalComments
    if (surgeonName == null) {
      final comments = caseNotesFromIOS ?? additionalCommentsFromFlutter;
      if (comments != null && comments.startsWith('Surgeon: ')) {
        // Extract surgeon name - stop at first newline
        final nameStart = comments.substring('Surgeon: '.length);
        final newlineIndex = nameStart.indexOf('\n');
        surgeonName = newlineIndex > 0 ? nameStart.substring(0, newlineIndex).trim() : nameStart.trim();
      }
    }

    print('DEBUG MODEL: surgeonName=$surgeonName');

    // Parse patient age - iOS stores as "42 years", need to extract the number
    int? patientAge;
    final patientAgeString = parseObject.get<String>('patientAge');
    if (patientAgeString != null && patientAgeString.isNotEmpty) {
      // Extract digits from string like "42 years"
      final ageMatch = RegExp(r'\d+').firstMatch(patientAgeString);
      if (ageMatch != null) {
        patientAge = int.tryParse(ageMatch.group(0)!);
      }
    }
    print('DEBUG MODEL: patientAgeString=$patientAgeString, parsed patientAge=$patientAge');

    // Get surgeryClass first as we may need it for image determination
    final surgeryClass = parseObject.get<String>('surgeryClass') ?? 'General';

    // Image name logic matching iOS:
    // 1. First check jclImageName from server
    // 2. Then check legacy imageName field
    // 3. Finally fallback to surgeryClass-based image
    final jclImageName = parseObject.get<String>('jclImageName');
    final legacyImageName = parseObject.get<String>('imageName');
    final imageName = jclImageName ??
                     legacyImageName ??
                     getImageNameForSurgeryClass(surgeryClass);
    print('DEBUG MODEL: jclImageName=$jclImageName, legacyImageName=$legacyImageName, surgeryClass=$surgeryClass, final imageName=$imageName');

    // Parse list fields
    final complicationsListData = parseObject.get<List<dynamic>>('complicationsList');
    final complicationsList = complicationsListData
            ?.map((e) => e.toString())
            .where((item) => item.trim().isNotEmpty)
            .toList() ??
        [];

    final comorbiditiesData = parseObject.get<List<dynamic>>('comorbidities');
    final comorbidities = comorbiditiesData
            ?.map((e) => e.toString())
            .where((item) => item.trim().isNotEmpty)
            .toList() ??
        [];

    final skillsData = parseObject.get<List<dynamic>>('skills');
    final skills = skillsData
            ?.map((e) => e.toString())
            .where((item) => item.trim().isNotEmpty)
            .toList() ??
        [];

    // For createdAt: use Parse's createdAt if available, otherwise use the surgery date
    // This ensures old cases without createdAt don't appear as "recent"
    final createdAt = parseObject.createdAt ?? date;
    final updatedAt = parseObject.updatedAt ?? date;

    print('DEBUG MODEL: createdAt from Parse=${parseObject.createdAt}, using=$createdAt');
    print('DEBUG MODEL: updatedAt from Parse=${parseObject.updatedAt}, using=$updatedAt');

    return CaseModel(
      objectId: parseObject.objectId ?? '',
      userEmail: userEmail,
      date: date,
      patientAge: patientAge,
      gender: parseObject.get<String>('gender'),
      surgeonName: surgeonName,
      asaClassification: asaPlan,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      secondaryAnesthetic: secondaryAnesthetic,
      anestheticsUsed: [], // iOS uses different structure
      surgeryClass: surgeryClass,
      location: parseObject.get<String>('location'),
      airwayManagement: parseObject.get<String>('airwayManagement'),
      additionalComments: additionalComments,
      complications: parseObject.get<bool>('complications'),
      complicationsList: complicationsList,
      comorbidities: comorbidities,
      skills: skills,
      imageName: imageName,
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }

  /// Convert to ParseObject
  ParseObject toParseObject() {
    final parseObject = ParseObject(ApiConstants.casesClass)
      ..objectId = objectId
      ..set('userEmail', userEmail)
      ..set('date', date)
      ..set('asaClassification', asaClassification)
      ..set('procSurgery', procedureSurgery)
      ..set('anestheticPlan', anestheticPlan)
      ..set('anestheticsUsed', anestheticsUsed)
      ..set('surgeryClass', surgeryClass);

    if (patientAge != null) parseObject.set('patientAge', patientAge);
    if (gender != null) parseObject.set('gender', gender);
    if (surgeonName != null) parseObject.set('surgeonName', surgeonName);
    // Save secondary anesthetic to 'secPlan' field (iOS compatibility)
    if (secondaryAnesthetic != null) {
      parseObject.set('secPlan', secondaryAnesthetic);
      parseObject.set('secondaryAnesthetic', secondaryAnesthetic); // Keep for Flutter compatibility
    }
    if (location != null) parseObject.set('location', location);
    if (airwayManagement != null) {
      parseObject.set('airwayManagement', airwayManagement);
    }
    if (additionalComments != null) {
      parseObject.set('additionalComments', additionalComments);
    }
    if (complications != null) parseObject.set('complications', complications);
    if (complicationsList.isNotEmpty) {
      parseObject.set('complicationsList', complicationsList);
    }
    if (comorbidities.isNotEmpty) {
      parseObject.set('comorbidities', comorbidities);
    }
    if (skills.isNotEmpty) parseObject.set('skills', skills);
    if (imageName != null) parseObject.set('imageName', imageName);

    return parseObject;
  }

  /// Create from JSON (for local storage)
  factory CaseModel.fromJson(Map<String, dynamic> json) {
    return CaseModel(
      objectId: json['objectId'] as String,
      userEmail: json['userEmail'] as String,
      date: DateTime.parse(json['date'] as String),
      patientAge: json['patientAge'] as int?,
      gender: json['gender'] as String?,
      surgeonName: json['surgeonName'] as String?,
      asaClassification: json['asaClassification'] as String,
      procedureSurgery: json['procedureSurgery'] as String,
      anestheticPlan: json['anestheticPlan'] as String,
      secondaryAnesthetic: json['secondaryAnesthetic'] as String?,
      anestheticsUsed: (json['anestheticsUsed'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      surgeryClass: json['surgeryClass'] as String,
      location: json['location'] as String?,
      airwayManagement: json['airwayManagement'] as String?,
      additionalComments: json['additionalComments'] as String?,
      complications: json['complications'] as bool?,
      complicationsList: (json['complicationsList'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      comorbidities: (json['comorbidities'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      skills: (json['skills'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      imageName: json['imageName'] as String?,
      createdAt: DateTime.parse(json['createdAt'] as String),
      updatedAt: DateTime.parse(json['updatedAt'] as String),
    );
  }

  /// Convert to JSON (for local storage)
  Map<String, dynamic> toJson() {
    return {
      'objectId': objectId,
      'userEmail': userEmail,
      'date': date.toIso8601String(),
      'patientAge': patientAge,
      'gender': gender,
      'surgeonName': surgeonName,
      'asaClassification': asaClassification,
      'procedureSurgery': procedureSurgery,
      'anestheticPlan': anestheticPlan,
      'secondaryAnesthetic': secondaryAnesthetic,
      'anestheticsUsed': anestheticsUsed,
      'surgeryClass': surgeryClass,
      'location': location,
      'airwayManagement': airwayManagement,
      'additionalComments': additionalComments,
      'complications': complications,
      'complicationsList': complicationsList,
      'comorbidities': comorbidities,
      'skills': skills,
      'imageName': imageName,
      'createdAt': createdAt.toIso8601String(),
      'updatedAt': updatedAt.toIso8601String(),
    };
  }

  /// Convert to Map for SQLite
  Map<String, dynamic> toSqliteMap() {
    return {
      'objectId': objectId,
      'userEmail': userEmail,
      'date': date.millisecondsSinceEpoch,
      'patientAge': patientAge,
      'gender': gender,
      'surgeonName': surgeonName,
      'asaClassification': asaClassification,
      'procedureSurgery': procedureSurgery,
      'anestheticPlan': anestheticPlan,
      'secondaryAnesthetic': secondaryAnesthetic,
      'anestheticsUsed': anestheticsUsed.join(','), // Store as comma-separated
      'surgeryClass': surgeryClass,
      'location': location,
      'airwayManagement': airwayManagement,
      'additionalComments': additionalComments,
      'complications': complications == true ? 1 : 0,
      'complicationsList': complicationsList.join(','),
      'comorbidities': comorbidities.join(','),
      'skills': skills.join(','),
      'imageName': imageName,
      'createdAt': createdAt.millisecondsSinceEpoch,
      'updatedAt': updatedAt.millisecondsSinceEpoch,
    };
  }

  /// Create from SQLite Map
  factory CaseModel.fromSqliteMap(Map<String, dynamic> map) {
    return CaseModel(
      objectId: map['objectId'] as String,
      userEmail: map['userEmail'] as String,
      date: DateTime.fromMillisecondsSinceEpoch(map['date'] as int),
      patientAge: map['patientAge'] as int?,
      gender: map['gender'] as String?,
      surgeonName: map['surgeonName'] as String?,
      asaClassification: map['asaClassification'] as String,
      procedureSurgery: map['procedureSurgery'] as String,
      anestheticPlan: map['anestheticPlan'] as String,
      secondaryAnesthetic: map['secondaryAnesthetic'] as String?,
      anestheticsUsed: (map['anestheticsUsed'] as String).isNotEmpty
          ? (map['anestheticsUsed'] as String).split(',')
          : [],
      surgeryClass: map['surgeryClass'] as String,
      location: map['location'] as String?,
      airwayManagement: map['airwayManagement'] as String?,
      additionalComments: map['additionalComments'] as String?,
      complications: (map['complications'] as int) == 1,
      complicationsList: (map['complicationsList'] as String?)?.isNotEmpty == true
          ? (map['complicationsList'] as String).split(',')
          : [],
      comorbidities: (map['comorbidities'] as String?)?.isNotEmpty == true
          ? (map['comorbidities'] as String).split(',')
          : [],
      skills: (map['skills'] as String?)?.isNotEmpty == true
          ? (map['skills'] as String).split(',')
          : [],
      imageName: map['imageName'] as String?,
      createdAt:
          DateTime.fromMillisecondsSinceEpoch(map['createdAt'] as int),
      updatedAt:
          DateTime.fromMillisecondsSinceEpoch(map['updatedAt'] as int),
    );
  }

  /// Convert to domain entity
  Case toDomain() {
    return Case(
      objectId: objectId,
      userEmail: userEmail,
      date: date,
      patientAge: patientAge,
      gender: gender,
      surgeonName: surgeonName,
      asaClassification: asaClassification,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      secondaryAnesthetic: secondaryAnesthetic,
      anestheticsUsed: anestheticsUsed,
      surgeryClass: surgeryClass,
      location: location,
      airwayManagement: airwayManagement,
      additionalComments: additionalComments,
      complications: complications,
      complicationsList: complicationsList,
      comorbidities: comorbidities,
      skills: skills,
      imageName: imageName,
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
