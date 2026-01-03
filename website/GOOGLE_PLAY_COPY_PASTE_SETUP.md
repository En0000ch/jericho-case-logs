# Google Play Store - Exact Copy/Paste Setup
## Zero Thinking Required - Just Follow Steps

This guide has EXACT text to copy/paste. No decisions needed.

---

## STEP 1: Create Account (5 minutes)

1. Go to: https://play.google.com/console/signup
2. Sign in with your Google account
3. Pay $25 registration fee
4. Accept terms (read and click agree)
5. **Done** ✓

---

## STEP 2: Create App (2 minutes)

1. Click **"Create app"** button
2. Fill in form EXACTLY as shown:

```
App name: Jericho Case Logs
Default language: English (United States) – United States
App or game: App
Free or paid: Free
```

3. Check both declaration boxes (privacy policy, export laws)
4. Click **"Create app"**
5. **Done** ✓

---

## STEP 3: Store Listing (10 minutes)

Click **"Store presence"** → **"Main store listing"**

### App name
```
Jericho Case Logs
```

### Short description (COPY THIS EXACTLY)
```
Track cases, grow skills, find opportunities—all in one powerful app.
```

### Full description (COPY THIS EXACTLY)
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

### App icon
- Upload your app icon (same as iOS)
- Must be 512x512 PNG
- Get from: `ios/Runner/Assets.xcassets/AppIcon.appiconset/`

### Feature graphic
- Size: 1024 x 500 pixels
- You need to create this (use Canva or Photoshop)
- Simple option: Use app name on gradient background

### Screenshots
- Upload your iOS screenshots (they'll work fine)
- Minimum: 2 screenshots
- Recommended: 4-8 screenshots

### Category
```
Medical
```

### Contact email
```
[YOUR EMAIL - the one you use for support]
```

### Privacy Policy URL
```
[YOUR PRIVACY POLICY URL]
```
*Note: If you don't have one, use: https://www.freeprivacypolicy.com/free-privacy-policy-generator/*

### Click "Save" at bottom
**Done** ✓

---

## STEP 4: App Content (5 minutes)

Click **"Policy"** → **"App content"**

### Privacy Policy
- URL: [Same as above]
- Click "Save"

### App access
- Select: "All functionality is available without special access"
- Click "Save"

### Ads
- Select: "No, my app does not contain ads"
- Click "Save"

### Content ratings
1. Click "Start questionnaire"
2. Your email: [YOUR EMAIL]
3. Select: **Medical**
4. Answer all questions with: **No** (unless app actually has these features)
5. Click "Save questionnaire"
6. Click "Calculate rating"
7. Click "Apply rating"

### Target audience
- Age: **18+**
- Click "Save"

### News app
- Select: "No"
- Click "Save"

### COVID-19 contact tracing
- Select: "No"
- Click "Save"

### Data safety
1. Click "Start"
2. **Data collection**: Select "Yes, we collect or share data"
3. **Location**: No
4. **Personal info**:
   - Email: Yes → Collected → Required → Account management
   - Name: Yes → Collected → Required → Account management
5. **Health and fitness**:
   - Health info: Yes → Collected → Required → App functionality
6. **App activity**:
   - In-app actions: Yes → Collected → Required → App functionality
7. Click "Next" → Review → "Submit"

**Done** ✓

---

## STEP 5: Enable Pre-Registration (3 minutes)

1. Click **"Release"** → **"Production"**
2. Click **"Create new release"**
3. Scroll past the "App bundles" section (leave empty)
4. Find section: **"Make this app available for pre-registration"**
5. Toggle it **ON**
6. Expected release date: Pick a date 2-3 months from now
7. Release notes (COPY THIS):
```
Coming soon! Pre-register to be notified when Jericho Case Logs launches on Android.
```
8. Click **"Save"**
9. Click **"Review release"**
10. Fix any errors it shows (usually just need to complete sections above)
11. Click **"Start rollout to Production"**
12. Confirm

**Done** ✓

---

## STEP 6: Get Your URL (Wait 1-3 days)

After Google approves (usually 24-48 hours):

1. You'll get email: "Your app is now available for pre-registration"
2. Go to Play Console
3. Click your app
4. Look for: **"View in Google Play"** button
5. Click it
6. Copy URL from browser address bar
7. Format will be: `https://play.google.com/store/apps/details?id=com.yourcompany.app`

---

## STEP 7: Update Your Website

Once you have the URL, replace this in `home-page-content.html`:

### Find this code (line ~157):
```html
<a href="#" class="download-button disabled" onclick="return false;">
    <span style="font-size: 24px;">📱</span>
    <span>Google Play (Coming Soon)</span>
</a>
```

### Replace with:
```html
<a href="YOUR_PLAY_STORE_URL_HERE" target="_blank" class="download-button">
    <span style="font-size: 24px;">📱</span>
    <span>Pre-Register on Google Play</span>
</a>
```

**Done** ✓

---

## TROUBLESHOOTING

**Error: "You must upload an APK or Bundle"**
- Make sure you toggled "Make available for pre-registration" to ON
- If still showing, try saving draft first, then enable pre-registration

**Error: "Privacy policy required"**
- Add privacy policy URL in App content section
- Use generator if needed: https://www.freeprivacypolicy.com

**Error: "Content rating required"**
- Complete the Content rating questionnaire
- Select Medical category
- Answer all questions

**Account under review**
- Google may take 1-2 weeks to verify new developer accounts
- This is normal for first-time developers
- You'll get email when approved

---

## TOTAL TIME: ~30 minutes

- Step 1: 5 min
- Step 2: 2 min
- Step 3: 10 min
- Step 4: 5 min
- Step 5: 3 min
- Step 6: Wait for approval
- Step 7: 2 min

---

## WHAT YOU NEED READY

Before starting, have these ready:

- [ ] Google account login
- [ ] Credit card ($25 payment)
- [ ] App icon (512x512 PNG)
- [ ] App screenshots (from iOS)
- [ ] Support email address
- [ ] Privacy policy URL (or use generator)

---

## SCREENSHOTS FOR GOOGLE PLAY

### Where to get them:
1. Take screenshots from your iOS simulator
2. Or use screenshots from App Store Connect
3. Or create new ones specifically for Play Store

### Requirements:
- Format: PNG or JPG
- Minimum: 2 screenshots
- Recommended: 4-8 screenshots
- Dimensions: 1080 x 1920 pixels (or your device resolution)

### Quick way:
1. Open iOS simulator
2. Run your app
3. Navigate to key screens
4. Cmd+S to save screenshot
5. Upload to Play Console

---

## AFTER APPROVAL

When approved, you'll be able to:

1. Track pre-registrations
2. See how many people signed up
3. When Android app is ready:
   - Upload APK/AAB
   - Toggle pre-registration OFF
   - Change status to Production
   - All pre-registered users get notified automatically

---

## NEED HELP?

If you get stuck:
1. Google Play Console has inline help tooltips
2. Click the "?" icons for explanations
3. Or message me with the exact error message

---

**IMPORTANT**: You MUST do this yourself because:
- Requires your Google login
- Needs payment authorization
- Legal agreements require your acceptance
- Google verifies the account owner

But this guide has EVERYTHING you need to copy/paste. No guessing!

---

Last Updated: December 30, 2025
iOS App: https://apps.apple.com/us/app/jericho-case-logs/id6466726836
