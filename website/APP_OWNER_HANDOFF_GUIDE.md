# App Owner Handoff Guide
## What You Need to Do to Own & Control Your App

This guide explains everything you need to set up as the app owner. Your developer has built the app, but you need to own the accounts and control distribution.

---

## 📱 OVERVIEW: What You're Getting

- ✅ **iOS App**: Already live on App Store (ID: 6466726836)
- 🔜 **Android App**: Can be built when ready
- 🌐 **Website**: Content ready to publish
- 💼 **Job Posting System**: Backend already configured

---

## 🎯 YOUR ACTION ITEMS

Follow these steps in order. Each takes 10-30 minutes.

---

## STEP 1: Apple Developer Account Setup ✅
**Status**: Likely already done (since iOS app is live)
**Cost**: $99/year

### What This Is:
- Required to publish apps on the Apple App Store
- Annual subscription that auto-renews
- Gives you control over the iOS app

### If Not Set Up Yet:
1. Go to: https://developer.apple.com/programs/enroll/
2. Sign in with your Apple ID (or create one)
3. Choose: **Individual** or **Organization**
   - Individual: Uses your personal name
   - Organization: Uses company name (requires D-U-N-S number)
4. Pay $99 annual fee
5. Wait for approval (usually 24-48 hours)

### What to Give Your Developer:
- **App Store Connect access**: Invite them as "Developer" role
- Go to: https://appstoreconnect.apple.com
- Users and Access → Add user → Select "Developer" role
- Enter their email address

---

## STEP 2: Google Play Developer Account ⚠️
**Status**: NOT SET UP YET (You need to do this)
**Cost**: $25 one-time fee

### What This Is:
- Required to publish Android apps on Google Play Store
- One-time $25 fee (never expires)
- Gives you control over the Android app

### How to Set It Up:
1. Go to: https://play.google.com/console/signup
2. Sign in with a Google account
   - ⚠️ **IMPORTANT**: Use a business email or Google Workspace account
   - Don't use personal Gmail if possible (harder to transfer later)
3. Pay $25 registration fee
4. Complete identity verification
   - Google may ask for phone verification
   - May ask for ID upload
5. Accept developer agreement
6. Wait for account approval (can take 1-2 weeks for new accounts)

### What to Give Your Developer:
**Option A - Add them as User (Recommended):**
1. Go to: https://play.google.com/console
2. Click: Settings → Users and permissions
3. Click: Invite new users
4. Enter developer's email
5. Grant permissions:
   - ✅ View app information
   - ✅ Manage production releases
   - ✅ Manage store presence
   - ✅ Reply to reviews
   - ❌ Manage orders (leave unchecked)
   - ❌ Global app reports (optional)

**Option B - Share Login (Less Secure):**
- Give them the Google account email and password
- Enable 2-factor authentication
- Give them backup codes

---

## STEP 3: Back4App Account Setup ✅
**Status**: Already configured
**Cost**: Free tier (upgrade if needed)

### What This Is:
- Backend database service (stores user data, cases, jobs)
- Currently on free tier
- May need upgrade as users grow

### Current Setup:
```
App ID: 9Zso4zMCOLF1kANT60AXD2JgqUuaKjJjQYEIsFMH
JavaScript Key: xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI
```

### What You Should Do:
1. Get login credentials from your developer
2. Go to: https://dashboard.back4app.com
3. Change password to something only you know
4. Add your own email as admin
5. Remove developer access when project is complete (if desired)

### Future Costs:
- Free: Up to 25,000 requests/month
- Upgrade: $5-25/month for more users
- Monitor usage in dashboard

---

## STEP 4: Website/Domain Setup
**Status**: Depends on your hosting

### What You Need:
- **Domain name**: jerichocaselogs.com (or whatever you chose)
- **Web hosting**: WordPress-compatible hosting
- **Access**: cPanel/FTP login credentials

### Current Setup:
- Developer has website files ready
- Need to upload to your hosting
- WordPress site at: [YOUR DOMAIN]

### What to Give Your Developer:
- FTP/SFTP credentials
- WordPress admin login
- Or cPanel access

### Provider Examples:
- DreamHost (current setup)
- Bluehost
- SiteGround
- WP Engine

---

## STEP 5: Google Analytics / App Analytics (Optional)
**Recommended but not required**

### Why You Need This:
- Track how many people use your app
- See which features are popular
- Understand user demographics
- Make data-driven decisions

### How to Set Up:

**For Website:**
1. Go to: https://analytics.google.com
2. Create account
3. Get tracking ID
4. Give to developer to add to website

**For Mobile Apps:**
1. Use built-in analytics:
   - Apple: App Store Connect → Analytics
   - Google: Play Console → Statistics
2. Or use Firebase Analytics (free)
   - Go to: https://firebase.google.com
   - Create project
   - Give developer credentials to integrate

---

## STEP 6: Payment Processing (For Job Posts)
**Status**: Needs to be set up

### What This Is:
- How employers pay for job posting credits
- Required before you can charge money

### Options:

**Option A: Stripe (Recommended)**
- Easy to set up
- 2.9% + 30¢ per transaction
- Sign up: https://stripe.com
- Give developer API keys to integrate

**Option B: PayPal**
- Slightly higher fees
- More familiar to users
- Sign up: https://paypal.com/business

**Option C: In-App Purchases (Apple/Google)**
- 30% fee (very high!)
- Only for digital goods
- Not recommended for job posts

### Setup Steps:
1. Create Stripe account
2. Complete business verification
3. Connect bank account
4. Get API keys (Publishable & Secret)
5. Give keys to developer
6. Test with small transaction
7. Go live

---

## STEP 7: Legal Documents
**Status**: REQUIRED before going live

### What You Need:

**1. Privacy Policy** (REQUIRED)
- Explains how you handle user data
- Required by Apple & Google
- Use generator: https://www.freeprivacypolicy.com
- Or hire lawyer ($300-1000)

**2. Terms of Service** (REQUIRED)
- Rules for using your app
- Protects you legally
- Use generator or lawyer

**3. HIPAA Compliance** (IMPORTANT)
- Your app deals with medical data
- Even though you don't store patient names, be careful
- Consider consulting healthcare lawyer
- Back4App is HIPAA-compliant if configured correctly

**4. Business Entity** (Recommended)
- Form LLC or Corporation
- Protects personal assets
- Costs $100-500 depending on state
- Use: LegalZoom, Incfile, or local lawyer

### Where These Go:
- Add URLs to app listings (Apple & Google)
- Link in website footer
- Include in app settings

---

## STEP 8: App Store Listings
**Status**: iOS done, Android pending

### What Your Developer Needs from You:

**Screenshots:**
- Take from the built app
- Or developer can provide
- Need different sizes for iPhone/iPad/Android

**App Description:**
- Already written (in home-page-content.html)
- Can customize if desired

**App Name:**
- Jericho Case Logs (currently)
- Can change if desired (affects branding)

**Support Email:**
- Where users contact you for help
- Create: support@jerichocaselogs.com
- Or use personal email initially

**App Icon:**
- Already designed
- Make sure you have high-res version saved

---

## STEP 9: Ongoing Maintenance Access

### What Developer Needs Long-Term:

**For Updates & Bug Fixes:**
- App Store Connect access (Developer role)
- Google Play Console access (Release manager role)
- Back4App access (or just API keys)
- Website FTP/WordPress access

**For New Features:**
- Source code access (should be in git repository)
- Design assets
- API documentation

### Recommended: Set Up Contract
- Hourly rate or retainer for updates
- Response time SLA (how fast they fix bugs)
- Who owns the code (you should!)
- Non-compete clause
- Confidentiality agreement

---

## STEP 10: Business Operations

### Customer Support:
- Create support email: support@jerichocaselogs.com
- Set up help desk (Zendesk, Help Scout, or simple email)
- Create FAQ page on website
- Monitor app store reviews and respond

### Marketing:
- App Store Optimization (ASO)
- Social media accounts
- Email list (MailChimp, ConvertKit)
- Paid ads (Google, Facebook)

### Financials:
- Business bank account
- Accounting software (QuickBooks, Wave)
- Track revenue from job postings
- Track expenses (Apple fee, hosting, etc.)

---

## 📋 CHECKLIST: What You Need to Do NOW

Priority order:

### HIGH PRIORITY (Do This Week):
- [ ] Create Google Play Developer account ($25)
- [ ] Add developer as user to Apple App Store Connect
- [ ] Add developer as user to Google Play Console
- [ ] Get Back4App login credentials from developer
- [ ] Create support email address
- [ ] Generate privacy policy & terms of service

### MEDIUM PRIORITY (Do This Month):
- [ ] Set up Stripe or PayPal for payments
- [ ] Set up Google Analytics
- [ ] Register business entity (LLC)
- [ ] Review and approve app store listings
- [ ] Set up customer support system

### LOW PRIORITY (Do Eventually):
- [ ] Set up Firebase Analytics
- [ ] Create social media accounts
- [ ] Plan marketing strategy
- [ ] Hire customer support help (if needed)

---

## 💰 COST SUMMARY

### One-Time Costs:
- Google Play: $25
- Privacy Policy (DIY): $0 (or $300-1000 with lawyer)
- Business Formation: $100-500
- Domain Name: $10-20/year
- **Total: ~$135-1,545**

### Annual Costs:
- Apple Developer: $99/year
- Web Hosting: $60-300/year
- Domain Renewal: $10-20/year
- Back4App: $0-300/year (depending on usage)
- **Total: ~$169-719/year**

### Transaction Costs:
- Stripe: 2.9% + 30¢ per transaction
- If you sell $1000 worth of job posts: ~$29 in fees

---

## 🔐 PASSWORD MANAGEMENT

**CRITICAL**: Keep all these secure!

### Accounts You'll Have:
1. Apple ID / App Store Connect
2. Google Play Console
3. Back4App
4. Domain registrar
5. Web hosting
6. Stripe/PayPal
7. Analytics accounts

### Recommended:
- Use password manager (1Password, LastPass, Bitwarden)
- Enable 2-factor authentication on EVERYTHING
- Save backup codes
- Share access with trusted person in case of emergency

---

## 📞 SUPPORT & HANDOFF

### Questions to Ask Your Developer:

1. **Code Ownership:**
   - "Do I own the source code?"
   - "Where is it stored?" (GitHub, etc.)
   - "Can I hire another developer to work on it?"

2. **Post-Launch:**
   - "What's included in this project?"
   - "What's your rate for future updates?"
   - "How fast can you fix critical bugs?"
   - "What if I want to switch developers?"

3. **Technical:**
   - "What happens if Back4App goes down?"
   - "How do I make changes to job posting prices?"
   - "Can you document the admin tools?"
   - "What's the backup strategy for user data?"

### Get These from Developer:
- [ ] Source code repository access
- [ ] All login credentials (in password manager)
- [ ] Documentation for admin features
- [ ] Emergency contact info
- [ ] List of all services used
- [ ] Backup of database
- [ ] Design files (Figma, Sketch, etc.)

---

## ⚠️ RED FLAGS TO AVOID

**Don't Let Developer:**
- Use their own Apple/Google accounts (you lose control)
- Keep source code secret or locked away
- Require only them to do updates (vendor lock-in)
- Use their personal payment accounts
- Register domain in their name

**You Should:**
- Own all accounts
- Have source code access
- Be able to hire different developers
- Control payment processing
- Own domain name

---

## 🎓 LEARNING RESOURCES

### Non-Technical Resources:
- Apple App Store Guidelines: https://developer.apple.com/app-store/review/guidelines/
- Google Play Policies: https://play.google.com/about/developer-content-policy/
- HIPAA for Apps: https://www.hhs.gov/hipaa/for-professionals/security/guidance/index.html

### Business Resources:
- How to Market an App: https://www.apptentive.com/blog/mobile-app-marketing-guide/
- App Store Optimization: https://www.apptentive.com/blog/app-store-optimization/
- Running a SaaS Business: https://www.saastr.com

---

## 📝 NEXT STEPS

**Week 1:**
1. Read this entire document
2. Create Google Play account
3. Set up support email
4. Add developer to accounts

**Week 2:**
1. Generate legal documents
2. Set up payment processing
3. Review app listings

**Week 3:**
1. Launch Android pre-registration
2. Set up analytics
3. Plan marketing strategy

**Week 4:**
1. Soft launch to small audience
2. Collect feedback
3. Fix any issues
4. Full public launch

---

## 💬 Questions?

Create a document with:
- What you've completed
- What you're stuck on
- Specific questions for developer

Keep communication clear and organized!

---

**Remember**: You're not expected to understand all the technical details. That's what you hired a developer for. But you DO need to own and control the business assets (accounts, domain, code).

Good luck with your app launch!

---

**Document Version**: 1.0
**Last Updated**: December 30, 2025
**Your App**: Jericho Case Logs
**iOS App ID**: 6466726836
**Status**: iOS Live, Android Pending
