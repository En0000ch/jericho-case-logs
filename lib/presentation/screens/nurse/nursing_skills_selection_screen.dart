import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/data/nursing_skills_data.dart';
import '../../../data/datasources/remote/parse_nursing_skills_service.dart';
import '../../providers/auth_provider.dart';

/// Nursing Skills Selection Screen
/// Allows nurses to select multiple skills from a collection view with search
class NursingSkillsSelectionScreen extends ConsumerStatefulWidget {
  final List<String> initiallySelectedSkills;

  const NursingSkillsSelectionScreen({
    super.key,
    this.initiallySelectedSkills = const [],
  });

  @override
  ConsumerState<NursingSkillsSelectionScreen> createState() =>
      _NursingSkillsSelectionScreenState();
}

class _NursingSkillsSelectionScreenState
    extends ConsumerState<NursingSkillsSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ParseNursingSkillsService _skillsService = ParseNursingSkillsService();

  List<String> _allSkills = [];
  List<String> _filteredSkills = [];
  Set<String> _selectedSkills = {};
  List<String> _customSkills = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _selectedSkills = Set.from(widget.initiallySelectedSkills);
    _searchController.addListener(_filterSkills);
    _loadSkills();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadSkills() async {
    setState(() => _isLoading = true);

    try {
      final user = ref.read(currentUserProvider);
      if (user != null) {
        // Fetch custom skills from Parse
        _customSkills = await _skillsService.fetchCustomSkills(user.email);

        // Combine default and custom skills, sort alphabetically
        _allSkills = NursingSkills.getAllSkills(_customSkills);
        _filteredSkills = List.from(_allSkills);
      }
    } catch (e) {
      print('Error loading skills: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterSkills() {
    final query = _searchController.text;
    setState(() {
      _filteredSkills = NursingSkills.filterSkills(_allSkills, query);
    });
  }

  void _toggleSkill(String skill) {
    setState(() {
      if (_selectedSkills.contains(skill)) {
        _selectedSkills.remove(skill);
      } else {
        _selectedSkills.add(skill);
      }
    });
  }

  void _showAddCustomSkillDialog() {
    final textController = TextEditingController();

    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          backgroundColor: AppColors.jclWhite,
          title: const Text(
            'Add Custom Skill',
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
              hintText: 'Enter skill name',
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
                final skillName = textController.text.trim();
                if (skillName.isNotEmpty) {
                  Navigator.of(dialogContext).pop();
                  await _addCustomSkill(skillName);
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

  Future<void> _addCustomSkill(String skillName) async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    // Check if skill already exists in all skills
    if (_allSkills.any((s) => s.toLowerCase() == skillName.toLowerCase())) {
      _showMessage('Skill already exists');
      return;
    }

    final success = await _skillsService.addCustomSkill(user.email, skillName);

    if (success) {
      _showMessage('Custom skill added');
      await _loadSkills();
      // Auto-select the newly added skill
      setState(() {
        _selectedSkills.add(skillName);
      });
    } else {
      _showMessage('Failed to add custom skill');
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

  void _removeSelectedSkill(String skill) {
    setState(() {
      _selectedSkills.remove(skill);
    });
  }

  void _confirmSelection() {
    Navigator.of(context).pop(_selectedSkills.toList());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Select Skills',
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
                hintText: 'Search skills...',
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

          // Add custom skill button
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _showAddCustomSkillDialog,
                icon: const Icon(Icons.add, color: AppColors.jclOrange),
                label: const Text(
                  'Add Custom Skill',
                  style: TextStyle(color: AppColors.jclOrange),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: AppColors.jclOrange),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ),

          // Skills collection view (grid)
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: AppColors.jclOrange,
                    ),
                  )
                : _filteredSkills.isEmpty
                    ? Center(
                        child: Text(
                          _searchController.text.isEmpty
                              ? 'No skills available'
                              : 'No skills match your search',
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
                        itemCount: _filteredSkills.length,
                        itemBuilder: (context, index) {
                          final skill = _filteredSkills[index];
                          final isSelected = _selectedSkills.contains(skill);
                          final isCustom = _customSkills.contains(skill);

                          return InkWell(
                            onTap: () => _toggleSkill(skill),
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
                                          skill,
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

          // Selected skills display frame
          if (_selectedSkills.isNotEmpty)
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
                          'Selected Skills (${_selectedSkills.length})',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.jclGray,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            setState(() {
                              _selectedSkills.clear();
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
                        children: _selectedSkills.map((skill) {
                          return Chip(
                            label: Text(
                              skill,
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
                            onDeleted: () => _removeSelectedSkill(skill),
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
