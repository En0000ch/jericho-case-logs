import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/regional_anesthetic_data.dart';
import '../../providers/auth_provider.dart';

/// Regional Anesthetic multi-selection screen
/// Matches iOS app's regTableViewController
class RegionalAnestheticSelectionScreen extends ConsumerStatefulWidget {
  const RegionalAnestheticSelectionScreen({super.key});

  @override
  ConsumerState<RegionalAnestheticSelectionScreen> createState() =>
      _RegionalAnestheticSelectionScreenState();
}

class _RegionalAnestheticSelectionScreenState
    extends ConsumerState<RegionalAnestheticSelectionScreen> {
  List<String> _allItems = [];
  List<String> _filteredItems = [];
  List<String> _userAddedItems = [];
  Set<String> _selectedItems = {};
  final TextEditingController _searchController = TextEditingController();
  bool _isLoading = true;
  String? _parseObjectId;

  @override
  void initState() {
    super.initState();
    _loadItems();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadItems() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      // Load user-added items from Parse
      final query = QueryBuilder<ParseObject>(ParseObject('addedRegionals'))
        ..whereEqualTo('userEmail', user.email);

      final response = await query.query();

      if (response.success &&
          response.results != null &&
          response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        _parseObjectId = parseObject.objectId;
        final arrayData = parseObject.get<List<dynamic>>('addedRegionals');

        if (arrayData != null) {
          _userAddedItems = arrayData.map((e) => e.toString()).toList();
        }
      }

      // Combine default items with user-added items
      _allItems = [
        ...RegionalAnestheticData.defaultOptions,
        ..._userAddedItems
      ];
      _allItems.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
      _filteredItems = List.from(_allItems);

      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error loading data: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _filterItems(String query) {
    if (query.isEmpty) {
      setState(() {
        _filteredItems = List.from(_allItems);
      });
    } else {
      setState(() {
        _filteredItems = _allItems
            .where((item) => item.toLowerCase().contains(query.toLowerCase()))
            .toList();
      });
    }
  }

  Future<void> _showAddItemDialog() async {
    final textController = TextEditingController();

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Add Regional Anesthetic Option'),
        content: TextField(
          controller: textController,
          autofocus: true,
          autocorrect: false,
          textCapitalization: TextCapitalization.words,
          decoration: InputDecoration(
            hintText: 'Enter option',
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(4),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            style: TextButton.styleFrom(
              foregroundColor: AppColors.jclGray,
            ),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () async {
              final text = textController.text.trim();
              if (text.isNotEmpty) {
                await _addItem(text);
              }
              if (mounted) {
                Navigator.of(context).pop();
              }
            },
            style: TextButton.styleFrom(
              foregroundColor: AppColors.jclOrange,
            ),
            child: const Text('Add'),
          ),
        ],
      ),
    );
  }

  Future<void> _addItem(String item) async {
    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      // Add to user-added items
      _userAddedItems.add(item);

      // Combine and sort
      _allItems = [
        ...RegionalAnestheticData.defaultOptions,
        ..._userAddedItems
      ];
      _allItems.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
      _filteredItems = List.from(_allItems);

      setState(() {});

      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('jclRegionalArry', _userAddedItems);

      // Save to Parse Server
      if (_parseObjectId != null) {
        // Update existing object
        final query = QueryBuilder<ParseObject>(ParseObject('addedRegionals'))
          ..whereEqualTo('userEmail', user.email);

        final response = await query.query();
        if (response.success &&
            response.results != null &&
            response.results!.isNotEmpty) {
          final parseObject = response.results!.first as ParseObject;
          parseObject.set('addedRegionals', _userAddedItems);
          await parseObject.save();
        }
      } else {
        // Create new object
        final parseObject = ParseObject('addedRegionals')
          ..set('userEmail', user.email)
          ..set('addedRegionals', _userAddedItems);

        final saveResponse = await parseObject.save();
        if (saveResponse.success) {
          _parseObjectId = parseObject.objectId;
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error adding item: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _showConfirmationDialog() async {
    String title;
    String message;

    if (_selectedItems.isEmpty) {
      title = 'No Selection';
      message = 'Please select at least one option.';
    } else if (_selectedItems.length == 1) {
      title = 'Selected Regional Anesthetic';
      message = _selectedItems.first;
    } else {
      title = 'Selected Regional Anesthetic';
      message = _selectedItems.join('\n');
    }

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.jclWhite,
        title: Text(
          title,
          style: const TextStyle(color: AppColors.jclGray),
        ),
        content: Text(
          message,
          style: const TextStyle(color: AppColors.jclGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text(
              'Edit',
              style: TextStyle(color: AppColors.jclGray),
            ),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop(); // Close dialog
              Navigator.of(context).pop(); // Close screen without returning
            },
            child: const Text(
              'Exit',
              style: TextStyle(color: AppColors.jclGray),
            ),
          ),
          ElevatedButton(
            onPressed: () {
              // Format the return string with Regional prefix
              final formattedResult = 'Reg. Anesthetic - ${_selectedItems.join(', ')}';

              Navigator.of(context).pop(); // Close dialog
              Navigator.of(context).pop(formattedResult); // Return formatted string
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: AppColors.jclWhite,
            ),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Regional Anesthetic',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclGray,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.jclWhite),
        leading: IconButton(
          icon: Image.asset(
            'assets/images/add-30.png',
            width: 24,
            height: 24,
            color: AppColors.jclWhite,
          ),
          onPressed: _showAddItemDialog,
        ),
        actions: [
          IconButton(
            icon: Image.asset(
              'assets/images/done-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            onPressed: _showConfirmationDialog,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Search bar
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search',
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                    ),
                    onChanged: _filterItems,
                  ),
                ),
                // List
                Expanded(
                  child: ListView.builder(
                    itemCount: _filteredItems.length,
                    itemBuilder: (context, index) {
                      final item = _filteredItems[index];
                      final isSelected = _selectedItems.contains(item);

                      return Container(
                        decoration: BoxDecoration(
                          border: Border(
                            bottom: BorderSide(
                              color: Colors.grey[300]!,
                              width: 1,
                            ),
                          ),
                        ),
                        child: ListTile(
                          title: Text(
                            item,
                            style: const TextStyle(
                              color: AppColors.jclGray,
                              fontSize: 16,
                            ),
                          ),
                          trailing: isSelected
                              ? const Icon(Icons.check, color: AppColors.jclOrange)
                              : null,
                          tileColor: AppColors.jclWhite,
                          onTap: () {
                            setState(() {
                              if (isSelected) {
                                _selectedItems.remove(item);
                              } else {
                                _selectedItems.add(item);
                              }
                            });
                          },
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
    );
  }
}
