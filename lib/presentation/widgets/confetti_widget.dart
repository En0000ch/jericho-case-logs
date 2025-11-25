import 'dart:math';
import 'package:flutter/material.dart';

/// Confetti animation widget
/// Matches iOS letItSnow: method from logCaseVController
class ConfettiWidget extends StatefulWidget {
  final Widget child;
  final bool showConfetti;
  final VoidCallback? onAnimationComplete;

  const ConfettiWidget({
    super.key,
    required this.child,
    required this.showConfetti,
    this.onAnimationComplete,
  });

  @override
  State<ConfettiWidget> createState() => _ConfettiWidgetState();
}

class _ConfettiWidgetState extends State<ConfettiWidget>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  final List<ConfettiParticle> _particles = [];
  final Random _random = Random();

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(seconds: 5),
      vsync: this,
    );

    _controller.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        widget.onAnimationComplete?.call();
      }
    });

    if (widget.showConfetti) {
      _startConfetti();
    }
  }

  @override
  void didUpdateWidget(ConfettiWidget oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.showConfetti && !oldWidget.showConfetti) {
      _startConfetti();
    }
  }

  void _startConfetti() {
    _particles.clear();

    // Create confetti particles matching iOS implementation
    // iOS has 6 different colored confetti particles
    for (int i = 0; i < 50; i++) {
      _particles.add(ConfettiParticle(
        color: _getRandomColor(),
        x: _random.nextDouble(),
        y: -0.1,
        velocity: 60 + _random.nextDouble() * 400, // velocity range 60-460
        lifetime: 48 + _random.nextDouble() * 32, // lifetime range 48-80
        rotation: _random.nextDouble() * pi * 2,
        rotationSpeed: (_random.nextDouble() - 0.5) * 4,
      ));
    }

    _controller.reset();
    _controller.forward();
  }

  Color _getRandomColor() {
    final colors = [
      Colors.red,
      Colors.green,
      const Color(0xFFFF00FF), // magenta
      Colors.pink,
      const Color(0xFF9C27B0), // purple
      Colors.orange,
    ];
    return colors[_random.nextInt(colors.length)];
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        widget.child,
        if (widget.showConfetti)
          AnimatedBuilder(
            animation: _controller,
            builder: (context, child) {
              return CustomPaint(
                painter: ConfettiPainter(
                  particles: _particles,
                  progress: _controller.value,
                ),
                size: Size.infinite,
              );
            },
          ),
      ],
    );
  }
}

class ConfettiParticle {
  final Color color;
  final double x;
  final double y;
  final double velocity;
  final double lifetime;
  final double rotation;
  final double rotationSpeed;

  ConfettiParticle({
    required this.color,
    required this.x,
    required this.y,
    required this.velocity,
    required this.lifetime,
    required this.rotation,
    required this.rotationSpeed,
  });
}

class ConfettiPainter extends CustomPainter {
  final List<ConfettiParticle> particles;
  final double progress;

  ConfettiPainter({
    required this.particles,
    required this.progress,
  });

  @override
  void paint(Canvas canvas, Size size) {
    for (var particle in particles) {
      final yPosition = particle.y * size.height + particle.velocity * progress * 5;

      // Only draw if within screen bounds
      if (yPosition < size.height) {
        final xPosition = particle.x * size.width +
            sin(progress * pi * 2) * 20; // Add some horizontal movement

        final rotation = particle.rotation + particle.rotationSpeed * progress * pi * 2;

        canvas.save();
        canvas.translate(xPosition, yPosition);
        canvas.rotate(rotation);

        // Draw confetti as small rectangles
        final paint = Paint()
          ..color = particle.color
          ..style = PaintingStyle.fill;

        canvas.drawRect(
          const Rect.fromLTWH(-4, -8, 8, 16),
          paint,
        );

        canvas.restore();
      }
    }
  }

  @override
  bool shouldRepaint(ConfettiPainter oldDelegate) {
    return oldDelegate.progress != progress;
  }
}
