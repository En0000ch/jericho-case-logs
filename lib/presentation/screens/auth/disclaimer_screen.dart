import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/auth_provider.dart';

class DisclaimerScreen extends ConsumerWidget {
  const DisclaimerScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Disclaimer'),
        automaticallyImplyLeading: false, // Prevent back navigation
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Icon
              const Icon(
                Icons.info_outline,
                size: 64,
                color: Colors.blue,
              ),
              const SizedBox(height: 24),

              // Title
              Text(
                'Important Disclaimer',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),

              // Disclaimer Content (scrollable)
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Terms of Use and Privacy Notice',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'This application is designed to help medical professionals log and track anesthesia cases for educational and professional development purposes.',
                        style: TextStyle(height: 1.5),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Data Privacy',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        '• Your case data is stored securely on Parse Server (Back4App)\n'
                        '• Local copies are encrypted on your device\n'
                        '• Patient information should be de-identified\n'
                        '• We do not share your data with third parties\n'
                        '• You retain ownership of all case data',
                        style: TextStyle(height: 1.5),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Professional Responsibility',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        '• This app is a tool for personal record-keeping\n'
                        '• Always follow HIPAA guidelines when logging cases\n'
                        '• Do not include patient identifiable information\n'
                        '• Ensure compliance with your institution\'s policies\n'
                        '• This app does not replace official medical records',
                        style: TextStyle(height: 1.5),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Subscription and Purchases',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        '• Free tier allows up to 5 case entries\n'
                        '• Unlimited access requires a one-time purchase\n'
                        '• Purchases are non-refundable\n'
                        '• All purchases are handled through Apple/Google stores',
                        style: TextStyle(height: 1.5),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Limitation of Liability',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'This application is provided "as is" without warranties. '
                        'The developers are not liable for any loss of data, '
                        'inaccuracies, or any consequences arising from use of this application.',
                        style: TextStyle(height: 1.5),
                      ),
                      const SizedBox(height: 24),
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.blue.shade200),
                        ),
                        child: const Text(
                          'By accepting this disclaimer, you acknowledge that you have read, '
                          'understood, and agree to these terms. You also confirm that you will '
                          'use this application responsibly and in compliance with all applicable '
                          'laws and regulations.',
                          style: TextStyle(
                            height: 1.5,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Accept Button
              ElevatedButton(
                onPressed: authState.isLoading
                    ? null
                    : () async {
                        await ref.read(authProvider.notifier).acceptDisclaimer();
                        if (context.mounted) {
                          // Navigation will be handled by main.dart watching auth state
                          Navigator.of(context).pop();
                        }
                      },
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: authState.isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text(
                        'I Accept',
                        style: TextStyle(fontSize: 16),
                      ),
              ),
              const SizedBox(height: 8),

              // Decline Button
              TextButton(
                onPressed: authState.isLoading
                    ? null
                    : () async {
                        // Logout if user declines
                        await ref.read(authProvider.notifier).logout();
                        if (context.mounted) {
                          Navigator.of(context).pop();
                        }
                      },
                child: const Text('Decline and Logout'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
