import 'package:flutter/material.dart';

/// App Colors - Matching iOS Jericho Case Logs color scheme
class AppColors {
  // Primary Brand Colors (from iOS defs.h)
  static const Color jclOrange = Color(0xFFEE6C4D);      // RGB(0.933, 0.424, 0.302)
  static const Color jclGray = Color(0xFF2B3241);        // RGB(0.167, 0.196, 0.255)
  static const Color jclWhite = Color(0xFFE0FBFC);       // RGB(0.878, 0.984, 0.988)
  static const Color jclTaupe = Color(0xFF483C32);       // RGB(0.282, 0.235, 0.196)
  static const Color jclOrangeLite = Color(0x80EE6C4D);  // jclOrange with 50% alpha
  static const Color jclGrayLite = Color(0x802B3241);    // jclGray with 50% alpha

  // Primary Colors (using jclOrange as main brand color)
  static const Color primary = jclOrange;
  static const Color primaryDark = Color(0xFFCC5A3D);    // Darker orange
  static const Color primaryLight = jclOrangeLite;

  // Accent Colors (using jclGray)
  static const Color accent = jclGray;
  static const Color accentDark = Color(0xFF1A1F2A);
  static const Color accentLight = jclGrayLite;

  // Status Colors
  static const Color success = Color(0xFF4CAF50);
  static const Color warning = Color(0xFFFFC107);
  static const Color error = Color(0xFFF44336);
  static const Color info = jclGray;

  // Neutral Colors
  static const Color textPrimary = jclGray;
  static const Color textSecondary = Color(0xFF757575);
  static const Color divider = Color(0xFFBDBDBD);
  static const Color background = jclWhite;

  // Card & Surface Colors
  static const Color cardBackground = Colors.white;
  static const Color surfaceColor = jclWhite;
}
