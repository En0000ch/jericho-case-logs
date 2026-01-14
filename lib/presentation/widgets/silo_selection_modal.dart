import 'package:flutter/material.dart';
import '../../core/themes/app_colors.dart';
import '../../core/constants/user_roles.dart';

/// Modal for admin users to select which silo to enter
/// Displayed immediately after login for jclAll users
class SiloSelectionModal extends StatefulWidget {
  final Function(String selectedSilo) onSiloSelected;
  final VoidCallback? onCancel;

  const SiloSelectionModal({
    super.key,
    required this.onSiloSelected,
    this.onCancel,
  });

  @override
  State<SiloSelectionModal> createState() => _SiloSelectionModalState();
}

class _SiloSelectionModalState extends State<SiloSelectionModal> {
  String? _selectedSilo;

  @override
  Widget build(BuildContext context) {
    // Get enabled silos for selection
    final selectableSilos = SiloConfig.getAdminSelectableSilos();

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      backgroundColor: AppColors.jclWhite,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 400, minHeight: 300),
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Title
            const Text(
              'Select Section',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: AppColors.jclGray,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),

            // Subtitle
            Text(
              'Choose which section to access',
              style: TextStyle(
                fontSize: 14,
                color: AppColors.jclGray.withOpacity(0.7),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),

            // Silo options
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                itemCount: selectableSilos.length,
                separatorBuilder: (context, index) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final silo = selectableSilos[index];
                  final siloName = SiloConfig.getSiloDisplayName(silo);
                  final isSelected = _selectedSilo == silo;

                  return InkWell(
                    onTap: () {
                      setState(() {
                        _selectedSilo = silo;
                      });
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? AppColors.jclOrange.withOpacity(0.1)
                            : AppColors.jclGray.withOpacity(0.05),
                        border: Border.all(
                          color: isSelected
                              ? AppColors.jclOrange
                              : AppColors.jclGray.withOpacity(0.2),
                          width: 2,
                        ),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          // Selection indicator
                          Container(
                            width: 24,
                            height: 24,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: isSelected
                                  ? AppColors.jclOrange
                                  : Colors.transparent,
                              border: Border.all(
                                color: isSelected
                                    ? AppColors.jclOrange
                                    : AppColors.jclGray.withOpacity(0.3),
                                width: 2,
                              ),
                            ),
                            child: isSelected
                                ? const Icon(
                                    Icons.check,
                                    color: AppColors.jclWhite,
                                    size: 16,
                                  )
                                : null,
                          ),
                          const SizedBox(width: 16),

                          // Silo name
                          Expanded(
                            child: Text(
                              siloName,
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: isSelected
                                    ? FontWeight.w600
                                    : FontWeight.normal,
                                color: AppColors.jclGray,
                              ),
                            ),
                          ),

                          // Arrow icon
                          Icon(
                            Icons.arrow_forward_ios,
                            size: 16,
                            color: isSelected
                                ? AppColors.jclOrange
                                : AppColors.jclGray.withOpacity(0.3),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 24),

            // Action buttons
            Row(
              children: [
                // Cancel button (if callback provided)
                if (widget.onCancel != null)
                  Expanded(
                    child: OutlinedButton(
                      onPressed: widget.onCancel,
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        side: const BorderSide(color: AppColors.jclGray),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: const Text(
                        'Cancel',
                        style: TextStyle(
                          fontSize: 16,
                          color: AppColors.jclGray,
                        ),
                      ),
                    ),
                  ),
                if (widget.onCancel != null) const SizedBox(width: 12),

                // Continue button
                Expanded(
                  child: ElevatedButton(
                    onPressed: _selectedSilo != null
                        ? () => widget.onSiloSelected(_selectedSilo!)
                        : null,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      backgroundColor: AppColors.jclOrange,
                      foregroundColor: AppColors.jclWhite,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      disabledBackgroundColor: AppColors.jclGray.withOpacity(0.3),
                    ),
                    child: const Text(
                      'Continue',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
