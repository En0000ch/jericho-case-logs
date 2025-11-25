import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/themes/app_colors.dart';
import '../../providers/auth_provider.dart';

/// Settings Update Screen - Flutter equivalent of settingsUpdateTableViewController
/// Handles updates for Facilities, Surgeons, and Skills
class SettingsUpdateScreen extends ConsumerStatefulWidget {
  final String updateType; // "facilities", "surgeons", or "skills"

  const SettingsUpdateScreen({
    super.key,
    required this.updateType,
  });

  @override
  ConsumerState<SettingsUpdateScreen> createState() => _SettingsUpdateScreenState();
}

class _SettingsUpdateScreenState extends ConsumerState<SettingsUpdateScreen> {
  List<String> _items = [];
  bool _isLoading = true;
  Set<String> _selectedSkills = {}; // Track selected skills for multi-selection

  @override
  void initState() {
    super.initState();
    _loadItems();
  }

  String get _title {
    switch (widget.updateType) {
      case 'facilities':
        return 'Add Facilities';
      case 'surgeons':
        return 'Add Surgeons';
      case 'skills':
        return 'Add Skilled Procedures';
      default:
        return 'Update';
    }
  }

  String get _sharedPrefsKey {
    switch (widget.updateType) {
      case 'facilities':
        return 'jclFacilityArray';
      case 'surgeons':
        return 'jclSurgeonArray';
      case 'skills':
        return 'jclSkillsArray';
      default:
        return '';
    }
  }

  String get _parseClassName {
    switch (widget.updateType) {
      case 'facilities':
        return 'savedFacilities';
      case 'surgeons':
        return 'savedSurgeons';
      case 'skills':
        return 'savedSkills';
      default:
        return '';
    }
  }

  String get _parseArrayKey {
    switch (widget.updateType) {
      case 'facilities':
        return 'userFacilities';
      case 'surgeons':
        return 'userSurgeons';
      case 'skills':
        return 'userSkills';
      default:
        return '';
    }
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

      // First, try to load from Parse Server
      final query = QueryBuilder<ParseObject>(ParseObject(_parseClassName))
        ..whereEqualTo('userEmail', user.email);

      final response = await query.query();

      List<String> items = [];

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        // Data exists in Parse Server
        final parseObject = response.results!.first as ParseObject;
        final arrayData = parseObject.get<List<dynamic>>(_parseArrayKey);

        if (arrayData != null) {
          items = arrayData.map((e) => e.toString()).toList();
        }
      } else {
        // No data in Parse Server, try SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        items = prefs.getStringList(_sharedPrefsKey) ?? [];
      }

      if (!mounted) return;

      setState(() {
        // CRITICAL: Aggressively filter out empty, null, and whitespace-only strings
        _items = items
            .where((item) => item != null && item.trim().isNotEmpty && item.trim().length > 0)
            .map((item) => item.trim())
            .where((item) => item.isNotEmpty && item != '') // Double-check after trim
            .where((item) => item.replaceAll(' ', '').isNotEmpty) // Ensure not just spaces
            .toSet() // Remove duplicates
            .toList();

        print('Loaded ${_items.length} valid items from database');
        if (_items.isNotEmpty) {
          print('Sample items: ${_items.take(3).toList()}');
        }

        _items.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
        _isLoading = false;
      });

      // Save to SharedPreferences for offline access - only non-empty items
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList(_sharedPrefsKey, _items);

      // Show add dialog after loading ONLY for facilities and surgeons, NOT for skills
      if (mounted && widget.updateType != 'skills') {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _showAddDialog();
          }
        });
      }
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error loading data: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _saveItems() async {
    try {
      // CRITICAL: Filter out empty strings before saving anywhere
      final nonEmptyItems = _items
          .where((item) => item.trim().isNotEmpty)
          .map((item) => item.trim())
          .toList();

      // Update the local state to match what we're saving
      if (!mounted) return;

      setState(() {
        _items = nonEmptyItems;
      });

      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList(_sharedPrefsKey, nonEmptyItems);

      // Save to Parse Server - ONLY non-empty items
      await _saveToParseServer();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error saving: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _saveToParseServer() async {
    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        print('ERROR: No user logged in, cannot save to Parse');
        return;
      }

      // CRITICAL: Triple-filter to ensure we NEVER send empty/blank strings
      // 1. Filter out null, empty, and whitespace-only strings
      // 2. Trim all remaining strings
      // 3. Remove any duplicates that may exist
      final nonEmptyItems = _items
          .where((item) => item != null && item.trim().isNotEmpty && item.trim().length > 0)
          .map((item) => item.trim())
          .where((item) => item.isNotEmpty) // Double-check after trim
          .toSet() // Remove duplicates
          .toList();

      // ABSOLUTE FINAL CHECK: Ensure no item is empty
      final validItems = nonEmptyItems.where((item) {
        final isValid = item.isNotEmpty && item.trim().isNotEmpty && item != '';
        if (!isValid) {
          print('WARNING: Filtered out invalid item: "$item"');
        }
        return isValid;
      }).toList();

      // Sort for consistency
      validItems.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));

      print('Saving to Parse: $_parseClassName for user ${user.email}');
      print('Items count: ${validItems.length}');
      print('Items to save: $validItems');

      // Validate one final time before sending to Parse
      for (final item in validItems) {
        if (item.isEmpty || item.trim().isEmpty) {
          print('CRITICAL ERROR: Empty item detected before Parse save! Aborting save.');
          throw Exception('Cannot save empty strings to database');
        }
      }

      final query = QueryBuilder<ParseObject>(ParseObject(_parseClassName))
        ..whereEqualTo('userEmail', user.email);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        // Update existing object
        final parseObject = response.results!.first as ParseObject;
        parseObject.set(_parseArrayKey, validItems);
        final saveResponse = await parseObject.save();

        if (saveResponse.success) {
          print('Successfully updated existing Parse object with ${validItems.length} items');
        } else {
          print('ERROR saving to Parse: ${saveResponse.error?.message}');
          throw Exception('Parse save failed: ${saveResponse.error?.message}');
        }
      } else {
        // Create new object - only if we have items
        if (validItems.isEmpty) {
          print('No items to save, skipping Parse object creation');
          return;
        }

        final parseObject = ParseObject(_parseClassName)
          ..set('userEmail', user.email)
          ..set(_parseArrayKey, validItems);

        final saveResponse = await parseObject.save();

        if (saveResponse.success) {
          print('Successfully created new Parse object with ${validItems.length} items');
        } else {
          print('ERROR creating Parse object: ${saveResponse.error?.message}');
          throw Exception('Parse create failed: ${saveResponse.error?.message}');
        }
      }
    } catch (e) {
      print('EXCEPTION in _saveToParseServer: $e');
      rethrow; // Re-throw so _saveItems can show error to user
    }
  }

  void _showAddDialog() {
    String dialogTitle;
    String dialogMessage;
    String placeholder;
    String addAnotherLabel;

    switch (widget.updateType) {
      case 'facilities':
        dialogTitle = 'Facility/Office Name';
        dialogMessage = 'Please enter the name of the facility you work in most often or most recently';
        placeholder = 'Facility Name';
        addAnotherLabel = 'Add Another Facility';
        break;
      case 'surgeons':
        dialogTitle = 'Add Surgeon';
        dialogMessage = 'Please enter the name of the surgeon you work with most often or most recently.';
        placeholder = 'Surgeon Name';
        addAnotherLabel = 'Add Another Surgeon';
        break;
      case 'skills':
        dialogTitle = 'Skilled Procedure';
        dialogMessage = 'Please add a skilled procedure.';
        placeholder = 'Enter Skill';
        addAnotherLabel = 'Add Skill';
        break;
      default:
        return;
    }

    final textController = TextEditingController();

    showDialog(
      context: context,
      barrierDismissible: false, // Prevent dismissing by tapping outside
      builder: (context) => WillPopScope(
        onWillPop: () async => false, // Prevent back button dismiss
        child: AlertDialog(
          title: Text(dialogTitle),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(dialogMessage),
              const SizedBox(height: 16),
              TextField(
                controller: textController,
                autofocus: true,
                autocorrect: false,
                textCapitalization: TextCapitalization.words,
                style: const TextStyle(
                  color: AppColors.jclGray, // Dark text color
                  fontSize: 16,
                ),
                decoration: InputDecoration(
                  hintText: placeholder,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
            ],
          ),
          actions: widget.updateType == 'skills'
              ? [
                  // For skills: Done, Add Skill
                  TextButton(
                    onPressed: () async {
                      final text = textController.text.trim();
                      Navigator.of(context).pop();
                      if (text.isNotEmpty) {
                        await _addItem(text);
                      }
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.jclOrange,
                    ),
                    child: const Text('Done'),
                  ),
                  TextButton(
                    onPressed: () async {
                      final text = textController.text.trim();
                      Navigator.of(context).pop();
                      if (text.isNotEmpty) {
                        await _addItem(text);
                        _showAddDialog();
                      }
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.jclOrange,
                    ),
                    child: Text(addAnotherLabel),
                  ),
                ]
              : [
                  // For facilities and surgeons: Done, Add Another
                  TextButton(
                    onPressed: () async {
                      final text = textController.text.trim();
                      Navigator.of(context).pop();
                      if (text.isNotEmpty) {
                        await _addItem(text);
                      }
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.jclOrange,
                    ),
                    child: const Text('Done'),
                  ),
                  TextButton(
                    onPressed: () async {
                      final text = textController.text.trim();
                      Navigator.of(context).pop();
                      if (text.isNotEmpty) {
                        await _addItem(text);
                        _showAddDialog();
                      }
                    },
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.jclOrange,
                    ),
                    child: Text(addAnotherLabel),
                  ),
                ],
        ),
      ),
    );
  }

  Future<void> _addItem(String item) async {
    // CRITICAL: Validate input thoroughly
    final trimmedItem = item.trim();

    // Reject empty, whitespace-only, or invalid strings
    if (trimmedItem.isEmpty || trimmedItem.length == 0 || trimmedItem == '') {
      print('REJECTED: Empty or whitespace-only item');
      return;
    }

    // Additional validation: must have at least one non-whitespace character
    if (trimmedItem.replaceAll(' ', '').isEmpty) {
      print('REJECTED: Item contains only spaces');
      return;
    }

    print('Adding valid item: "$trimmedItem"');

    if (!mounted) {
      print('Widget not mounted, skipping add');
      return;
    }

    setState(() {
      // Only add if it's truly valid
      if (trimmedItem.isNotEmpty && trimmedItem.trim().length > 0) {
        _items.add(trimmedItem);
      }

      // Aggressive filtering: remove any empty, null, or whitespace-only strings
      _items = _items
          .where((item) => item != null && item.trim().isNotEmpty && item.trim().length > 0)
          .map((item) => item.trim())
          .where((item) => item.isNotEmpty)
          .toList();

      // Remove duplicates
      _items = _items.toSet().toList();

      _items.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    });

    print('Items after add: $_items');

    // Save asynchronously without blocking UI
    await _saveItems();
  }

  Future<void> _deleteItem(int index) async {
    if (!mounted) return;

    setState(() {
      _items.removeAt(index);
      // Remove any empty strings
      _items = _items.where((item) => item.trim().isNotEmpty).toList();
    });

    await _saveItems();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: Text(
          _title,
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
        leading: Container(), // Hide back button
        actions: [
          IconButton(
            icon: Image.asset(
              'assets/images/done-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            onPressed: () async {
              // Save selected skills to SharedPreferences when Done is pressed
              if (widget.updateType == 'skills' && _selectedSkills.isNotEmpty) {
                final prefs = await SharedPreferences.getInstance();
                await prefs.setStringList('jclSkillsArray', _selectedSkills.toList());
              }
              if (mounted) {
                Navigator.of(context).pop();
              }
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _items.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.list,
                        size: 64,
                        color: Colors.grey[400],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'No items yet',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 8),
                      ElevatedButton(
                        onPressed: _showAddDialog,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.jclOrange,
                          foregroundColor: AppColors.jclWhite,
                        ),
                        child: const Text('Add Item'),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  itemCount: _items.length,
                  itemBuilder: (context, index) {
                    return Dismissible(
                      key: Key('${_items[index]}_$index'),
                      direction: DismissDirection.endToStart,
                      background: Container(
                        color: Colors.red,
                        alignment: Alignment.centerRight,
                        padding: const EdgeInsets.only(right: 16),
                        child: const Icon(
                          Icons.delete,
                          color: Colors.white,
                        ),
                      ),
                      onDismissed: (direction) {
                        _deleteItem(index);
                      },
                      child: Container(
                        decoration: BoxDecoration(
                          border: Border(
                            bottom: BorderSide(
                              color: Colors.grey[300]!,
                              width: 1,
                            ),
                          ),
                        ),
                        child: widget.updateType == 'skills'
                            ? ListTile(
                                title: Text(
                                  _items[index],
                                  style: const TextStyle(
                                    color: AppColors.jclGray,
                                    fontSize: 16,
                                  ),
                                ),
                                trailing: _selectedSkills.contains(_items[index])
                                    ? const Icon(Icons.check, color: AppColors.jclOrange)
                                    : null,
                                tileColor: AppColors.jclWhite,
                                onTap: () {
                                  setState(() {
                                    if (_selectedSkills.contains(_items[index])) {
                                      _selectedSkills.remove(_items[index]);
                                    } else {
                                      _selectedSkills.add(_items[index]);
                                    }
                                  });
                                },
                              )
                            : ListTile(
                                title: Text(
                                  _items[index],
                                  style: const TextStyle(
                                    color: AppColors.jclGray,
                                    fontSize: 16,
                                  ),
                                ),
                                tileColor: AppColors.jclWhite,
                              ),
                      ),
                    );
                  },
                ),
    );
  }
}
