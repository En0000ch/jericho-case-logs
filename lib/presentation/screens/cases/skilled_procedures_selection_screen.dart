import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/skilled_procedures_data.dart';

/// Skilled Procedures Selection Screen
/// Allows users to select multiple skilled procedures from default list and their saved skills
class SkilledProceduresSelectionScreen extends StatefulWidget {
  final List<String> initiallySelectedProcedures;

  const SkilledProceduresSelectionScreen({
    super.key,
    this.initiallySelectedProcedures = const [],
  });

  @override
  State<SkilledProceduresSelectionScreen> createState() =>
      _SkilledProceduresSelectionScreenState();
}

class _SkilledProceduresSelectionScreenState
    extends State<SkilledProceduresSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();

  List<String> _allProcedures = [];
  List<String> _filteredProcedures = [];
  Set<String> _selectedProcedures = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _selectedProcedures = Set.from(widget.initiallySelectedProcedures);
    _searchController.addListener(_filterProcedures);
    _loadProcedures();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadProcedures() async {
    setState(() => _isLoading = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final userSkills = prefs.getStringList('jclSkillsArray') ?? [];

      // Combine default procedures with user's custom skills
      _allProcedures = SkilledProceduresData.getAllProcedures(userSkills);
      _filteredProcedures = List.from(_allProcedures);
    } catch (e) {
      print('Error loading procedures: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterProcedures() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredProcedures = List.from(_allProcedures);
      } else {
        _filteredProcedures = _allProcedures
            .where((procedure) => procedure.toLowerCase().contains(query))
            .toList();
      }
    });
  }

  void _toggleProcedure(String procedure) {
    setState(() {
      if (_selectedProcedures.contains(procedure)) {
        _selectedProcedures.remove(procedure);
      } else {
        _selectedProcedures.add(procedure);
      }
    });
  }

  void _removeSelectedProcedure(String procedure) {
    setState(() {
      _selectedProcedures.remove(procedure);
    });
  }

  void _confirmSelection() {
    Navigator.of(context).pop(_selectedProcedures.toList());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Skilled Procedures',
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
        leading: const BackButton(),
        actions: [
          IconButton(
            icon: const Icon(Icons.check, color: AppColors.jclWhite),
            onPressed: _confirmSelection,
            tooltip: 'Confirm Selection',
          ),
        ],
      ),
      body: Column(
        children: [
          // Search bar
          Container(
            padding: const EdgeInsets.all(16),
            color: AppColors.jclGray.withOpacity(0.05),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search skilled procedures...',
                prefixIcon: const Icon(Icons.search, color: AppColors.jclGray),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, color: AppColors.jclGray),
                        onPressed: () {
                          _searchController.clear();
                        },
                      )
                    : null,
                filled: true,
                fillColor: AppColors.jclWhite,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
              ),
            ),
          ),

          // Procedures collection view (grid)
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: AppColors.jclOrange,
                    ),
                  )
                : _filteredProcedures.isEmpty
                    ? Center(
                        child: Text(
                          'No procedures match your search',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: AppColors.jclGray.withOpacity(0.6),
                            fontSize: 16,
                          ),
                        ),
                      )
                    : GridView.builder(
                        padding: const EdgeInsets.all(16),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 2.5,
                        ),
                        itemCount: _filteredProcedures.length,
                        itemBuilder: (context, index) {
                          final procedure = _filteredProcedures[index];
                          final isSelected = _selectedProcedures.contains(procedure);

                          return InkWell(
                            onTap: () => _toggleProcedure(procedure),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? AppColors.jclOrange
                                    : AppColors.jclGray.withOpacity(0.05),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: isSelected
                                      ? AppColors.jclOrange
                                      : AppColors.jclGray.withOpacity(0.2),
                                  width: isSelected ? 2 : 1,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    isSelected
                                        ? Icons.check_circle
                                        : Icons.circle_outlined,
                                    size: 20,
                                    color: isSelected
                                        ? AppColors.jclWhite
                                        : AppColors.jclGray.withOpacity(0.5),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      procedure,
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: isSelected
                                            ? FontWeight.w600
                                            : FontWeight.w500,
                                        color: isSelected
                                            ? AppColors.jclWhite
                                            : AppColors.jclGray,
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),

          // Selected procedures display frame
          if (_selectedProcedures.isNotEmpty)
            Container(
              constraints: const BoxConstraints(maxHeight: 200),
              decoration: BoxDecoration(
                color: AppColors.jclGray.withOpacity(0.05),
                border: Border(
                  top: BorderSide(
                    color: AppColors.jclGray.withOpacity(0.2),
                    width: 1,
                  ),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Selected Procedures (${_selectedProcedures.length})',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _selectedProcedures.clear();
                            });
                          },
                          child: const Text(
                            'Clear All',
                            style: TextStyle(
                              color: AppColors.jclOrange,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                      child: Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _selectedProcedures.map((procedure) {
                          return Chip(
                            label: Text(
                              procedure,
                              style: const TextStyle(
                                fontSize: 12,
                                color: AppColors.jclWhite,
                              ),
                            ),
                            backgroundColor: AppColors.jclOrange,
                            deleteIcon: const Icon(
                              Icons.close,
                              size: 16,
                              color: AppColors.jclWhite,
                            ),
                            onDeleted: () => _removeSelectedProcedure(procedure),
                          );
                        }).toList(),
                      ),
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
