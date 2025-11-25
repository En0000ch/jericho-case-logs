import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';
import '../cases/case_detail_screen.dart';

/// Sort Surgeries Screen - Flutter equivalent of sortSurgeriesVController
/// Allows filtering past surgeries by surgery type and surgeon
class SortSurgeriesScreen extends ConsumerStatefulWidget {
  const SortSurgeriesScreen({super.key});

  @override
  ConsumerState<SortSurgeriesScreen> createState() => _SortSurgeriesScreenState();
}

class _SortSurgeriesScreenState extends ConsumerState<SortSurgeriesScreen> {
  bool _surgeryChecked = false;
  bool _surgeonChecked = false;
  String? _selectedSurgery;
  String? _selectedSurgeon;
  List<String> _availableSurgeries = [];
  List<String> _availableSurgeons = [];
  List<Map<String, dynamic>> _allCases = [];
  List<Map<String, dynamic>> _filteredCases = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final currentUser = await ParseUser.currentUser() as ParseUser?;
      if (currentUser == null) return;

      // Fetch all cases for current user
      final query = QueryBuilder<ParseObject>(ParseObject('jclCases'))
        ..whereEqualTo('userEmail', currentUser.emailAddress)
        ..setLimit(10000);

      final response = await query.query();

      if (response.success && response.results != null) {
        final cases = <Map<String, dynamic>>[];
        final surgeries = <String>{};
        final surgeons = <String>{};

        for (var obj in response.results!) {
          final caseObj = obj as ParseObject;
          final caseData = {
            'caseID': caseObj.objectId,
            'userEmail': caseObj.get<String>('userEmail') ?? '',
            'facilityName': caseObj.get<String>('facilityName') ?? '',
            'surgeonName': caseObj.get<String>('surgeonName') ?? '',
            'surgeryClass': caseObj.get<String>('surgeryCategory') ?? '',
            'surgery': caseObj.get<String>('surgery') ?? '',
            'primePlan': caseObj.get<String>('primePlan') ?? '',
            'secPlan': caseObj.get<String>('secPlan') ?? '',
            'asaClass': caseObj.get<String>('asaPlan') ?? '',
            'skillsArry': caseObj.get<List>('skilledProcsArray') ?? [],
            'jclImageName': caseObj.get<String>('jclImageName') ?? '',
            'dateTime': caseObj.get<DateTime>('dateTime'),
          };

          cases.add(caseData);

          final surgeryClass = caseData['surgeryClass'] as String?;
          final surgeonName = caseData['surgeonName'] as String?;

          if (surgeryClass != null && surgeryClass.isNotEmpty) {
            surgeries.add(surgeryClass);
          }
          if (surgeonName != null && surgeonName.isNotEmpty) {
            surgeons.add(surgeonName);
          }
        }

        setState(() {
          _allCases = cases;
          _filteredCases = cases;
          _availableSurgeries = surgeries.toList()..sort();
          _availableSurgeons = surgeons.toList()..sort();
        });
      }
    } catch (e) {
      debugPrint('Error loading data: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterCases() {
    setState(() {
      _filteredCases = _allCases.where((caseData) {
        bool matchesSurgery = true;
        bool matchesSurgeon = true;

        if (_surgeryChecked && _selectedSurgery != null) {
          matchesSurgery = caseData['surgeryClass'] == _selectedSurgery;
        }

        if (_surgeonChecked && _selectedSurgeon != null) {
          matchesSurgeon = caseData['surgeonName'] == _selectedSurgeon;
        }

        return matchesSurgery && matchesSurgeon;
      }).toList();
    });
  }

  void _showSurgeryPicker() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Select Surgery'),
        content: SizedBox(
          width: double.maxFinite,
          height: 300,
          child: ListView.builder(
            itemCount: _availableSurgeries.length,
            itemBuilder: (context, index) {
              final surgery = _availableSurgeries[index];
              return ListTile(
                title: Text(surgery),
                onTap: () {
                  setState(() {
                    _selectedSurgery = surgery;
                    _surgeryChecked = true;

                    // Filter available surgeons based on selected surgery
                    final surgeons = _allCases
                        .where((c) => c['surgeryClass'] == surgery)
                        .map((c) => c['surgeonName'] as String?)
                        .where((s) => s != null && s.isNotEmpty)
                        .cast<String>()
                        .toSet()
                        .toList();
                    _availableSurgeons = surgeons..sort();
                  });
                  Navigator.pop(context);
                },
              );
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );
  }

  void _showSurgeonPicker() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Select Surgeon'),
        content: SizedBox(
          width: double.maxFinite,
          height: 300,
          child: ListView.builder(
            itemCount: _availableSurgeons.length,
            itemBuilder: (context, index) {
              final surgeon = _availableSurgeons[index];
              return ListTile(
                title: Text(surgeon),
                onTap: () {
                  setState(() {
                    _selectedSurgeon = surgeon;
                    _surgeonChecked = true;

                    // Filter available surgeries based on selected surgeon
                    final surgeries = _allCases
                        .where((c) => c['surgeonName'] == surgeon)
                        .map((c) => c['surgeryClass'] as String?)
                        .where((s) => s != null && s.isNotEmpty)
                        .cast<String>()
                        .toSet()
                        .toList();
                    _availableSurgeries = surgeries..sort();
                  });
                  Navigator.pop(context);
                },
              );
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );
  }

  void _reset() {
    setState(() {
      _surgeryChecked = false;
      _surgeonChecked = false;
      _selectedSurgery = null;
      _selectedSurgeon = null;
      _filteredCases = _allCases;
    });
    _loadData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Past Surgeries',
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
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: AppColors.jclWhite),
            onPressed: _reset,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.jclOrange),
            )
          : Column(
              children: [
                // Filter section
                Container(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      // Surgery filter
                      Row(
                        children: [
                          Checkbox(
                            value: _surgeryChecked,
                            onChanged: (value) {
                              setState(() => _surgeryChecked = value ?? false);
                            },
                            fillColor: MaterialStateProperty.all(AppColors.jclOrange),
                          ),
                          Expanded(
                            child: GestureDetector(
                              onTap: _showSurgeryPicker,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 12,
                                ),
                                decoration: BoxDecoration(
                                  color: AppColors.jclWhite,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _selectedSurgery ?? 'Select Surgery',
                                  style: TextStyle(
                                    color: _selectedSurgery == null
                                        ? AppColors.jclGray.withOpacity(0.5)
                                        : AppColors.jclGray,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      // Surgeon filter
                      Row(
                        children: [
                          Checkbox(
                            value: _surgeonChecked,
                            onChanged: (value) {
                              setState(() => _surgeonChecked = value ?? false);
                            },
                            fillColor: MaterialStateProperty.all(AppColors.jclOrange),
                          ),
                          Expanded(
                            child: GestureDetector(
                              onTap: _showSurgeonPicker,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 12,
                                ),
                                decoration: BoxDecoration(
                                  color: AppColors.jclWhite,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _selectedSurgeon ?? 'Select Surgeon',
                                  style: TextStyle(
                                    color: _selectedSurgeon == null
                                        ? AppColors.jclGray.withOpacity(0.5)
                                        : AppColors.jclGray,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      // Search button
                      ElevatedButton(
                        onPressed: _filterCases,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.jclOrange,
                          foregroundColor: AppColors.jclGray,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 40,
                            vertical: 12,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        child: const Text(
                          'Search',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ),
                // Results list
                Expanded(
                  child: Container(
                    color: AppColors.jclWhite,
                    child: _filteredCases.isEmpty
                        ? Center(
                            child: Text(
                              'No cases found',
                              style: TextStyle(
                                color: AppColors.jclGray.withOpacity(0.5),
                                fontSize: 16,
                              ),
                            ),
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(8),
                            itemCount: _filteredCases.length,
                            separatorBuilder: (context, index) => const Divider(height: 1),
                            itemBuilder: (context, index) {
                              final caseData = _filteredCases[index];
                              return ListTile(
                                contentPadding: const EdgeInsets.all(12),
                                title: Text(
                                  '${caseData['surgery']} / ASA: ${caseData['asaClass']}',
                                  style: const TextStyle(
                                    color: AppColors.jclGray,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                subtitle: Text(
                                  caseData['primePlan'] ?? '',
                                  style: TextStyle(
                                    color: AppColors.jclGray.withOpacity(0.7),
                                  ),
                                ),
                                trailing: const Icon(
                                  Icons.arrow_forward_ios,
                                  size: 16,
                                  color: AppColors.jclOrange,
                                ),
                                onTap: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => CaseDetailScreen(
                                        caseId: caseData['caseID'] as String,
                                      ),
                                    ),
                                  );
                                },
                              );
                            },
                          ),
                  ),
                ),
              ],
            ),
    );
  }
}
