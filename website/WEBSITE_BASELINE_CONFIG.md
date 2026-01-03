# Website Baseline Configuration
## Jericho Case Logs - Official Baseline Snapshot
**Created**: December 31, 2025
**Purpose**: Master reference for website configuration and deployment

---

## 📋 Overview

This document captures the **baseline configuration** for the Jericho Case Logs website, including:
- WordPress plugin configuration (JCL Complete Site Fix V35)
- Parse/Back4App backend integration
- Static landing pages
- File structure and assets

---

## 🏗️ Website Architecture

### Primary Files

| File | Purpose | Status |
|------|---------|--------|
| `index.html` | Static landing page for mobile/desktop | ✅ Active |
| `index.php` | Mobile redirect handler | ✅ Active |
| `jcl-complete-site-fix-v35.php` | **BASELINE PLUGIN** - WordPress homepage animation | ✅ Baseline |
| `jcl-complete-site-fix-v36.php` | Next version (with banners re-added) | 🔄 Development |
| `admin-dashboard.html` | Standalone admin dashboard | ✅ Active |

### Supporting Files

**Home Page Variations** (for testing/reference):
- `page-home.php`
- `page-home-animated.php`
- `page-home-stars.php`
- `page-home-minimal.php`
- `page-home-refined.php`
- `page-home-simple-float.php`
- Various v2, v3, v4 iterations

**Documentation**:
- `ACTIVATE_HOME_PAGE.md`
- `ACTIVATE_HOME_PAGE_EXISTING.md`
- `ADMIN_DASHBOARD_SETUP.md`
- `APP_OWNER_HANDOFF_GUIDE.md`
- `GOOGLE_PLAY_SETUP.md`
- `WORDPRESS_UPLOAD_GUIDE.md`
- `BREAKDANCE-DESIGN-SPEC.md`

---

## 🔌 Baseline Plugin: JCL Complete Site Fix V35

**File**: `jcl-complete-site-fix-v35.php`
**Version**: 35.2
**Description**: FIXED - All 4 animations in Phase 1, images converge to exact center (50%, 50%), login modal popup

### Key Features

#### 1. Visual Design
- **Background**: Starry space theme (`starry_bkgd.jpg`)
- **Font**: Century Gothic (lightweight 300 weight)
- **Colors**:
  - Primary cyan: `#00f3ff`
  - Secondary purple: `#a855f7`
- **Transparent headers/footers**

#### 2. Employers Menu
- **Position**: Fixed top-right (20px from top, 30px from right)
- **Style**: Dropdown with login + create account options
- **Hover effect**: Cyan → purple glow transition

#### 3. Homepage Scroll Animation (5 Phases)

**Phase 1 (0-25% scroll)**: Robot animations
- Wake up → Arise → Handstand flip → Charged slam
- All 4 animations play sequentially
- Robot visible at bottom-center

**Phase 2 (25-50% scroll)**: Banner elements (REMOVED in v35)
- Originally: banners slide from sides
- **v35 status**: Banner CSS and elements removed per user request

**Phase 3 (50-70% scroll)**: Convergence (REMOVED in v35)
- Originally: images merge to exact center (50%, 50%)
- Star burst effect intensifies
- **v35 status**: Banner convergence removed

**Phase 4 (70-85% scroll)**: Disintegration (REMOVED in v35)
- Originally: images fade at center, maximum star burst
- Robot fades out
- **v35 status**: Banner disintegration removed

**Phase 5 (85-100% scroll)**: Final state (REMOVED in v35)
- Originally: small logo in header
- **v35 status**: Header banner removed
- Clean scrollable content

#### 4. Visual Effects
- **Meteor shower**: Animated meteors with parallax
- **Twinkling stars**: 80 stars with dynamic opacity
- **Light burst**: Radial/conic gradient explosion effect
- **Mouse parallax**: Slight movement based on cursor position

#### 5. 3D Robot Model
- **Model**: `Meshy_AI_Meshy_Merged_Animations.glb`
- **Library**: Three.js (v0.160.0)
- **Loader**: GLTFLoader
- **Lighting**: Dual directional lights (cyan + purple) + ambient
- **Animations**:
  - Index 4: wake_up
  - Index 6: arise
  - Index 1: handstand_flip
  - Index 7: charged_slam

---

## 🗄️ Backend Configuration

### Parse/Back4App Credentials

**Application ID**: `9Zso4zMCOLF1kANT60AXD2JgqUuaKjJjQYEIsFMH`
**JavaScript Key**: `xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI`
**Master Key**: [Stored in Back4App Dashboard → Settings → Security]
**Server URL**: `https://parseapi.back4app.com/`

### Database Tables

| Table | Purpose |
|-------|---------|
| `_User` | Built-in Parse user authentication |
| `PosterProfile` | Job poster profiles (employers) |
| `Organization` | Employer organizations with verification |
| `EmployerOrganization` | Alternative employer table |
| `JobPosting` | Job listings |
| `AppSettings` | Global app configuration (cost per post, etc.) |

### AppSettings Configuration
- **costPerPost**: 1 (default credit cost per job posting)
- **postingLimit**: 1 (default number of posts per organization)

---

## 📦 Required Assets

### Images
- `starry_bkgd.jpg` - Space background
- `jclBanner_3d.png` - 3D banner logo (REMOVED from v35)
- `1024Logo.png` - App logo
- `jclRobot.png` - Robot reference image
- `apple-app-store.webp` - iOS download badge
- `google-play-1-300x93.webp` - Android download badge

### 3D Models
- `Meshy_AI_Meshy_Merged_Animations.glb` - Animated robot model

### External Dependencies
- Three.js v0.160.0 (via CDN)
- GLTFLoader (Three.js addon)
- Parse SDK v5.3.0 (via npmcdn)

---

## 🚀 WordPress Deployment

### Plugin Installation
1. Upload `jcl-complete-site-fix-v35.php` to `/wp-content/plugins/`
2. Activate in WordPress admin
3. Plugin hooks automatically apply to homepage

### Required WordPress Setup
- Theme: Any (plugin overrides with !important)
- Pages needed:
  - Homepage (automatically styled)
  - Employer Dashboard page (for job posting interface)
  - Employer Registration page

### Page Builder Integration
- Compatible with Breakdance page builder
- Transparent headers/footers override theme styles
- Animation container: `#jcl-scroll-container` (500vh height)

---

## 🌐 Static Landing Page

### index.html Features
- **Responsive design**: Mobile-first, desktop-optimized
- **Sections**:
  1. Hero: Title + tagline
  2. Professional types: CRNAs, Nurses, Scrub Techs, Physicians
  3. Key features: 8 feature cards
  4. Benefits: Why choose JCL
  5. Download buttons: App Store + Google Play
  6. Footer: Links to privacy, post-a-job, employer login

### index.php Mobile Redirect
- Detects mobile phones (not tablets)
- Serves `index.html.mobile` for mobile devices
- Loads full WordPress for desktop/tablet
- **Note**: `index.html.mobile` file not present (needs creation)

---

## 📝 Configuration Checklist

### Before Deployment
- [ ] Upload all image assets to `/wp-content/uploads/2025/12/`
- [ ] Upload 3D model to same directory
- [ ] Verify Parse credentials in all files
- [ ] Test admin dashboard Parse connection
- [ ] Create `index.html.mobile` or update `index.php` redirect
- [ ] Install and activate v35 plugin
- [ ] Test scroll animation on homepage
- [ ] Verify Employers dropdown menu works
- [ ] Test mobile responsiveness

### Post-Deployment Testing
- [ ] Homepage loads with starry background
- [ ] Robot model loads and animates on scroll
- [ ] Meteor shower displays correctly
- [ ] Employers menu appears top-right
- [ ] Login modal opens from Employers menu
- [ ] Mobile devices see correct content
- [ ] Admin dashboard connects to Parse
- [ ] No console errors

---

## 🔧 Known Issues & Notes

### V35 Changes
- **Banners removed**: User requested removal of banner elements
- All banner-related CSS and HTML commented out with "REMOVED" markers
- v36 adds banners back (available for comparison)

### Missing Files
- `index.html.mobile` referenced by `index.php` but not present
- Job poster dashboard files on Desktop, not in website/ folder

### Credentials Inconsistency
⚠️ **WARNING**: Desktop files (`jcl-poster-dashboard.js`) use DIFFERENT App ID:
- Correct: `9Zso4zMCOLF1kANT60AXD2JgqUuaKjJjQYEIsFMH`
- Incorrect (in old files): `9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF`

Always use credentials from `admin-dashboard.html` and this baseline document.

---

## 📂 File Locations Reference

### Website Files
```
website/
├── jcl-complete-site-fix-v35.php    ← BASELINE PLUGIN
├── jcl-complete-site-fix-v36.php    (next version)
├── index.html                        (static landing)
├── index.php                         (mobile redirect)
├── admin-dashboard.html              (admin panel)
├── home-page-content.html
├── test-standalone.html
├── Various page-home-*.php files
├── Documentation *.md files
├── breakdance/                       (page builder assets)
├── wordpress/                        (WP core files)
└── jerichocreations.com/            (old site backup)
```

### Desktop Files (Not in Git)
```
/Users/barrett/Desktop/JCL HTMLs/
├── jcl-poster-dashboard.js          (⚠️ wrong credentials)
├── jcl-poster-dashboard.css
├── back4app-main.js                 (Cloud Code)
├── add-posting-limit-field.html
└── Various setup/documentation files
```

---

## 🔄 Version History

| Version | Date | Changes |
|---------|------|---------|
| v35.2 | Dec 30, 2024 | **BASELINE** - Removed banners per user request |
| v36 | Dec 30, 2024 | Re-added banners with 25%/75% positioning |

---

## 📞 Support Information

### Documentation Files
- `WORDPRESS_UPLOAD_GUIDE.md` - How to upload to DreamHost
- `ADMIN_DASHBOARD_SETUP.md` - Admin dashboard configuration
- `APP_OWNER_HANDOFF_GUIDE.md` - For app owner setup
- `ACTIVATE_HOME_PAGE.md` - Homepage activation guide

### External Resources
- Back4App Dashboard: https://dashboard.back4app.com
- App Store Connect: https://appstoreconnect.apple.com
- DreamHost SSH: `dh_w5amcg@iad1-shared-b7-38.dreamhost.com`

---

## ✅ Baseline Status

**Baseline Established**: December 31, 2025
**Plugin Version**: V35.2
**Status**: Production-ready configuration captured
**Next Steps**: Use this as reference for all future website deployments

---

**🔒 This is the master baseline configuration. Any future changes should reference this document.**
