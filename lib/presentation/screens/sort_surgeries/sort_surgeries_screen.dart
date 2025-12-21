import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:intl/intl.dart';
import '../../../core/themes/app_colors.dart';
import '../../../core/utils/surgery_image_helper.dart';
import '../cases/case_detail_screen.dart';
import '../../widgets/marquee_text.dart';

/// Sort Surgeries Screen - Search past surgeries across all fields
/// Allows searching by surgeon, facility, surgery class, or surgery type
class SortSurgeriesScreen extends ConsumerStatefulWidget {
  const SortSurgeriesScreen({super.key});

  @override
  ConsumerState<SortSurgeriesScreen> createState() => _SortSurgeriesScreenState();
}

class _SortSurgeriesScreenState extends ConsumerState<SortSurgeriesScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> _allCases = [];
  List<Map<String, dynamic>> _filteredCases = [];
  bool _isLoading = false;
  final DateFormat _dateFormat = DateFormat('M/d/yyyy');

  @override
  void initState() {
    super.initState();
    _loadData();
    _searchController.addListener(_filterCases);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final currentUser = await ParseUser.currentUser() as ParseUser?;
      if (currentUser == null) return;

      // Fetch all cases for current user
      final query = QueryBuilder<ParseObject>(ParseObject('jclCases'))
        ..whereEqualTo('userEmail', currentUser.emailAddress)
        ..orderByDescending('dateTime')
        ..setLimit(10000);

      final response = await query.query();

      if (response.success && response.results != null) {
        final cases = <Map<String, dynamic>>[];

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
            'procSurgery': caseObj.get<String>('procSurgery') ?? caseObj.get<String>('surgery') ?? '',
          };

          cases.add(caseData);
        }

        setState(() {
          _allCases = cases;
          _filteredCases = cases;
        });
      }
    } catch (e) {
      debugPrint('Error loading data: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterCases() {
    final searchTerm = _searchController.text.toLowerCase().trim();

    if (searchTerm.isEmpty) {
      setState(() {
        _filteredCases = _allCases;
      });
      return;
    }

    setState(() {
      _filteredCases = _allCases.where((caseData) {
        final surgeonName = (caseData['surgeonName'] as String? ?? '').toLowerCase();
        final facilityName = (caseData['facilityName'] as String? ?? '').toLowerCase();
        final surgeryClass = (caseData['surgeryClass'] as String? ?? '').toLowerCase();
        final surgery = (caseData['surgery'] as String? ?? '').toLowerCase();

        return surgeonName.contains(searchTerm) ||
            facilityName.contains(searchTerm) ||
            surgeryClass.contains(searchTerm) ||
            surgery.contains(searchTerm);
      }).toList();

      // Sort by date descending (newest first)
      _filteredCases.sort((a, b) {
        final dateA = a['dateTime'] as DateTime?;
        final dateB = b['dateTime'] as DateTime?;

        if (dateA == null && dateB == null) return 0;
        if (dateA == null) return 1;
        if (dateB == null) return -1;

        return dateB.compareTo(dateA);
      });
    });
  }

  void _clearSearch() {
    _searchController.clear();
    setState(() {
      _filteredCases = _allCases;
    });
  }

  String _formatDate(DateTime? date) {
    if (date == null) return 'N/A';
    return _dateFormat.format(date);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Search Past Surgeries',
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
            onPressed: () {
              _clearSearch();
              _loadData();
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.jclOrange),
            )
          : Column(
              children: [
                // Search field section
                Container(
                  color: AppColors.jclGray,
                  padding: const EdgeInsets.all(16),
                  child: TextField(
                    controller: _searchController,
                    autocorrect: false,
                    enableSuggestions: false,
                    style: const TextStyle(color: AppColors.jclGray),
                    decoration: InputDecoration(
                      hintText: 'Search by surgeon, facility, or surgery...',
                      hintStyle: TextStyle(
                        color: AppColors.jclGray.withOpacity(0.5),
                      ),
                      filled: true,
                      fillColor: AppColors.jclWhite,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      prefixIcon: const Icon(
                        Icons.search,
                        color: AppColors.jclOrange,
                      ),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(
                                Icons.clear,
                                color: AppColors.jclGray,
                              ),
                              onPressed: _clearSearch,
                            )
                          : null,
                    ),
                  ),
                ),
                // Results count
                Container(
                  color: AppColors.jclGray,
                  padding: const EdgeInsets.only(left: 16, right: 16, bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '${_filteredCases.length} case${_filteredCases.length == 1 ? '' : 's'} found',
                        style: const TextStyle(
                          color: AppColors.jclWhite,
                          fontSize: 14,
                        ),
                      ),
                      if (_searchController.text.isNotEmpty)
                        Text(
                          'of ${_allCases.length} total',
                          style: TextStyle(
                            color: AppColors.jclWhite.withOpacity(0.7),
                            fontSize: 14,
                          ),
                        ),
                    ],
                  ),
                ),
                // Results list
                Expanded(
                  child: _filteredCases.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.search_off,
                                size: 64,
                                color: AppColors.jclWhite.withOpacity(0.3),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                _searchController.text.isEmpty
                                    ? 'No cases found'
                                    : 'No cases match your search',
                                style: TextStyle(
                                  color: AppColors.jclWhite.withOpacity(0.7),
                                  fontSize: 16,
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(0),
                          itemCount: _filteredCases.length,
                          separatorBuilder: (context, index) => const Divider(
                            height: 1,
                            color: AppColors.jclWhite,
                            thickness: 0.5,
                          ),
                          itemBuilder: (context, index) {
                            final caseData = _filteredCases[index];
                            final dateTime = caseData['dateTime'] as DateTime?;

                            return ListTile(
                              tileColor: AppColors.jclGray,
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 8,
                              ),
                              leading: Container(
                                width: 40,
                                height: 40,
                                decoration: const BoxDecoration(
                                  color: AppColors.jclWhite,
                                  shape: BoxShape.circle,
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.all(6.0),
                                  child: Image.asset(
                                    SurgeryImageHelper.getAssetPath(
                                      caseData['jclImageName'] as String?,
                                      surgeryClass: caseData['surgeryClass'] as String?,
                                    ),
                                    fit: BoxFit.contain,
                                    errorBuilder: (context, error, stackTrace) {
                                      return const Icon(
                                        Icons.medical_services,
                                        color: AppColors.jclOrange,
                                        size: 24,
                                      );
                                    },
                                  ),
                                ),
                              ),
                              title: MarqueeText(
                                caseData['procSurgery'] as String? ?? caseData['surgery'] as String? ?? 'Unknown',
                                maxLines: 1,
                                scrollSpeed: 30,
                                pauseInterval: 1.5,
                                labelSpacing: 30,
                                style: const TextStyle(
                                  color: AppColors.jclWhite,
                                  fontSize: 15,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 4),
                                  MarqueeText(
                                    caseData['primePlan'] as String? ?? '',
                                    maxLines: 1,
                                    scrollSpeed: 24,
                                    pauseInterval: 1.8,
                                    labelSpacing: 30,
                                    style: TextStyle(
                                      color: AppColors.jclWhite.withOpacity(0.7),
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Row(
                                    children: [
                                      Text(
                                        _formatDate(dateTime),
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: AppColors.jclWhite.withOpacity(0.5),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        '•',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: AppColors.jclWhite.withOpacity(0.5),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          caseData['surgeryClass'] as String? ?? '',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: AppColors.jclWhite.withOpacity(0.5),
                                          ),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              trailing: const Icon(
                                Icons.chevron_right,
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
              ],
            ),
    );
  }
}
