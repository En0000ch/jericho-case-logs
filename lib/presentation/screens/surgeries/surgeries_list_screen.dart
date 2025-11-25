import 'package:flutter/material.dart';
import '../../../core/themes/app_colors.dart';
import '../../../domain/entities/surgery_specialty.dart';

/// Surgeries List Screen
/// Displays a list of surgeries within a selected specialty
/// Flutter equivalent of iOS surgeriesTableViewController
class SurgeriesListScreen extends StatefulWidget {
  final SurgerySpecialty specialty;

  const SurgeriesListScreen({
    super.key,
    required this.specialty,
  });

  @override
  State<SurgeriesListScreen> createState() => _SurgeriesListScreenState();
}

class _SurgeriesListScreenState extends State<SurgeriesListScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<String> _filteredSurgeries = [];
  List<String> _allSurgeries = [];

  @override
  void initState() {
    super.initState();
    _allSurgeries = widget.specialty.surgeries;
    _filteredSurgeries = _allSurgeries;
    _searchController.addListener(_filterSurgeries);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filterSurgeries() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredSurgeries = _allSurgeries;
      } else {
        _filteredSurgeries = _allSurgeries
            .where((surgery) => surgery.toLowerCase().contains(query))
            .toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: Text(
          widget.specialty.title,
          style: const TextStyle(
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
      body: Column(
        children: [
          // Search bar
          Container(
            padding: const EdgeInsets.all(16),
            color: AppColors.jclOrange,
            child: TextField(
              controller: _searchController,
              style: const TextStyle(color: AppColors.jclWhite),
              decoration: InputDecoration(
                hintText: 'Search surgeries...',
                hintStyle: TextStyle(color: AppColors.jclWhite.withOpacity(0.7)),
                prefixIcon: const Icon(Icons.search, color: AppColors.jclWhite),
                filled: true,
                fillColor: AppColors.jclGray.withOpacity(0.3),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),
          // Surgeries list
          Expanded(
            child: _filteredSurgeries.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.search_off,
                          size: 64,
                          color: AppColors.jclGray.withOpacity(0.3),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _searchController.text.isEmpty
                              ? 'No surgeries available'
                              : 'No surgeries match your search',
                          style: TextStyle(
                            color: AppColors.jclGray.withOpacity(0.5),
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: _filteredSurgeries.length,
                    separatorBuilder: (context, index) => Divider(
                      height: 1,
                      color: AppColors.jclGray.withOpacity(0.1),
                    ),
                    itemBuilder: (context, index) {
                      final surgery = _filteredSurgeries[index];
                      return ListTile(
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 8,
                        ),
                        title: Text(
                          surgery,
                          style: const TextStyle(
                            color: AppColors.jclGray,
                            fontSize: 16,
                          ),
                        ),
                        trailing: const Icon(
                          Icons.arrow_forward_ios,
                          size: 16,
                          color: AppColors.jclOrange,
                        ),
                        onTap: () {
                          // Return selected surgery
                          Navigator.pop(context, {
                            'surgeryCategory': widget.specialty.title,
                            'surgery': surgery,
                            'imageName': widget.specialty.imageName,
                          });
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
