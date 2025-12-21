import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/general_anesthetic_data.dart';
import '../../providers/auth_provider.dart';

/// General Anesthetic multi-selection screen
/// Matches iOS app's gaTableViewController
class GeneralAnestheticSelectionScreen extends ConsumerStatefulWidget {
  final bool isTIVA; // If true, returns "TIVA" prefix instead of "Gen. Anesthetic - "

  const GeneralAnestheticSelectionScreen({
    super.key,
    this.isTIVA = false,
  });

  @override
  ConsumerState<GeneralAnestheticSelectionScreen> createState() =>
      _GeneralAnestheticSelectionScreenState();
}

class _GeneralAnestheticSelectionScreenState
    extends ConsumerState<GeneralAnestheticSelectionScreen> {
  List<String> _allItems = [];
  List<String> _userAddedItems = [];
  Set<String> _selectedItems = {};
  bool _isLoading = true;
  String? _parseObjectId;

  @override
  void initState() {
    super.initState();
    _loadItems();
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
      final query = QueryBuilder<ParseObject>(ParseObject('addedGenerals'))
        ..whereEqualTo('userEmail', user.email);

      final response = await query.query();

      if (response.success &&
          response.results != null &&
          response.results!.isNotEmpty) {
        final parseObject = response.results!.first as ParseObject;
        _parseObjectId = parseObject.objectId;
        final arrayData = parseObject.get<List<dynamic>>('addedGenerals');

        if (arrayData != null) {
          _userAddedItems = arrayData.map((e) => e.toString()).toList();
        }
      }

      // Combine default items with user-added items
      _allItems = [
        ...GeneralAnestheticData.defaultOptions,
        ..._userAddedItems
      ];
      _allItems.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));

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

  Future<void> _showAddItemDialog() async {
    final textController = TextEditingController();

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Add General Anesthetic Option'),
        content: TextField(
          controller: textController,
          autofocus: true,
          autocorrect: false,
          enableSuggestions: false,
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
        ...GeneralAnestheticData.defaultOptions,
        ..._userAddedItems
      ];
      _allItems.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));

      setState(() {});

      // Save to SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('jclGeneralsArry', _userAddedItems);

      // Save to Parse Server
      if (_parseObjectId != null) {
        // Update existing object
        final query = QueryBuilder<ParseObject>(ParseObject('addedGenerals'))
          ..whereEqualTo('userEmail', user.email);

        final response = await query.query();
        if (response.success &&
            response.results != null &&
            response.results!.isNotEmpty) {
          final parseObject = response.results!.first as ParseObject;
          parseObject.set('addedGenerals', _userAddedItems);
          await parseObject.save();
        }
      } else {
        // Create new object
        final parseObject = ParseObject('addedGenerals')
          ..set('userEmail', user.email)
          ..set('addedGenerals', _userAddedItems);

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
      title = 'Selected General Anesthetic';
      message = _selectedItems.first;
    } else {
      title = 'Selected General Anesthetic';
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
              // Format the return string based on TIVA flag
              String formattedResult;
              if (widget.isTIVA) {
                formattedResult = 'TIVA';
                if (_selectedItems.isNotEmpty) {
                  formattedResult += ' - ${_selectedItems.join(', ')}';
                }
              } else {
                formattedResult = 'Gen. Anesthetic - ${_selectedItems.join(', ')}';
              }

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
        title: Text(
          widget.isTIVA ? 'TIVA Options' : 'General Anesthetic',
          style: const TextStyle(
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
          : ListView.builder(
              itemCount: _allItems.length,
              itemBuilder: (context, index) {
                final item = _allItems[index];
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
    );
  }
}
