/// GlowButton Usage Examples
///
/// This file demonstrates how to use the GlowButton widget throughout the app

import 'package:flutter/material.dart';
import 'glow_button.dart';

/// Example 1: Primary Full-Width Button (Default)
/// Use for main actions like "Save", "Submit", "Continue"
void primaryButtonExample() {
  // Default: Primary (filled orange) and full-width
  GlowButton(
    text: 'Save Case',
    onPressed: () {
      // Your action here
    },
  );
}

/// Example 2: Secondary Outlined Button
/// Use for secondary actions like "Cancel", "Skip"
void secondaryButtonExample() {
  GlowButton(
    text: 'Cancel',
    onPressed: () {
      // Your action here
    },
    isPrimary: false, // Makes it outlined instead of filled
  );
}

/// Example 3: Button with Icon
/// Add an icon for visual clarity
void buttonWithIconExample() {
  GlowButton(
    text: 'Sign In',
    icon: Icons.login,
    onPressed: () {
      // Your action here
    },
    isPrimary: false,
  );
}

/// Example 4: Compact Button (Not Full Width)
/// Use for inline actions
void compactButtonExample() {
  GlowButtonSmall(
    text: 'Edit',
    onPressed: () {
      // Your action here
    },
    icon: Icons.edit,
  );
}

/// Example 5: Multiple Buttons in Row
void buttonRowExample() {
  Row(
    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
    children: [
      Expanded(
        child: GlowButton(
          text: 'Cancel',
          onPressed: () {},
          isPrimary: false,
        ),
      ),
      const SizedBox(width: 16),
      Expanded(
        child: GlowButton(
          text: 'Confirm',
          onPressed: () {},
        ),
      ),
    ],
  );
}

/// Example 6: Loading State
/// Show loading indicator while processing
class LoadingButtonExample extends StatefulWidget {
  const LoadingButtonExample({super.key});

  @override
  State<LoadingButtonExample> createState() => _LoadingButtonExampleState();
}

class _LoadingButtonExampleState extends State<LoadingButtonExample> {
  bool _isLoading = false;

  Future<void> _handleSubmit() async {
    setState(() => _isLoading = true);

    // Simulate API call
    await Future.delayed(const Duration(seconds: 2));

    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return _isLoading
        ? const Center(child: CircularProgressIndicator())
        : GlowButton(
            text: 'Submit',
            onPressed: _handleSubmit,
          );
  }
}

/// Quick Reference:
///
/// Properties:
/// - text: String (required) - Button text
/// - onPressed: VoidCallback (required) - Action when pressed
/// - isPrimary: bool (default: true) - Filled vs Outlined style
/// - isFullWidth: bool (default: true) - Full width vs compact
/// - icon: IconData? (optional) - Add icon before text
///
/// Glow Effect:
/// - Automatically triggers when button is pressed
/// - 3-layer expanding glow with orange color (#EE6C4D)
/// - 600ms animation duration
/// - Matches the website button design
