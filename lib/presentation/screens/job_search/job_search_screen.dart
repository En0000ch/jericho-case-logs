import 'package:flutter/material.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';

/// Job Search Screen - Flutter equivalent of jobSearchTableVController
/// Allows searching for anesthesia jobs with profession, type, and location filters
class JobSearchScreen extends StatefulWidget {
  const JobSearchScreen({super.key});

  @override
  State<JobSearchScreen> createState() => _JobSearchScreenState();
}

class _JobSearchScreenState extends State<JobSearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> _allJobs = [];
  List<Map<String, dynamic>> _filteredJobs = [];
  bool _isLoading = false;
  bool _showFilterOverlay = false;

  // Filter selections
  String? _selectedProfession;
  String? _selectedType;
  String? _selectedState;

  // Filter options
  final List<String> _professionOptions = ['CRNA', 'AA', 'Anesthesiologist'];
  final List<String> _typeOptions = ['Locum', 'Permanent', 'Both/Either'];
  final Map<String, String> _stateAbbreviations = {
    'Alabama': 'AL', 'Alaska': 'AK', 'Arizona': 'AZ', 'Arkansas': 'AR', 'California': 'CA',
    'Colorado': 'CO', 'Connecticut': 'CT', 'Delaware': 'DE', 'Florida': 'FL', 'Georgia': 'GA',
    'Hawaii': 'HI', 'Idaho': 'ID', 'Illinois': 'IL', 'Indiana': 'IN', 'Iowa': 'IA',
    'Kansas': 'KS', 'Kentucky': 'KY', 'Louisiana': 'LA', 'Maine': 'ME', 'Maryland': 'MD',
    'Massachusetts': 'MA', 'Michigan': 'MI', 'Minnesota': 'MN', 'Mississippi': 'MS', 'Missouri': 'MO',
    'Montana': 'MT', 'Nebraska': 'NE', 'Nevada': 'NV', 'New Hampshire': 'NH', 'New Jersey': 'NJ',
    'New Mexico': 'NM', 'New York': 'NY', 'North Carolina': 'NC', 'North Dakota': 'ND', 'Ohio': 'OH',
    'Oklahoma': 'OK', 'Oregon': 'OR', 'Pennsylvania': 'PA', 'Rhode Island': 'RI', 'South Carolina': 'SC',
    'South Dakota': 'SD', 'Tennessee': 'TN', 'Texas': 'TX', 'Utah': 'UT', 'Vermont': 'VT',
    'Virginia': 'VA', 'Washington': 'WA', 'West Virginia': 'WV', 'Wisconsin': 'WI', 'Wyoming': 'WY',
  };

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_filterJobs);
    _loadJobs();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadJobs() async {
    setState(() => _isLoading = true);
    try {
      final query = QueryBuilder<ParseObject>(ParseObject('jclJobs'));

      // Apply filters if any
      if (_selectedProfession != null && _selectedProfession != 'All Professions') {
        query.whereEqualTo('jclProf', _selectedProfession);
      }
      if (_selectedType != null && _selectedType != 'All Types') {
        final type = _selectedType == 'Both/Either' ? 'Both' : _selectedType;
        query.whereEqualTo('jTypeString', type);
      }
      if (_selectedState != null && _selectedState != 'All Locations') {
        query.whereEqualTo('jobState', _selectedState);
      }

      final response = await query.query();

      if (response.success && response.results != null) {
        final jobs = <Map<String, dynamic>>[];

        for (var obj in response.results!) {
          final jobObj = obj as ParseObject;
          jobs.add({
            'jclProf': jobObj.get<String>('jclProf') ?? '',
            'jobTitle': jobObj.get<String>('jobTitle') ?? '',
            'reqNum': jobObj.get<String>('reqNum') ?? '',
            'assignmntDates': jobObj.get<String>('assignmntDates') ?? '',
            'jobCity': jobObj.get<String>('jobCity') ?? '',
            'jobState': jobObj.get<String>('jobState') ?? '',
            'facName': jobObj.get<String>('facName') ?? '',
            'facContactName': jobObj.get<String>('facContactName') ?? '',
            'facContactPhone': jobObj.get<String>('facContactPhone') ?? '',
            'facContactEmail': jobObj.get<String>('facContactEmail') ?? '',
            'jTypeString': jobObj.get<String>('jTypeString') ?? '',
            'jStatusString': jobObj.get<String>('jStatusString') ?? '',
            'durationString': jobObj.get<String>('durationString') ?? '',
            'jobDescText': jobObj.get<String>('jobDescText') ?? '',
            'clientID': jobObj.get<String>('clientID') ?? '',
            'clientName': jobObj.get<String>('clientName') ?? '',
            'clientEmail': jobObj.get<String>('clientEmail') ?? '',
            'startDate': jobObj.get<DateTime>('startDate'),
            'endDate': jobObj.get<DateTime>('endDate'),
            'emBOOL': jobObj.get<bool>('emBOOL') ?? false,
          });
        }

        setState(() {
          _allJobs = jobs;
          _filteredJobs = jobs;
        });
      }
    } catch (e) {
      debugPrint('Error loading jobs: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _filterJobs() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      if (query.isEmpty) {
        _filteredJobs = _allJobs;
      } else {
        _filteredJobs = _allJobs.where((job) {
          final title = (job['jobTitle'] as String).toLowerCase();
          final city = (job['jobCity'] as String).toLowerCase();
          final state = (job['jobState'] as String).toLowerCase();
          final desc = (job['jobDescText'] as String).toLowerCase();
          final type = (job['jTypeString'] as String).toLowerCase();
          return title.contains(query) ||
              city.contains(query) ||
              state.contains(query) ||
              desc.contains(query) ||
              type.contains(query);
        }).toList();
      }
    });
  }

  void _showFilters() {
    setState(() => _showFilterOverlay = true);
  }

  void _hideFilters() {
    setState(() => _showFilterOverlay = false);
  }

  void _applyFilters() {
    _hideFilters();
    _loadJobs();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'Job Search',
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
            icon: const Icon(Icons.filter_list, color: AppColors.jclWhite),
            onPressed: _showFilters,
          ),
        ],
      ),
      body: Stack(
        children: [
          Column(
            children: [
              // Search bar
              Container(
                padding: const EdgeInsets.all(16),
                color: AppColors.jclOrange,
                child: TextField(
                  controller: _searchController,
                  autocorrect: false,
                  enableSuggestions: false,
                  style: const TextStyle(color: AppColors.jclWhite),
                  decoration: InputDecoration(
                    hintText: 'Search jobs...',
                    hintStyle: TextStyle(color: AppColors.jclWhite.withOpacity(0.7)),
                    prefixIcon: const Icon(Icons.search, color: AppColors.jclWhite),
                    filled: true,
                    fillColor: Colors.white.withOpacity(0.2),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
              ),
              // Jobs list
              Expanded(
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(color: AppColors.jclOrange),
                      )
                    : _filteredJobs.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.work_off,
                                  size: 64,
                                  color: AppColors.jclGray.withOpacity(0.3),
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  'No jobs found',
                                  style: TextStyle(
                                    color: AppColors.jclGray.withOpacity(0.5),
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _loadJobs,
                            color: AppColors.jclOrange,
                            child: ListView.separated(
                              padding: const EdgeInsets.all(8),
                              itemCount: _filteredJobs.length,
                              separatorBuilder: (context, index) => const Divider(height: 1),
                              itemBuilder: (context, index) {
                                final job = _filteredJobs[index];
                                final stateAbbr = job['jobState'] as String;
                                return Container(
                                  margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: AppColors.jclWhite,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: AppColors.jclGray.withOpacity(0.2),
                                      width: 1,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withOpacity(0.05),
                                        blurRadius: 2,
                                        offset: const Offset(0, 1),
                                      ),
                                    ],
                                  ),
                                  child: ListTile(
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                    leading: stateAbbr.isNotEmpty
                                        ? Container(
                                            width: 48,
                                            height: 48,
                                            decoration: BoxDecoration(
                                              borderRadius: BorderRadius.circular(4),
                                              border: Border.all(
                                                color: AppColors.jclGray.withOpacity(0.2),
                                                width: 1,
                                              ),
                                            ),
                                            child: ClipRRect(
                                              borderRadius: BorderRadius.circular(4),
                                              child: Image.asset(
                                                'assets/images/$stateAbbr.png',
                                                fit: BoxFit.cover,
                                                errorBuilder: (context, error, stackTrace) {
                                                  return Container(
                                                    color: AppColors.jclGray.withOpacity(0.1),
                                                    child: Center(
                                                      child: Text(
                                                        stateAbbr,
                                                        style: TextStyle(
                                                          fontSize: 12,
                                                          color: AppColors.jclGray.withOpacity(0.5),
                                                          fontWeight: FontWeight.w600,
                                                        ),
                                                      ),
                                                    ),
                                                  );
                                                },
                                              ),
                                            ),
                                          )
                                        : null,
                                    title: Text(
                                      '${job['jobTitle']} - ${job['jTypeString']}',
                                      style: const TextStyle(
                                        color: AppColors.jclGray,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 16,
                                      ),
                                    ),
                                    subtitle: Padding(
                                      padding: const EdgeInsets.only(top: 6),
                                      child: Text(
                                        '${job['jobCity']}, ${job['jobState']} | ${job['assignmntDates']}',
                                        style: TextStyle(
                                          color: AppColors.jclGray.withOpacity(0.85),
                                          fontSize: 14,
                                        ),
                                      ),
                                    ),
                                    trailing: const Icon(
                                      Icons.arrow_forward_ios,
                                      size: 18,
                                      color: AppColors.jclOrange,
                                    ),
                                    onTap: () => _showJobDetails(job),
                                  ),
                                );
                              },
                            ),
                          ),
              ),
            ],
          ),
          // Filter overlay
          if (_showFilterOverlay)
            GestureDetector(
              onTap: _hideFilters,
              child: Container(
                color: Colors.black.withOpacity(0.5),
                child: Center(
                  child: GestureDetector(
                    onTap: () {}, // Prevent closing when tapping inside
                    child: Container(
                      margin: const EdgeInsets.symmetric(horizontal: 20),
                      padding: const EdgeInsets.all(20),
                      constraints: const BoxConstraints(maxHeight: 450),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Done button in top right
                          Row(
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              TextButton(
                                onPressed: _applyFilters,
                                child: const Text(
                                  'Done',
                                  style: TextStyle(
                                    color: AppColors.jclOrange,
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          // Profession section
                          _buildCheckboxSection('Profession', _professionOptions, _selectedProfession, (value) {
                            setState(() => _selectedProfession = value);
                          }),
                          const Divider(height: 30, color: Colors.grey),
                          // Job Type section
                          _buildCheckboxSection('Job Type', _typeOptions, _selectedType, (value) {
                            setState(() => _selectedType = value);
                          }),
                          const Divider(height: 30, color: Colors.grey),
                          // Location section
                          _buildLocationFilter(),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCheckboxSection(String title, List<String> options, String? selected, Function(String) onSelect) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: AppColors.jclGray,
          ),
        ),
        const SizedBox(height: 10),
        ...options.map((option) => GestureDetector(
              onTap: () => onSelect(option),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 5),
                child: Row(
                  children: [
                    Image.asset(
                      selected == option
                          ? 'assets/images/checkBoxMarked.png'
                          : 'assets/images/checkBox.png',
                      width: 24,
                      height: 24,
                      errorBuilder: (context, error, stackTrace) {
                        // Fallback to icon if image not found
                        return Icon(
                          selected == option ? Icons.check_box : Icons.check_box_outline_blank,
                          color: AppColors.jclOrange,
                          size: 24,
                        );
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        option,
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppColors.jclGray,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            )),
      ],
    );
  }

  Widget _buildLocationFilter() {
    // Get the state name from abbreviation for display
    String getStateName(String? abbr) {
      if (abbr == null || abbr.isEmpty) return 'Pick Location';
      for (var entry in _stateAbbreviations.entries) {
        if (entry.value == abbr) return entry.key;
      }
      return 'Pick Location';
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Location',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: AppColors.jclGray,
          ),
        ),
        const SizedBox(height: 10),
        GestureDetector(
          onTap: () {
            showModalBottomSheet(
              context: context,
              builder: (context) => Container(
                height: 300,
                color: Colors.white,
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: const BoxDecoration(
                        color: AppColors.jclOrange,
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(12),
                          topRight: Radius.circular(12),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Spacer(),
                          const Text(
                            'Select State',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Spacer(),
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: const Text(
                              'Done',
                              style: TextStyle(color: Colors.white),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: ListView(
                        children: _stateAbbreviations.entries.map((entry) {
                          return ListTile(
                            title: Text(entry.key),
                            onTap: () {
                              setState(() => _selectedState = entry.value);
                              Navigator.pop(context);
                            },
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey),
              borderRadius: BorderRadius.circular(5),
            ),
            child: Text(
              getStateName(_selectedState),
              style: TextStyle(
                fontSize: 14,
                color: _selectedState == null ? Colors.grey : AppColors.jclGray,
              ),
            ),
          ),
        ),
      ],
    );
  }

  void _showJobDetails(Map<String, dynamic> job) {
    final bool isEmergent = job['emBOOL'] as bool? ?? false;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(job['jobTitle']),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow('Type', job['jTypeString']),
              _buildDetailRow('Location', '${job['jobCity']}, ${job['jobState']}'),
              _buildDetailRow('Dates', job['assignmntDates']),
              // Emergent indicator
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 80,
                      child: Text(
                        'Emergent:',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppColors.jclOrange,
                        ),
                      ),
                    ),
                    Image.asset(
                      isEmergent
                          ? 'assets/images/sirenOn.gif'
                          : 'assets/images/sirenOff.png',
                      width: 40,
                      height: 40,
                      errorBuilder: (context, error, stackTrace) {
                        return Text(isEmergent ? 'YES' : 'NO');
                      },
                    ),
                  ],
                ),
              ),
              _buildDetailRow('Facility', job['facName']),
              _buildDetailRow('Duration', job['durationString']),
              _buildDetailRow('Status', job['jStatusString']),
              if (job['jobDescText'].isNotEmpty) ...[
                const SizedBox(height: 12),
                const Text(
                  'Description:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(job['jobDescText']),
              ],
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
