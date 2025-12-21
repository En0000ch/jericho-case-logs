# WordPress + Breakdance Setup Guide
## Complete Installation Instructions for Mac

---

## Prerequisites Checklist

- ✅ Breakdance plugin folder: `/Users/barrett/Downloads/breakdance`
- ✅ Breakdance license key: `ca8882be94f618cb6e30dd126f695451`
- ✅ Design specification: `BREAKDANCE-DESIGN-SPEC.md`
- ✅ Website assets in: `/Users/barrett/Desktop/JerichoCaseLogs 2/jericho_case_logs/website/`

---

## Option A: Local by Flywheel (RECOMMENDED - Fastest & Easiest)

### Step 1: Download & Install Local

1. **Download Local:**
   - Visit: https://localwp.com/
   - Click "DOWNLOAD FOR FREE"
   - Download the Mac version
   - No account required (can skip)

2. **Install Local:**
   - Open the downloaded DMG file
   - Drag Local to Applications folder
   - Open Local from Applications
   - Allow any security prompts (System Settings → Security & Privacy)

### Step 2: Create New WordPress Site

1. **Launch Local** and click "Create a new site"

2. **Name your site:**
   - Site name: `jericho-case-logs`
   - Advanced Options: Leave default
   - Click "Continue"

3. **Choose environment:**
   - Select "Preferred"
   - PHP: 8.0 or higher
   - Web Server: Nginx (Preferred)
   - Database: MySQL 8.0
   - Click "Continue"

4. **WordPress Setup:**
   - Username: `admin` (or your choice)
   - Password: Choose a strong password
   - Email: Your email
   - Click "Add Site"

5. **Wait for installation** (30-60 seconds)

### Step 3: Start WordPress Site

1. In Local, click on your site name `jericho-case-logs`
2. Click the **"START SITE"** button (green)
3. Wait for "RUNNING" status
4. Note the site URL (usually `http://jericho-case-logs.local`)

### Step 4: Access WordPress Admin

1. In Local, click **"WP Admin"** button
   - Or visit: `http://jericho-case-logs.local/wp-admin`
2. Login with the username/password you created
3. You should see the WordPress dashboard

### Step 5: Prepare for Breakdance Installation

**Create Plugin ZIP:**

Open Terminal and run:
```bash
cd /Users/barrett/Downloads
zip -r breakdance.zip breakdance/
```

This creates `breakdance.zip` in your Downloads folder.

### Step 6: Install Breakdance Plugin

1. **In WordPress Admin:**
   - Go to: Plugins → Add New
   - Click "Upload Plugin" (top of page)
   - Click "Choose File"
   - Select: `/Users/barrett/Downloads/breakdance.zip`
   - Click "Install Now"

2. **Activate Plugin:**
   - Click "Activate Plugin"
   - You'll see Breakdance in the sidebar

3. **Enter License:**
   - Go to: Breakdance → License
   - Enter license key: `ca8882be94f618cb6e30dd126f695451`
   - Click "Activate License"

### Step 7: Install a Minimal Theme

Breakdance works best with a minimal theme:

1. **Option 1: Use Hello Elementor (Free):**
   - Appearance → Themes → Add New
   - Search "Hello Elementor"
   - Install and Activate

2. **Option 2: Use GeneratePress (Free):**
   - Appearance → Themes → Add New
   - Search "GeneratePress"
   - Install and Activate

### Step 8: Upload Website Assets

1. **In WordPress Admin:**
   - Go to: Media → Add New
   - Upload these files from `/Users/barrett/Desktop/JerichoCaseLogs 2/jericho_case_logs/website/`:
     - `1024Logo.png`
     - `apple-app-store.webp`
     - `google-play-1-300x93.webp`
     - `app-mockup.png`

### Step 9: Configure Breakdance Global Settings

1. **Go to: Breakdance → Settings**

2. **Fonts:**
   - Add Font: Poppins
     - Type: Google Fonts
     - Weights: 300, 400, 500, 600
   - Add Font: Century Gothic
     - Type: System Font (or upload custom)

3. **Colors:**
   - Add Global Color: "jclGray"
     - Hex: `#2B3241`
   - Add Global Color: "jclOrange"
     - Hex: `#EE6C4D`
   - Add Global Color: "jclWhite"
     - Hex: `#E0FBFC`

4. **Save Changes**

### Step 10: Create Homepage

1. **Create New Page:**
   - Pages → Add New
   - Title: "Home"
   - Click "Edit with Breakdance" button

2. **You're now in Breakdance Builder!**
   - Start building following the `BREAKDANCE-DESIGN-SPEC.md` document

3. **Set as Homepage:**
   - Exit Breakdance Builder
   - Go to: Settings → Reading
   - Select "A static page"
   - Homepage: Choose "Home"
   - Save Changes

### Step 11: Access Your Local Site

**View Site:**
- In Local, click "Open Site" button
- Or visit: `http://jericho-case-logs.local`

**Edit with Breakdance:**
- Click "WP Admin" → Pages → Home → Edit with Breakdance

---

## Option B: Using Existing Web Hosting

If you have existing WordPress hosting (Bluehost, SiteGround, etc.):

### Step 1: Login to Your Hosting Control Panel

### Step 2: Install WordPress
- Use your host's 1-click WordPress installer
- Or manually install from wordpress.org

### Step 3: Upload Breakdance via FTP

1. **Connect via FTP/SFTP:**
   - Use FileZilla or Cyberduck
   - Connect to your server

2. **Upload Plugin:**
   - Navigate to: `/public_html/wp-content/plugins/`
   - Upload the entire `/Users/barrett/Downloads/breakdance` folder
   - Result: `/public_html/wp-content/plugins/breakdance/`

3. **Activate in WordPress:**
   - Login to WordPress Admin
   - Plugins → Installed Plugins
   - Find "Breakdance" → Click "Activate"
   - Enter license key

### Step 4: Follow Steps 7-11 from Option A

---

## Quick Reference: Folder Locations (Local)

**When using Local by Flywheel:**

- WordPress files: `~/Local Sites/jericho-case-logs/app/public/`
- Plugins folder: `~/Local Sites/jericho-case-logs/app/public/wp-content/plugins/`
- Themes folder: `~/Local Sites/jericho-case-logs/app/public/wp-content/themes/`
- Uploads folder: `~/Local Sites/jericho-case-logs/app/public/wp-content/uploads/`

**Alternative: Copy plugin directly (skip ZIP):**
```bash
cp -r /Users/barrett/Downloads/breakdance ~/Local\ Sites/jericho-case-logs/app/public/wp-content/plugins/
```

---

## Troubleshooting

### Breakdance Won't Activate
- Make sure PHP version is 7.4 or higher
- Check file permissions (755 for folders, 644 for files)
- Try uploading as ZIP instead of direct copy

### License Won't Activate
- Check internet connection
- Verify license key is correct: `ca8882be94f618cb6e30dd126f695451`
- Contact Breakdance support if license is already used on another domain

### Site Won't Load
- In Local, make sure site status is "RUNNING"
- Restart the site (Stop → Start)
- Check if port 80 is in use by another app

### Can't Upload Large Files
- In Local: Database → Open Adminer
- Or edit `php.ini`: increase `upload_max_filesize` and `post_max_size`

---

## Next Steps After Installation

1. ✅ WordPress running
2. ✅ Breakdance installed & activated
3. ✅ License activated
4. ✅ Minimal theme installed
5. ✅ Assets uploaded
6. ✅ Global colors/fonts configured
7. ✅ Blank homepage created

**Now ready to build!**

Open `BREAKDANCE-DESIGN-SPEC.md` and start building section by section:
1. Header with navigation
2. Hero section with two-column layout
3. Features section with 6 cards
4. Stats section
5. Final CTA
6. Footer

**Apply scroll effects as you build:**
- Section → Effects → Scroll Transform (for parallax)
- Element → Effects → Scroll Effects (for fade/slide/zoom)

---

## Quick Start Commands (Terminal)

**Create Breakdance ZIP:**
```bash
cd /Users/barrett/Downloads
zip -r breakdance.zip breakdance/
```

**Or copy directly to Local (after site is created):**
```bash
cp -r /Users/barrett/Downloads/breakdance ~/Local\ Sites/jericho-case-logs/app/public/wp-content/plugins/
```

**Open Local sites folder:**
```bash
open ~/Local\ Sites/
```

---

## Resources

- **Local by Flywheel:** https://localwp.com/
- **Breakdance Docs:** https://breakdance.com/documentation/
- **Breakdance YouTube:** https://www.youtube.com/@Breakdance
- **Your Design Spec:** `BREAKDANCE-DESIGN-SPEC.md`
- **License Key:** `ca8882be94f618cb6e30dd126f695451`

---

**Created:** 2025-12-08
**For:** Jericho Case Logs WordPress + Breakdance Setup
