# Jericho Case Logs - Breakdance Design Specification

## Complete reference for rebuilding the site in WordPress + Breakdance

---

## 🎨 Color Palette

### Primary Colors
- **jclGray (Dark Background):** `#2B3241` - Used for all section backgrounds
- **jclOrange (Brand Accent):** `#EE6C4D` - Used for CTAs, borders, highlights
- **jclWhite (Text/Light):** `#E0FBFC` - Used for all body text and headings

### Secondary Colors
- **Success Green:** `#48bb78` - Used for checkmarks
- **Star Yellow:** `#FFB800` - Used for rating stars
- **Footer Black:** `#1a1a1a` - Used for footer background

### Opacity Variants
- Gray with 5% opacity: `rgba(43, 50, 65, 0.05)`
- Gray with 10% opacity: `rgba(43, 50, 65, 0.1)`
- Gray with 20% opacity: `rgba(43, 50, 65, 0.2)`
- Orange with 30% opacity: `rgba(238, 108, 77, 0.3)`
- White with 10% opacity: `rgba(255, 255, 255, 0.1)`

---

## 📝 Typography

### Font Families
1. **Primary Font:** Poppins (Google Fonts)
   - Weights: 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-Bold)
   - Import: `https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap`

2. **Hero Tagline Font:** Century Gothic
   - Weight: 100 (Ultra Light)
   - Fallback: sans-serif

### Text Styles

**Body Text:**
- Font: Poppins
- Size: 18px
- Weight: 300
- Line height: 1.6
- Color: #E0FBFC

**H1 (Main Hero Heading):**
- Font: Poppins
- Size: 42px (desktop), 34px (mobile)
- Weight: 500
- Line height: 1.2
- Color: #E0FBFC

**H2 (Section Headings):**
- Font: Poppins
- Size: 32px
- Weight: 500
- Color: #E0FBFC

**H3 (Feature Card Titles):**
- Font: Poppins
- Size: 21px
- Weight: 400
- Color: #E0FBFC

**Hero Tagline:**
- Font: Century Gothic
- Size: 21px
- Weight: 100
- Color: #EE6C4D
- Max-width: 700px

---

## 📐 Layout Structure

### Container
- Max-width: 1200px
- Centered with `margin: 0 auto`
- Padding: 20px (sides)

### Section Padding
- Top/Bottom: 80px
- Responsive: Adjust on mobile

### Grid Systems

**Feature Grid:**
- Display: CSS Grid
- Columns: `repeat(auto-fit, minmax(300px, 1fr))`
- Gap: 40px

**Stats Grid:**
- Display: CSS Grid
- Columns: `repeat(auto-fit, minmax(250px, 1fr))`
- Gap: 40px

**Hero Layout:**
- Display: Flexbox
- Direction: Row (desktop), Column (mobile)
- Align: Center
- Gap: 60px
- Flex-wrap: wrap
- Two equal columns (flex: 1 each)
- Min-width per column: 400px

---

## 🧭 Navigation Header

### Structure
```
Header (Sticky)
├── Logo Text: "Jericho Case Logs"
└── Navigation
    └── Dropdown: "Employers" ▼
        ├── Employer Login
        └── Create Employer Account
```

### Styling
- Background: #2B3241
- Padding: 20px 0
- Position: Sticky (top: 0)
- Z-index: 1000
- Box-shadow: `0 2px 10px rgba(0,0,0,0.1)`

**Logo Text:**
- Font-size: 24px
- Weight: 500
- Color: #E0FBFC

**Dropdown:**
- Background: #2B3241
- Border-radius: 8px
- Box-shadow: `0 4px 12px rgba(0, 0, 0, 0.15)`
- Min-width: 220px
- Links: 15px padding, border-bottom separator

---

## 📱 Hero Section

### Layout
Two-column flexbox layout:
- Left column: Text content
- Right column: App mockup image

### Content (Left Column)

**Logo:**
- File: `1024Logo.png`
- Size: 120px × 120px (desktop), 100px × 100px (mobile)
- Margin-bottom: 30px

**Heading:**
"Professional Case Logging for Medical Professionals"

**Tagline:**
"Track your clinical cases effortlessly. Build your portfolio. Advance your career."

**Store Badges:**
- Apple App Store badge: `apple-app-store.webp`
- Google Play badge: `google-play-1-300x93.webp`
- Height: 60px
- Gap: 15px
- Links:
  - App Store: `https://apps.apple.com/us/app/jericho-case-logs/id6466726836`
  - Google Play: `https://play.google.com/store/apps`

### Content (Right Column)

**App Mockup:**
- File: `app-mockup.png`
- Max-width: 100%
- Max-height: 600px
- Auto height

### Scroll Animations (Breakdance)
- Logo: Fade-down, 800ms
- Heading: Fade-right, 1000ms, 200ms delay
- Tagline: Fade-right, 1000ms, 400ms delay
- Store badges: Fade-up, 1000ms, 600ms delay
- App mockup: Fade-left, 1200ms, 400ms delay

---

## ✨ Features Section

### Header
"Everything You Need to Track Your Cases"

### Feature Cards (6 total)

**Card 1: Quick Case Entry**
- Icon: 📝
- Title: "Quick Case Entry"
- Description: "Log clinical cases in seconds. Track patient demographics, ASA classifications, procedures, and clinical plans with our intuitive interface."

**Card 2: Visual Analytics**
- Icon: 📊
- Title: "Visual Analytics"
- Description: "Beautiful charts showing your cases by ASA classification, clinical plan, surgery type, and timeline. Understand your practice patterns at a glance."

**Card 3: Calendar View**
- Icon: 📅
- Title: "Calendar View"
- Description: "See all your cases organized by date in an interactive calendar. Perfect for reviewing your monthly activity and planning documentation."

**Card 4: Cloud Sync**
- Icon: ☁️
- Title: "Cloud Sync"
- Description: "Your data automatically syncs to the cloud and is available across all your devices. Never lose your case logs again."

**Card 5: Export Reports**
- Icon: 📄
- Title: "Export Reports"
- Description: "Generate professional PDF reports of your cases for certifications, job applications, and continuing education requirements."

**Card 6: Advanced Search**
- Icon: 🔍
- Title: "Advanced Search"
- Description: "Find any case instantly with powerful search and filtering by procedure, date range, ASA classification, facility, and more."

### Card Styling
- Background: #2B3241
- Padding: 40px
- Border-radius: 8px
- Border: 2px solid #EE6C4D
- Box-shadow: `0 2px 8px rgba(0,0,0,0.06)`
- Transition: `all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)`

**Hover Effect:**
- Transform: `translateY(-10px) scale(1.02)`
- Box-shadow: `0 15px 35px rgba(238, 108, 77, 0.3)`

**Icon:**
- Font-size: 2.5rem (40px)
- Margin-bottom: 20px

### Scroll Animations (Breakdance)
- Section heading: Fade-up, 1000ms
- Cards 1,4: Fade-up, 800ms, 100ms delay
- Cards 2,5: Fade-up, 800ms, 200ms delay
- Cards 3,6: Fade-up, 800ms, 300ms delay

---

## 📊 Stats Section

### Header
"Trusted by Medical Professionals Nationwide"

### Stat Items (3 total)

**Stat 1:**
- Number: "10,000+"
- Label: "Cases Logged"

**Stat 2:**
- Number: "500+"
- Label: "Active Users"

**Stat 3:**
- Visual: ★★★★★ (5 stars)
- Label: "4.8 App Store Rating"

### Styling

**Number (h3):**
- Font-size: 42px
- Color: #2B3241
- Weight: 500
- Margin-bottom: 10px

**Label (p):**
- Font-size: 18px
- Color: #E0FBFC

**Rating Stars:**
- Color: #FFB800
- Font-size: 24px
- Letter-spacing: 2px

### Scroll Animations (Breakdance)
- Section heading: Fade-up, 1000ms
- Stat 1: Zoom-in, 800ms, 100ms delay
- Stat 2: Zoom-in, 800ms, 300ms delay
- Stat 3: Zoom-in, 800ms, 500ms delay

---

## 📣 Final CTA Section

### Content

**Heading:**
"Ready to Transform Your Case Logging?"

**Subheading:**
"Join hundreds of Medical Professionals who trust Jericho Case Logs"

**Store Badges:**
(Same as hero section)

### Styling
- Background: #2B3241
- Text color: #E0FBFC
- Padding: 80px 20px
- Text-align: center

### Scroll Animations (Breakdance)
- Heading: Fade-up, 1000ms
- Subheading: Fade-up, 1000ms, 200ms delay
- Store badges: Fade-up, 1000ms, 400ms delay

---

## 🦶 Footer

### Content
"© 2025 Jericho Case Logs. All rights reserved."

### Styling
- Background: #1a1a1a
- Text color: #EE6C4D
- Padding: 40px 20px
- Text-align: center

---

## 🎬 Animation Settings

### Global Animation Settings (for Breakdance)

**Easing:**
- Default: `ease-in-out-cubic`
- Button hover: `cubic-bezier(0.175, 0.885, 0.32, 1.275)`

**Durations:**
- Standard: 1000ms
- Fast: 800ms
- Slow: 1200ms

**AOS Settings:**
- Once: false (animations repeat)
- Mirror: true (animate out on scroll up)
- Offset: 120px
- Duration: 1000ms default

---

## 🖱️ Interactive Elements

### Buttons/Store Badges Hover
- Transform: `translateY(-5px) scale(1.05)`
- Duration: 0.4s
- Easing: `cubic-bezier(0.175, 0.885, 0.32, 1.275)`
- Shadow: `drop-shadow(0 8px 16px rgba(238, 108, 77, 0.3))`

### Feature Cards Hover
- Transform: `translateY(-10px) scale(1.02)`
- Box-shadow: `0 15px 35px rgba(238, 108, 77, 0.3)`
- Duration: 0.4s

### Dropdown Toggle
- Arrow rotation: 180deg when active
- Transition: 0.3s ease

---

## 🎯 Breakdance-Specific Scroll Effects

### Tunnel/Parallax Effect (Sylo.io Style)

**Hero Section:**
1. Add section
2. Background → Add 3 layers:
   - Layer 1 (Back): Static image/gradient, parallax speed: 0.2
   - Layer 2 (Mid): Logo/graphics, parallax speed: 0.5
   - Layer 3 (Front): Content, parallax speed: 1.0
3. Effects → Scroll Transform:
   - Enable viewport motion
   - translateZ for depth: -100px to 0px
   - Scale: 0.9 to 1.0

**Feature Cards:**
1. Each card → Effects → Scroll Effects
2. Fade In: 0 to 1 opacity
3. Slide Up: translateY(50px) to 0
4. Scale: 0.95 to 1.0
5. Start: -20% (viewport)
6. End: 0% (viewport)

**App Mockup:**
1. Image → Effects → Scroll Transform
2. Parallax: -0.3 speed
3. Scale: 1.1 to 1.0 (slight zoom out)
4. Optional: Rotate: 2deg to 0deg

### Layer Movement Speeds (for tunnel effect)
- Background elements: 0.2 - 0.4 (slow)
- Mid-ground elements: 0.5 - 0.7 (medium)
- Foreground content: 1.0 (normal scroll speed)
- Overlay elements: 1.2 - 1.5 (fast)

---

## 📦 Assets Checklist

### Images Required:
- ✅ `1024Logo.png` - Logo (120×120px)
- ✅ `apple-app-store.webp` - App Store badge
- ✅ `google-play-1-300x93.webp` - Google Play badge
- ✅ `app-mockup.png` - Phone screenshots mockup

### Icons:
Using emoji/unicode:
- 📝 Quick Case Entry
- 📊 Visual Analytics
- 📅 Calendar View
- ☁️ Cloud Sync
- 📄 Export Reports
- 🔍 Advanced Search
- ★ Star ratings

---

## 📱 Responsive Breakpoints

### Desktop (default)
- Container: 1200px max-width
- Hero columns: Side by side

### Tablet (≤768px)
- Hero heading: 34px
- Hero tagline: 18px
- Logo: 100×100px
- Nav: Simplified
- Columns: May stack

### Mobile (≤480px)
- Hero columns: Stack vertically
- Feature grid: Single column
- Stats grid: Stack
- Padding reduced

---

## 🚀 WordPress + Breakdance Implementation Steps

### 1. Install & Configure
- Install WordPress
- Install Breakdance plugin
- Import Poppins font (Google Fonts integration)
- Set Century Gothic as custom font

### 2. Create Global Styles
- Colors → Add jclGray, jclOrange, jclWhite
- Typography → Set Poppins as default
- Buttons → Create store badge style

### 3. Build Header
- Breakdance → Header Builder
- Add logo text + dropdown menu
- Set sticky position
- Style dropdown with custom CSS if needed

### 4. Build Homepage
- Create new page
- Use Breakdance builder
- Add sections in order:
  1. Hero
  2. Features
  3. Stats
  4. CTA
  5. Footer

### 5. Apply Scroll Effects
- For each section:
  - Section → Effects → Scroll Transform
  - Add parallax to backgrounds
  - Add viewport motion to elements
- For feature cards:
  - Element → Effects → Scroll Effects
  - Fade + Slide + Scale
  - Set viewport triggers

### 6. Test & Refine
- Preview on different devices
- Adjust scroll speeds
- Fine-tune animation timing
- Optimize images

---

## 🎓 Key Breakdance Terms Reference

**Scroll Transform:** Section-level scroll effects (parallax, sticky, etc.)
**Scroll Effects:** Element-level scroll animations (fade, slide, scale)
**Viewport Motion:** Triggers based on element position in viewport
**Parallax Speed:** Negative = slower than scroll, Positive = faster
**translateZ:** Creates depth/3D effect (requires perspective)
**Mirror:** Animation reverses when scrolling back up

---

## 📝 Notes for Developer

1. **Font Loading:** Ensure Poppins loads with all weights (300,400,500,600)
2. **Century Gothic Fallback:** If Century Gothic unavailable, use Futura or sans-serif
3. **Image Optimization:** Compress app-mockup.png for faster loading
4. **Mobile Testing:** Pay special attention to scroll effects on mobile (may need to disable some)
5. **Browser Support:** Test tunnel effects in Chrome, Safari, Firefox
6. **Performance:** Monitor scroll performance, reduce complexity if needed

---

## 🔗 External Links

- App Store: `https://apps.apple.com/us/app/jericho-case-logs/id6466726836`
- Google Play: `https://play.google.com/store/apps`
- Employer Login: `#employer-login` (placeholder)
- Create Employer Account: `#create-account` (placeholder)

---

## ✅ Final Checklist

- [ ] WordPress installed
- [ ] Breakdance Pro activated
- [ ] Fonts imported (Poppins, Century Gothic)
- [ ] Colors added to global styles
- [ ] All images uploaded to media library
- [ ] Header built with sticky navigation
- [ ] Hero section built with two-column layout
- [ ] Features section built with 6 cards
- [ ] Stats section built with 3 items
- [ ] CTA section built
- [ ] Footer added
- [ ] Scroll effects applied to all sections
- [ ] Parallax/tunnel effect configured
- [ ] Mobile responsive tested
- [ ] Store badge links verified
- [ ] Page published and set as homepage

---

**Document Version:** 1.0
**Last Updated:** 2025-12-08
**Created for:** Jericho Case Logs WordPress + Breakdance Migration
