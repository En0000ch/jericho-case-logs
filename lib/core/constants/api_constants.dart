/// Parse Server API Configuration
class ApiConstants {
  // Parse Server Configuration
  static const String parseApplicationId = '9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF';
  static const String parseClientKey = 'fTLpvY4OQG1kj1Njq7rz6WqcFuN0HQT70mMI90SQ';
  static const String parseServerUrl = 'https://parseapi.back4app.com';

  // Parse Server Class Names
  static const String casesClass = 'jclCases';
  static const String facilitiesClass = 'jclFacilities';
  static const String surgeonsClass = 'jclSurgeons';
  static const String skillsClass = 'jclSkills';
  static const String cmeClass = 'jclCME';
  static const String jobsClass = 'jclJobs';
  static const String usersClass = '_User';

  // Local Database
  static const String localDatabaseName = 'jclDB.db';
  static const int localDatabaseVersion = 1;

  // Sync Status Codes
  static const int syncStatusPending = 0;
  static const int syncStatusSynced = 1;
  static const int syncStatusError = 2;
}
