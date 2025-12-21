# New Computer Setup Guide
## JerichoCaseLogs - Flutter App & Website

This guide will help you set up your development environment on a new computer.

---

## 📋 Prerequisites Checklist

Before starting, ensure you have:
- [ ] GitHub/GitLab account credentials
- [ ] Back4App account credentials
- [ ] WordPress/DreamHost credentials
- [ ] Apple Developer account (for iOS development)
- [ ] Admin access to your new Mac

---

## 🛠️ Part 1: Install Required Software

### 1. Install Homebrew
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### 2. Install Flutter
```bash
# Using Homebrew
brew install --cask flutter

# Add Flutter to PATH (add to ~/.zshrc or ~/.bash_profile)
export PATH="$PATH:`pwd`/flutter/bin"

# Verify installation
flutter doctor
```

### 3. Install Xcode
```bash
# From Mac App Store or
xcode-select --install

# Accept license
sudo xcodebuild -license accept

# Install CocoaPods
sudo gem install cocoapods
```

### 4. Install Android Studio (Optional - for Android development)
Download from: https://developer.android.com/studio

### 5. Install Git (should be pre-installed)
```bash
git --version
# If not installed:
xcode-select --install
```

### 6. Install Claude Code
Visit: https://claude.com/claude-code
Or use your preferred AI coding assistant.

---

## 📦 Part 2: Clone Your Repository

### Get Repository URL
Your git repository should be at:
- GitHub: `https://github.com/YOUR_USERNAME/jericho_case_logs`
- Or wherever you pushed it

### Clone the Repository
```bash
# Navigate to desired location
cd ~/Desktop

# Clone the repository
git clone <YOUR_REPOSITORY_URL>

# Navigate into the project
cd jericho_case_logs
```

---

## 🔧 Part 3: Flutter App Setup

### 1. Install Dependencies
```bash
cd jericho_case_logs
flutter pub get
```

### 2. Run Flutter Doctor
```bash
flutter doctor
```

Fix any issues it reports. Common issues:
- Xcode not installed → Install from App Store
- CocoaPods not installed → `sudo gem install cocoapods`
- Android toolchain missing → Install Android Studio (if needed)

### 3. Configure iOS Signing
```bash
# Open iOS project in Xcode
open ios/Runner.xcworkspace
```

In Xcode:
1. Select "Runner" in the project navigator
2. Go to "Signing & Capabilities"
3. Select your Team
4. Update Bundle Identifier if needed

### 4. Test the App
```bash
# List available devices
flutter devices

# Run on iOS Simulator
flutter run -d iphone

# Or run on physical device
flutter run -d <device-id>
```

---

## 🌐 Part 4: Website Integration Setup

### Key Website Files Location
All website files are in the `website/` folder:
```
website/
├── jcl-poster-dashboard.js       # Main dashboard JavaScript
├── jcl-poster-dashboard.css      # Dashboard styles
├── setup-app-settings.html       # Admin tool for settings
├── add-posting-limit-field.html  # Admin tool for posting limits
├── back4app-main.js              # Cloud Code (deploy to Back4App)
└── breakdance/                   # Page builder files
```

### Website Files on Desktop
Some files were also saved to Desktop for easy access:
- `/Users/barrett/Desktop/jcl-poster-dashboard.js`
- `/Users/barrett/Desktop/jcl-poster-dashboard.css`
- `/Users/barrett/Desktop/setup-app-settings.html`
- `/Users/barrett/Desktop/back4app-main.js`

Copy these to your new Desktop if needed.

---

## 🔐 Part 5: Configure Back4App

### 1. Back4App Credentials
```
Application ID: 9Zso4zMCOLF1kANT60AXD2JgqUuaKjJjQYEIsFMH
JavaScript Key: xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI
Master Key: [Get from Back4App Dashboard → Settings → Security & Keys]
```

### 2. Deploy Cloud Code
1. Go to Back4App Dashboard: https://dashboard.back4app.com
2. Navigate to: Cloud Code → Cloud Functions
3. Upload `website/back4app-main.js` or copy/paste the contents
4. Click "Save" to deploy

### 3. Set Up AppSettings Table
1. Open `website/setup-app-settings.html` in a browser
2. Enter your Application ID and Master Key
3. Set cost per post (default: 1)
4. Click "Create/Update Settings"

### 4. Verify Tables Exist
Check that these tables exist in Back4App:
- `_User` (built-in)
- `PosterProfile`
- `Organization` or `EmployerOrganization`
- `JobPosting`
- `AppSettings`

Add `postingLimit` field to Organization table if not present:
- Field name: `postingLimit`
- Type: Number
- Default value: 1

---

## 🚀 Part 6: WordPress Integration

### 1. Upload Dashboard Files
Upload these files to your WordPress site:

```bash
# Via FTP/SFTP or SSH
scp website/jcl-poster-dashboard.js user@your-site.com:/path/to/wordpress/wp-content/themes/your-theme/js/
scp website/jcl-poster-dashboard.css user@your-site.com:/path/to/wordpress/wp-content/themes/your-theme/css/
```

### 2. Enqueue Scripts in WordPress
Add to your theme's `functions.php`:

```php
function jcl_enqueue_poster_dashboard() {
    if (is_page('employer-dashboard')) {
        wp_enqueue_script('parse-sdk', 'https://npmcdn.com/parse@3.4.1/dist/parse.min.js', array(), '3.4.1', true);
        wp_enqueue_script('jcl-poster-dashboard', get_template_directory_uri() . '/js/jcl-poster-dashboard.js', array('parse-sdk'), '1.0', true);
        wp_enqueue_style('jcl-poster-dashboard', get_template_directory_uri() . '/css/jcl-poster-dashboard.css', array(), '1.0');
    }
}
add_action('wp_enqueue_scripts', 'jcl_enqueue_poster_dashboard');
```

### 3. Create Dashboard Page
1. Create a new page in WordPress: "Employer Dashboard"
2. Add this HTML to the page content:
```html
<div id="jcl-dashboard-root"></div>
```

---

## 🔑 Part 7: Important Credentials Reference

### Back4App
```
Application ID: 9Zso4zMCOLF1kANT60AXD2JgqUuaKjJjQYEIsFMH
JavaScript Key: xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI
Master Key: [From Back4App Dashboard]
Server URL: https://parseapi.back4app.com/
```

### DreamHost SSH
```
Host: iad1-shared-b7-38.dreamhost.com
Username: dh_w5amcg
Password: Sylo0001!
Port: 22
```

### Firebase (if using)
Configuration files are in:
- `lib/firebase_options.dart`
- `FIREBASE_SETUP.md`

---

## ✅ Part 8: Verify Everything Works

### Test Checklist

#### Flutter App:
- [ ] `flutter doctor` shows all green checkmarks
- [ ] App runs on iOS Simulator
- [ ] App runs on physical device
- [ ] Can create and save cases
- [ ] Database sync works with Back4App

#### Website:
- [ ] Dashboard loads at /employer-dashboard page
- [ ] User can log in
- [ ] Credits display shows correct values
- [ ] Job posting works (if user has credits)
- [ ] Purchase modal appears when credits = 0
- [ ] Verification status displays correctly

---

## 📚 Part 9: Documentation Files

### Available Documentation:
- `FIREBASE_SETUP.md` - Firebase configuration guide
- `SURGERY_FEATURE_QUICK_REF.md` - Surgery feature reference
- `SURGERY_LOADING_FEATURE.md` - Surgery loading documentation
- `website/WORDPRESS-SETUP-GUIDE.md` - WordPress setup
- `website/BREAKDANCE-DESIGN-SPEC.md` - Design specifications

---

## 🐛 Common Issues & Solutions

### Flutter Issues

**Issue: "Unable to locate Android SDK"**
```bash
# Install Android Studio
brew install --cask android-studio
# Open Android Studio and install SDK
```

**Issue: "CocoaPods not installed"**
```bash
sudo gem install cocoapods
cd ios && pod install
```

**Issue: "Xcode license not accepted"**
```bash
sudo xcodebuild -license accept
```

### Website Issues

**Issue: "Parse SDK not loading"**
- Make sure Parse SDK is loaded before dashboard JS
- Check browser console for errors
- Verify Parse initialization with correct credentials

**Issue: "Credits not displaying"**
- Check AppSettings table exists in Back4App
- Verify costPerPost field is set
- Check browser console for API errors

**Issue: "Can't create job posts"**
- Verify Cloud Code is deployed
- Check organization verificationStatus
- Ensure postingLimit field exists

---

## 🔄 Part 10: Keeping Code in Sync

### Pull Latest Changes
```bash
cd ~/Desktop/jericho_case_logs
git pull origin master
flutter pub get
```

### Commit Your Changes
```bash
git add .
git commit -m "Your commit message"
git push origin master
```

### Use Claude Code
```bash
# Start Claude Code in project directory
cd ~/Desktop/jericho_case_logs
claude
```

---

## 📞 Support & Resources

### Useful Links:
- Flutter Docs: https://docs.flutter.dev
- Back4App Docs: https://www.back4app.com/docs
- Parse SDK Docs: https://docs.parseplatform.org
- Claude Code: https://claude.com/claude-code

### Your Git Repository:
**IMPORTANT**: Add your actual repository URL here after pushing:
```
Repository URL: [YOUR_REPO_URL_HERE]
```

---

## 🎯 Quick Start Commands

```bash
# One-time setup
cd ~/Desktop
git clone <YOUR_REPO_URL>
cd jericho_case_logs
flutter pub get
flutter doctor

# Daily development
cd ~/Desktop/jericho_case_logs
git pull
flutter run -d iphone
# ... make changes ...
git add .
git commit -m "Description of changes"
git push
```

---

## ✨ You're All Set!

Follow this guide step-by-step and you'll have your development environment ready on your new computer. If you encounter any issues, refer to the troubleshooting section or check the documentation files.

Happy coding! 🚀

---

**Last Updated**: December 21, 2025
**Commit**: 5818671 - Add job posting system with dynamic credit pricing and payment flow
