# Firebase Analytics & Crashlytics Setup Guide

Firebase Analytics and Crashlytics have been integrated into the Jericho Case Logs app. Follow these steps to complete the setup:

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Add Project" or select an existing project
3. Enter project name (e.g., "Jericho Case Logs")
4. Follow the wizard to create your project

## Step 2: Add iOS App to Firebase

1. In Firebase Console, click the iOS icon to add an iOS app
2. **iOS bundle ID**: `com.jerichocreations.jerichoCaseLogs` (found in `ios/Runner.xcodeproj/project.pbxproj`)
3. App nickname (optional): "Jericho Case Logs iOS"
4. Download `GoogleService-Info.plist`
5. Place the file in: `ios/Runner/GoogleService-Info.plist`
6. **Important**: Open Xcode and drag the file into the Runner folder to ensure it's included in the project

## Step 3: Add Android App to Firebase (Future)

1. In Firebase Console, click the Android icon
2. **Android package name**: `com.jerichocreations.jericho_case_logs`
3. Download `google-services.json`
4. Place the file in: `android/app/google-services.json`

## Step 4: Enable Crashlytics

1. In Firebase Console, go to **Crashlytics** in the left menu
2. Click "Enable Crashlytics"
3. Follow the setup wizard

## Step 5: Enable Analytics

1. In Firebase Console, go to **Analytics** in the left menu
2. Analytics should be enabled by default
3. Configure any additional settings as needed

## Step 6: Run FlutterFire Configure (Optional)

You can use the FlutterFire CLI to automate configuration:

```bash
# Install FlutterFire CLI
dart pub global activate flutterfire_cli

# Run configuration (from project root)
flutterfire configure
```

This will automatically generate `firebase_options.dart` and configure your apps.

## Step 7: Test the Integration

1. Run the app in debug mode
2. Cause a test crash by adding this button temporarily:
```dart
ElevatedButton(
  onPressed: () => throw Exception('Test Crash'),
  child: Text('Test Crash'),
)
```
3. Check Firebase Console → Crashlytics to see the crash report

## What's Tracked

### Crashlytics
- All uncaught Flutter errors
- All uncaught Dart errors
- Fatal crashes from native code (iOS/Android)

### Analytics
- Screen views
- User engagement
- Custom events (can be added as needed)

## Privacy & Compliance

- Ensure Firebase usage complies with your privacy policy
- Update App Store privacy declarations if needed
- Consider HIPAA compliance requirements

## Troubleshooting

### Build fails with missing configuration
- Ensure `GoogleService-Info.plist` is in `ios/Runner/`
- Ensure `google-services.json` is in `android/app/`
- Clean and rebuild: `flutter clean && flutter pub get`

### Crashlytics not receiving crashes
- Wait 5-10 minutes for first crash report
- Ensure you're running a Release build for production testing
- Check Firebase Console → Crashlytics → Settings

### Analytics not showing data
- Analytics data can take 24 hours to appear
- Check that Analytics is enabled in Firebase Console
- Verify `measurementId` in configuration files

## Back4App Email Template Customization

To customize the password reset email template in Back4App:

1. Log in to [Back4App Dashboard](https://www.back4app.com/)
2. Select your app from the dashboard
3. In the left sidebar, click **App Settings** (or **Server Settings**)
4. Click **Verification emails** (or **Email Verification**)
5. Here you can customize:
   - **Password Reset Email Template**: Subject line and email body (HTML supported)
   - **Email Verification Template**: For new user verification
   - **From Email Address**: The sender email that users will see
   - **Reply-To Email**: Where replies should go

### Email Template Variables

You can use these variables in your email templates:
- `%username%` - User's username
- `%link%` - Password reset link (required)
- `%appname%` - Your app name

### Example Password Reset Email

```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { background-color: #EE6C4D; color: white; padding: 12px 24px;
                 text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hi %username%,</p>
        <p>You requested to reset your password for %appname%.</p>
        <p>Click the button below to reset your password:</p>
        <p><a href="%link%" class="button">Reset Password</a></p>
        <p>If you didn't request this, you can safely ignore this email.</p>
        <p>This link will expire in 24 hours.</p>
    </div>
</body>
</html>
```

## Additional Resources

- [FlutterFire Documentation](https://firebase.flutter.dev/)
- [Firebase Crashlytics](https://firebase.google.com/docs/crashlytics)
- [Firebase Analytics](https://firebase.google.com/docs/analytics)
- [Back4App Email Configuration](https://www.back4app.com/docs/parse-server/email-configuration)
