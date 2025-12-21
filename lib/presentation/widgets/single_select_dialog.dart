import 'package:flutter/material.dart';
import '../../core/themes/app_colors.dart';

/// Single-select dialog replicating iOS SCLAlertView behavior
/// Displays a list of items with single selection capability
class SingleSelectDialog extends StatefulWidget {
  final String title;
  final String iconPath;
  final List<String> items;
  final Function(String?) onSelect;
  final VoidCallback onBack;

  const SingleSelectDialog({
    super.key,
    required this.title,
    required this.iconPath,
    required this.items,
    required this.onSelect,
    required this.onBack,
  });

  @override
  State<SingleSelectDialog> createState() => _SingleSelectDialogState();
}

class _SingleSelectDialogState extends State<SingleSelectDialog> {
  String? _selectedItem;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      backgroundColor: Colors.white,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 400, maxHeight: 500),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header with icon and title
            Container(
              padding: const EdgeInsets.symmetric(vertical: 20),
              decoration: BoxDecoration(
                color: AppColors.jclTaupe,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              child: Column(
                children: [
                  Image.asset(
                    widget.iconPath,
                    width: 40,
                    height: 40,
                    color: Colors.white,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    widget.title,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),

            // List container with jclWhiteBg background
            Container(
              width: 400,
              height: 200,
              color: AppColors.jclWhiteBg,
              padding: const EdgeInsets.all(8),
              child: widget.items.isEmpty
                  ? const Center(
                      child: Text(
                        'No items available',
                        style: TextStyle(color: Colors.grey),
                      ),
                    )
                  : ListView.builder(
                      itemCount: widget.items.length,
                      itemBuilder: (context, index) {
                        final item = widget.items[index];
                        final isSelected = _selectedItem == item;

                        return ListTile(
                          dense: true,
                          selected: isSelected,
                          selectedTileColor: AppColors.jclTaupe.withOpacity(0.2),
                          title: Text(
                            item,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                              color: isSelected ? AppColors.jclTaupe : Colors.black87,
                            ),
                          ),
                          onTap: () {
                            setState(() {
                              _selectedItem = item;
                            });
                          },
                        );
                      },
                    ),
            ),

            // Button row
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  // Back button
                  Expanded(
                    child: OutlinedButton(
                      onPressed: widget.onBack,
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        side: BorderSide(color: AppColors.jclTaupe),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(25),
                        ),
                      ),
                      child: Text(
                        'Back',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: AppColors.jclTaupe,
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(width: 12),

                  // Select button (green)
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => widget.onSelect(_selectedItem),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.jclGreen,
                        foregroundColor: AppColors.jclBrownText,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(25),
                        ),
                        elevation: 2,
                      ),
                      child: const Text(
                        'Select',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
