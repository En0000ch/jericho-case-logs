/// User roles and silo mapping configuration
/// This file defines all user roles and their mapping to application silos

class UserRoles {
  // Professional roles (exact labels shown to user during signup)
  static const String roleCRNA = 'CRNA';
  static const String roleCAA = 'CAA';
  static const String roleAnesthesiologist = 'Anesthesiologist';
  static const String roleRN = 'RN';
  static const String roleLPN = 'LPN';
  static const String roleNurse = 'Nurse';

  // Future roles (not yet enabled but supported by architecture)
  static const String roleScrubTech = 'Scrub Tech';
  static const String rolePhysician = 'Physician';

  // All available roles for signup
  static const List<String> availableRoles = [
    roleCRNA,
    roleCAA,
    roleAnesthesiologist,
    roleRN,
    roleLPN,
    roleNurse,
  ];

  // Future roles (not shown in UI yet)
  static const List<String> futureRoles = [
    roleScrubTech,
    rolePhysician,
  ];

  /// Map a role to its corresponding silo
  static String mapRoleToSilo(String role) {
    switch (role) {
      // Anesthesia roles -> jclAnes
      case roleCRNA:
      case roleCAA:
      case roleAnesthesiologist:
        return 'jclAnes';

      // Nursing roles -> jclNurse
      case roleRN:
      case roleLPN:
      case roleNurse:
        return 'jclNurse';

      // Future roles (not active but supported)
      case roleScrubTech:
        return 'jclTech';
      case rolePhysician:
        return 'jclDoc';

      // Default fallback to Anesthesia
      default:
        return 'jclAnes';
    }
  }

  /// Get display name for a role
  static String getRoleDisplayName(String role) {
    return role; // Roles are already in display format
  }
}

/// Silo configuration
class SiloConfig {
  // All defined silos
  static const String siloAnes = 'jclAnes';
  static const String siloNurse = 'jclNurse';
  static const String siloJobs = 'jclJobs';
  static const String siloAll = 'jclAll'; // Admin access
  static const String siloTech = 'jclTech'; // Future
  static const String siloDoc = 'jclDoc'; // Future

  // Enabled silos (silos that users can access)
  static const List<String> enabledSilos = [
    siloAnes,
    siloNurse,
  ];

  // Future silos (defined but not yet enabled)
  static const List<String> futureSilos = [
    siloTech,
    siloDoc,
  ];

  // All known silos
  static const List<String> allSilos = [
    siloAnes,
    siloNurse,
    siloJobs,
    siloAll,
    siloTech,
    siloDoc,
  ];

  /// Check if a silo is enabled
  static bool isSiloEnabled(String silo) {
    return enabledSilos.contains(silo) || silo == siloAll;
  }

  /// Get display name for a silo
  static String getSiloDisplayName(String silo) {
    switch (silo) {
      case siloAnes:
        return 'Anesthesia';
      case siloNurse:
        return 'Nurse';
      case siloJobs:
        return 'Jobs';
      case siloTech:
        return 'Scrub Tech';
      case siloDoc:
        return 'Physician';
      case siloAll:
        return 'All';
      default:
        return 'Unknown';
    }
  }

  /// Get silos available for admin selection (only enabled silos)
  static List<String> getAdminSelectableSilos() {
    return enabledSilos;
  }
}
