/// Silo resolution logic
/// Determines which silo a user should be routed to based on strict precedence rules

import '../../domain/entities/user.dart';
import '../constants/user_roles.dart';
import '../config/admin_config.dart';

class SiloResolver {
  /// Resolve the effective silo for a user using strict precedence:
  /// 1. If user is admin (via email allowlist or silo==jclAll) -> effectiveSilo = jclAll
  /// 2. Else if user has jclSilo stored -> effectiveSilo = stored value
  /// 3. Else (existing user missing field) -> effectiveSilo = jclAnes (default for existing users)
  ///
  /// Returns the effective silo string
  static String resolveEffectiveSilo(User user) {
    // Priority 1: Check if user is admin
    if (AdminConfig.isUserAdmin(user)) {
      print('[SiloResolver] User ${user.email} is admin -> routing to jclAll');
      return SiloConfig.siloAll;
    }

    // Priority 2: Use stored jclSilo if present and valid
    if (user.silo.isNotEmpty && SiloConfig.allSilos.contains(user.silo)) {
      print('[SiloResolver] User ${user.email} has silo=${user.silo} -> routing to ${user.silo}');
      return user.silo;
    }

    // Priority 3: Default fallback for existing users (no silo field)
    print('[SiloResolver] User ${user.email} has no valid silo -> defaulting to jclAnes');
    return SiloConfig.siloAnes;
  }

  /// Check if a silo requires admin selection modal
  /// Returns true only for jclAll
  static bool requiresSiloSelection(String effectiveSilo) {
    return effectiveSilo == SiloConfig.siloAll;
  }
}
