import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';
import '../../providers/auth_provider.dart';
import 'cme_review_screen.dart';

class CMETableScreen extends ConsumerStatefulWidget {
  const CMETableScreen({super.key});

  @override
  ConsumerState<CMETableScreen> createState() => _CMETableScreenState();
}

class _CMETableScreenState extends ConsumerState<CMETableScreen> {
  List<Map<String, dynamic>> _cmeList = [];
  bool _isLoading = false;
  String? _error;
  int? _selectedYear;
  bool _hasShownYearPicker = false;
  double _totalCredits = 0.0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _showYearPicker();
    });
  }

  Future<void> _showYearPicker() async {
    final currentYear = DateTime.now().year;
    final years = List.generate(10, (index) => currentYear - index);

    // Find initial index (default to current year if not set)
    int initialIndex = _selectedYear != null
        ? years.indexOf(_selectedYear!)
        : 0;
    if (initialIndex == -1) initialIndex = 0;

    int tempSelectedIndex = initialIndex;

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
                      'Select Year',
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
                          _selectedYear = years[tempSelectedIndex];
                          _hasShownYearPicker = true;
                        });
                        _loadCMEData();
                      },
                    ),
                  ],
                ),
              ),
              // Year picker
              Expanded(
                child: CupertinoPicker(
                  scrollController: FixedExtentScrollController(
                    initialItem: initialIndex,
                  ),
                  itemExtent: 40,
                  onSelectedItemChanged: (int index) {
                    tempSelectedIndex = index;
                  },
                  children: years.map((year) {
                    return Center(
                      child: Text(
                        year.toString(),
                        style: const TextStyle(
                          fontSize: 20,
                          color: AppColors.jclGray,
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _loadCMEData() async {
    if (_selectedYear == null) return;

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final user = ref.read(currentUserProvider);
      if (user == null) {
        throw Exception('User not logged in');
      }

      // Query Parse Server for CME data
      // Note: iOS uses 'savedCME' class, but task specifies 'jclCME'
      final query = QueryBuilder<ParseObject>(ParseObject('savedCME'))
        ..whereEqualTo('userEmail', user.email);

      final response = await query.query();

      if (response.success && response.results != null) {
        final cmeList = <Map<String, dynamic>>[];
        double totalCredits = 0.0;

        for (final obj in response.results!) {
          final parseObj = obj as ParseObject;

          // Get end date and extract year
          final endDateString = parseObj.get<String>('endDate');
          if (endDateString != null && endDateString.isNotEmpty) {
            // Parse date format "M/d/yyyy"
            try {
              final parts = endDateString.split('/');
              if (parts.length == 3) {
                final year = int.parse(parts[2]);

                if (year == _selectedYear) {
                  final courseName = parseObj.get<String>('courseName') ?? '';
                  final instructorName = parseObj.get<String>('instructorName') ?? '';
                  final numberCredits = parseObj.get<String>('numberCredits') ?? '0';
                  final startDate = parseObj.get<String>('startDate') ?? '';
                  final accredNum = parseObj.get<String>('accredNum') ?? '';
                  final courseCost = parseObj.get<String>('courseCost') ?? '';
                  final courseLocation = parseObj.get<String>('courseLocation') ?? '';
                  final courseOverview = parseObj.get<String>('courseOverview') ?? '';
                  final cmeId = parseObj.objectId ?? '';

                  cmeList.add({
                    'courseName': courseName,
                    'instructorName': instructorName,
                    'numberCredits': numberCredits,
                    'startDate': startDate,
                    'endDate': endDateString,
                    'accredNum': accredNum,
                    'courseCost': courseCost,
                    'courseLocation': courseLocation,
                    'courseOverview': courseOverview,
                    'cmeId': cmeId,
                    'year': year,
                  });

                  // Add to total credits
                  totalCredits += double.tryParse(numberCredits) ?? 0.0;
                }
              }
            } catch (e) {
              print('Error parsing date: $endDateString - $e');
            }
          }
        }

        setState(() {
          _cmeList = cmeList;
          _totalCredits = totalCredits;
          _isLoading = false;
        });
      } else {
        throw Exception(response.error?.message ?? 'Failed to load CME data');
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _navigateToCMEReview(Map<String, dynamic> cmeData) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CMEReviewScreen(cmeData: cmeData),
      ),
    ).then((_) {
      // Reload data when returning from review screen
      _loadCMEData();
    });
  }

  void _navigateToAddCME() {
    // TODO: Navigate to CME builder screen when implemented
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('CME Builder coming soon!'),
        backgroundColor: AppColors.jclOrange,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: _selectedYear != null
            ? GestureDetector(
                onTap: _showYearPicker,
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('$_selectedYear'),
                    const SizedBox(width: 4),
                    const Icon(Icons.arrow_drop_down, size: 24),
                  ],
                ),
              )
            : const Text('Continuing Medical Education'),
        backgroundColor: AppColors.jclOrange,
        foregroundColor: AppColors.jclWhite,
        leading: IconButton(
          icon: Image.asset(
            'assets/images/home-30.png',
            width: 24,
            height: 24,
            color: AppColors.jclWhite,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(
            icon: Image.asset(
              'assets/images/add-30.png',
              width: 24,
              height: 24,
              color: AppColors.jclWhite,
            ),
            onPressed: _navigateToAddCME,
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
                        onPressed: _loadCMEData,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _selectedYear == null
                  ? const Center(child: Text('Please select a year'))
                  : RefreshIndicator(
                      onRefresh: _loadCMEData,
                      child: Column(
                        children: [
                          Expanded(
                            child: _cmeList.isEmpty
                                ? Center(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.school,
                                          size: 64,
                                          color: Colors.grey[400],
                                        ),
                                        const SizedBox(height: 16),
                                        const Text(
                                          'No CME courses found',
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(height: 8),
                                        Text(
                                          'for $_selectedYear',
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: Colors.grey[600],
                                          ),
                                        ),
                                      ],
                                    ),
                                  )
                                : ListView.builder(
                                    itemCount: _cmeList.length,
                                    padding: const EdgeInsets.all(16),
                                    itemBuilder: (context, index) {
                                      final cme = _cmeList[index];
                                      return Card(
                                        margin: const EdgeInsets.only(bottom: 12),
                                        elevation: 2,
                                        child: InkWell(
                                          onTap: () => _navigateToCMEReview(cme),
                                          borderRadius: BorderRadius.circular(8),
                                          child: Padding(
                                            padding: const EdgeInsets.all(16.0),
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Row(
                                                  crossAxisAlignment: CrossAxisAlignment.start,
                                                  children: [
                                                    Expanded(
                                                      child: Column(
                                                        crossAxisAlignment: CrossAxisAlignment.start,
                                                        children: [
                                                          Text(
                                                            'Course: ${cme['courseName']}',
                                                            style: const TextStyle(
                                                              fontSize: 14,
                                                              fontWeight: FontWeight.w600,
                                                            ),
                                                          ),
                                                          const SizedBox(height: 4),
                                                          Text(
                                                            'Instructor: ${cme['instructorName']}',
                                                            style: const TextStyle(
                                                              fontSize: 12,
                                                              color: AppColors.jclGray,
                                                            ),
                                                          ),
                                                        ],
                                                      ),
                                                    ),
                                                    const SizedBox(width: 12),
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(
                                                        horizontal: 12,
                                                        vertical: 6,
                                                      ),
                                                      decoration: BoxDecoration(
                                                        color: AppColors.jclOrange,
                                                        borderRadius: BorderRadius.circular(20),
                                                      ),
                                                      child: Text(
                                                        'Credits: ${cme['numberCredits']}',
                                                        style: const TextStyle(
                                                          fontSize: 12,
                                                          fontWeight: FontWeight.bold,
                                                          color: AppColors.jclWhite,
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                          ),
                          // Total Credits Footer
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: AppColors.cardBackground,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.1),
                                  blurRadius: 4,
                                  offset: const Offset(0, -2),
                                ),
                              ],
                            ),
                            child: SafeArea(
                              top: false,
                              child: Text(
                                'Total Credits: ${_totalCredits.toStringAsFixed(1)}',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.jclOrange,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
    );
  }
}
