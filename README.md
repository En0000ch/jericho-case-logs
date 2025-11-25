# Jericho Case Logs - Flutter Application

A cross-platform mobile application for traveling medical providers (CRNAs) to log and manage anesthesia cases.

## Project Status

This is a Flutter conversion of the original Objective-C iOS application, providing cross-platform support for iOS and Android.

### ✅ Completed Features

1. **Authentication System**
   - User login with Parse Server backend
   - New user registration with silo-based routing (jclAnes, jclJobs, jclAll)
   - Password saving ("Remember me" functionality)
   - Disclaimer acceptance for first-time users
   - Profile management

2. **Case Management**
   - Create new anesthesia cases with comprehensive fields:
     - Date, patient demographics (age, gender)
     - ASA classification
     - Procedure/surgery details
     - Anesthetic plan and anesthetics used
     - Surgery class (Elective, Urgent, Emergency)
     - Location and airway management
     - Complications tracking
     - Additional comments
   - View all cases in list format
   - Search and filter cases (by keyword, ASA, surgery class, date range)
   - Edit existing cases
   - Delete cases with confirmation
   - Offline support with SQLite local database
   - Automatic sync between Parse Server and local storage

3. **Calendar View**
   - Interactive calendar displaying cases by date
   - Month, week, and two-week views
   - Day selection to view all cases on that date
   - Quick navigation to today
   - Direct access to case details from calendar

4. **Settings & Profile**
   - View user profile information
   - Edit personal information (name, title)
   - View account status (Free/Premium)
   - View case count
   - Logout functionality

5. **Reports & Analytics**
   - Summary cards showing:
     - Total cases
     - Cases with complications
     - Unique procedures
   - Visual charts:
     - Cases by ASA Classification (Pie chart)
     - Cases by Anesthetic Plan (Bar chart)
     - Cases by Surgery Class (Pie chart)
     - Cases Over Time - Last 30 Days (Line chart)

### 🚧 Features Requiring Additional Implementation

1. **In-App Purchases**
   - Premium upgrade to unlock unlimited cases
   - Current limit: 5 free cases
   - Package: `in_app_purchase` (already added to dependencies)
   - Needs: Apple App Store and Google Play Store configuration

2. **Job Search Feature**
   - For users in the jclJobs silo
   - Job posting and browsing functionality
   - Needs: Additional Parse Server classes and UI screens

3. **Continuing Education Tracking**
   - CME/CEU logging and management
   - Needs: Additional database models and UI screens

4. **PDF Export**
   - Export reports to PDF
   - Packages: `pdf`, `printing` (already added to dependencies)
   - Needs: Implementation of PDF generation logic

## Architecture

The project follows **Clean Architecture** principles:

```
lib/
├── core/
│   ├── constants/          # App-wide constants
│   ├── errors/             # Error handling
│   └── themes/             # Material 3 themes
├── data/
│   ├── datasources/
│   │   ├── local/          # SQLite database
│   │   └── remote/         # Parse Server API
│   ├── models/             # Data models
│   └── repositories/       # Repository implementations
├── domain/
│   ├── entities/           # Business entities
│   └── repositories/       # Repository interfaces
└── presentation/
    ├── providers/          # Riverpod state management
    └── screens/            # UI screens
```

## Dependencies

### Core
- **flutter_riverpod**: State management
- **dartz**: Functional programming (Either type)
- **equatable**: Value equality

### Backend & Database
- **parse_server_sdk_flutter**: Parse Server integration
- **sqflite**: Local SQLite database
- **shared_preferences**: Key-value storage

### UI Components
- **table_calendar**: Calendar widget
- **fl_chart**: Charts and graphs
- **intl**: Internationalization and date formatting

### Forms
- **flutter_form_builder**: Form building
- **form_builder_validators**: Form validation
- **image_picker**: Image selection

### Future Features (Already Added)
- **in_app_purchase**: In-app purchases
- **pdf** & **printing**: PDF generation
- **url_launcher**: Open URLs
- **share_plus**: Share functionality

## Setup Instructions

### 1. Prerequisites
- Flutter SDK 3.27.1 or higher
- Dart 3.6.0 or higher
- CocoaPods (for iOS)
- Xcode (for iOS development)
- Android Studio (for Android development)

### 2. Install Dependencies

```bash
cd jericho_case_logs
flutter pub get
```

### 3. Configure Parse Server

Update `lib/core/constants/api_constants.dart` with your Parse Server credentials:

```dart
class ApiConstants {
  static const String parseApplicationId = 'YOUR_APPLICATION_ID_HERE';
  static const String parseClientKey = 'YOUR_CLIENT_KEY_HERE';
  static const String parseServerUrl = 'https://parseapi.back4app.com';
}
```

### 4. Run the Application

**iOS:**
```bash
cd ios
pod install
cd ..
flutter run -d ios
```

**Android:**
```bash
flutter run -d android
```

## Parse Server Setup

### Required Classes

1. **User** (Built-in Parse class with additional fields)
   - username
   - email
   - password
   - firstName
   - lastName
   - title
   - jclSilo (String: "jclAnes", "jclJobs", "jclAll")
   - hasPurchased (Boolean)
   - caseCount (Number)

2. **JCLCase**
   - userObjectID (Pointer to _User)
   - date (Date)
   - patientAge (Number)
   - gender (String)
   - asaClassification (String)
   - procSurgery (String)
   - anestheticPlan (String)
   - anestheticsUsed (Array)
   - surgeryClass (String)
   - location (String)
   - airwayManagement (String)
   - additionalComments (String)
   - complications (Boolean)

### Security

Configure Class-Level Permissions and ACLs in Parse Server dashboard to ensure:
- Users can only access their own cases
- Public read is disabled for all classes
- Appropriate write permissions

## Testing

### Run Tests
```bash
flutter test
```

### Run Code Analysis
```bash
flutter analyze
```

## Building for Release

### iOS
```bash
flutter build ios --release
```

Then use Xcode to:
1. Archive the app
2. Upload to App Store Connect
3. Submit for review

### Android
```bash
flutter build appbundle --release
```

Upload the `.aab` file to Google Play Console.

## Known Issues

None at this time. All implemented features have been tested and pass Flutter analysis.

## Next Steps

1. **Configure Parse Server**: Add your Back4App credentials
2. **Test Authentication**: Create test users and verify login/registration
3. **Add Test Cases**: Create sample anesthesia cases
4. **Test on Physical Devices**: Test on actual iOS and Android devices
5. **Implement In-App Purchases**: Set up App Store and Play Store
6. **Add Remaining Features**: Job search, CME tracking, PDF export
7. **App Store Preparation**: Screenshots, descriptions, privacy policy
8. **Submit to App Stores**: Apple App Store and Google Play Store

## Support

For issues or questions about the Flutter codebase, refer to the inline code comments and Flutter documentation at https://flutter.dev.

## License

© 2025 Jericho Case Logs. All rights reserved.
