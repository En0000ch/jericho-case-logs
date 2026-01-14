import 'package:flutter/material.dart';
import '../../core/themes/app_colors.dart';

/// Button with orange outline and glow effect when pressed
class OrangeOutlineButton extends StatefulWidget {
  final VoidCallback? onPressed;
  final Widget icon;
  final Widget label;
  final Color? backgroundColor;

  const OrangeOutlineButton({
    super.key,
    required this.onPressed,
    required this.icon,
    required this.label,
    this.backgroundColor,
  });

  @override
  State<OrangeOutlineButton> createState() => _OrangeOutlineButtonState();
}

class _OrangeOutlineButtonState extends State<OrangeOutlineButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _glowController;
  late Animation<double> _glowAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _glowController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );

    _glowAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _glowController,
        curve: Curves.easeOut,
      ),
    );
  }

  @override
  void dispose() {
    _glowController.dispose();
    super.dispose();
  }

  void _handleTap() {
    if (widget.onPressed != null) {
      setState(() => _isPressed = true);
      _glowController.forward(from: 0.0).then((_) {
        setState(() => _isPressed = false);
      });
      widget.onPressed!();
    }
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _glowAnimation,
      builder: (context, child) {
        return Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(8),
            boxShadow: _glowAnimation.value > 0
                ? [
                    BoxShadow(
                      color: AppColors.jclOrange
                          .withOpacity(0.6 * (1 - _glowAnimation.value)),
                      blurRadius: 20 * _glowAnimation.value,
                      spreadRadius: 5 * _glowAnimation.value,
                    ),
                  ]
                : null,
          ),
          child: ElevatedButton.icon(
            onPressed: _handleTap,
            icon: widget.icon,
            label: widget.label,
            style: ElevatedButton.styleFrom(
              backgroundColor:
                  widget.backgroundColor ?? AppColors.jclOrange,
              foregroundColor: AppColors.jclWhite,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
                side: const BorderSide(
                  color: AppColors.jclOrange,
                  width: 2,
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
