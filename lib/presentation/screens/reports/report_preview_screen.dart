import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import '../../../core/themes/app_colors.dart';

/// Report Preview Screen - Displays generated report data with PDF export
class ReportPreviewScreen extends StatefulWidget {
  final DateTime startDate;
  final DateTime endDate;
  final List<Map<String, dynamic>> cases;
  final Map<String, bool> selectedOptions;

  const ReportPreviewScreen({
    super.key,
    required this.startDate,
    required this.endDate,
    required this.cases,
    required this.selectedOptions,
  });

  @override
  State<ReportPreviewScreen> createState() => _ReportPreviewScreenState();
}

class _ReportPreviewScreenState extends State<ReportPreviewScreen> {
  final DateFormat _dateFormat = DateFormat('M/d/yyyy');

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclWhite,
      appBar: AppBar(
        title: const Text(
          'JCL Report Review',
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
            icon: const Icon(Icons.share),
            onPressed: _generateAndSharePDF,
            tooltip: 'Share PDF',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Report Header - full width
            Container(
              padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
              decoration: BoxDecoration(
                color: AppColors.jclGray,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Text(
                    'Jericho Case Logs Report',
                    style: TextStyle(
                      color: AppColors.jclWhite,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${_dateFormat.format(widget.startDate)} - ${_dateFormat.format(widget.endDate)}',
                    style: const TextStyle(
                      color: AppColors.jclWhite,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Total Cases: ${widget.cases.length}',
                    style: const TextStyle(
                      color: AppColors.jclOrange,
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Cases List - more compact
            if (widget.cases.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(40),
                  child: Column(
                    children: [
                      Icon(
                        Icons.inbox,
                        size: 64,
                        color: Colors.grey[400],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'No cases found for this date range',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              ...widget.cases.asMap().entries.map((entry) {
                final index = entry.key;
                final caseData = entry.value;
                return _buildCompactCaseCard(index + 1, caseData);
              }),
          ],
        ),
      ),
    );
  }

  Widget _buildCompactCaseCard(int caseNumber, Map<String, dynamic> caseData) {
    // Build case text content
    final buffer = StringBuffer();

    // Date
    if (caseData['surgeryDateTime'] != null) {
      buffer.writeln(_formatDate(caseData['surgeryDateTime']));
    }

    // Surgery Type (if selected)
    if (widget.selectedOptions['Surgery Type'] == true) {
      if (caseData['surgeryCategory'] != null && caseData['surgery'] != null) {
        buffer.writeln('${caseData['surgeryCategory']}/${caseData['surgery']}');
      }
    }

    // Primary Anesthetic (if selected)
    if (widget.selectedOptions['Primary Anesthetic'] == true && caseData['primePlan'] != null) {
      buffer.writeln('Primary Anesthetic: ${caseData['primePlan']}');
    }

    // Secondary Anesthetic (if selected)
    if (widget.selectedOptions['Secondary Anesthetic'] == true && caseData['secPlan'] != null) {
      buffer.writeln('Secondary Anesthetic: ${caseData['secPlan']}');
    }

    // Skilled Procedures (if selected)
    if (widget.selectedOptions['Skilled Procedures'] == true && caseData['skillsArry'] != null) {
      final skills = caseData['skillsArry'] as List?;
      if (skills != null && skills.isNotEmpty) {
        final skillsStr = skills.map((s) => s.toString()).join(', ');
        buffer.writeln('Skills Used: $skillsStr');
      }
    }

    // Surgeon (if selected)
    if (widget.selectedOptions['Surgeon'] == true && caseData['surgeonName'] != null) {
      buffer.writeln('Surgeon: ${caseData['surgeonName']}');
    }

    // Facility (if selected)
    if (widget.selectedOptions['Facility'] == true && caseData['facilityName'] != null) {
      buffer.writeln('Facility: ${caseData['facilityName']}');
    }

    // ASA (if selected)
    if (widget.selectedOptions['ASA'] == true && caseData['asaPlan'] != null) {
      buffer.writeln('ASA: ${caseData['asaPlan']}');
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.grey[300]!),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        buffer.toString().trim(),
        style: const TextStyle(
          fontSize: 13,
          color: Color(0xFF2B3241), // Darker text for readability
          height: 1.4,
        ),
      ),
    );
  }

  String _formatDate(dynamic dateValue) {
    if (dateValue is DateTime) {
      return _dateFormat.format(dateValue);
    } else if (dateValue is String) {
      try {
        final parsedDate = DateTime.parse(dateValue);
        return _dateFormat.format(parsedDate);
      } catch (e) {
        return dateValue;
      }
    }
    return 'N/A';
  }

  Future<void> _generateAndSharePDF() async {
    try {
      // Show loading
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(
          child: CircularProgressIndicator(color: AppColors.jclOrange),
        ),
      );

      final pdf = await _generatePDF();

      if (!mounted) return;
      Navigator.of(context).pop(); // Close loading

      // Share the PDF
      await Printing.sharePdf(
        bytes: await pdf.save(),
        filename: 'JCL_Report_${_dateFormat.format(widget.startDate)}_to_${_dateFormat.format(widget.endDate)}.pdf',
      );
    } catch (e) {
      if (!mounted) return;
      Navigator.of(context).pop(); // Close loading if still open

      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Error'),
          content: Text('Failed to generate PDF: $e'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('OK'),
            ),
          ],
        ),
      );
    }
  }

  Future<pw.Document> _generatePDF() async {
    final pdf = pw.Document();

    // Load logo if available
    pw.ImageProvider? logoImage;
    try {
      final logoData = await rootBundle.load('assets/images/1024Logo.png');
      logoImage = pw.MemoryImage(logoData.buffer.asUint8List());
    } catch (e) {
      print('Could not load logo: $e');
    }

    pdf.addPage(
      pw.MultiPage(
        pageFormat: PdfPageFormat.letter,
        margin: const pw.EdgeInsets.all(40),
        build: (context) {
          final widgets = <pw.Widget>[];

          // Header with logo
          widgets.add(
            pw.Row(
              mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text(
                      'Jericho Case Logs Report',
                      style: pw.TextStyle(
                        fontSize: 18,
                        fontWeight: pw.FontWeight.bold,
                      ),
                    ),
                    pw.SizedBox(height: 4),
                    pw.Text(
                      '${_dateFormat.format(widget.startDate)} - ${_dateFormat.format(widget.endDate)}',
                      style: const pw.TextStyle(fontSize: 12),
                    ),
                    pw.SizedBox(height: 2),
                    pw.Text(
                      'Total Cases: ${widget.cases.length}',
                      style: const pw.TextStyle(fontSize: 12),
                    ),
                  ],
                ),
                if (logoImage != null)
                  pw.Image(logoImage, width: 50, height: 50),
              ],
            ),
          );

          widgets.add(pw.SizedBox(height: 20));
          widgets.add(pw.Divider());
          widgets.add(pw.SizedBox(height: 10));

          // Cases
          for (var i = 0; i < widget.cases.length; i++) {
            final caseData = widget.cases[i];
            final buffer = StringBuffer();

            // Date
            if (caseData['surgeryDateTime'] != null) {
              buffer.writeln(_formatDate(caseData['surgeryDateTime']));
            }

            // Surgery Type
            if (widget.selectedOptions['Surgery Type'] == true) {
              if (caseData['surgeryCategory'] != null && caseData['surgery'] != null) {
                buffer.writeln('${caseData['surgeryCategory']}/${caseData['surgery']}');
              }
            }

            // Primary Anesthetic
            if (widget.selectedOptions['Primary Anesthetic'] == true && caseData['primePlan'] != null) {
              buffer.writeln('Primary Anesthetic: ${caseData['primePlan']}');
            }

            // Secondary Anesthetic
            if (widget.selectedOptions['Secondary Anesthetic'] == true && caseData['secPlan'] != null) {
              buffer.writeln('Secondary Anesthetic: ${caseData['secPlan']}');
            }

            // Skilled Procedures
            if (widget.selectedOptions['Skilled Procedures'] == true && caseData['skillsArry'] != null) {
              final skills = caseData['skillsArry'] as List?;
              if (skills != null && skills.isNotEmpty) {
                final skillsStr = skills.map((s) => s.toString()).join(', ');
                buffer.writeln('Skills Used: $skillsStr');
              }
            }

            // Surgeon
            if (widget.selectedOptions['Surgeon'] == true && caseData['surgeonName'] != null) {
              buffer.writeln('Surgeon: ${caseData['surgeonName']}');
            }

            // Facility
            if (widget.selectedOptions['Facility'] == true && caseData['facilityName'] != null) {
              buffer.writeln('Facility: ${caseData['facilityName']}');
            }

            // ASA
            if (widget.selectedOptions['ASA'] == true && caseData['asaPlan'] != null) {
              buffer.writeln('ASA: ${caseData['asaPlan']}');
            }

            widgets.add(
              pw.Container(
                margin: const pw.EdgeInsets.only(bottom: 8),
                padding: const pw.EdgeInsets.all(8),
                decoration: pw.BoxDecoration(
                  border: pw.Border.all(color: PdfColors.grey400),
                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                ),
                child: pw.Text(
                  buffer.toString().trim(),
                  style: const pw.TextStyle(fontSize: 10),
                ),
              ),
            );
          }

          return widgets;
        },
      ),
    );

    return pdf;
  }
}
