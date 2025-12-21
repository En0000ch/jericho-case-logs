import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import '../../providers/case_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/themes/app_colors.dart';

class CaseDetailScreen extends ConsumerStatefulWidget {
  final String caseId;

  const CaseDetailScreen({
    super.key,
    required this.caseId,
  });

  @override
  ConsumerState<CaseDetailScreen> createState() => _CaseDetailScreenState();
}

class _CaseDetailScreenState extends ConsumerState<CaseDetailScreen> {
  @override
  void initState() {
    super.initState();
    // Load case data when screen opens
    Future.microtask(() {
      ref.read(caseDetailProvider.notifier).loadCase(widget.caseId);
    });
  }

  void _deleteCase() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Confirmation'),
        content: const Text(
          'Are you sure you want to delete this case?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final repository = ref.read(caseRepositoryProvider);
      final success = await repository.deleteCase(widget.caseId);

      success.fold(
        (failure) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(failure.message),
              backgroundColor: Colors.red,
            ),
          );
        },
        (_) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Case deleted successfully'),
              backgroundColor: Colors.green,
            ),
          );
          Navigator.of(context).pop();
        },
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(caseDetailProvider);

    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text(
          'Case Review',
          style: TextStyle(
            color: AppColors.jclWhite,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: AppColors.jclOrange,
        elevation: 0,
        leading: IconButton(
          icon: Image.asset(
            'assets/images/cancel-30.png',
            width: 24,
            height: 24,
            color: AppColors.jclWhite,
          ),
          onPressed: () => Navigator.of(context).pop(),
          tooltip: 'Cancel',
        ),
        actions: [
          if (state.caseData != null)
            IconButton(
              icon: Image.asset(
                'assets/images/trash-30.png',
                width: 24,
                height: 24,
                color: AppColors.jclWhite,
              ),
              onPressed: _deleteCase,
              tooltip: 'Delete',
            ),
        ],
      ),
      body: state.isLoading
          ? const Center(
              child: CircularProgressIndicator(
                color: AppColors.jclOrange,
              ),
            )
          : state.error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.error_outline,
                        size: 48,
                        color: AppColors.jclOrange,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        state.error!,
                        style: const TextStyle(color: AppColors.jclWhite),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          ref
                              .read(caseDetailProvider.notifier)
                              .loadCase(widget.caseId);
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.jclOrange,
                        ),
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : state.caseData == null
                  ? const Center(
                      child: Text(
                        'Case not found',
                        style: TextStyle(color: AppColors.jclWhite),
                      ),
                    )
                  : SingleChildScrollView(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 35,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          // Date - centered, bold italic
                          Text(
                            DateFormat('M/d/yyyy').format(state.caseData!.date),
                            style: const TextStyle(
                              color: AppColors.jclWhite,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Surgeon and Location - horizontal
                          if (state.caseData!.location != null)
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                              children: [
                                Expanded(
                                  child: Text(
                                    state.caseData!.surgeonName ?? 'N/A',
                                    style: const TextStyle(
                                      color: AppColors.jclWhite,
                                      fontSize: 16,
                                      fontStyle: FontStyle.italic,
                                    ),
                                    textAlign: TextAlign.center,
                                  ),
                                ),
                                const SizedBox(width: 40),
                                Expanded(
                                  child: Text(
                                    state.caseData!.location ?? 'N/A',
                                    style: const TextStyle(
                                      color: AppColors.jclWhite,
                                      fontSize: 16,
                                      fontStyle: FontStyle.italic,
                                    ),
                                    textAlign: TextAlign.center,
                                  ),
                                ),
                              ],
                            ),
                          if (state.caseData!.location != null)
                            const SizedBox(height: 20),

                          // Surgery
                          _LabelValueRow(
                            label: 'Surgery:',
                            value: state.caseData!.procedureSurgery,
                          ),
                          const SizedBox(height: 20),

                          // Main Anesthetic
                          _LabelValueRow(
                            label: 'Main Anesthetic:',
                            value: state.caseData!.anestheticPlan,
                          ),
                          const SizedBox(height: 20),

                          // Secondary Anesthetic (if available)
                          if (state.caseData!.airwayManagement != null)
                            _LabelValueRow(
                              label: 'Secondary Anesthetic:',
                              value: state.caseData!.airwayManagement!,
                            ),
                          if (state.caseData!.airwayManagement != null)
                            const SizedBox(height: 20),

                          // ASA Class
                          _LabelValueRow(
                            label: 'ASA Class:',
                            value: state.caseData!.asaClassification,
                          ),
                          const SizedBox(height: 40),

                          // Patient Age and Gender - horizontal
                          Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                                children: [
                                  const Text(
                                    'Patient Age:',
                                    style: TextStyle(
                                      color: AppColors.jclWhite,
                                      fontSize: 14,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                  const SizedBox(width: 32),
                                  const Text(
                                    'Patient Gender:',
                                    style: TextStyle(
                                      color: AppColors.jclWhite,
                                      fontSize: 14,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                                children: [
                                  Text(
                                    state.caseData!.patientAge?.toString() ?? 'N/A',
                                    style: const TextStyle(
                                      color: AppColors.jclOrange,
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                  const SizedBox(width: 32),
                                  Text(
                                    state.caseData!.gender ?? 'N/A',
                                    style: const TextStyle(
                                      color: AppColors.jclOrange,
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 32),

                          // Surgical Notes
                          if (state.caseData!.additionalComments != null &&
                              state.caseData!.additionalComments!.isNotEmpty)
                            _TextSection(
                              title: 'Surgical Notes',
                              content: state.caseData!.additionalComments!,
                            ),
                          if (state.caseData!.additionalComments != null &&
                              state.caseData!.additionalComments!.isNotEmpty)
                            const SizedBox(height: 32),

                          // Comorbidities
                          _TextSection(
                            title: 'Comorbidities',
                            content: state.caseData!.comorbidities.isEmpty
                                ? 'None'
                                : state.caseData!.comorbidities.join(', '),
                          ),
                          const SizedBox(height: 32),

                          // Complications
                          _TextSection(
                            title: 'Complications',
                            content: state.caseData!.complicationsList.isEmpty
                                ? 'None'
                                : state.caseData!.complicationsList.join(', '),
                          ),
                          const SizedBox(height: 32),

                          // Skills Used
                          _TextSection(
                            title: 'Skills Used',
                            content: state.caseData!.skills.isEmpty
                                ? 'None'
                                : state.caseData!.skills.join(', '),
                          ),
                          const SizedBox(height: 50),
                        ],
                      ),
                    ),
    );
  }
}

class _LabelValueRow extends StatelessWidget {
  final String label;
  final String value;

  const _LabelValueRow({
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: AppColors.jclWhite,
            fontSize: 14,
            fontStyle: FontStyle.italic,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              color: AppColors.jclOrange,
              fontSize: 15,
            ),
            textAlign: TextAlign.left,
            overflow: TextOverflow.ellipsis,
            maxLines: 2,
          ),
        ),
      ],
    );
  }
}

class _TextSection extends StatelessWidget {
  final String title;
  final String content;

  const _TextSection({
    required this.title,
    required this.content,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          title,
          style: const TextStyle(
            color: AppColors.jclWhite,
            fontSize: 16,
            fontStyle: FontStyle.italic,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          width: 300,
          padding: const EdgeInsets.all(12),
          child: Text(
            content,
            style: const TextStyle(
              color: AppColors.jclOrange,
              fontSize: 14,
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ],
    );
  }
}
