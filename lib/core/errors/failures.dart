/// Base failure class
abstract class Failure {
  final String message;
  const Failure(this.message);
}

/// Server failure (Parse Server errors)
class ServerFailure extends Failure {
  const ServerFailure(super.message);
}

/// Authentication failure
class AuthFailure extends Failure {
  const AuthFailure(super.message);
}

/// Database failure (SQLite errors)
class DatabaseFailure extends Failure {
  const DatabaseFailure(super.message);
}

/// Network failure
class NetworkFailure extends Failure {
  const NetworkFailure(super.message);
}

/// Cache failure (SharedPreferences errors)
class CacheFailure extends Failure {
  const CacheFailure(super.message);
}

/// Validation failure
class ValidationFailure extends Failure {
  const ValidationFailure(super.message);
}
