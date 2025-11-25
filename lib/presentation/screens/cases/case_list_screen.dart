import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants/app_constants.dart';
import 'case_creation_flow_screen.dart';
import 'case_detail_screen.dart';

class CaseListScreen extends ConsumerStatefulWidget {
  const CaseListScreen({super.key});

  @override
  ConsumerState<CaseListScreen> createState() => _CaseListScreenState();
}

class _CaseListScreenState extends ConsumerState<CaseListScreen> {
  final _searchController = TextEditingController();
  String? _selectedAsaFilter;
  String? _selectedSurgeryClassFilter;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _performSearch() {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    ref.read(caseListProvider(user.email).notifier).searchCases(
          keyword: _searchController.text.trim().isEmpty
              ? null
              : _searchController.text.trim(),
          asaClassification: _selectedAsaFilter,
          surgeryClass: _selectedSurgeryClassFilter,
        );
  }

  void _clearFilters() {
    setState(() {
      _searchController.clear();
      _selectedAsaFilter = null;
      _selectedSurgeryClassFilter = null;
    });
    final user = ref.read(currentUserProvider);
    if (user != null) {
      ref.read(caseListProvider(user.email).notifier).loadCases();
    }
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter Cases'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            DropdownButtonFormField<String>(
              value: _selectedAsaFilter,
              decoration: const InputDecoration(
                labelText: 'ASA Classification',
              ),
              items: [
                const DropdownMenuItem(value: null, child: Text('All')),
                ...AppConstants.asaClassifications.map(
                  (asa) => DropdownMenuItem(value: asa, child: Text(asa)),
                ),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedAsaFilter = value;
                });
              },
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _selectedSurgeryClassFilter,
              decoration: const InputDecoration(
                labelText: 'Surgery Class',
              ),
              items: [
                const DropdownMenuItem(value: null, child: Text('All')),
                ...AppConstants.surgeryClasses.map(
                  (surgeryClass) => DropdownMenuItem(
                    value: surgeryClass,
                    child: Text(surgeryClass),
                  ),
                ),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedSurgeryClassFilter = value;
                });
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _clearFilters();
            },
            child: const Text('Clear'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              _performSearch();
            },
            child: const Text('Apply'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(currentUserProvider);

    if (user == null) {
      return const Scaffold(
        body: Center(
          child: Text('Please log in to view cases'),
        ),
      );
    }

    final caseListState = ref.watch(caseListProvider(user.email));
    final hasReachedLimit = user.hasReachedFreeLimit(AppConstants.freeCaseLimit);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Cases'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
            tooltip: 'Filter',
          ),
          IconButton(
            icon: const Icon(Icons.sync),
            onPressed: () {
              ref.read(caseListProvider(user.email).notifier).syncCases();
            },
            tooltip: 'Sync',
          ),
        ],
      ),
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search procedures...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: _clearFilters,
                      )
                    : null,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onChanged: (value) {
                setState(() {});
              },
              onSubmitted: (value) => _performSearch(),
            ),
          ),

          // Case count and filter chips
          if (_selectedAsaFilter != null || _selectedSurgeryClassFilter != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Row(
                children: [
                  if (_selectedAsaFilter != null)
                    Chip(
                      label: Text('ASA: $_selectedAsaFilter'),
                      onDeleted: () {
                        setState(() {
                          _selectedAsaFilter = null;
                        });
                        _performSearch();
                      },
                    ),
                  if (_selectedSurgeryClassFilter != null) ...[
                    const SizedBox(width: 8),
                    Chip(
                      label: Text('Class: $_selectedSurgeryClassFilter'),
                      onDeleted: () {
                        setState(() {
                          _selectedSurgeryClassFilter = null;
                        });
                        _performSearch();
                      },
                    ),
                  ],
                ],
              ),
            ),

          // Premium limit warning
          if (hasReachedLimit)
            Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.orange.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.orange),
              ),
              child: Row(
                children: [
                  Icon(Icons.lock, color: Colors.orange.shade700),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Free case limit reached. Upgrade to add more cases.',
                      style: TextStyle(color: Colors.orange.shade900),
                    ),
                  ),
                ],
              ),
            ),

          // Case list
          Expanded(
            child: caseListState.isLoading
                ? const Center(child: CircularProgressIndicator())
                : caseListState.error != null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.error_outline,
                                size: 48, color: Colors.red),
                            const SizedBox(height: 16),
                            Text(caseListState.error!),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: () {
                                ref
                                    .read(caseListProvider(user.email)
                                        .notifier)
                                    .loadCases();
                              },
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      )
                    : caseListState.cases.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.folder_open,
                                    size: 64, color: Colors.grey[400]),
                                const SizedBox(height: 16),
                                Text(
                                  'No cases yet',
                                  style: Theme.of(context).textTheme.titleLarge,
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Tap + to add your first case',
                                  style: TextStyle(color: Colors.grey[600]),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: () async {
                              await ref
                                  .read(caseListProvider(user.email)
                                      .notifier)
                                  .loadCases();
                            },
                            child: ListView.builder(
                              itemCount: caseListState.cases.length,
                              itemBuilder: (context, index) {
                                final caseItem = caseListState.cases[index];
                                return Card(
                                  margin: const EdgeInsets.symmetric(
                                    horizontal: 16,
                                    vertical: 8,
                                  ),
                                  child: ListTile(
                                    leading: CircleAvatar(
                                      child: Text(caseItem.asaClassification),
                                    ),
                                    title: Text(
                                      caseItem.procedureSurgery,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    subtitle: Text(
                                      '${DateFormat.yMMMd().format(caseItem.date)} • ${caseItem.anestheticPlan}',
                                    ),
                                    trailing: const Icon(Icons.chevron_right),
                                    onTap: () {
                                      Navigator.of(context).push(
                                        MaterialPageRoute(
                                          builder: (_) => CaseDetailScreen(
                                            caseId: caseItem.objectId,
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: hasReachedLimit
            ? () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text(
                        'Please upgrade to premium to add more cases'),
                  ),
                );
              }
            : () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => const CaseCreationFlowScreen(),
                  ),
                ).then((_) {
                  // Reload cases after returning
                  ref.read(caseListProvider(user.email).notifier).loadCases();
                });
              },
        child: const Icon(Icons.add),
      ),
    );
  }
}
