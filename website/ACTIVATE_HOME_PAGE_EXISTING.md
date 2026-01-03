# Activate Home Page - For Existing Page with Slug [home]
## Replace "Under Construction" with Your New Home Page

You already have a home page (slug: `home`) but it's showing an "Under Construction" page. Here's how to replace it with your new content.

---

## 🎯 Current Situation:

- ✅ You have an existing page with slug: `home`
- ⚠️ "Under Construction" plugin is active
- ✅ Custom template uploaded to server
- 🎯 Need to disable construction mode and apply new template

---

## 📝 Step-by-Step Instructions (3 minutes)

### STEP 1: Log Into WordPress (30 seconds)

1. Go to: https://jerichocaselogs.com/wp-admin
2. Username: `d_barrett`
3. Password: `P$alms34:8`
4. Click **Log In**

---

### STEP 2: Disable "Under Construction" Plugin (1 minute)

**IMPORTANT: Do this first!**

1. In WordPress admin sidebar, click **Plugins**
2. Find "Under Construction Page" (or similar name)
3. Click **Deactivate** under the plugin name
4. Your site is now live! (but still has old content)

**Done!** ✓ Construction mode disabled

---

### STEP 3: Find Your Home Page (30 seconds)

1. In WordPress sidebar, click **Pages** → **All Pages**
2. Look for page with title "Home" or slug showing "home"
3. Click **Edit** on that page

**Found it!** ✓

---

### STEP 4: Apply New Template (1 minute)

1. You're now editing the Home page
2. **On the right side**, look for **Page Attributes** panel
   - If you don't see it, click the ⋮ (three dots) in top right
   - Select **Preferences**
   - Make sure **Page Attributes** is checked
   - Close preferences

3. In **Page Attributes** panel, find **Template** dropdown
4. Select: **"Home Page - Jericho Case Logs"**
5. Click **Update** button at top right

**Done!** ✓ Template applied

---

### STEP 5: Set as Homepage (30 seconds)

Make sure it's set as the site's front page:

1. Go to **Settings** → **Reading**
2. Under **Your homepage displays**, select **A static page**
3. **Homepage** dropdown: Select your "Home" page
4. Click **Save Changes** at bottom

**Done!** ✓ Set as homepage

---

### STEP 6: View Your New Site! (10 seconds)

1. Click **Visit Site** in top left corner
2. Or go to: https://jerichocaselogs.com

You should now see your beautiful new home page! 🎉

---

## ✅ What You Should See:

- ✅ Orange hero section: "Your Career Command Center"
- ✅ Four professional types (CRNAs, Nurses, Scrub Techs, Physicians)
- ✅ Nine feature cards
- ✅ Download buttons (iOS live, Android coming soon)
- ✅ Benefits section
- ✅ Final call-to-action

---

## 🐛 Troubleshooting:

### Still seeing "Under Construction" page?
1. Clear browser cache: Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
2. Try private/incognito window
3. Check plugin is actually deactivated
4. Check another device/browser

### Template not in dropdown?
1. Verify file uploaded: `/wp-content/themes/astra/page-home.php`
2. SSH: `ls -la /home/dh_w5amcg/jerichocaselogs.com/wp-content/themes/astra/page-home.php`
3. File should be 12KB and exist
4. Try refreshing the page editor

### Page looks weird/broken?
1. Clear all caches (browser, WordPress, CDN)
2. Make sure Astra theme is active (**Appearance** → **Themes**)
3. Disable any page builder plugins temporarily
4. Check browser console (F12) for errors

### Old content still visible?
- The template ignores page content
- Old content won't show (that's good!)
- Template has all the new content built-in

---

## 💡 Important Notes:

### About the Page Content:
- ✅ You can leave the existing page content as-is
- ✅ The template overrides it with new content
- ✅ Old content won't show on the page
- ✅ It's stored in database but not displayed

### About the Under Construction Plugin:
- After deactivating, you can delete it if you want
- Go to **Plugins** → Find plugin → Click **Delete**
- Or keep it deactivated for future use

### About Updates:
- If you need to change content, edit the template file
- Location: `/wp-content/themes/astra/page-home.php`
- Or ask me to make changes

---

## 🔄 Alternative Method (If Template Dropdown Missing):

If the template doesn't appear in the dropdown, you can edit your existing home page:

1. **Edit Home Page** in WordPress
2. **Switch to Code Editor:**
   - Click ⋮ (three dots) in top right
   - Select "Code editor"
3. **Delete all existing content**
4. **Open file:** `website/home-page-content.html`
5. **Copy lines 44-210** (just the content sections)
6. **Paste into WordPress code editor**
7. **Click Update**

But the template method is cleaner and easier to update later!

---

## 📋 Quick Checklist:

- [ ] Log into WordPress admin
- [ ] Deactivate "Under Construction" plugin
- [ ] Find your Home page (slug: home)
- [ ] Edit the Home page
- [ ] Select template: "Home Page - Jericho Case Logs"
- [ ] Click Update
- [ ] Set as static homepage (Settings → Reading)
- [ ] Save changes
- [ ] Clear browser cache
- [ ] View site and verify

---

## 🎨 What's Different from Before:

**Old (Under Construction):**
- Generic "coming soon" message
- No app information
- No download links
- No professional branding

**New (Your Home Page):**
- Professional hero section
- Complete app feature showcase
- Download links to App Store
- Professional types highlighted
- Benefits section
- Call-to-action buttons
- Mobile responsive
- Beautiful design

---

## 🚀 After Activation:

Once your home page is live, you can:

1. **Test all links** - Make sure App Store link works
2. **Test on mobile** - Check responsive design
3. **Share the URL** - Start promoting your app
4. **Add admin link** - When ready (see ADMIN_DASHBOARD_SETUP.md)
5. **Monitor traffic** - Set up analytics

---

## 📱 Admin Dashboard Access:

Remember, you also have the admin dashboard ready:
- URL: https://jerichocaselogs.com/admin-dashboard.html
- See: `ADMIN_DASHBOARD_SETUP.md` for setup
- Add hidden link to home page later

---

## ⏱️ Total Time: ~3 minutes

- Step 1: 30 sec (Login)
- Step 2: 1 min (Disable plugin)
- Step 3: 30 sec (Find page)
- Step 4: 1 min (Apply template)
- Step 5: 30 sec (Set homepage)
- Step 6: 10 sec (View site)

---

## 🎉 You're Done!

Your professional home page is now live at:
**https://jerichocaselogs.com**

Share it with the world! 🌍

---

**WordPress Admin:** https://jerichocaselogs.com/wp-admin
**Your Site:** https://jerichocaselogs.com
**Admin Dashboard:** https://jerichocaselogs.com/admin-dashboard.html

**Questions?** Check ACTIVATE_HOME_PAGE.md for more details or troubleshooting tips.

---

Last Updated: December 30, 2025
Status: Ready to activate - just disable Under Construction plugin!
