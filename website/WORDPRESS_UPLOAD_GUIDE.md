# WordPress Home Page Upload - Copy/Paste Guide
## Zero Thinking Required - Just Follow Steps

This guide shows you EXACTLY how to upload the home page content to WordPress.

---

## BEFORE YOU START

**You Need:**
- [ ] WordPress admin login (username and password)
- [ ] File: `website/home-page-content.html` (open it in a text editor)

**Time Required:** 5-10 minutes

---

## STEP 1: Log Into WordPress (2 minutes)

1. Go to: `https://jerichocaselogs.com/wp-admin`
   - Or: `https://yoursite.com/wp-admin`
2. Enter your username
3. Enter your password
4. Click **Log In**

**Done** ✓

---

## STEP 2: Create Home Page (2 minutes)

1. Look at left sidebar in WordPress admin
2. Click **Pages**
3. Click **Add New**
4. Title: Type `Home` at the top
5. **IMPORTANT**: Click the three dots (⋮) in top right corner
6. Select **Code editor** (NOT Visual editor)
   - You should now see HTML code view

**Done** ✓

---

## STEP 3: Copy Content (1 minute)

1. Open file: `website/home-page-content.html`
2. Find line 44 (starts with: `<div class="hero">`)
3. Select EVERYTHING from line 44 to line 210 (ends with: `</div>`)
4. Copy it (Cmd+C or Ctrl+C)

**What to copy:**
```
<div class="hero">
    ...
    (all the content)
    ...
</div>
```

**Do NOT copy:**
- The `<html>`, `<head>`, `<body>` tags
- The `<style>` section
- The comments at the bottom

**Done** ✓

---

## STEP 4: Paste Into WordPress (30 seconds)

1. Click inside the code editor box in WordPress
2. Paste (Cmd+V or Ctrl+V)
3. You should see all the HTML code

**Done** ✓

---

## STEP 5: Add Custom CSS (3 minutes)

### If Using Breakdance Builder:
1. Skip this - Breakdance has its own styling
2. Jump to Step 6

### If Using Block Editor:
1. In WordPress sidebar, click **Appearance**
2. Click **Customize**
3. Look for **Additional CSS** (usually near bottom)
4. Click it
5. Open file: `website/home-page-content.html`
6. Copy EVERYTHING between `<style>` tags (lines 6-37)
7. Paste into Additional CSS box
8. Click **Publish** at top

**Done** ✓

---

## STEP 6: Set as Home Page (1 minute)

1. Still in **Appearance** → **Customize**
2. Click **Homepage Settings**
3. Select: **A static page**
4. Homepage dropdown: Select **Home**
5. Click **Publish**

**Done** ✓

---

## STEP 7: View Your Page (30 seconds)

1. Click **Visit Site** in top left
2. You should see your new home page!

**Done** ✓

---

## TROUBLESHOOTING

**Problem: "Can't find Code editor"**
- Look for icon that looks like `<>`
- Or click three dots (⋮) → Options → Code editor

**Problem: "Page looks weird/unstyled"**
- Make sure you added the CSS from Step 5
- Clear browser cache (Cmd+Shift+R or Ctrl+Shift+R)
- Check if theme is overriding styles

**Problem: "Download buttons don't work"**
- That's expected for Google Play (shows "Coming Soon")
- Apple button should work: https://apps.apple.com/us/app/jericho-case-logs/id6466726836

**Problem: "Want to edit content"**
1. Go to Pages → All Pages
2. Click **Edit** on Home page
3. Switch to Code editor
4. Make changes
5. Click **Update**

---

## ALTERNATIVE: Use Page Builder (If Available)

If your WordPress has a page builder (Elementor, Breakdance, Divi):

1. Instead of Code editor, use the page builder
2. Add HTML widgets for each section
3. Copy/paste HTML into each widget
4. Style using the page builder's visual tools

**Benefits:**
- Easier to edit later
- Better mobile responsiveness
- No CSS knowledge needed

---

## WHAT EACH SECTION DOES

**Hero Section** (Line 44-48)
- Big orange banner at top
- "Your Career Command Center" headline
- Download button

**Who It's For** (Line 53-81)
- 4 professional types (CRNA, Nurses, Scrub Techs, Physicians)
- Shows which are available now vs coming soon

**Features Section** (Line 86-142)
- 9 feature cards
- Icons + descriptions

**App Showcase** (Line 147-173)
- Dark blue section
- Download buttons for iOS and Android

**Benefits Section** (Line 178-200)
- 4 benefit cards
- Why professionals choose the app

**Final CTA** (Line 205-210)
- Bottom orange banner
- Final call to action

---

## CUSTOMIZATION OPTIONS

### Change Colors:
Find these in CSS:
- Orange: `#EE6C4D`
- Dark blue: `#293241`

### Change Text:
Edit directly in the HTML

### Add Images:
1. Upload images to WordPress Media Library
2. Get URL
3. Add `<img src="URL">` tags in HTML

### Remove Sections:
Delete entire `<div class="section-name">...</div>` block

---

## AFTER YOU'RE DONE

**Update Google Play Button Later:**

When you get your Google Play Store URL, find this in the code:

```html
<a href="#" class="download-button disabled" onclick="return false;">
    <span style="font-size: 24px;">📱</span>
    <span>Google Play (Coming Soon)</span>
</a>
```

Replace with:

```html
<a href="YOUR_PLAY_STORE_URL" target="_blank" class="download-button">
    <span style="font-size: 24px;">📱</span>
    <span>Pre-Register on Google Play</span>
</a>
```

---

## NEED HELP?

**Can't figure it out?**
- Hire on Fiverr: Search "wordpress page setup" ($20-50)
- Post on r/wordpress: Include screenshots
- Contact your web host support: They can help

**WordPress Knowledge Base:**
- https://wordpress.org/support/article/pages/
- https://wordpress.org/support/article/appearance-customize-screen/

---

## TOTAL TIME: ~10 minutes

- Step 1: 2 min
- Step 2: 2 min
- Step 3: 1 min
- Step 4: 30 sec
- Step 5: 3 min
- Step 6: 1 min
- Step 7: 30 sec

---

**IMPORTANT NOTES:**

1. **Make a backup** before editing (if possible)
2. **Preview first** before publishing
3. **Test on mobile** after publishing
4. **Check all links** work correctly

---

**File Locations:**
- Content HTML: `website/home-page-content.html`
- CSS styles: Inside `<style>` tags in same file
- WordPress admin: https://jerichocaselogs.com/wp-admin

---

Last Updated: December 30, 2025
iOS App: https://apps.apple.com/us/app/jericho-case-logs/id6466726836
WordPress Guide: For copy/paste implementation
