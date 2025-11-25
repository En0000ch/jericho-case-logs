import 'package:flutter/material.dart';
import '../../../core/themes/app_colors.dart';
import '../../../domain/entities/surgery_specialty.dart';
import 'surgeries_list_screen.dart';

/// Surgeries Selection Screen
/// Flutter equivalent of iOS surgeriesViewController
/// Displays a grid of surgery specialty categories
class SurgeriesSelectionScreen extends StatelessWidget {
  const SurgeriesSelectionScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Surgery Specialty',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclOrange,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.jclWhite),
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 24.0),
        child: GridView.builder(
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 1.0,
            crossAxisSpacing: 20,
            mainAxisSpacing: 30,
          ),
          itemCount: SurgerySpecialties.all.length,
          itemBuilder: (context, index) {
            final specialty = SurgerySpecialties.all[index];
            return _SurgerySpecialtyCard(
              specialty: specialty,
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => SurgeriesListScreen(
                      specialty: specialty,
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}

/// Individual surgery specialty card widget
class _SurgerySpecialtyCard extends StatelessWidget {
  final SurgerySpecialty specialty;
  final VoidCallback onTap;

  const _SurgerySpecialtyCard({
    required this.specialty,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Surgery icon/image
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: AppColors.jclOrange.withOpacity(0.1),
                borderRadius: BorderRadius.circular(30),
              ),
              child: Icon(
                _getIconForSpecialty(specialty.title),
                size: 32,
                color: AppColors.jclOrange,
              ),
            ),
            const SizedBox(height: 12),
            // Surgery specialty title
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Text(
                specialty.title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: AppColors.jclGray,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  height: 1.2,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Get appropriate icon for each specialty
  IconData _getIconForSpecialty(String title) {
    switch (title) {
      case 'Cardiovascular':
        return Icons.favorite;
      case 'Dental':
        return Icons.medical_services;
      case 'General':
        return Icons.healing;
      case 'Neurosurgery':
        return Icons.psychology;
      case 'Obstetric/Gynecologic':
        return Icons.pregnant_woman;
      case 'Ophthalmic':
        return Icons.visibility;
      case 'Orthopedic':
        return Icons.accessibility_new;
      case 'Otolaryngology Head/Neck':
        return Icons.hearing;
      case 'Out-of-Operating Room Procedures':
        return Icons.local_hospital;
      case 'Pediatric':
        return Icons.child_care;
      case 'Plastics & Reconstructive':
        return Icons.face;
      case 'Thoracic':
        return Icons.air;
      case 'Urology':
        return Icons.medical_information;
      default:
        return Icons.medical_services;
    }
  }
}
