import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/themes/app_colors.dart';

/// Custom button with animated glow effect on press
/// Matches the website button design
class GlowButton extends StatefulWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isPrimary;
  final bool isFullWidth;
  final IconData? icon;

  const GlowButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isPrimary = true,
    this.isFullWidth = true,
    this.icon,
  });

  @override
  State<GlowButton> createState() => _GlowButtonState();
}

class _GlowButtonState extends State<GlowButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _glowAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _glowAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: Curves.easeOut,
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    setState(() => _isPressed = true);
    _controller.forward(from: 0.0);
    // Trigger haptic feedback (vibration)
    HapticFeedback.mediumImpact();
  }

  void _handleTapUp(TapUpDetails details) {
    setState(() => _isPressed = false);
    widget.onPressed();
  }

  void _handleTapCancel() {
    setState(() => _isPressed = false);
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: _handleTapDown,
      onTapUp: _handleTapUp,
      onTapCancel: _handleTapCancel,
      child: AnimatedBuilder(
        animation: _glowAnimation,
        builder: (context, child) {
          return Container(
            width: widget.isFullWidth ? double.infinity : null,
            padding: widget.isFullWidth
                ? const EdgeInsets.symmetric(vertical: 15)
                : const EdgeInsets.symmetric(horizontal: 40, vertical: 15),
            decoration: BoxDecoration(
              color: widget.isPrimary
                  ? AppColors.jclOrange
                  : Colors.transparent,
              borderRadius: BorderRadius.circular(50),
              border: widget.isPrimary
                  ? null
                  : Border.all(color: AppColors.jclOrange, width: 2),
              boxShadow: [
                // Base shadow
                if (widget.isPrimary)
                  BoxShadow(
                    color: AppColors.jclOrange.withOpacity(0.3),
                    blurRadius: 10,
                    spreadRadius: 0,
                  ),
                // Animated glow layers
                if (_glowAnimation.value > 0)
                  BoxShadow(
                    color: AppColors.jclOrange.withOpacity(
                      0.5 * (1 - _glowAnimation.value),
                    ),
                    blurRadius: 20 * _glowAnimation.value,
                    spreadRadius: 10 * _glowAnimation.value,
                  ),
                if (_glowAnimation.value > 0)
                  BoxShadow(
                    color: AppColors.jclOrange.withOpacity(
                      0.3 * (1 - _glowAnimation.value),
                    ),
                    blurRadius: 40 * _glowAnimation.value,
                    spreadRadius: 20 * _glowAnimation.value,
                  ),
                if (_glowAnimation.value > 0)
                  BoxShadow(
                    color: AppColors.jclOrange.withOpacity(
                      0.1 * (1 - _glowAnimation.value),
                    ),
                    blurRadius: 60 * _glowAnimation.value,
                    spreadRadius: 30 * _glowAnimation.value,
                  ),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize:
                  widget.isFullWidth ? MainAxisSize.max : MainAxisSize.min,
              children: [
                if (widget.icon != null) ...[
                  Icon(
                    widget.icon,
                    color: widget.isPrimary
                        ? AppColors.jclWhite
                        : AppColors.jclOrange,
                    size: 20,
                  ),
                  const SizedBox(width: 10),
                ],
                Text(
                  widget.text,
                  style: TextStyle(
                    color: widget.isPrimary
                        ? AppColors.jclWhite
                        : AppColors.jclOrange,
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

/// Smaller version of GlowButton for inline actions
class GlowButtonSmall extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isPrimary;
  final IconData? icon;

  const GlowButtonSmall({
    super.key,
    required this.text,
    required this.onPressed,
    this.isPrimary = true,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return GlowButton(
      text: text,
      onPressed: onPressed,
      isPrimary: isPrimary,
      isFullWidth: false,
      icon: icon,
    );
  }
}
