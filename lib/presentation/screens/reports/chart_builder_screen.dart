import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';
import '../../../domain/entities/case.dart';
import '../../providers/auth_provider.dart';

class ChartBuilderScreen extends ConsumerStatefulWidget {
  const ChartBuilderScreen({super.key});

  @override
  ConsumerState<ChartBuilderScreen> createState() => _ChartBuilderScreenState();
}

class _ChartBuilderScreenState extends ConsumerState<ChartBuilderScreen> {
  String _selectedTimeRange = 'Lifetime';
  String _selectedChartType = 'Specialties';
  List<Case> _cases = [];
  bool _isLoading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadCases();
    });
  }

  Future<void> _loadCases() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      final query = QueryBuilder<ParseObject>(ParseObject('jclCases'))
        ..whereEqualTo('userEmail', user.email)
        ..orderByDescending('dateTime');

      final response = await query.query();

      if (response.success && response.results != null) {
        final cases = <Case>[];
        for (final obj in response.results!) {
          final parseObj = obj as ParseObject;

          // Get date from iOS 'dateTime' field or Flutter 'date' field
          final dateTime = parseObj.get<DateTime>('dateTime') ??
                          parseObj.get<DateTime>('date') ??
                          DateTime.now();

          // Get surgeryCategory (iOS) or surgeryClass (Flutter)
          final surgeryClass = parseObj.get<String>('surgeryCategory') ??
                              parseObj.get<String>('surgeryClass') ??
                              'General Surgery';

          // Get surgery name
          final surgery = parseObj.get<String>('surgery') ??
                         parseObj.get<String>('procSurgery') ??
                         'Unknown';

          // Get ASA plan
          final asaPlan = parseObj.get<String>('asaPlan') ??
                         parseObj.get<String>('asaClassification') ??
                         'I';

          // Get anesthetic plan
          final primePlan = parseObj.get<String>('primePlan') ??
                           parseObj.get<String>('anestheticPlan') ??
                           'General Anesthetic';

          cases.add(Case(
            objectId: parseObj.objectId ?? '',
            userEmail: user.email,
            date: dateTime,
            asaClassification: asaPlan,
            procedureSurgery: surgery,
            anestheticPlan: primePlan,
            anestheticsUsed: const [],
            surgeryClass: surgeryClass,
            createdAt: parseObj.createdAt ?? DateTime.now(),
            updatedAt: parseObj.updatedAt ?? DateTime.now(),
          ));
        }

        setState(() {
          _cases = cases;
          _isLoading = false;
        });
      } else {
        throw Exception(response.error?.message ?? 'Failed to load cases');
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Case> _getFilteredCases() {
    final now = DateTime.now();
    final currentYear = now.year.toString();
    final lastYear = (now.year - 1).toString();

    if (_selectedTimeRange == '7 Days') {
      final sevenDaysAgo = now.subtract(const Duration(days: 7));
      return _cases.where((c) => c.date.isAfter(sevenDaysAgo)).toList();
    } else if (_selectedTimeRange == '30 Days') {
      final thirtyDaysAgo = now.subtract(const Duration(days: 30));
      return _cases.where((c) => c.date.isAfter(thirtyDaysAgo)).toList();
    } else if (_selectedTimeRange == currentYear) {
      return _cases.where((c) => c.date.year == now.year).toList();
    } else if (_selectedTimeRange == lastYear) {
      final lastYearNum = now.year - 1;
      return _cases.where((c) => c.date.year == lastYearNum).toList();
    } else {
      // 'Lifetime' or default
      return _cases;
    }
  }

  /// Categorize anesthetic types into 4 main categories matching iOS logic
  /// Returns: "Gen Anes", "TIVA", "MAC", or "Reg Anes"
  String _categorizeAnesthetic(String anesthetic) {
    if (anesthetic.startsWith('TIVA')) {
      return 'TIVA';
    } else if (anesthetic.startsWith('Gen. Anesthetic:')) {
      return 'Gen Anes';
    } else if (!anesthetic.startsWith('MAC')) {
      // Parse comma-separated components
      final components = anesthetic.split(',').map((e) => e.trim()).toList();
      if (components.contains('Gen Anes')) {
        return 'Gen Anes';
      } else if (components.contains('TIVA')) {
        return 'TIVA';
      } else if (components.contains('Reg Anes')) {
        return 'Reg Anes';
      } else {
        return 'Reg Anes'; // Default
      }
    } else {
      return anesthetic; // Returns "MAC"
    }
  }

  Map<String, int> _getChartData() {
    final filteredCases = _getFilteredCases();
    final Map<String, int> data = {};

    for (final caseItem in filteredCases) {
      String key;

      switch (_selectedChartType) {
        case 'Specialties':
          key = caseItem.surgeryClass;
          break;
        case 'ASA Classes':
          key = 'ASA ${caseItem.asaClassification}';
          break;
        case 'Anesthetics':
          // Categorize anesthetics into 4 main types matching iOS
          key = _categorizeAnesthetic(caseItem.anestheticPlan);
          break;
        case 'Cardiovascular Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('cardio')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Dental Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('dental')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'General Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('general')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Neurosurgery':
          if (caseItem.surgeryClass.toLowerCase().contains('neuro')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Obstetric/Gynecologic':
          if (caseItem.surgeryClass.toLowerCase().contains('obstetric') ||
              caseItem.surgeryClass.toLowerCase().contains('gynecologic')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Ophthalmic Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('ophthalmic')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Orthopedic':
          if (caseItem.surgeryClass.toLowerCase().contains('orthopedic')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Otolaryngology Head/Neck':
          if (caseItem.surgeryClass.toLowerCase().contains('otolaryngology') ||
              caseItem.surgeryClass.toLowerCase().contains('head') ||
              caseItem.surgeryClass.toLowerCase().contains('neck')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Out-of-Operating Room Procedures':
          if (caseItem.surgeryClass.toLowerCase().contains('out-of')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Pediatric Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('pediatric')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Plastics & Reconstructive':
          if (caseItem.surgeryClass.toLowerCase().contains('plastic') ||
              caseItem.surgeryClass.toLowerCase().contains('reconstructive')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Thoracic Surgery':
          if (caseItem.surgeryClass.toLowerCase().contains('thoracic')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        case 'Urology':
          if (caseItem.surgeryClass.toLowerCase().contains('urology')) {
            key = caseItem.procedureSurgery;
          } else {
            continue;
          }
          break;
        default:
          // Fallback for any unmatched types
          key = caseItem.surgeryClass;
      }

      data[key] = (data[key] ?? 0) + 1;
    }

    return data;
  }

  List<PieChartSectionData> _buildPieChartSections() {
    final data = _getChartData();
    if (data.isEmpty) return [];

    final total = data.values.reduce((a, b) => a + b);
    final colors = _generateColors(data.length);

    int index = 0;
    return data.entries.map((entry) {
      final percentage = (entry.value / total * 100).toStringAsFixed(1);
      final color = colors[index % colors.length];
      index++;

      return PieChartSectionData(
        value: entry.value.toDouble(),
        title: '$percentage%',
        radius: 100,
        titleStyle: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
        color: color,
      );
    }).toList();
  }

  List<Color> _generateColors(int count) {
    return [
      AppColors.jclOrange,
      Colors.blue,
      Colors.green,
      Colors.purple,
      Colors.amber,
      Colors.teal,
      Colors.pink,
      Colors.indigo,
      Colors.lime,
      Colors.cyan,
      Colors.deepOrange,
      Colors.lightGreen,
      Colors.deepPurple,
      Colors.brown,
    ];
  }

  Widget _buildLegend() {
    final data = _getChartData();
    if (data.isEmpty) return const SizedBox.shrink();

    final colors = _generateColors(data.length);

    return Column(
      children: data.entries.toList().asMap().entries.map((entry) {
        final index = entry.key;
        final dataEntry = entry.value;
        final color = colors[index % colors.length];

        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 4.0),
          child: Row(
            children: [
              Container(
                width: 16,
                height: 16,
                decoration: BoxDecoration(
                  color: color,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  '${dataEntry.key}: ${dataEntry.value}',
                  style: const TextStyle(fontSize: 14),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final currentYear = DateTime.now().year;
    final lastYear = currentYear - 1;

    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: Text(
          _selectedChartType,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: AppColors.jclWhite,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclOrange,
        foregroundColor: AppColors.jclWhite,
        leading: IconButton(
          icon: Image.asset(
            'assets/images/report-30.png',
            width: 24,
            height: 24,
            color: AppColors.jclWhite,
          ),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          PopupMenuButton<String>(
            icon: Image.asset(
              'assets/images/menu-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            color: AppColors.jclWhite,
            offset: const Offset(0, 50),
            onSelected: (value) {
              setState(() {
                _selectedChartType = value;
              });
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'Specialties',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/surgery-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Specialties',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'ASA Classes',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/asa-40.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'ASA Classes',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'Anesthetics',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/anesthesia-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Anesthetics',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              const PopupMenuDivider(),
              // Surgery Classes submenu
              PopupMenuItem(
                enabled: false,
                child: PopupMenuButton<String>(
                  color: AppColors.jclWhite,
                  offset: const Offset(0, 0),
                  child: const Row(
                    children: [
                      Text(
                        'Surgery Classes',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                      Spacer(),
                      Icon(Icons.arrow_right, color: AppColors.jclGray),
                    ],
                  ),
                  onSelected: (value) {
                    Navigator.of(context).pop(); // Close main menu
                    setState(() {
                      _selectedChartType = value;
                    });
                  },
                  itemBuilder: (context) => [
                    const PopupMenuItem(
                      value: 'Cardiovascular Surgery',
                      child: Text(
                        'Cardiovascular Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Dental Surgery',
                      child: Text(
                        'Dental Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'General Surgery',
                      child: Text(
                        'General Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Neurosurgery',
                      child: Text(
                        'Neurosurgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Obstetric/Gynecologic',
                      child: Text(
                        'Obstetric/Gynecologic',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Ophthalmic Surgery',
                      child: Text(
                        'Ophthalmic Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Orthopedic',
                      child: Text(
                        'Orthopedic',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Otolaryngology Head/Neck',
                      child: Text(
                        'Otolaryngology Head/Neck',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Out-of-Operating Room Procedures',
                      child: Text(
                        'Out-of-Operating Room Procedures',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Pediatric Surgery',
                      child: Text(
                        'Pediatric Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Plastics & Reconstructive',
                      child: Text(
                        'Plastics & Reconstructive',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Thoracic Surgery',
                      child: Text(
                        'Thoracic Surgery',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                    const PopupMenuItem(
                      value: 'Urology',
                      child: Text(
                        'Urology',
                        style: TextStyle(color: AppColors.jclGray),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text('Error: $_error'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadCases,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Pie Chart
                    Expanded(
                      child: SingleChildScrollView(
                        child: Column(
                          children: [
                            const SizedBox(height: 16),

                            // Pie Chart or No Data Message
                            _getFilteredCases().isEmpty || _getChartData().isEmpty
                                ? SizedBox(
                                    height: 270,
                                    child: Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.pie_chart, size: 64, color: Colors.grey[400]),
                                          const SizedBox(height: 16),
                                          Text(
                                            'No data for the time range',
                                            style: TextStyle(
                                              fontSize: 16,
                                              color: Colors.grey[600],
                                              fontWeight: FontWeight.w500,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  )
                                : SizedBox(
                                    height: 270,
                                    child: PieChart(
                                      PieChartData(
                                        sections: _buildPieChartSections(),
                                        sectionsSpace: 2,
                                        centerSpaceRadius: 40,
                                        borderData: FlBorderData(show: false),
                                      ),
                                    ),
                                  ),
                            const SizedBox(height: 16),

                            // Time Range Segmented Control (iOS style - always visible)
                            Container(
                              height: 30,
                              padding: const EdgeInsets.symmetric(horizontal: 8.0),
                              child: Row(
                                children: [
                                  Expanded(child: _buildTimeRangeButton('7 Days')),
                                  const SizedBox(width: 4),
                                  Expanded(child: _buildTimeRangeButton('30 Days')),
                                  const SizedBox(width: 4),
                                  Expanded(child: _buildTimeRangeButton(currentYear.toString())),
                                  const SizedBox(width: 4),
                                  Expanded(child: _buildTimeRangeButton(lastYear.toString())),
                                  const SizedBox(width: 4),
                                  Expanded(child: _buildTimeRangeButton('Lifetime')),
                                ],
                              ),
                            ),

                            // Label showing current selection (iOS style - right aligned)
                            Padding(
                              padding: const EdgeInsets.only(right: 16.0, top: 8.0, bottom: 8.0),
                              child: Align(
                                alignment: Alignment.centerRight,
                                child: Text(
                                  '$_selectedTimeRange Cases',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: AppColors.jclGrayLite,
                                  ),
                                  textAlign: TextAlign.right,
                                ),
                              ),
                            ),

                            // Legend/Table (Breakdown) - only show if data exists
                            if (_getFilteredCases().isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.all(16.0),
                                child: Card(
                                  child: Padding(
                                    padding: const EdgeInsets.all(16.0),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text(
                                          'Breakdown',
                                          style: TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(height: 12),
                                        _buildLegend(),
                                        const SizedBox(height: 12),
                                        const Divider(),
                                        Text(
                                          'Total Cases: ${_getFilteredCases().length}',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildTimeRangeButton(String label) {
    final isSelected = _selectedTimeRange == label;

    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedTimeRange = label;
        });
      },
      child: Container(
        height: 30,
        decoration: BoxDecoration(
          color: isSelected ? AppColors.jclOrange : Colors.grey[300],
          borderRadius: BorderRadius.circular(4),
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              color: isSelected ? AppColors.jclWhite : AppColors.jclGray,
              fontSize: 10,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ),
    );
  }
}
