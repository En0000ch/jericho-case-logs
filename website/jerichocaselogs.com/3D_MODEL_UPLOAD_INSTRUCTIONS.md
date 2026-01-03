# Upload 3D Model to Website

## Files Ready on Your Desktop

✅ `Meshy_Merged_Animations.glb` - Your 3D robot model (~file size)
✅ `3d-viewer.html` - HTML viewer for the 3D model

## Step-by-Step Upload Instructions

### Step 1: Access Dreamhost File Manager

1. Go to: https://panel.dreamhost.com
2. Click **Manage Websites**
3. Find `jerichocaselogs.com`
4. Click **Manage Files** button

### Step 2: Upload Both Files to Root Directory

1. Make sure you're in the **root directory** (where `wp-config.php` is located)
2. Click the **Upload** button
3. Upload both files from your Desktop:
   - `Meshy_Merged_Animations.glb`
   - `3d-viewer.html`

**Important:** These files must be in the **root directory**, NOT in a subdirectory!

### Step 3: Verify Files Are Uploaded

After uploading, you should see these files in the root directory:
- ✅ `Meshy_Merged_Animations.glb`
- ✅ `3d-viewer.html`
- `wp-config.php` (already there)
- Other WordPress files...

### Step 4: Test the 3D Viewer

Visit this URL to test if the viewer works:
**https://jerichocaselogs.com/3d-viewer.html**

You should see:
- ✅ Your animated robot 3D model
- ✅ Auto-rotating
- ✅ Play/Pause button at bottom
- ✅ Animation selector dropdown

### Step 5: Refresh Your Hero Section

Visit: **https://jerichocaselogs.com/home/**

Hard refresh the page:
- **Windows:** Ctrl + F5
- **Mac:** Cmd + Shift + R

The 3D model should now appear in place of the placeholder!

---

## Troubleshooting

### If 3D model doesn't show:

1. **Check file location:**
   - Files must be in the root directory
   - NOT in `/wp-content/` or any other folder

2. **Check file names are exact:**
   - `Meshy_Merged_Animations.glb` (case-sensitive!)
   - `3d-viewer.html` (lowercase, no spaces)

3. **Test viewer directly:**
   - Visit: https://jerichocaselogs.com/3d-viewer.html
   - If this shows the model, then it's working

4. **Clear browser cache:**
   - Hard refresh the page
   - Try in incognito/private window

### If you see "Error loading model":

- The `.glb` file is not in the root directory
- The file name doesn't match exactly
- The file didn't upload completely (check file size)

---

## File Sizes

- `Meshy_Merged_Animations.glb`: Check file size on your Desktop
- `3d-viewer.html`: ~10 KB

---

## What Happens After Upload

Once uploaded, your hero section will show:
- ✅ Neon blue heading with glow
- ✅ **Animated 3D robot** with auto-rotation
- ✅ Animation controls (play/pause, animation selector)
- ✅ App store download buttons
- ✅ Dark futuristic theme
- ✅ GSAP scroll animations

The 3D model will have:
- **Auto-rotation**: Model spins slowly
- **Interactive controls**: Click and drag to rotate
- **Multiple animations**: Dropdown selector
- **Play/Pause button**: Control animation playback
- **Neon lighting**: Blue and purple lights

---

## Need Help?

If you have issues uploading:
1. Make sure the files are on your Desktop
2. Check Dreamhost file size limits (usually 512MB max)
3. Try uploading one file at a time
4. Contact Dreamhost support if upload fails

Once uploaded successfully, the 3D model will appear immediately on your hero section!
