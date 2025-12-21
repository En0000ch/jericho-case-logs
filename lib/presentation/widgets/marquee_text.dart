import 'package:flutter/material.dart';

/// A widget that automatically scrolls text horizontally when it overflows
/// Replicates the iOS CBAutoScrollLabel behavior
class MarqueeText extends StatefulWidget {
  final String text;
  final TextStyle? style;
  final double scrollSpeed;
  final double pauseInterval;
  final double labelSpacing;
  final int maxLines;

  const MarqueeText(
    this.text, {
    super.key,
    this.style,
    this.scrollSpeed = 30,
    this.pauseInterval = 1.5,
    this.labelSpacing = 30,
    this.maxLines = 1,
  });

  @override
  State<MarqueeText> createState() => _MarqueeTextState();
}

class _MarqueeTextState extends State<MarqueeText>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late ScrollController _scrollController;
  bool _needsScrolling = false;
  double _textWidth = 0;
  double _containerWidth = 0;

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    _controller = AnimationController(
      vsync: this,
      duration: Duration.zero,
    );

    // Check if scrolling is needed after first frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkIfScrollingNeeded();
    });
  }

  @override
  void didUpdateWidget(MarqueeText oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.text != widget.text || oldWidget.style != widget.style) {
      _checkIfScrollingNeeded();
    }
  }

  void _checkIfScrollingNeeded() {
    if (!mounted) return;

    // Calculate text width
    final textPainter = TextPainter(
      text: TextSpan(text: widget.text, style: widget.style),
      maxLines: widget.maxLines,
      textDirection: TextDirection.ltr,
    )..layout();

    _textWidth = textPainter.width;

    // Get container width
    final RenderBox? renderBox = context.findRenderObject() as RenderBox?;
    if (renderBox != null) {
      _containerWidth = renderBox.size.width;

      // Only scroll if text is wider than container
      if (_textWidth > _containerWidth) {
        setState(() {
          _needsScrolling = true;
        });
        _startScrolling();
      } else {
        setState(() {
          _needsScrolling = false;
        });
        _controller.stop();
      }
    }
  }

  void _startScrolling() async {
    if (!mounted || !_needsScrolling) return;

    // Calculate duration based on scroll speed (pixels per second)
    final scrollDistance = _textWidth + widget.labelSpacing;
    final duration = Duration(
      milliseconds: (scrollDistance / widget.scrollSpeed * 1000).round(),
    );

    // Wait for pause interval before starting
    await Future.delayed(Duration(
      milliseconds: (widget.pauseInterval * 1000).round(),
    ));

    if (!mounted) return;

    // Animate the scroll
    _controller.duration = duration;
    _controller.forward(from: 0).then((_) {
      if (!mounted) return;
      // Reset scroll position
      _scrollController.jumpTo(0);
      // Start again after pause
      _startScrolling();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_needsScrolling) {
      // Text fits, no scrolling needed
      return Text(
        widget.text,
        style: widget.style,
        maxLines: widget.maxLines,
        overflow: TextOverflow.ellipsis,
      );
    }

    // Text overflows, use scrolling animation
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        // Calculate scroll position based on animation progress
        final scrollDistance = _textWidth + widget.labelSpacing;
        final scrollOffset = scrollDistance * _controller.value;

        // Update scroll position
        if (_scrollController.hasClients && scrollOffset <= _scrollController.position.maxScrollExtent) {
          _scrollController.jumpTo(scrollOffset);
        }

        return SingleChildScrollView(
          controller: _scrollController,
          scrollDirection: Axis.horizontal,
          physics: const NeverScrollableScrollPhysics(),
          child: Row(
            children: [
              Text(
                widget.text,
                style: widget.style,
                maxLines: widget.maxLines,
              ),
              SizedBox(width: widget.labelSpacing),
              // Duplicate text for seamless loop
              Text(
                widget.text,
                style: widget.style,
                maxLines: widget.maxLines,
              ),
            ],
          ),
        );
      },
    );
  }
}
