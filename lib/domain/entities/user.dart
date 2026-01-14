/// User domain entity
class User {
  final String objectId;
  final String email;
  final String? firstName;
  final String? lastName;
  final String? title;
  final String silo; // jclAnes, jclJobs, jclAll, etc.
  final String? jclRole; // Professional role: CRNA, CAA, Anesthesiologist, RN, LPN, Nurse, etc.
  final bool hasPurchased;
  final int caseCount;
  final bool acceptedDisclaimer;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  User({
    required this.objectId,
    required this.email,
    this.firstName,
    this.lastName,
    this.title,
    required this.silo,
    this.jclRole,
    this.hasPurchased = false,
    this.caseCount = 0,
    this.acceptedDisclaimer = false,
    this.createdAt,
    this.updatedAt,
  });

  /// Check if user is a CRNA
  bool get isCRNA => silo == 'jclAnes';

  /// Check if user is a job provider
  bool get isJobProvider => silo == 'jclJobs';

  /// Check if user is an administrator
  bool get isAdmin => silo == 'jclAll';

  /// Check if user has reached free tier limit
  bool hasReachedFreeLimit(int freeLimit) {
    return !hasPurchased && caseCount >= freeLimit;
  }

  /// Get user's full name
  String get fullName {
    if (firstName != null && lastName != null) {
      return '$firstName $lastName';
    } else if (firstName != null) {
      return firstName!;
    } else if (lastName != null) {
      return lastName!;
    }
    return email;
  }

  User copyWith({
    String? objectId,
    String? email,
    String? firstName,
    String? lastName,
    String? title,
    String? silo,
    String? jclRole,
    bool? hasPurchased,
    int? caseCount,
    bool? acceptedDisclaimer,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return User(
      objectId: objectId ?? this.objectId,
      email: email ?? this.email,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      title: title ?? this.title,
      silo: silo ?? this.silo,
      jclRole: jclRole ?? this.jclRole,
      hasPurchased: hasPurchased ?? this.hasPurchased,
      caseCount: caseCount ?? this.caseCount,
      acceptedDisclaimer: acceptedDisclaimer ?? this.acceptedDisclaimer,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
