import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../../models/case_model.dart';

/// SQLite Database Helper
class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('jericho_cases.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 2,
      onCreate: _createDB,
      onUpgrade: _upgradeDB,
    );
  }

  Future<void> _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE cases (
        objectId TEXT PRIMARY KEY,
        userEmail TEXT NOT NULL,
        date INTEGER NOT NULL,
        patientAge INTEGER,
        gender TEXT,
        asaClassification TEXT NOT NULL,
        procedureSurgery TEXT NOT NULL,
        anestheticPlan TEXT NOT NULL,
        anestheticsUsed TEXT,
        surgeryClass TEXT NOT NULL,
        location TEXT,
        airwayManagement TEXT,
        additionalComments TEXT,
        complications INTEGER,
        imageName TEXT,
        createdAt INTEGER NOT NULL,
        updatedAt INTEGER NOT NULL
      )
    ''');

    // Create index on userEmail for faster queries
    await db.execute('''
      CREATE INDEX idx_user_object_id ON cases(userEmail)
    ''');

    // Create index on date for faster sorting
    await db.execute('''
      CREATE INDEX idx_date ON cases(date)
    ''');
  }

  Future<void> _upgradeDB(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      // Add imageName column for version 2
      await db.execute('''
        ALTER TABLE cases ADD COLUMN imageName TEXT
      ''');
    }
  }

  /// Insert a case
  Future<void> insertCase(CaseModel caseModel) async {
    final db = await database;
    await db.insert(
      'cases',
      caseModel.toSqliteMap(),
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Get all cases for a user
  Future<List<CaseModel>> getCases(String userEmail) async {
    final db = await database;
    final maps = await db.query(
      'cases',
      where: 'userEmail = ?',
      whereArgs: [userEmail],
      orderBy: 'date DESC',
    );

    return maps.map((map) => CaseModel.fromSqliteMap(map)).toList();
  }

  /// Get a single case by ID
  Future<CaseModel?> getCase(String caseId) async {
    final db = await database;
    final maps = await db.query(
      'cases',
      where: 'objectId = ?',
      whereArgs: [caseId],
      limit: 1,
    );

    if (maps.isEmpty) return null;
    return CaseModel.fromSqliteMap(maps.first);
  }

  /// Update a case
  Future<void> updateCase(CaseModel caseModel) async {
    final db = await database;
    await db.update(
      'cases',
      caseModel.toSqliteMap(),
      where: 'objectId = ?',
      whereArgs: [caseModel.objectId],
    );
  }

  /// Delete a case
  Future<void> deleteCase(String caseId) async {
    final db = await database;
    await db.delete(
      'cases',
      where: 'objectId = ?',
      whereArgs: [caseId],
    );
  }

  /// Search cases with filters
  Future<List<CaseModel>> searchCases({
    required String userEmail,
    String? keyword,
    DateTime? startDate,
    DateTime? endDate,
    String? asaClassification,
    String? surgeryClass,
  }) async {
    final db = await database;
    String whereClause = 'userEmail = ?';
    List<dynamic> whereArgs = [userEmail];

    if (keyword != null && keyword.isNotEmpty) {
      whereClause += ' AND (procedureSurgery LIKE ? OR additionalComments LIKE ?)';
      whereArgs.add('%$keyword%');
      whereArgs.add('%$keyword%');
    }

    if (startDate != null) {
      whereClause += ' AND date >= ?';
      whereArgs.add(startDate.millisecondsSinceEpoch);
    }

    if (endDate != null) {
      whereClause += ' AND date <= ?';
      whereArgs.add(endDate.millisecondsSinceEpoch);
    }

    if (asaClassification != null && asaClassification.isNotEmpty) {
      whereClause += ' AND asaClassification = ?';
      whereArgs.add(asaClassification);
    }

    if (surgeryClass != null && surgeryClass.isNotEmpty) {
      whereClause += ' AND surgeryClass = ?';
      whereArgs.add(surgeryClass);
    }

    final maps = await db.query(
      'cases',
      where: whereClause,
      whereArgs: whereArgs,
      orderBy: 'date DESC',
    );

    return maps.map((map) => CaseModel.fromSqliteMap(map)).toList();
  }

  /// Get case count for a user
  Future<int> getCaseCount(String userEmail) async {
    final db = await database;
    final result = await db.rawQuery(
      'SELECT COUNT(*) as count FROM cases WHERE userEmail = ?',
      [userEmail],
    );
    return Sqflite.firstIntValue(result) ?? 0;
  }

  /// Delete all cases for a user
  Future<void> deleteAllCases(String userEmail) async {
    final db = await database;
    await db.delete(
      'cases',
      where: 'userEmail = ?',
      whereArgs: [userEmail],
    );
  }

  /// Close the database
  Future<void> close() async {
    final db = await database;
    await db.close();
  }
}
