import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../domain/entities/user.dart';

/// User data model for Parse Server integration
class UserModel extends User {
  UserModel({
    required super.objectId,
    required super.email,
    super.firstName,
    super.lastName,
    super.title,
    required super.silo,
    super.hasPurchased,
    super.caseCount,
    super.acceptedDisclaimer,
    super.createdAt,
    super.updatedAt,
  });

  /// Create UserModel from Parse User object
  factory UserModel.fromParseUser(ParseUser parseUser) {
    return UserModel(
      objectId: parseUser.objectId!,
      email: parseUser.emailAddress ?? '',
      firstName: parseUser.get<String>('firstName'),
      lastName: parseUser.get<String>('lastName'),
      title: parseUser.get<String>('title'),
      silo: parseUser.get<String>('jclSilo') ?? 'jclAnes',
      hasPurchased: parseUser.get<bool>('hasPurchased') ?? false,
      caseCount: parseUser.get<int>('caseCount') ?? 0,
      acceptedDisclaimer: false, // Will be fetched from local preferences
      createdAt: parseUser.createdAt,
      updatedAt: parseUser.updatedAt,
    );
  }

  /// Convert to Parse User object
  ParseUser toParseUser() {
    final parseUser = ParseUser(email, null, email);
    parseUser.objectId = objectId;
    parseUser.set('firstName', firstName);
    parseUser.set('lastName', lastName);
    parseUser.set('title', title);
    parseUser.set('jclSilo', silo);
    parseUser.set('hasPurchased', hasPurchased);
    parseUser.set('caseCount', caseCount);
    return parseUser;
  }

  /// Create UserModel from JSON (for local storage)
  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      objectId: json['objectId'] as String,
      email: json['email'] as String,
      firstName: json['firstName'] as String?,
      lastName: json['lastName'] as String?,
      title: json['title'] as String?,
      silo: json['silo'] as String,
      hasPurchased: json['hasPurchased'] as bool? ?? false,
      caseCount: json['caseCount'] as int? ?? 0,
      acceptedDisclaimer: json['acceptedDisclaimer'] as bool? ?? false,
      createdAt: json['createdAt'] != null
          ? DateTime.parse(json['createdAt'] as String)
          : null,
      updatedAt: json['updatedAt'] != null
          ? DateTime.parse(json['updatedAt'] as String)
          : null,
    );
  }

  /// Convert to JSON (for local storage)
  Map<String, dynamic> toJson() {
    return {
      'objectId': objectId,
      'email': email,
      'firstName': firstName,
      'lastName': lastName,
      'title': title,
      'silo': silo,
      'hasPurchased': hasPurchased,
      'caseCount': caseCount,
      'acceptedDisclaimer': acceptedDisclaimer,
      'createdAt': createdAt?.toIso8601String(),
      'updatedAt': updatedAt?.toIso8601String(),
    };
  }

  /// Convert to domain entity
  User toDomain() {
    return User(
      objectId: objectId,
      email: email,
      firstName: firstName,
      lastName: lastName,
      title: title,
      silo: silo,
      hasPurchased: hasPurchased,
      caseCount: caseCount,
      acceptedDisclaimer: acceptedDisclaimer,
      createdAt: createdAt,
      updatedAt: updatedAt,
    );
  }
}
