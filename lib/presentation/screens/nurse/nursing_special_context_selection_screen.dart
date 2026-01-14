import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/nursing_special_context_data.dart';
import '../../../data/datasources/remote/parse_nursing_special_context_service.dart';
import '../../providers/auth_provider.dart';

/// Nursing Special Context Selection Screen
/// Allows nurses to select multiple special contexts from a collection view with search
class NursingSpecialContextSelectionScreen extends ConsumerStatefulWidget {
  final List<String> initiallySelectedContexts;

  const NursingSpecialContextSelectionScreen({
    super.key,
    this.initiallySelectedContexts = const [],
  });

  @override
  ConsumerState<NursingSpecialContextSelectionScreen> createState() =>
      _NursingSpecialContextSelectionScreenState();
}

class _NursingSpecialContextSelectionScreenState
    extends ConsumerState<NursingSpecialContextSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ParseNursingSpecialContextService _contextService = ParseNursingSpecialContextService();

  List<String> _allContexts = [];
  List<String> _filteredContexts = [];
  Set<String> _selectedContexts = {};
  List<String> _customContexts = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _selectedContexts = Set.from(widget.initiallySelectedContexts);
    _searchController.addListener(_filterContexts);
    _loadContexts();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadContexts() async {
    setState(() => _isLoading = true);

    try {
      final user = ref.read(currentUserProvider);
      if (user != null) {
        // Fetch custom contexts from Parse
        _customContexts = await _contextService.fetchCustomContexts(user.email);

        // Combine default and custom contexts, sort alphabetically
        _allContexts = NursingSpecialContext.getAllContexts(_customContexts);
        _filteredContexts = List.from(_allContexts);
      }
    } catch (e) {
      print('Error loading special contexts: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterContexts() {
    final query = _searchController.text;
    setState(() {
      _filteredContexts = NursingSpecialContext.filterContexts(_allContexts, query);
    });
  }

  void _toggleContext(String context) {
    setState(() {
      if (_selectedContexts.contains(context)) {
        _selectedContexts.remove(context);
      } else {
        _selectedContexts.add(context);
      }
    });
  }

  void _removeSelectedContext(String context) {
    setState(() {
      _selectedContexts.remove(context);
    });
  }

  void _showAddCustomContextDialog() {
    final textController = TextEditingController();

    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Add Custom Special Context',
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
              hintText: 'Enter context name',
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
                final contextName = textController.text.trim();
                if (contextName.isNotEmpty) {
                  Navigator.of(dialogContext).pop();
                  await _addCustomContext(contextName);
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

  Future<void> _addCustomContext(String contextName) async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    // Check if context already exists in all contexts
    if (_allContexts.any((c) => c.toLowerCase() == contextName.toLowerCase())) {
      _showMessage('Special context already exists');
      return;
    }

    final success = await _contextService.addCustomContext(user.email, contextName);

    if (success) {
      _showMessage('Custom special context added');
      await _loadContexts();
      // Auto-select the newly added context
      setState(() {
        _selectedContexts.add(contextName);
      });
    } else {
      _showMessage('Failed to add custom special context');
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

  void _confirmSelection() {
    Navigator.of(context).pop(_selectedContexts.toList());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Special Context',
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
                hintText: 'Search special contexts...',
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

          // Add custom context button
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _showAddCustomContextDialog,
                icon: const Icon(Icons.add, color: AppColors.jclOrange),
                label: const Text(
                  'Add Custom Special Context',
                  style: TextStyle(color: AppColors.jclOrange),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: AppColors.jclOrange),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ),

          // Contexts collection view (grid)
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: AppColors.jclOrange,
                    ),
                  )
                : _filteredContexts.isEmpty
                    ? Center(
                        child: Text(
                          _searchController.text.isEmpty
                              ? 'No special contexts available'
                              : 'No contexts match your search',
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
                        itemCount: _filteredContexts.length,
                        itemBuilder: (context, index) {
                          final contextItem = _filteredContexts[index];
                          final isSelected = _selectedContexts.contains(contextItem);
                          final isCustom = _customContexts.contains(contextItem);

                          return InkWell(
                            onTap: () => _toggleContext(contextItem),
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
                                          contextItem,
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

          // Selected contexts display frame
          if (_selectedContexts.isNotEmpty)
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
                          'Selected Contexts (${_selectedContexts.length})',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _selectedContexts.clear();
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
                        children: _selectedContexts.map((contextItem) {
                          return Chip(
                            label: Text(
                              contextItem,
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
                            onDeleted: () => _removeSelectedContext(contextItem),
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
