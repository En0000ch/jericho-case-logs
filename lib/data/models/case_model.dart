import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../domain/entities/case.dart';
import '../../core/constants/api_constants.dart';

/// Case model for data layer
class CaseModel extends Case {
  const CaseModel({
    required super.objectId,
    required super.userEmail,
    required super.date,
    super.patientAge,
    super.gender,
    required super.asaClassification,
    required super.procedureSurgery,
    required super.anestheticPlan,
    super.anestheticsUsed,
    required super.surgeryClass,
    super.location,
    super.airwayManagement,
    super.additionalComments,
    super.complications,
    super.imageName,
    required super.createdAt,
    required super.updatedAt,
  });

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

    final caseNotesFromIOS = parseObject.get<String>('caseNotes');
    final additionalCommentsFromFlutter = parseObject.get<String>('additionalComments');
    final additionalComments = caseNotesFromIOS ?? additionalCommentsFromFlutter;
    print('DEBUG MODEL: Comments - iOS caseNotes=$caseNotesFromIOS, Flutter additionalComments=$additionalCommentsFromFlutter, final=$additionalComments');

    final userEmail = parseObject.get<String>('userEmail') ?? '';
    print('DEBUG MODEL: userEmail=$userEmail');

    final imageName = parseObject.get<String>('jclImageName') ??
                     parseObject.get<String>('imageName');
    print('DEBUG MODEL: imageName=$imageName');

    return CaseModel(
      objectId: parseObject.objectId ?? '',
      userEmail: userEmail,
      date: date,
      patientAge: null, // iOS stores as string "42 years"
      gender: parseObject.get<String>('gender'),
      asaClassification: asaPlan,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      anestheticsUsed: [], // iOS uses different structure
      surgeryClass: parseObject.get<String>('surgeryClass') ?? 'General Surgery',
      location: parseObject.get<String>('location'),
      airwayManagement: parseObject.get<String>('airwayManagement'),
      additionalComments: additionalComments,
      complications: parseObject.get<bool>('complications'),
      imageName: imageName,
      createdAt: parseObject.createdAt ?? DateTime.now(),
      updatedAt: parseObject.updatedAt ?? DateTime.now(),
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
    if (location != null) parseObject.set('location', location);
    if (airwayManagement != null) {
      parseObject.set('airwayManagement', airwayManagement);
    }
    if (additionalComments != null) {
      parseObject.set('additionalComments', additionalComments);
    }
    if (complications != null) parseObject.set('complications', complications);
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
      asaClassification: json['asaClassification'] as String,
      procedureSurgery: json['procedureSurgery'] as String,
      anestheticPlan: json['anestheticPlan'] as String,
      anestheticsUsed: (json['anestheticsUsed'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          [],
      surgeryClass: json['surgeryClass'] as String,
      location: json['location'] as String?,
      airwayManagement: json['airwayManagement'] as String?,
      additionalComments: json['additionalComments'] as String?,
      complications: json['complications'] as bool?,
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
      'asaClassification': asaClassification,
      'procedureSurgery': procedureSurgery,
      'anestheticPlan': anestheticPlan,
      'anestheticsUsed': anestheticsUsed,
      'surgeryClass': surgeryClass,
      'location': location,
      'airwayManagement': airwayManagement,
      'additionalComments': additionalComments,
      'complications': complications,
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
      'asaClassification': asaClassification,
      'procedureSurgery': procedureSurgery,
      'anestheticPlan': anestheticPlan,
      'anestheticsUsed': anestheticsUsed.join(','), // Store as comma-separated
      'surgeryClass': surgeryClass,
      'location': location,
      'airwayManagement': airwayManagement,
      'additionalComments': additionalComments,
      'complications': complications == true ? 1 : 0,
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
      asaClassification: map['asaClassification'] as String,
      procedureSurgery: map['procedureSurgery'] as String,
      anestheticPlan: map['anestheticPlan'] as String,
      anestheticsUsed: (map['anestheticsUsed'] as String).isNotEmpty
          ? (map['anestheticsUsed'] as String).split(',')
          : [],
      surgeryClass: map['surgeryClass'] as String,
      location: map['location'] as String?,
      airwayManagement: map['airwayManagement'] as String?,
      additionalComments: map['additionalComments'] as String?,
      complications: (map['complications'] as int) == 1,
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
      asaClassification: asaClassification,
      procedureSurgery: procedureSurgery,
      anestheticPlan: anestheticPlan,
      anestheticsUsed: anestheticsUsed,
      surgeryClass: surgeryClass,
      location: location,
      airwayManagement: airwayManagement,
      additionalComments: additionalComments,
      complications: complications,
      imageName: imageName,
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
