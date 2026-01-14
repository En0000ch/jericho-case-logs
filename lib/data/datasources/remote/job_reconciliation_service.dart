import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';

/// Service for reconciling and fetching jobs from both jclJobs and JobPosting Parse classes
/// jclJobs = jobs posted from iOS app
/// JobPosting = jobs posted from website
class JobReconciliationService {
  /// Unified job data structure
  static Map<String, dynamic> _parseJobPosting(ParseObject job, String source) {
    // Map professionalTitle to jclSilo for website jobs
    String deriveSiloFromProfession(String? profession) {
      if (profession == null || profession.isEmpty) return '';
      final prof = profession.toUpperCase();
      if (prof.contains('CRNA') || prof.contains('AA') || prof.contains('ANESTHESIOLOGIST')) {
        return 'jclAnes';
      } else if (prof.contains('NURSE') || prof.contains('RN')) {
        return 'jclNurse';
      }
      return 'jclAll';
    }

    // Website JobPosting uses: professionalTitle, city, state, description
    // iOS jclJobs uses: jobTitle, jobCity, jobState, jobDescText
    final professionalTitle = job.get<String>('professionalTitle');
    final derivedSilo = deriveSiloFromProfession(professionalTitle);

    // Extract dates and duration
    String assignmentDates = job.get<String>('assignmntDates') ?? '';
    String durationString = job.get<String>('durationString') ?? job.get<String>('jobDuration') ?? '';

    // For website jobs, format startDate if available
    if (assignmentDates.isEmpty) {
      final startDate = job.get<DateTime>('startDate');
      if (startDate != null) {
        assignmentDates = '${startDate.month}/${startDate.day}/${startDate.year}';
      }
    }

    // Check emergent status
    bool isEmergent = job.get<bool>('emBOOL') ?? false;
    if (!isEmergent) {
      final emergentStatus = job.get<String>('emergentStatus');
      isEmergent = emergentStatus?.toLowerCase() == 'yes';
    }

    // Extract contact email from various sources
    String contactEmail = job.get<String>('facContactEmail') ??
                         job.get<String>('clientEmail') ?? '';

    // Try to get email from postedBy user (if it's a ParseUser pointer)
    if (contactEmail.isEmpty) {
      try {
        final postedByUser = job.get('postedBy');
        if (postedByUser != null && postedByUser is ParseObject) {
          contactEmail = postedByUser.get<String>('email') ??
                        postedByUser.get<String>('username') ?? '';
        }
      } catch (e) {
        print('⚠️ Error extracting email from postedBy: $e');
      }
    }

    print('🔧 Job Reconciliation for ${job.objectId}:');
    print('  - assignmentDates: $assignmentDates');
    print('  - durationString: $durationString');
    print('  - isEmergent: $isEmergent');
    print('  - contactEmail: $contactEmail');

    return {
      'objectId': job.objectId,
      'jobTitle': job.get<String>('jobTitle') ?? professionalTitle ?? '',
      'jobState': job.get<String>('jobState') ?? job.get<String>('state') ?? '',
      'jobCity': job.get<String>('jobCity') ?? job.get<String>('city') ?? '',
      'jobDescription': job.get<String>('jobDescription') ?? job.get<String>('description') ?? '',
      'jobDescText': job.get<String>('jobDescText') ?? job.get<String>('description') ?? '',
      'jobType': job.get<String>('jobType') ?? job.get<String>('employmentType') ?? '',
      'jTypeString': job.get<String>('jTypeString') ?? job.get<String>('jobType') ?? job.get<String>('employmentType') ?? '',
      'jobSalary': job.get<String>('jobSalary') ?? job.get<String>('salary') ?? '',
      'jclSilo': job.get<String>('jclSilo') ?? derivedSilo,
      'jclProf': job.get<String>('jclProf') ?? professionalTitle ?? '',
      'createdAt': job.get<DateTime>('createdAt') ?? DateTime.now(),
      'postedBy': _getPostedBy(job),
      'facilityName': job.get<String>('facName') ?? job.get<String>('facilityName') ?? '',
      'requisitionNumber': job.get<String>('reqNum') ?? job.get<String>('requisitionNumber') ?? '',
      'assignmntDates': assignmentDates,
      'durationString': durationString,
      'emBOOL': isEmergent,
      'contactEmail': contactEmail,
      'hideFacility': job.get<bool>('hideFacility') ?? false,
      'source': source,
      'originalObject': job,
    };
  }

  /// Safely extract postedBy field (can be String or ParseUser)
  static String _getPostedBy(ParseObject job) {
    try {
      // Try as String first
      final postedByString = job.get<String>('postedBy');
      if (postedByString != null) return postedByString;
    } catch (e) {
      // Might be a ParseUser pointer
    }

    try {
      // Try as ParseUser
      final postedByUser = job.get('postedBy');
      if (postedByUser != null && postedByUser is ParseObject) {
        return postedByUser.get<String>('email') ?? postedByUser.get<String>('username') ?? '';
      }
    } catch (e) {
      // Ignore
    }

    // Try employerName as fallback
    return job.get<String>('employerName') ?? '';
  }

  /// Fetch jobs from both Parse classes and return unified list
  /// Filters by silo if provided (nurses see nurse jobs, CRNAs see CRNA jobs)
  Future<List<Map<String, dynamic>>> fetchUnifiedJobs({
    String? siloFilter,
    int limit = 50,
  }) async {
    try {
      print('🔍 Fetching jobs from both jclJobs and JobPosting classes...');

      final List<Map<String, dynamic>> allJobs = [];

      // Fetch from jclJobs (iOS app jobs)
      final jclJobsQuery = QueryBuilder<ParseObject>(ParseObject('jclJobs'))
        ..orderByDescending('createdAt')
        ..setLimit(limit);

      if (siloFilter != null && siloFilter != 'jclAll') {
        jclJobsQuery.whereEqualTo('jclSilo', siloFilter);
      }

      final jclJobsResponse = await jclJobsQuery.query();

      if (jclJobsResponse.success && jclJobsResponse.results != null) {
        print('✅ Found ${jclJobsResponse.results!.length} jobs from jclJobs');
        for (var job in jclJobsResponse.results!) {
          allJobs.add(_parseJobPosting(job as ParseObject, 'app'));
        }
      }

      // Fetch from JobPosting (website jobs)
      // NOTE: JobPosting doesn't have jclSilo field, so we fetch all and filter after parsing
      final jobPostingQuery = QueryBuilder<ParseObject>(ParseObject('JobPosting'))
        ..orderByDescending('createdAt')
        ..setLimit(limit)
        ..includeObject(['postedBy']); // Include user data for contact email

      final jobPostingResponse = await jobPostingQuery.query();

      if (jobPostingResponse.success && jobPostingResponse.results != null) {
        print('✅ Found ${jobPostingResponse.results!.length} jobs from JobPosting');
        for (var job in jobPostingResponse.results!) {
          final parsedJob = _parseJobPosting(job as ParseObject, 'website');

          // Filter by silo if needed (silo is derived from professionalTitle)
          if (siloFilter == null || siloFilter == 'jclAll') {
            allJobs.add(parsedJob);
          } else {
            final jobSilo = parsedJob['jclSilo'] as String?;
            if (jobSilo == siloFilter) {
              allJobs.add(parsedJob);
            }
          }
        }
      }

      // Sort all jobs by creation date (most recent first)
      allJobs.sort((a, b) {
        final aDate = a['createdAt'] as DateTime;
        final bDate = b['createdAt'] as DateTime;
        return bDate.compareTo(aDate);
      });

      print('📦 Total unified jobs: ${allJobs.length}');
      return allJobs;

    } catch (e) {
      print('❌ Error fetching unified jobs: $e');
      return [];
    }
  }

  /// Fetch a single job by ID from either class
  Future<Map<String, dynamic>?> fetchJobById(String objectId, String className) async {
    try {
      final query = QueryBuilder<ParseObject>(ParseObject(className))
        ..whereEqualTo('objectId', objectId);

      final response = await query.query();

      if (response.success && response.results != null && response.results!.isNotEmpty) {
        final source = className == 'jclJobs' ? 'app' : 'website';
        return _parseJobPosting(response.results!.first as ParseObject, source);
      }

      return null;
    } catch (e) {
      print('❌ Error fetching job by ID: $e');
      return null;
    }
  }

  /// Search jobs by keyword across both classes
  Future<List<Map<String, dynamic>>> searchJobs({
    required String keyword,
    String? siloFilter,
  }) async {
    try {
      print('🔍 Searching jobs for keyword: $keyword');

      final List<Map<String, dynamic>> allJobs = [];

      // Search in jclJobs
      final jclJobsQuery = QueryBuilder<ParseObject>(ParseObject('jclJobs'))
        ..whereContains('jobTitle', keyword, caseSensitive: false)
        ..orderByDescending('createdAt');

      if (siloFilter != null && siloFilter != 'jclAll') {
        jclJobsQuery.whereEqualTo('jclSilo', siloFilter);
      }

      final jclJobsResponse = await jclJobsQuery.query();

      if (jclJobsResponse.success && jclJobsResponse.results != null) {
        for (var job in jclJobsResponse.results!) {
          allJobs.add(_parseJobPosting(job as ParseObject, 'app'));
        }
      }

      // Search in JobPosting
      final jobPostingQuery = QueryBuilder<ParseObject>(ParseObject('JobPosting'))
        ..orderByDescending('createdAt');

      // Try searching in both title and jobTitle fields
      final titleQuery1 = QueryBuilder<ParseObject>(ParseObject('JobPosting'))
        ..whereContains('title', keyword, caseSensitive: false);
      final titleQuery2 = QueryBuilder<ParseObject>(ParseObject('JobPosting'))
        ..whereContains('jobTitle', keyword, caseSensitive: false);

      final combinedQuery = QueryBuilder.or(
        ParseObject('JobPosting'),
        [titleQuery1, titleQuery2],
      );

      if (siloFilter != null && siloFilter != 'jclAll') {
        combinedQuery.whereEqualTo('jclSilo', siloFilter);
      }

      final jobPostingResponse = await combinedQuery.query();

      if (jobPostingResponse.success && jobPostingResponse.results != null) {
        for (var job in jobPostingResponse.results!) {
          allJobs.add(_parseJobPosting(job as ParseObject, 'website'));
        }
      }

      // Sort by date
      allJobs.sort((a, b) {
        final aDate = a['createdAt'] as DateTime;
        final bDate = b['createdAt'] as DateTime;
        return bDate.compareTo(aDate);
      });

      print('📦 Found ${allJobs.length} jobs matching "$keyword"');
      return allJobs;

    } catch (e) {
      print('❌ Error searching jobs: $e');
      return [];
    }
  }
}
