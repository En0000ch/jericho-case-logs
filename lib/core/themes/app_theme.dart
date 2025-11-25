import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'app_colors.dart';

class AppTheme {
  // iOS-style theme matching the original app design
  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    scaffoldBackgroundColor: AppColors.jclGray,
    colorScheme: const ColorScheme.light(
      primary: AppColors.jclOrange,
      secondary: AppColors.jclGray,
      surface: AppColors.jclGray,
      surfaceContainerHighest: AppColors.jclWhite,
      onPrimary: AppColors.jclWhite,
      onSecondary: AppColors.jclOrange,
      onSurface: AppColors.jclWhite,
    ),

    // AppBar theme - iOS style with jclGray background
    appBarTheme: const AppBarTheme(
      centerTitle: false,
      elevation: 0,
      backgroundColor: AppColors.jclGray,
      foregroundColor: AppColors.jclOrange,
      systemOverlayStyle: SystemUiOverlayStyle.light,
      iconTheme: IconThemeData(color: AppColors.jclOrange),
      titleTextStyle: TextStyle(
        color: AppColors.jclOrange,
        fontSize: 20,
        fontWeight: FontWeight.w600,
      ),
    ),

    // Bottom Navigation Bar theme
    bottomNavigationBarTheme: const BottomNavigationBarThemeData(
      backgroundColor: AppColors.jclGray,
      selectedItemColor: AppColors.jclOrange,
      unselectedItemColor: AppColors.jclWhite,
      type: BottomNavigationBarType.fixed,
      elevation: 0,
    ),

    // Text theme - iOS style
    textTheme: const TextTheme(
      // Headers
      headlineLarge: TextStyle(
        fontSize: 32,
        fontWeight: FontWeight.bold,
        color: AppColors.jclWhite,
      ),
      headlineMedium: TextStyle(
        fontSize: 24,
        fontWeight: FontWeight.w600,
        color: AppColors.jclWhite,
      ),
      headlineSmall: TextStyle(
        fontSize: 20,
        fontWeight: FontWeight.w600,
        color: AppColors.jclWhite,
      ),
      // Body text
      bodyLarge: TextStyle(
        fontSize: 16,
        color: AppColors.jclWhite,
      ),
      bodyMedium: TextStyle(
        fontSize: 14,
        color: AppColors.jclWhite,
      ),
      bodySmall: TextStyle(
        fontSize: 12,
        color: AppColors.jclWhite,
      ),
      // Labels
      labelLarge: TextStyle(
        fontSize: 14,
        fontWeight: FontWeight.w500,
        color: AppColors.jclWhite,
      ),
      labelMedium: TextStyle(
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: AppColors.jclWhite,
      ),
      labelSmall: TextStyle(
        fontSize: 10,
        fontWeight: FontWeight.w500,
        color: AppColors.jclWhite,
      ),
    ),

    // Button themes - iOS style
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.transparent,
        foregroundColor: AppColors.jclOrange,
        elevation: 0,
        side: const BorderSide(color: AppColors.jclOrange, width: 1.5),
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        textStyle: const TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.w500,
        ),
      ),
    ),

    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.jclOrange,
        textStyle: const TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.w500,
        ),
      ),
    ),

    // Input decoration theme - iOS style
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.jclWhite,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide.none,
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.jclOrange, width: 1),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.error, width: 1),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppColors.error, width: 2),
      ),
      hintStyle: const TextStyle(
        color: AppColors.textSecondary,
        fontSize: 14,
      ),
      labelStyle: const TextStyle(
        color: AppColors.textSecondary,
        fontSize: 14,
      ),
    ),

    // Card theme - iOS style with dark background
    cardTheme: const CardThemeData(
      color: AppColors.jclGray,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      margin: EdgeInsets.symmetric(vertical: 4, horizontal: 0),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.all(Radius.circular(8)),
        side: BorderSide(color: AppColors.jclWhite, width: 0.5),
      ),
    ),

    // List tile theme - iOS style
    listTileTheme: const ListTileThemeData(
      tileColor: AppColors.jclGray,
      textColor: AppColors.jclWhite,
      iconColor: AppColors.jclOrange,
      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    ),

    // Divider theme - iOS style
    dividerTheme: const DividerThemeData(
      color: AppColors.jclWhite,
      thickness: 0.5,
      space: 1,
    ),

    // Switch theme - iOS style
    switchTheme: SwitchThemeData(
      thumbColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.jclWhite;
        }
        return Colors.grey[400];
      }),
      trackColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.jclOrange;
        }
        return Colors.grey[600];
      }),
    ),

    // Dialog theme - iOS style
    dialogTheme: DialogThemeData(
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      titleTextStyle: const TextStyle(
        color: AppColors.jclGray,
        fontSize: 18,
        fontWeight: FontWeight.w600,
      ),
      contentTextStyle: const TextStyle(
        color: AppColors.jclGray,
        fontSize: 14,
      ),
    ),

    // Floating Action Button theme
    floatingActionButtonTheme: const FloatingActionButtonThemeData(
      backgroundColor: AppColors.jclOrange,
      foregroundColor: AppColors.jclWhite,
    ),
  );

  // Dark theme can be the same or slightly adjusted
  static ThemeData darkTheme = lightTheme; // iOS app uses same dark theme
}
