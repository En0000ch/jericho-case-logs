# Google Play Store Pre-Registration Setup Guide
## Jericho Case Logs - Android Coming Soon

You can set up a Google Play Store listing NOW, even before your Android app is ready. This allows you to:
- Build anticipation for the Android version
- Collect email addresses of interested users
- Get your Play Store URL early
- Start appearing in Play Store searches
- Track pre-registration metrics

---

## Option 1: Pre-Registration (Recommended)

### Step 1: Create Google Play Developer Account
1. Go to https://play.google.com/console/signup
2. Pay one-time $25 registration fee
3. Complete account setup

### Step 2: Create App Listing
1. Go to Google Play Console: https://play.google.com/console
2. Click "Create app"
3. Fill in basic details:
   - **App name**: Jericho Case Logs
   - **Default language**: English (United States)
   - **App or game**: App
   - **Free or paid**: Free

### Step 3: Fill Store Listing
Navigate to "Store presence" → "Main store listing":

**App name**: Jericho Case Logs

**Short description** (80 chars max):
```
Track cases, grow skills, find opportunities—all in one powerful app.
```

**Full description** (4000 chars max):
```
Your Career Command Center for Medical Professionals

Jericho Case Logs is the ultimate tool for CRNAs, nurses, and traveling medical professionals who want to advance their careers. Track your clinical experience, generate professional reports, and discover new opportunities—all in one beautifully designed app.

🏥 INTELLIGENT CASE LOGGING
Quickly document cases with smart forms designed specifically for medical professionals. Track procedures, patient demographics, anesthetic techniques, and outcomes with ease.

📊 VISUAL ANALYTICS & REPORTS
See your experience at a glance with beautiful charts and graphs. Generate professional reports for credentialing, CME tracking, or performance reviews in seconds.

🔍 MEDICAL JOB SEARCH
Discover travel nursing, CRNA, and locum opportunities across the country. Filter by location, pay, facility type, and more to find your perfect next assignment.

📅 CALENDAR MANAGEMENT
Keep track of your work schedule, assignments, and important dates. Sync with your device calendar and never miss a shift.

🏥 FACILITY & SURGEON DATABASE
Build your own database of facilities and surgeons you've worked with. Quickly add them to new cases.

📱 WORKS OFFLINE
Log cases even without internet connection. Your data automatically syncs when you're back online.

🔒 HIPAA-COMPLIANT & SECURE
Your data is encrypted and stored securely. We never store identifiable patient information.

✨ DESIGNED FOR:
• CRNAs & Anesthesia Professionals (Available Now)
• Nurses (Available Now)
• Scrub Techs (Coming Soon)
• Physicians (Coming Soon)

💯 100% FREE
• No ads
• No hidden fees
• No subscription required

Download Jericho Case Logs today and take control of your professional future.
```

**App icon**: Use your iOS app icon (512x512 PNG)

**Feature graphic**: Create 1024x500 banner (required)

**Phone screenshots**: Upload at least 2 screenshots (recommended: 4-8)

**Category**: Medical

**Contact email**: Your support email

**Privacy Policy URL**: Your privacy policy URL

### Step 4: Enable Pre-Registration
1. Go to "Release" → "Production"
2. Click "Create new release"
3. Instead of uploading an APK/AAB, scroll down
4. Enable "Make this app available for pre-registration"
5. Set expected launch date (you can change this later)
6. Click "Save" then "Review release"
7. Submit for review

### Step 5: Get Your Play Store URL
Once approved (usually 1-3 days):
1. Go to "Store presence" → "Main store listing"
2. Find your Play Store URL at the top
3. Format: `https://play.google.com/store/apps/details?id=com.yourcompany.jerichocaselogs`

### Step 6: Update Website
Replace this in `home-page-content.html`:
```html
<a href="#" class="download-button disabled" onclick="return false;">
    <span style="font-size: 24px;">📱</span>
    <span>Google Play (Coming Soon)</span>
</a>
```

With:
```html
<a href="YOUR_PLAY_STORE_URL" target="_blank" class="download-button">
    <span style="font-size: 24px;">📱</span>
    <span>Pre-Register on Google Play</span>
</a>
```

---

## Option 2: "Coming Soon" Without Pre-Registration

If you don't want to set up pre-registration yet, you can:

### Create a Landing Page
Add a "Notify Me" form on your website:
```html
<div class="notify-section">
    <h3>Android Version Coming Soon!</h3>
    <p>Be the first to know when Jericho Case Logs launches on Android.</p>
    <form action="YOUR_EMAIL_SERVICE" method="post">
        <input type="email" placeholder="Enter your email" required>
        <button type="submit">Notify Me</button>
    </form>
</div>
```

### Email Services You Can Use:
- Mailchimp (free for <500 contacts)
- ConvertKit
- EmailOctopus
- Google Forms (simplest option)

---

## What You Need for Android App

When you're ready to build the Android version:

### Required Files:
1. **App Bundle (AAB)** or APK file
   - Build with Flutter: `flutter build appbundle`
   - File location: `build/app/outputs/bundle/release/app-release.aab`

2. **App Signing Key**
   - Generate with: `keytool -genkey -v -keystore ~/jericho-release-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias jericho`
   - Store securely and back up!

3. **Build Configuration**
   - Update `android/app/build.gradle`
   - Set correct package name (applicationId)
   - Configure signing

### Flutter Android Build Checklist:
- [ ] Set package name in `build.gradle`
- [ ] Create app icons for all densities
- [ ] Update `AndroidManifest.xml` permissions
- [ ] Test on multiple Android devices/emulators
- [ ] Generate signed AAB
- [ ] Upload to Play Console
- [ ] Fill out content rating questionnaire
- [ ] Complete privacy policy
- [ ] Submit for review

---

## Timeline Recommendation

**Do Now (Before Android App Ready):**
1. ✅ Create Google Play developer account ($25)
2. ✅ Set up pre-registration listing
3. ✅ Upload screenshots and graphics
4. ✅ Get Play Store URL
5. ✅ Update website with pre-registration link

**Benefits:**
- URL is secured and won't change
- Start collecting interested users
- App appears in Play Store searches
- Shows professionalism and commitment
- Users can wishlist your app

**Do Later (When Android App Ready):**
1. Build and test Android version
2. Generate signed AAB
3. Upload to existing listing
4. Change status from "Pre-registration" to "Production"
5. Launch!

---

## Current Status

**iOS App:**
- ✅ Live on App Store
- URL: https://apps.apple.com/us/app/jericho-case-logs/id6466726836
- Status: Production

**Android App:**
- ⏳ Not yet started
- Play Store: Not set up
- Recommendation: Set up pre-registration NOW

---

## Next Steps

1. **Immediate**: Create Google Play developer account
2. **This Week**: Set up pre-registration listing
3. **Get Play Store URL**: Update website
4. **Later**: Build Android version with Flutter

---

## Resources

- Google Play Console: https://play.google.com/console
- Pre-registration Guide: https://support.google.com/googleplay/android-developer/answer/9084187
- Flutter Android Build: https://docs.flutter.dev/deployment/android
- Play Store Asset Requirements: https://support.google.com/googleplay/android-developer/answer/9866151

---

**Last Updated**: December 30, 2025
**iOS App ID**: 6466726836
**Google Play**: Not yet set up
