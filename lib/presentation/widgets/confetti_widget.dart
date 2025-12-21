import 'dart:math';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

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
  final List<ui.Image> _snowflakeImages = [];
  bool _imagesLoaded = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(seconds: 18), // Doubled to allow slower fall to reach bottom
      vsync: this,
    );

    _controller.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        widget.onAnimationComplete?.call();
      }
    });

    _loadSnowflakeImages();

    if (widget.showConfetti) {
      _startConfetti();
    }
  }

  Future<void> _loadSnowflakeImages() async {
    final imageNames = [
      'assets/images/sn0wflake7.png',
      'assets/images/sn0wflake8.png',
      'assets/images/sn0wflake9.png',
      'assets/images/sn0wflake10.png',
      'assets/images/sn0wflake11.png',
    ];

    for (final imageName in imageNames) {
      final data = await rootBundle.load(imageName);
      final codec = await ui.instantiateImageCodec(data.buffer.asUint8List());
      final frameInfo = await codec.getNextFrame();
      _snowflakeImages.add(frameInfo.image);
    }

    setState(() {
      _imagesLoaded = true;
    });

    // Start confetti if it was requested before images were loaded
    if (widget.showConfetti && _particles.isEmpty) {
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
    if (!_imagesLoaded || _snowflakeImages.isEmpty) {
      return; // Wait for images to load
    }

    _particles.clear();

    // Create snowflake particles
    for (int i = 0; i < 50; i++) {
      _particles.add(ConfettiParticle(
        imageIndex: _random.nextInt(_snowflakeImages.length),
        x: _random.nextDouble(),
        y: -0.1,
        velocity: 30 + _random.nextDouble() * 200, // velocity range 30-230 (slower)
        lifetime: 48 + _random.nextDouble() * 32, // lifetime range 48-80
        rotation: _random.nextDouble() * pi * 2,
        rotationSpeed: (_random.nextDouble() - 0.5) * 4,
        scale: 0.8 + _random.nextDouble() * 0.4, // scale range 0.8-1.2
      ));
    }

    _controller.reset();
    _controller.forward();
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
        if (widget.showConfetti && _imagesLoaded)
          AnimatedBuilder(
            animation: _controller,
            builder: (context, child) {
              return CustomPaint(
                painter: ConfettiPainter(
                  particles: _particles,
                  progress: _controller.value,
                  snowflakeImages: _snowflakeImages,
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
  final int imageIndex;
  final double x;
  final double y;
  final double velocity;
  final double lifetime;
  final double rotation;
  final double rotationSpeed;
  final double scale;

  ConfettiParticle({
    required this.imageIndex,
    required this.x,
    required this.y,
    required this.velocity,
    required this.lifetime,
    required this.rotation,
    required this.rotationSpeed,
    required this.scale,
  });
}

class ConfettiPainter extends CustomPainter {
  final List<ConfettiParticle> particles;
  final double progress;
  final List<ui.Image> snowflakeImages;

  ConfettiPainter({
    required this.particles,
    required this.progress,
    required this.snowflakeImages,
  });

  @override
  void paint(Canvas canvas, Size size) {
    for (var particle in particles) {
      // Calculate position - ensure particles reach bottom of screen
      final yPosition = particle.y * size.height + particle.velocity * progress * (size.height / 40);

      // Draw particles until they're well past the bottom of screen
      if (yPosition < size.height + 50) {
        final xPosition = particle.x * size.width +
            sin(progress * pi * 2) * 20; // Add some horizontal movement

        final rotation = particle.rotation + particle.rotationSpeed * progress * pi * 2;

        canvas.save();
        canvas.translate(xPosition, yPosition);
        canvas.rotate(rotation);

        // Draw snowflake image
        final image = snowflakeImages[particle.imageIndex];
        final imageSize = 30.0 * particle.scale; // Base size of 30 pixels

        paintImage(
          canvas: canvas,
          rect: Rect.fromCenter(
            center: Offset.zero,
            width: imageSize,
            height: imageSize,
          ),
          image: image,
          fit: BoxFit.contain,
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
