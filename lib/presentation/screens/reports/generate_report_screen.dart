import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/glow_button.dart';
import 'chart_builder_screen.dart';
import 'cme_table_screen.dart' show CMETableScreen;
import 'report_preview_screen.dart';

/// Generate Report Screen - Flutter equivalent of generateReportVController
/// Allows users to select date range and report options before generating reports
class GenerateReportScreen extends ConsumerStatefulWidget {
  const GenerateReportScreen({super.key});

  @override
  ConsumerState<GenerateReportScreen> createState() => _GenerateReportScreenState();
}

class _GenerateReportScreenState extends ConsumerState<GenerateReportScreen> {
  DateTime? _startDate;
  DateTime? _endDate;
  bool _isDateRangeSet = false;

  // Checkbox states
  bool _includeSurgeryType = false;
  bool _includePrimaryAnesthetic = false;
  bool _includeSecondaryAnesthetic = false;
  bool _includeSkilledProcedures = false;
  bool _includeSurgeon = false;
  bool _includeFacility = false;
  bool _includeASA = false;

  final DateFormat _dateFormat = DateFormat('M/d/yyyy');

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'JCL Reporting',
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
              switch (value) {
                case 'charts':
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const ChartBuilderScreen(),
                    ),
                  );
                  break;
                case 'cme':
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const CMETableScreen(),
                    ),
                  );
                  break;
              }
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'charts',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/pie-chart-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Charts',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'cme',
                child: Row(
                  children: [
                    Image.asset(
                      'assets/images/health-report-30.png',
                      width: 20,
                      height: 20,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'Continuing Medical Ed.',
                      style: TextStyle(color: AppColors.jclGray),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: GestureDetector(
        onTap: () {
          // Dismiss keyboard when tapping outside
          FocusScope.of(context).unfocus();
        },
        child: SingleChildScrollView(
          child: Center(
            child: Column(
              children: [
                const SizedBox(height: 30),

                // Start Date Field
                SizedBox(
                  width: 300,
                  height: 34,
                  child: GestureDetector(
                    onTap: _selectStartDate,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.jclWhite,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        _startDate == null
                            ? 'Start Date'
                            : _dateFormat.format(_startDate!),
                        style: TextStyle(
                          color: _startDate == null
                              ? AppColors.jclGray.withAlpha(128)
                              : AppColors.jclGray,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 6),

                // End Date Field
                SizedBox(
                  width: 300,
                  height: 34,
                  child: GestureDetector(
                    onTap: _selectEndDate,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.jclWhite,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        _endDate == null
                            ? 'End Date'
                            : _dateFormat.format(_endDate!),
                        style: TextStyle(
                          color: _endDate == null
                              ? AppColors.jclGray.withAlpha(128)
                              : AppColors.jclGray,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 21),

                // Set Date Range Button with Glow
                GlowButtonSmall(
                  text: 'Set Date Range',
                  onPressed: _setDateRange,
                  isPrimary: true,
                  icon: Icons.calendar_today,
                ),

                const SizedBox(height: 30),

                // Checkboxes Section
                _buildCheckboxRow('Type of Surgery', _includeSurgeryType, (value) {
                  setState(() => _includeSurgeryType = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('Primary Anesthetic', _includePrimaryAnesthetic, (value) {
                  setState(() => _includePrimaryAnesthetic = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('Secondary Anesthetic', _includeSecondaryAnesthetic, (value) {
                  setState(() => _includeSecondaryAnesthetic = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('Skilled Procedures', _includeSkilledProcedures, (value) {
                  setState(() => _includeSkilledProcedures = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('Surgeon', _includeSurgeon, (value) {
                  setState(() => _includeSurgeon = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('Facility', _includeFacility, (value) {
                  setState(() => _includeFacility = value ?? false);
                }),

                const SizedBox(height: 24),

                _buildCheckboxRow('ASA', _includeASA, (value) {
                  setState(() => _includeASA = value ?? false);
                }),

                const SizedBox(height: 24),

                // Get Report Button with Glow (only enabled when date range is set)
                if (_isDateRangeSet)
                  GlowButtonSmall(
                    text: 'Get Report',
                    onPressed: _getReport,
                    isPrimary: true,
                    icon: Icons.description,
                  )
                else
                  Opacity(
                    opacity: 0.5,
                    child: GlowButtonSmall(
                      text: 'Get Report',
                      onPressed: () {}, // Disabled
                      isPrimary: true,
                      icon: Icons.description,
                    ),
                  ),

                const SizedBox(height: 50),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildCheckboxRow(String label, bool value, Function(bool?) onChanged) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        SizedBox(
          width: 200,
          child: Text(
            label,
            style: const TextStyle(
              color: AppColors.jclWhite,
              fontSize: 16,
            ),
            textAlign: TextAlign.right,
          ),
        ),
        const SizedBox(width: 16),
        SizedBox(
          width: 24,
          height: 24,
          child: Checkbox(
            value: value,
            onChanged: onChanged,
            fillColor: MaterialStateProperty.resolveWith<Color>(
              (Set<MaterialState> states) {
                if (states.contains(MaterialState.selected)) {
                  return AppColors.jclOrange;
                }
                return AppColors.jclWhite;
              },
            ),
            checkColor: AppColors.jclWhite,
          ),
        ),
      ],
    );
  }

  Future<void> _selectStartDate() async {
    DateTime tempDate = _startDate ?? DateTime.now();

    await showCupertinoModalPopup<void>(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              // Header with Done button
              Container(
                height: 50,
                decoration: const BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: AppColors.jclGray, width: 0.5),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const SizedBox(width: 70),
                    const Text(
                      'Start Date',
                      style: TextStyle(
                        color: AppColors.jclGray,
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    CupertinoButton(
                      child: const Text(
                        'Done',
                        style: TextStyle(color: AppColors.jclOrange),
                      ),
                      onPressed: () {
                        Navigator.of(context).pop();
                        setState(() {
                          _startDate = tempDate;
                          _isDateRangeSet = false;
                        });
                      },
                    ),
                  ],
                ),
              ),
              // Date picker
              Expanded(
                child: CupertinoDatePicker(
                  mode: CupertinoDatePickerMode.date,
                  initialDateTime: tempDate,
                  minimumDate: DateTime(2000),
                  maximumDate: DateTime.now(),
                  onDateTimeChanged: (DateTime newDate) {
                    tempDate = newDate;
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _selectEndDate() async {
    DateTime tempDate = _endDate ?? DateTime.now();

    await showCupertinoModalPopup<void>(
      context: context,
      builder: (BuildContext context) {
        return Container(
          height: 300,
          color: AppColors.jclWhite,
          child: Column(
            children: [
              // Header with Done button
              Container(
                height: 50,
                decoration: const BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: AppColors.jclGray, width: 0.5),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const SizedBox(width: 70),
                    const Text(
                      'End Date',
                      style: TextStyle(
                        color: AppColors.jclGray,
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    CupertinoButton(
                      child: const Text(
                        'Done',
                        style: TextStyle(color: AppColors.jclOrange),
                      ),
                      onPressed: () {
                        Navigator.of(context).pop();
                        setState(() {
                          _endDate = tempDate;
                          _isDateRangeSet = false;
                        });
                      },
                    ),
                  ],
                ),
              ),
              // Date picker
              Expanded(
                child: CupertinoDatePicker(
                  mode: CupertinoDatePickerMode.date,
                  initialDateTime: tempDate,
                  minimumDate: DateTime(2000),
                  maximumDate: DateTime.now(),
                  onDateTimeChanged: (DateTime newDate) {
                    tempDate = newDate;
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _setDateRange() {
    // Validate that both dates are selected
    if (_startDate == null || _endDate == null) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Invalid Date Range'),
          content: const Text('Please select both start and end dates.'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              style: TextButton.styleFrom(
                foregroundColor: AppColors.jclOrange,
              ),
              child: const Text('OK'),
            ),
          ],
        ),
      );
      return;
    }

    // Validate that start date is before end date
    if (_startDate!.isAfter(_endDate!)) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Invalid Date Range'),
          content: const Text('Start date must be before end date.'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              style: TextButton.styleFrom(
                foregroundColor: AppColors.jclOrange,
              ),
              child: const Text('OK'),
            ),
          ],
        ),
      );
      return;
    }

    // Set the date range flag to enable Get Report button - no dialog shown here
    setState(() {
      _isDateRangeSet = true;
    });
  }

  Future<void> _getReport() async {
    // Validate that dates are actually set
    if (_startDate == null || _endDate == null) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Invalid Date Range'),
          content: const Text('Please select and set a date range first.'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              style: TextButton.styleFrom(
                foregroundColor: AppColors.jclOrange,
              ),
              child: const Text('OK'),
            ),
          ],
        ),
      );
      return;
    }

    // Collect selected report options
    final selectedOptions = {
      'Surgery Type': _includeSurgeryType,
      'Primary Anesthetic': _includePrimaryAnesthetic,
      'Secondary Anesthetic': _includeSecondaryAnesthetic,
      'Skilled Procedures': _includeSkilledProcedures,
      'Surgeon': _includeSurgeon,
      'Facility': _includeFacility,
      'ASA': _includeASA,
    };

    print('=== Starting report generation ===');
    print('Start date: $_startDate');
    print('End date: $_endDate');
    print('Selected options: $selectedOptions');

    // Show loading indicator
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(color: AppColors.jclOrange),
            SizedBox(width: 20),
            Text('Loading cases...'),
          ],
        ),
      ),
    );

    bool dialogShowing = true;

    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      print('Querying cases for user: ${user.email}');

      // Query Parse Server for cases in date range
      // Note: 'dateTime' is the actual Date object field, 'surgeryDateTime' is just a formatted string
      final query = QueryBuilder<ParseObject>(ParseObject('jclCases'))
        ..whereEqualTo('userEmail', user.email)
        ..whereGreaterThanOrEqualsTo('dateTime', _startDate)
        ..whereLessThanOrEqualTo('dateTime', _endDate!.add(const Duration(days: 1)))
        ..orderByDescending('dateTime')
        ..setLimit(3000); // Match iOS pagination limit

      print('Executing Parse query...');
      print('Query date range: ${_startDate} to ${_endDate!.add(const Duration(days: 1))}');
      final response = await query.query();

      print('Query response: success=${response.success}, results count=${response.results?.length ?? 0}');
      if (!response.success) {
        print('Query error: ${response.error?.message}');
      }

      if (!mounted) {
        print('Widget not mounted, aborting');
        return;
      }

      // Close loading dialog
      Navigator.of(context).pop();
      dialogShowing = false;

      if (response.success && response.results != null) {
        final cases = <Map<String, dynamic>>[];

        print('Processing ${response.results!.length} result objects...');
        for (var obj in response.results!) {
          final caseObj = obj as ParseObject;
          // Use 'dateTime' (Date object) instead of 'surgeryDateTime' (string)
          final dateTime = caseObj.get<DateTime>('dateTime');

          cases.add({
            'surgeryDateTime': dateTime, // Pass as surgeryDateTime for display
            'facilityName': caseObj.get<String>('facilityName'),
            'surgeonName': caseObj.get<String>('surgeonName'),
            'surgeryCategory': caseObj.get<String>('surgeryCategory'),
            'surgery': caseObj.get<String>('surgery'),
            'primePlan': caseObj.get<String>('primePlan'),
            'secPlan': caseObj.get<String>('secPlan'),
            'asaPlan': caseObj.get<String>('asaPlan'),
            'skillsArry': caseObj.get<List<dynamic>>('skillsArry'),
          });
        }

        print('Successfully processed ${cases.length} cases');
        print('Navigating to report preview...');

        // Navigate to report preview
        if (mounted) {
          await Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ReportPreviewScreen(
                startDate: _startDate!,
                endDate: _endDate!,
                cases: cases,
                selectedOptions: selectedOptions,
              ),
            ),
          );
          print('Returned from report preview');
        }
      } else {
        throw Exception(response.error?.message ?? 'Failed to load cases');
      }
    } catch (e, stackTrace) {
      print('=== ERROR in _getReport ===');
      print('Error: $e');
      print('Stack trace: $stackTrace');

      if (!mounted) {
        print('Widget not mounted during error handling');
        return;
      }

      // Close loading dialog if still showing
      if (dialogShowing) {
        try {
          Navigator.of(context).pop();
          print('Closed loading dialog');
        } catch (navError) {
          print('Could not close loading dialog: $navError');
        }
      }

      // Show error dialog
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Error'),
            content: Text('Failed to generate report:\n\n$e'),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(),
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.jclOrange,
                ),
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    }
  }
}
