import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../domain/entities/case.dart';

class ReportsScreen extends ConsumerStatefulWidget {
  const ReportsScreen({super.key});

  @override
  ConsumerState<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends ConsumerState<ReportsScreen> {
  @override
  Widget build(BuildContext context) {
    final user = ref.watch(currentUserProvider);

    if (user == null) {
      return const Scaffold(
        body: Center(
          child: Text('Please log in to view reports'),
        ),
      );
    }

    final caseListState = ref.watch(caseListProvider(user.email));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Reports & Analytics'),
        actions: [
          IconButton(
            icon: const Icon(Icons.picture_as_pdf),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('PDF export coming soon!'),
                ),
              );
            },
            tooltip: 'Export to PDF',
          ),
        ],
      ),
      body: caseListState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : caseListState.cases.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.bar_chart, size: 64, color: Colors.grey[400]),
                      const SizedBox(height: 16),
                      Text(
                        'No data to display',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Add cases to see analytics',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ],
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    // Summary Cards
                    _SummaryCards(cases: caseListState.cases),
                    const SizedBox(height: 24),

                    // ASA Classification Chart
                    _ChartCard(
                      title: 'Cases by ASA Classification',
                      child: _AsaClassificationChart(
                        cases: caseListState.cases,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Anesthetic Plan Chart
                    _ChartCard(
                      title: 'Cases by Anesthetic Plan',
                      child: _AnestheticPlanChart(
                        cases: caseListState.cases,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Surgery Class Chart
                    _ChartCard(
                      title: 'Cases by Surgery Class',
                      child: _SurgeryClassChart(
                        cases: caseListState.cases,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Cases Over Time
                    _ChartCard(
                      title: 'Cases Over Time (Last 30 Days)',
                      child: _CasesOverTimeChart(
                        cases: caseListState.cases,
                      ),
                    ),
                  ],
                ),
    );
  }
}

class _SummaryCards extends StatelessWidget {
  final List<Case> cases;

  const _SummaryCards({required this.cases});

  @override
  Widget build(BuildContext context) {
    final totalCases = cases.length;
    final casesWithComplications =
        cases.where((c) => c.complications == true).length;
    final uniqueProcedures =
        cases.map((c) => c.procedureSurgery).toSet().length;

    return Row(
      children: [
        Expanded(
          child: _SummaryCard(
            title: 'Total Cases',
            value: '$totalCases',
            icon: Icons.folder,
            color: Colors.blue,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _SummaryCard(
            title: 'Complications',
            value: '$casesWithComplications',
            icon: Icons.warning,
            color: Colors.orange,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _SummaryCard(
            title: 'Unique Procedures',
            value: '$uniqueProcedures',
            icon: Icons.medical_services,
            color: Colors.green,
          ),
        ),
      ],
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _SummaryCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: Theme.of(context).textTheme.bodySmall,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

class _ChartCard extends StatelessWidget {
  final String title;
  final Widget child;

  const _ChartCard({
    required this.title,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ),
    );
  }
}

class _AsaClassificationChart extends StatelessWidget {
  final List<Case> cases;

  const _AsaClassificationChart({required this.cases});

  @override
  Widget build(BuildContext context) {
    final asaCounts = <String, int>{};
    for (final c in cases) {
      asaCounts[c.asaClassification] =
          (asaCounts[c.asaClassification] ?? 0) + 1;
    }

    if (asaCounts.isEmpty) {
      return const SizedBox(
        height: 200,
        child: Center(child: Text('No data')),
      );
    }

    return SizedBox(
      height: 250,
      child: PieChart(
        PieChartData(
          sections: asaCounts.entries.map((entry) {
            final percentage =
                (entry.value / cases.length * 100).toStringAsFixed(1);
            return PieChartSectionData(
              value: entry.value.toDouble(),
              title: 'ASA ${entry.key}\n$percentage%',
              radius: 100,
              titleStyle: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            );
          }).toList(),
          sectionsSpace: 2,
          centerSpaceRadius: 40,
        ),
      ),
    );
  }
}

class _AnestheticPlanChart extends StatelessWidget {
  final List<Case> cases;

  const _AnestheticPlanChart({required this.cases});

  @override
  Widget build(BuildContext context) {
    final planCounts = <String, int>{};
    for (final c in cases) {
      planCounts[c.anestheticPlan] = (planCounts[c.anestheticPlan] ?? 0) + 1;
    }

    if (planCounts.isEmpty) {
      return const SizedBox(
        height: 200,
        child: Center(child: Text('No data')),
      );
    }

    final sortedPlans = planCounts.entries.toList()
      ..sort((a, b) => b.value.compareTo(a.value));

    return SizedBox(
      height: 300,
      child: BarChart(
        BarChartData(
          alignment: BarChartAlignment.spaceAround,
          maxY: sortedPlans.first.value.toDouble() * 1.2,
          barGroups: sortedPlans.asMap().entries.map((entry) {
            return BarChartGroupData(
              x: entry.key,
              barRods: [
                BarChartRodData(
                  toY: entry.value.value.toDouble(),
                  width: 20,
                  borderRadius: const BorderRadius.vertical(
                    top: Radius.circular(4),
                  ),
                ),
              ],
            );
          }).toList(),
          titlesData: FlTitlesData(
            show: true,
            bottomTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                getTitlesWidget: (value, meta) {
                  if (value.toInt() >= 0 &&
                      value.toInt() < sortedPlans.length) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Text(
                        sortedPlans[value.toInt()].key,
                        style: const TextStyle(fontSize: 10),
                      ),
                    );
                  }
                  return const Text('');
                },
              ),
            ),
            leftTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                reservedSize: 40,
                getTitlesWidget: (value, meta) {
                  return Text(
                    value.toInt().toString(),
                    style: const TextStyle(fontSize: 10),
                  );
                },
              ),
            ),
            topTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false),
            ),
            rightTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false),
            ),
          ),
          borderData: FlBorderData(show: false),
          gridData: const FlGridData(show: true),
        ),
      ),
    );
  }
}

class _SurgeryClassChart extends StatelessWidget {
  final List<Case> cases;

  const _SurgeryClassChart({required this.cases});

  @override
  Widget build(BuildContext context) {
    final classCounts = <String, int>{};
    for (final c in cases) {
      classCounts[c.surgeryClass] = (classCounts[c.surgeryClass] ?? 0) + 1;
    }

    if (classCounts.isEmpty) {
      return const SizedBox(
        height: 200,
        child: Center(child: Text('No data')),
      );
    }

    return SizedBox(
      height: 250,
      child: PieChart(
        PieChartData(
          sections: classCounts.entries.map((entry) {
            final percentage =
                (entry.value / cases.length * 100).toStringAsFixed(1);
            return PieChartSectionData(
              value: entry.value.toDouble(),
              title: '${entry.key}\n$percentage%',
              radius: 100,
              titleStyle: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            );
          }).toList(),
          sectionsSpace: 2,
          centerSpaceRadius: 40,
        ),
      ),
    );
  }
}

class _CasesOverTimeChart extends StatelessWidget {
  final List<Case> cases;

  const _CasesOverTimeChart({required this.cases});

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final thirtyDaysAgo = now.subtract(const Duration(days: 30));
    final recentCases =
        cases.where((c) => c.date.isAfter(thirtyDaysAgo)).toList();

    if (recentCases.isEmpty) {
      return const SizedBox(
        height: 200,
        child: Center(child: Text('No cases in last 30 days')),
      );
    }

    // Group by date
    final casesPerDay = <DateTime, int>{};
    for (final c in recentCases) {
      final dateOnly = DateTime(c.date.year, c.date.month, c.date.day);
      casesPerDay[dateOnly] = (casesPerDay[dateOnly] ?? 0) + 1;
    }

    final sortedDates = casesPerDay.keys.toList()..sort();

    return SizedBox(
      height: 250,
      child: LineChart(
        LineChartData(
          lineBarsData: [
            LineChartBarData(
              spots: sortedDates.asMap().entries.map((entry) {
                return FlSpot(
                  entry.key.toDouble(),
                  casesPerDay[entry.value]!.toDouble(),
                );
              }).toList(),
              isCurved: true,
              dotData: const FlDotData(show: true),
              belowBarData: BarAreaData(show: true, color: Colors.blue.withValues(alpha: 0.3)),
            ),
          ],
          titlesData: FlTitlesData(
            show: true,
            bottomTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                reservedSize: 30,
                getTitlesWidget: (value, meta) {
                  if (value.toInt() >= 0 &&
                      value.toInt() < sortedDates.length) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Text(
                        DateFormat('M/d').format(sortedDates[value.toInt()]),
                        style: const TextStyle(fontSize: 10),
                      ),
                    );
                  }
                  return const Text('');
                },
              ),
            ),
            leftTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                reservedSize: 40,
                getTitlesWidget: (value, meta) {
                  return Text(
                    value.toInt().toString(),
                    style: const TextStyle(fontSize: 10),
                  );
                },
              ),
            ),
            topTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false),
            ),
            rightTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false),
            ),
          ),
          borderData: FlBorderData(show: true),
          gridData: const FlGridData(show: true),
        ),
      ),
    );
  }
}
