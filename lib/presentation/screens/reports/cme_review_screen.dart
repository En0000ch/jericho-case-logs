import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:parse_server_sdk_flutter/parse_server_sdk_flutter.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:share_plus/share_plus.dart';
import 'package:path_provider/path_provider.dart';
import 'dart:io';
import '../../../core/themes/app_colors.dart';
import '../../providers/auth_provider.dart';

class CMEReviewScreen extends ConsumerStatefulWidget {
  final Map<String, dynamic> cmeData;

  const CMEReviewScreen({
    super.key,
    required this.cmeData,
  });

  @override
  ConsumerState<CMEReviewScreen> createState() => _CMEReviewScreenState();
}

class _CMEReviewScreenState extends ConsumerState<CMEReviewScreen> {
  bool _isDeleting = false;

  Future<void> _deleteCME() async {
    final shouldDelete = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Warning'),
        content: const Text(
          'You are about to delete this CME record. This action cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(
              foregroundColor: Colors.red,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (shouldDelete != true) return;

    setState(() {
      _isDeleting = true;
    });

    try {
      final cmeId = widget.cmeData['cmeId'] as String?;
      if (cmeId == null || cmeId.isEmpty) {
        throw Exception('Invalid CME ID');
      }

      final cmeObject = ParseObject('savedCME')..objectId = cmeId;
      final response = await cmeObject.delete();

      if (response.success) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('CME successfully deleted'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context);
      } else {
        throw Exception(response.error?.message ?? 'Failed to delete CME');
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isDeleting = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error deleting CME: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _sharePDF() async {
    try {
      // Generate PDF
      final pdf = await _generatePDF();

      // Get temporary directory
      final directory = await getTemporaryDirectory();
      final file = File('${directory.path}/jcl_CME.pdf');

      // Write PDF to file
      await file.writeAsBytes(await pdf.save());

      // Share the file
      await Share.shareXFiles(
        [XFile(file.path)],
        subject: 'Jericho Case Logs - CME Report',
        text: 'Please find attached the CME PDF document.',
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error sharing PDF: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _printPDF() async {
    try {
      final pdf = await _generatePDF();
      await Printing.layoutPdf(
        onLayout: (format) async => pdf.save(),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error printing PDF: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<pw.Document> _generatePDF() async {
    final pdf = pw.Document();

    // Get user info
    final user = ref.read(currentUserProvider);
    final userName = user?.fullName ?? 'User';

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        build: (context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              // Header
              pw.Container(
                padding: const pw.EdgeInsets.all(20),
                decoration: pw.BoxDecoration(
                  color: PdfColors.grey300,
                  borderRadius: pw.BorderRadius.circular(8),
                ),
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.center,
                  children: [
                    pw.Text(
                      'Jericho Case Logs',
                      style: pw.TextStyle(
                        fontSize: 24,
                        fontWeight: pw.FontWeight.bold,
                      ),
                    ),
                    pw.SizedBox(height: 8),
                    pw.Text(
                      userName,
                      style: const pw.TextStyle(fontSize: 16),
                    ),
                    pw.SizedBox(height: 4),
                    pw.Text(
                      'Continuing Medical Education Report',
                      style: pw.TextStyle(
                        fontSize: 14,
                        fontStyle: pw.FontStyle.italic,
                      ),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 30),

              // Course Details
              _buildPDFRow('Course Name:', widget.cmeData['courseName'] ?? ''),
              _buildPDFRow('Instructor:', widget.cmeData['instructorName'] ?? ''),
              _buildPDFRow('Location:', widget.cmeData['courseLocation'] ?? ''),
              _buildPDFRow('Accreditation #:', widget.cmeData['accredNum'] ?? ''),
              _buildPDFRow('Credits:', widget.cmeData['numberCredits'] ?? ''),
              _buildPDFRow('Cost:', widget.cmeData['courseCost'] ?? ''),
              _buildPDFRow(
                'Dates:',
                '${widget.cmeData['startDate'] ?? ''} - ${widget.cmeData['endDate'] ?? ''}',
              ),

              pw.SizedBox(height: 20),
              pw.Divider(),
              pw.SizedBox(height: 20),

              // Overview
              pw.Text(
                'Course Overview:',
                style: pw.TextStyle(
                  fontSize: 14,
                  fontWeight: pw.FontWeight.bold,
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Container(
                padding: const pw.EdgeInsets.all(12),
                decoration: pw.BoxDecoration(
                  color: PdfColors.grey100,
                  borderRadius: pw.BorderRadius.circular(4),
                ),
                child: pw.Text(
                  widget.cmeData['courseOverview'] ?? 'No overview available',
                  style: const pw.TextStyle(fontSize: 12),
                ),
              ),
            ],
          );
        },
      ),
    );

    return pdf;
  }

  pw.Widget _buildPDFRow(String label, String value) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 8),
      child: pw.Row(
        crossAxisAlignment: pw.CrossAxisAlignment.start,
        children: [
          pw.SizedBox(
            width: 120,
            child: pw.Text(
              label,
              style: pw.TextStyle(
                fontSize: 12,
                fontWeight: pw.FontWeight.bold,
              ),
            ),
          ),
          pw.Expanded(
            child: pw.Container(
              padding: const pw.EdgeInsets.all(8),
              decoration: pw.BoxDecoration(
                color: PdfColors.grey200,
                borderRadius: pw.BorderRadius.circular(4),
              ),
              child: pw.Text(
                value,
                style: const pw.TextStyle(fontSize: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.jclGray,
      appBar: AppBar(
        title: const Text('CME Review'),
        backgroundColor: AppColors.jclOrange,
        foregroundColor: AppColors.jclWhite,
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.share),
            onSelected: (value) {
              if (value == 'share') {
                _sharePDF();
              } else if (value == 'print') {
                _printPDF();
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'share',
                child: Row(
                  children: [
                    Icon(Icons.share),
                    SizedBox(width: 8),
                    Text('Share PDF'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'print',
                child: Row(
                  children: [
                    Icon(Icons.print),
                    SizedBox(width: 8),
                    Text('Print PDF'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: Stack(
        children: [
          SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildDetailRow('Course:', widget.cmeData['courseName'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow('Instructor:', widget.cmeData['instructorName'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow('Location:', widget.cmeData['courseLocation'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow('Accred #:', widget.cmeData['accredNum'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow('Credits:', widget.cmeData['numberCredits'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow('Cost:', widget.cmeData['courseCost'] ?? ''),
                const SizedBox(height: 16),
                _buildDetailRow(
                  'Dates:',
                  '${widget.cmeData['startDate'] ?? ''} - ${widget.cmeData['endDate'] ?? ''}',
                ),
                const SizedBox(height: 24),
                const Text(
                  'Overview:',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: AppColors.jclWhite,
                  ),
                ),
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.jclGrayLite,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    widget.cmeData['courseOverview'] ?? 'No overview available',
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppColors.jclOrange,
                    ),
                  ),
                ),
                const SizedBox(height: 100), // Space for delete button
              ],
            ),
          ),
          // Delete Button (floating at bottom right)
          Positioned(
            right: 25,
            bottom: 25,
            child: FloatingActionButton(
              onPressed: _isDeleting ? null : _deleteCME,
              backgroundColor: _isDeleting ? Colors.grey : Colors.red,
              child: _isDeleting
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        color: Colors.white,
                        strokeWidth: 2,
                      ),
                    )
                  : const Icon(Icons.delete),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 90,
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.jclWhite,
            ),
            textAlign: TextAlign.right,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              border: Border(
                bottom: BorderSide(
                  color: AppColors.jclOrange,
                  width: 1,
                ),
              ),
            ),
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                color: AppColors.jclOrange,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
