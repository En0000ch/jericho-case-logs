/// Admin determination configuration
/// This file contains the logic for determining if a user is an administrator
///
/// IMPORTANT: This is the SINGLE source of truth for admin access
/// Product owner can update this file to manage admin access without touching login/routing logic

import '../constants/user_roles.dart';
import '../../domain/entities/user.dart';

class AdminConfig {
  /// Email addresses that have admin access
  /// Add or remove emails here to manage admin access
  static const List<String> adminEmails = [
    // Product owner - update this list as needed
    'barrett@jerichocreations.com',
    'admin@jerichocaselogs.com',
  ];

  /// Determine if a user is an administrator
  /// This is the canonical function for admin determination
  ///
  /// Returns true if:
  /// 1. User's email is in the admin allowlist
  /// 2. OR user's silo is explicitly set to jclAll (for backward compatibility)
  static bool isUserAdmin(User user) {
    // Check email allowlist first (highest priority)
    if (adminEmails.contains(user.email.toLowerCase())) {
      return true;
    }

    // Check if silo is explicitly set to jclAll (backward compatibility)
    if (user.silo == SiloConfig.siloAll) {
      return true;
    }

    return false;
  }

  /// Determine if an email has admin access (before user object is created)
  static bool isEmailAdmin(String email) {
    return adminEmails.contains(email.toLowerCase());
  }
}
