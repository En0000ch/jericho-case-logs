# Activate Home Page Template - 2 Minute Guide

The custom home page template has been uploaded to your server! Now you just need to activate it in WordPress.

---

## ✅ What's Already Done:

- ✅ Custom page template created (`page-home.php`)
- ✅ Uploaded to your Astra theme directory
- ✅ All content and styling included
- ✅ App Store links integrated
- ✅ Mobile responsive

---

## 📝 Step 1: Log Into WordPress (30 seconds)

1. Go to: https://jerichocaselogs.com/wp-admin
2. Username: `d_barrett`
3. Password: `P$alms34:8`
4. Click **Log In**

---

## 📄 Step 2: Create/Edit Home Page (1 minute)

### Option A: If you already have a "Home" page:

1. In WordPress admin, go to **Pages** → **All Pages**
2. Find your home page and click **Edit**
3. On the right side, look for **Page Attributes**
4. Under **Template**, select **"Home Page - Jericho Case Logs"**
5. Click **Update** at the top
6. **Done!** ✓

### Option B: If you need to create a new home page:

1. In WordPress admin, go to **Pages** → **Add New**
2. Title: Type **Home** at the top
3. Leave the content area empty (template has everything)
4. On the right side, look for **Page Attributes**
5. Under **Template**, select **"Home Page - Jericho Case Logs"**
6. Click **Publish** at the top
7. **Done!** ✓

---

## 🏠 Step 3: Set as Home Page (30 seconds)

1. Go to **Settings** → **Reading**
2. Under **Your homepage displays**, select **A static page**
3. Under **Homepage**, select the page you just created/edited
4. Click **Save Changes** at the bottom
5. **Done!** ✓

---

## 🎉 Step 4: View Your Page (10 seconds)

1. Click **Visit Site** at the top left of WordPress admin
2. Or go to: https://jerichocaselogs.com
3. You should see your beautiful new home page!

---

## 📱 What You'll See:

✅ **Hero Section** - Orange banner with "Your Career Command Center"
✅ **Professional Types** - 4 cards (CRNAs, Nurses, Scrub Techs, Physicians)
✅ **9 Feature Cards** - Including job search, analytics, offline mode
✅ **Download Buttons** - iOS (live) and Android (coming soon)
✅ **Benefits Section** - Why professionals choose the app
✅ **Final CTA** - Ready to take control call-to-action
✅ **Mobile Responsive** - Looks great on phones and tablets

---

## 🔧 Troubleshooting:

**Can't find "Page Attributes" panel:**
- Click the **⋮** (three dots) in top right
- Select **Options** or **Preferences**
- Make sure **Page Attributes** is checked

**Template doesn't appear in dropdown:**
- Make sure you're editing the page (not a post)
- The template should automatically appear
- Try refreshing the page

**Page looks weird:**
- Clear your browser cache (Cmd+Shift+R or Ctrl+Shift+R)
- Check that you're using the Astra theme
- Make sure no page builder is conflicting

**Want to customize content:**
- The template file is at: `/wp-content/themes/astra/page-home.php`
- You can edit it via SSH, FTP, or WordPress theme editor
- Or contact me for changes

---

## 🎨 Future Customization:

### Update Google Play Link Later:

When you get your Google Play Store URL:

1. SSH or FTP to your server
2. Edit: `/home/dh_w5amcg/jerichocaselogs.com/wp-content/themes/astra/page-home.php`
3. Find line ~141 (the disabled Google Play button)
4. Replace `href="#"` with your Play Store URL
5. Remove `class="jcl-download-button disabled" onclick="return false;"`
6. Change to `class="jcl-download-button"`
7. Change text from "Coming Soon" to "Pre-Register on Google Play"
8. Save file

### Change Colors:

In the same file, find the `<style>` section and change:
- Orange: `#EE6C4D` → your color
- Dark blue: `#293241` → your color

### Edit Text:

All text is in the same file - just find and edit directly.

---

## 📊 What's Included:

The template includes:
- All content from `home-page-content.html`
- Professional styling and layout
- Mobile responsive design
- Working download buttons
- Smooth animations
- HIPAA-compliant messaging
- SEO-friendly structure

---

## ⚠️ Important Notes:

1. **Don't delete the template file** - It's needed to display the page
2. **Theme updates** - If Astra updates, the template will stay (it's custom)
3. **Backups** - Always backup before making changes
4. **Testing** - Test on mobile devices after activating

---

## 🆘 Need Help?

If something doesn't work:
1. Check that Astra theme is active (**Appearance** → **Themes**)
2. Try deactivating conflicting page builders (Elementor, Breakdance)
3. Clear all caches (WordPress, browser, CDN)
4. Check file permissions on server
5. Contact me with specific error messages

---

## ✅ Checklist:

- [ ] Log into WordPress admin
- [ ] Create/edit Home page
- [ ] Select "Home Page - Jericho Case Logs" template
- [ ] Set as static homepage (Settings → Reading)
- [ ] View and test the live page
- [ ] Test on mobile devices
- [ ] Bookmark for future edits

---

**Total Time:** ~2 minutes
**Difficulty:** Easy
**Status:** Template file uploaded and ready to activate

**WordPress Login:** https://jerichocaselogs.com/wp-admin
**Username:** d_barrett
**Template Location:** `/wp-content/themes/astra/page-home.php`

**iOS App:** https://apps.apple.com/us/app/jericho-case-logs/id6466726836
**Google Play:** Coming soon (see GOOGLE_PLAY_COPY_PASTE_SETUP.md)

---

Last Updated: December 30, 2025
Template Uploaded: ✅ Yes
Ready to Activate: ✅ Yes
