import 'package:flutter/material.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/comorbidities_data.dart';

/// Anesthesia Comorbidities Selection Screen
/// Allows users to select multiple comorbidities from a collection view with search
class AnesthesiaComorbiditiesSelectionScreen extends StatefulWidget {
  final List<String> initiallySelectedComorbidities;

  const AnesthesiaComorbiditiesSelectionScreen({
    super.key,
    this.initiallySelectedComorbidities = const [],
  });

  @override
  State<AnesthesiaComorbiditiesSelectionScreen> createState() =>
      _AnesthesiaComorbiditiesSelectionScreenState();
}

class _AnesthesiaComorbiditiesSelectionScreenState
    extends State<AnesthesiaComorbiditiesSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();

  List<String> _allComorbidities = [];
  List<String> _filteredComorbidities = [];
  Set<String> _selectedComorbidities = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _selectedComorbidities = Set.from(widget.initiallySelectedComorbidities);
    _searchController.addListener(_filterComorbidities);
    _loadComorbidities();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadComorbidities() async {
    setState(() => _isLoading = true);

    try {
      _allComorbidities = ComorbiditiesData.getDefaultComorbidities();
      _filteredComorbidities = List.from(_allComorbidities);
    } catch (e) {
      print('Error loading comorbidities: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterComorbidities() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredComorbidities = List.from(_allComorbidities);
      } else {
        _filteredComorbidities = _allComorbidities
            .where((comorbidity) => comorbidity.toLowerCase().contains(query))
            .toList();
      }
    });
  }

  void _toggleComorbidity(String comorbidity) {
    setState(() {
      if (_selectedComorbidities.contains(comorbidity)) {
        _selectedComorbidities.remove(comorbidity);
      } else {
        _selectedComorbidities.add(comorbidity);
      }
    });
  }

  void _removeSelectedComorbidity(String comorbidity) {
    setState(() {
      _selectedComorbidities.remove(comorbidity);
    });
  }

  void _confirmSelection() {
    Navigator.of(context).pop(_selectedComorbidities.toList());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Comorbidities',
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
                hintText: 'Search comorbidities...',
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

          // Comorbidities collection view (grid)
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: AppColors.jclOrange,
                    ),
                  )
                : _filteredComorbidities.isEmpty
                    ? Center(
                        child: Text(
                          _searchController.text.isEmpty
                              ? 'No comorbidities available'
                              : 'No comorbidities match your search',
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
                        itemCount: _filteredComorbidities.length,
                        itemBuilder: (context, index) {
                          final comorbidity = _filteredComorbidities[index];
                          final isSelected = _selectedComorbidities.contains(comorbidity);

                          return InkWell(
                            onTap: () => _toggleComorbidity(comorbidity),
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
                                      comorbidity,
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

          // Selected comorbidities display frame
          if (_selectedComorbidities.isNotEmpty)
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
                          'Selected Comorbidities (${_selectedComorbidities.length})',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _selectedComorbidities.clear();
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
                        children: _selectedComorbidities.map((comorbidity) {
                          return Chip(
                            label: Text(
                              comorbidity,
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
                            onDeleted: () => _removeSelectedComorbidity(comorbidity),
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
