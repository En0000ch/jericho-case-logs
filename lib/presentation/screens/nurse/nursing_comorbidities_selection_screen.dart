import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/nursing_comorbidities_data.dart';
import '../../../data/datasources/remote/parse_nursing_comorbidities_service.dart';
import '../../providers/auth_provider.dart';

/// Nursing Clinical Scenarios Selection Screen
/// Allows nurses to select multiple clinical scenarios from a collection view with search
class NursingComorbiditiesSelectionScreen extends ConsumerStatefulWidget {
  final List<String> initiallySelectedComorbidities;

  const NursingComorbiditiesSelectionScreen({
    super.key,
    this.initiallySelectedComorbidities = const [],
  });

  @override
  ConsumerState<NursingComorbiditiesSelectionScreen> createState() =>
      _NursingComorbiditiesSelectionScreenState();
}

class _NursingComorbiditiesSelectionScreenState
    extends ConsumerState<NursingComorbiditiesSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ParseNursingComorbiditiesService _comorbiditiesService = ParseNursingComorbiditiesService();

  List<String> _allComorbidities = [];
  List<String> _filteredComorbidities = [];
  Set<String> _selectedComorbidities = {};
  List<String> _customComorbidities = [];
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
      final user = ref.read(currentUserProvider);
      if (user != null) {
        // Fetch custom comorbidities from Parse
        _customComorbidities = await _comorbiditiesService.fetchCustomComorbidities(user.email);

        // Combine default and custom comorbidities, sort alphabetically
        _allComorbidities = NursingComorbidities.getAllComorbidities(_customComorbidities);
        _filteredComorbidities = List.from(_allComorbidities);
      }
    } catch (e) {
      print('Error loading comorbidities: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterComorbidities() {
    final query = _searchController.text;
    setState(() {
      _filteredComorbidities = NursingComorbidities.filterComorbidities(_allComorbidities, query);
    });
  }

  void _toggleComorbidity(String comorbidity) {
    setState(() {
      if (comorbidity == 'None') {
        // If selecting "None", clear all other selections
        if (_selectedComorbidities.contains('None')) {
          _selectedComorbidities.remove('None');
        } else {
          _selectedComorbidities.clear();
          _selectedComorbidities.add('None');
        }
      } else {
        // If selecting any other comorbidity, remove "None" first
        _selectedComorbidities.remove('None');

        // Then toggle the selected comorbidity
        if (_selectedComorbidities.contains(comorbidity)) {
          _selectedComorbidities.remove(comorbidity);
        } else {
          _selectedComorbidities.add(comorbidity);
        }
      }
    });
  }

  void _showAddCustomComorbidityDialog() {
    final textController = TextEditingController();

    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Add Custom Clinical Scenario',
            style: TextStyle(
              color: AppColors.jclGray,
              fontWeight: FontWeight.w600,
            ),
          ),
          content: TextField(
            controller: textController,
            autofocus: true,
            textCapitalization: TextCapitalization.words,
            decoration: const InputDecoration(
              hintText: 'Enter clinical scenario name',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: const Text(
                'Cancel',
                style: TextStyle(color: AppColors.jclGray),
              ),
            ),
            TextButton(
              onPressed: () async {
                final comorbidityName = textController.text.trim();
                if (comorbidityName.isNotEmpty) {
                  Navigator.of(dialogContext).pop();
                  await _addCustomComorbidity(comorbidityName);
                }
              },
              child: const Text(
                'Add',
                style: TextStyle(
                  color: AppColors.jclOrange,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Future<void> _addCustomComorbidity(String comorbidityName) async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    // Check if comorbidity already exists in all comorbidities
    if (_allComorbidities.any((c) => c.toLowerCase() == comorbidityName.toLowerCase())) {
      _showMessage('Clinical scenario already exists');
      return;
    }

    final success = await _comorbiditiesService.addCustomComorbidity(user.email, comorbidityName);

    if (success) {
      _showMessage('Custom clinical scenario added');
      await _loadComorbidities();
      // Auto-select the newly added comorbidity
      setState(() {
        _selectedComorbidities.add(comorbidityName);
      });
    } else {
      _showMessage('Failed to add custom clinical scenario');
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.jclOrange,
        duration: const Duration(seconds: 2),
      ),
    );
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
          'Select Clinical Scenarios',
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
              style: const TextStyle(
                color: AppColors.jclGray,
                fontSize: 16,
              ),
              decoration: InputDecoration(
                hintText: 'Search clinical scenarios...',
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

          // Add custom comorbidity button
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _showAddCustomComorbidityDialog,
                icon: const Icon(Icons.add, color: AppColors.jclOrange),
                label: const Text(
                  'Add Custom Clinical Scenario',
                  style: TextStyle(color: AppColors.jclOrange),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: AppColors.jclOrange),
                  padding: const EdgeInsets.symmetric(vertical: 12),
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
                              ? 'No clinical scenarios available'
                              : 'No clinical scenarios match your search',
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
                          final isCustom = _customComorbidities.contains(comorbidity);

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
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
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
                                        if (isCustom)
                                          Text(
                                            'Custom',
                                            style: TextStyle(
                                              fontSize: 9,
                                              color: isSelected
                                                  ? AppColors.jclWhite.withOpacity(0.8)
                                                  : AppColors.jclOrange,
                                              fontStyle: FontStyle.italic,
                                            ),
                                          ),
                                      ],
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
                          'Selected Clinical Scenarios (${_selectedComorbidities.length})',
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
