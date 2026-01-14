import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../../core/themes/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../../core/utils/silo_resolver.dart';
import '../../../data/datasources/remote/job_reconciliation_service.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:intl/intl.dart';

/// Job Search Screen - Flutter equivalent of jobSearchTableVController
/// Allows searching for silo-specific jobs with profession, type, and location filters
class JobSearchScreen extends ConsumerStatefulWidget {
  const JobSearchScreen({super.key});

  @override
  ConsumerState<JobSearchScreen> createState() => _JobSearchScreenState();
}

class _JobSearchScreenState extends ConsumerState<JobSearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  final JobReconciliationService _jobService = JobReconciliationService();
  List<Map<String, dynamic>> _allJobs = [];
  List<Map<String, dynamic>> _filteredJobs = [];
  bool _isLoading = false;
  bool _showFilterOverlay = false;
  String? _userSilo;

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
    _initializeUserSilo();
  }

  /// Initialize user's silo and load jobs
  Future<void> _initializeUserSilo() async {
    final user = ref.read(currentUserProvider);
    if (user != null) {
      _userSilo = await SiloResolver.resolveEffectiveSilo(user);
      if (mounted) {
        _loadJobs();
      }
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadJobs() async {
    setState(() => _isLoading = true);
    try {
      // Fetch unified jobs from both jclJobs and JobPosting classes
      // CRITICAL: Filter by user's silo first
      // This ensures anesthesia users only see anesthesia jobs, nurses only see nurse jobs
      final unifiedJobs = await _jobService.fetchUnifiedJobs(
        siloFilter: _userSilo,
        limit: 100,
      );

      // Apply additional client-side filters
      var filteredJobs = unifiedJobs;

      if (_selectedProfession != null && _selectedProfession != 'All Professions') {
        filteredJobs = filteredJobs.where((job) {
          final jclProf = job['jclProf'] as String? ?? '';
          return jclProf == _selectedProfession;
        }).toList();
      }

      if (_selectedType != null && _selectedType != 'All Types') {
        final type = _selectedType == 'Both/Either' ? 'Both' : _selectedType;
        filteredJobs = filteredJobs.where((job) {
          final jobType = job['jTypeString'] as String? ?? '';
          return jobType == type || jobType.toLowerCase() == (type?.toLowerCase() ?? '');
        }).toList();
      }

      if (_selectedState != null && _selectedState != 'All Locations') {
        filteredJobs = filteredJobs.where((job) {
          final jobState = job['jobState'] as String? ?? '';
          return jobState == _selectedState;
        }).toList();
      }

      // Normalize job data to ensure all expected fields exist
      // IMPORTANT: Use pre-extracted data from reconciliation service first!
      final normalizedJobs = filteredJobs.map((job) {
        final originalObject = job['originalObject'] as ParseObject?;
        return {
          'jclProf': job['jclProf'] ?? originalObject?.get<String>('jclProf') ?? '',
          'jobTitle': job['jobTitle'] ?? '',
          'reqNum': job['requisitionNumber'] ?? originalObject?.get<String>('reqNum') ?? '',
          'assignmntDates': job['assignmntDates'] ?? originalObject?.get<String>('assignmntDates') ?? '',
          'jobCity': job['jobCity'] ?? '',
          'jobState': job['jobState'] ?? '',
          'facName': job['facilityName'] ?? originalObject?.get<String>('facName') ?? '',
          'facContactName': originalObject?.get<String>('facContactName') ?? '',
          'facContactPhone': originalObject?.get<String>('facContactPhone') ?? '',
          'facContactEmail': originalObject?.get<String>('facContactEmail') ?? '',
          'jTypeString': job['jTypeString'] ?? job['jobType'] ?? '',
          'jStatusString': originalObject?.get<String>('jStatusString') ?? 'Active',
          'durationString': job['durationString'] ?? originalObject?.get<String>('durationString') ?? '',
          'jobDescText': job['jobDescText'] ?? job['jobDescription'] ?? '',
          'clientID': originalObject?.get<String>('clientID') ?? '',
          'clientName': originalObject?.get<String>('clientName') ?? '',
          'clientEmail': originalObject?.get<String>('clientEmail') ?? '',
          'contactEmail': job['contactEmail'] ?? '',
          'startDate': originalObject?.get<DateTime>('startDate'),
          'endDate': originalObject?.get<DateTime>('endDate'),
          'emBOOL': job['emBOOL'] ?? originalObject?.get<bool>('emBOOL') ?? false,
          'hideFacility': job['hideFacility'] ?? originalObject?.get<bool>('hideFacility') ?? false,
          'source': job['source'] ?? 'unknown', // Track whether job is from app or website
          'originalObject': originalObject, // Keep reference to original Parse object
        };
      }).toList();

      setState(() {
        _allJobs = normalizedJobs;
        _filteredJobs = normalizedJobs;
      });
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
        leading: const BackButton(),
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
                                      '${job['jobTitle']} - ${_formatJobType(job['jTypeString'] as String? ?? '')}',
                                      style: const TextStyle(
                                        color: AppColors.jclGray,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 16,
                                      ),
                                    ),
                                    subtitle: Padding(
                                      padding: const EdgeInsets.only(top: 6),
                                      child: Text(
                                        '${_formatLocation(job)} | ${job['assignmntDates']}',
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
    final originalObject = job['originalObject'] as ParseObject?;

    // Debug: Print the job data
    print('📋 Job Details Data:');
    print('  - emBOOL: ${job['emBOOL']}');
    print('  - assignmntDates: ${job['assignmntDates']}');
    print('  - durationString: ${job['durationString']}');
    print('  - contactEmail: ${job['contactEmail']}');
    print('  - jTypeString: ${job['jTypeString']}');

    // Check emergent status from both sources
    bool isEmergent = job['emBOOL'] as bool? ?? false;
    if (!isEmergent && originalObject != null) {
      final emergentStatus = originalObject.get<String>('emergentStatus');
      isEmergent = emergentStatus?.toLowerCase() == 'yes';
    }
    print('  - isEmergent: $isEmergent');

    // Get dates and duration
    String dates = job['assignmntDates'] as String? ?? '';
    String duration = job['durationString'] as String? ?? '';

    if (dates.isEmpty && originalObject != null) {
      final startDate = originalObject.get<DateTime>('startDate');
      if (startDate != null) {
        dates = DateFormat('MM/dd/yyyy').format(startDate);
      }
      duration = originalObject.get<String>('jobDuration') ?? duration;
    }

    // Handle "Both" type to show as "Locum/Permanent"
    String jobType = job['jTypeString'] as String? ?? '';
    if (jobType.toLowerCase() == 'both') {
      jobType = 'Locum/Permanent';
    }

    // Get hideFacility flag
    final hideFacility = job['hideFacility'] as bool? ?? false;

    // Get contact email - check pre-extracted field first
    String contactEmail = job['contactEmail'] as String? ?? '';
    print('  - contactEmail from job: $contactEmail');

    // Fallback to originalObject if not pre-extracted
    if (contactEmail.isEmpty && originalObject != null) {
      contactEmail = originalObject.get<String>('facContactEmail') ??
                     originalObject.get<String>('clientEmail') ?? '';
      print('  - contactEmail from originalObject: $contactEmail');
    }
    print('  - Final contactEmail: $contactEmail');

    // Get description
    String description = job['jobDescText'] as String? ?? '';
    if (description.isEmpty && originalObject != null) {
      description = originalObject.get<String>('description') ?? '';
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(job['jobTitle'] as String? ?? 'Job Details'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow('Type', jobType),
              _buildDetailRow('Location', _formatLocation(job)),
              if (dates.isNotEmpty) _buildDetailRow('Start Date', dates),
              if (duration.isNotEmpty) _buildDetailRow('Duration', duration),

              // Emergent indicator - only show if emergent
              if (isEmergent)
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
                        'assets/images/sirenOn.gif',
                        width: 40,
                        height: 40,
                        errorBuilder: (context, error, stackTrace) {
                          return const Text('YES', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold));
                        },
                      ),
                    ],
                  ),
                ),

              // Only show facility name if not hidden
              if (!hideFacility && job['facilityName'] != null && (job['facilityName'] as String).isNotEmpty)
                _buildDetailRow('Facility', job['facilityName']),
              if (job['jStatusString'] != null && (job['jStatusString'] as String).isNotEmpty)
                _buildDetailRow('Status', job['jStatusString']),

              if (description.isNotEmpty) ...[
                const SizedBox(height: 12),
                const Text(
                  'Description:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(description),
              ],
            ],
          ),
        ),
        actions: [
          if (contactEmail.isNotEmpty)
            TextButton.icon(
              onPressed: () => _sendEmail(contactEmail, job['jobTitle'] as String? ?? 'Job'),
              icon: const Icon(Icons.email, color: AppColors.jclOrange),
              label: const Text('Contact Poster', style: TextStyle(color: AppColors.jclOrange)),
            ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  Future<void> _sendEmail(String email, String jobTitle) async {
    final Uri emailUri = Uri(
      scheme: 'mailto',
      path: email,
      query: 'subject=Inquiry about: $jobTitle',
    );

    if (await canLaunchUrl(emailUri)) {
      await launchUrl(emailUri);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not open email client for $email')),
        );
      }
    }
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

  /// Format job type - convert "Both" to "Locum/Permanent"
  String _formatJobType(String jobType) {
    if (jobType.toLowerCase() == 'both') {
      return 'Locum/Permanent';
    }
    return jobType;
  }

  /// Format location - honor hideFacility flag
  String _formatLocation(Map<String, dynamic> job) {
    final city = job['jobCity'] as String? ?? '';
    final state = job['jobState'] as String? ?? '';
    final hideFacility = job['hideFacility'] as bool? ?? false;

    // If hideFacility is true, only show state
    if (hideFacility) {
      return state;
    }

    // Otherwise show "City, State"
    if (city.isNotEmpty && state.isNotEmpty) {
      return '$city, $state';
    } else if (state.isNotEmpty) {
      return state;
    } else if (city.isNotEmpty) {
      return city;
    }
    return '';
  }
}
