=== JCL Mobile Responsive ===
Contributors: Jericho Case Logs
Tags: mobile, responsive, mobile-first
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later

Ensures all WordPress pages are mobile-responsive and optimized for mobile devices.

== Description ==

JCL Mobile Responsive automatically makes ALL WordPress pages mobile-friendly by:

* ✅ Adding responsive CSS to every page
* ✅ Optimizing forms for mobile input
* ✅ Making images responsive
* ✅ Ensuring touch-friendly buttons (44px minimum)
* ✅ Optimizing typography for readability
* ✅ Fixing tables to be mobile-scrollable
* ✅ Making modals mobile-friendly

**How It Works:**

This plugin automatically injects mobile-optimized CSS into every WordPress page, ensuring a consistent mobile experience across:

- Homepage (uses static HTML via index.php redirect)
- Employer Login page
- Employer Registration page
- Post a Job page
- Poster Dashboard
- Store/Shop pages
- Privacy Policy
- All other WordPress pages

**Key Features:**

1. **Universal Mobile CSS** - Applied to all pages automatically
2. **Touch-Friendly** - All buttons and links are at least 44px (Apple/Google guidelines)
3. **Form Optimization** - All inputs are 16px+ to prevent zoom on iOS
4. **Image Responsive** - All images scale to fit screen
5. **No Configuration** - Just activate and it works

**Mobile Optimizations:**

- Headings sized appropriately (h1: 28px, h2: 24px, etc.)
- Paragraphs: 16px with 1.6 line-height for readability
- Buttons: Minimum 44px height for easy tapping
- Forms: 16px font size to prevent mobile zoom
- Tables: Horizontal scroll on mobile
- Modals: 95% width, scrollable
- Columns: Stack vertically on mobile

**Works With:**

- Astra theme
- Elementor
- WooCommerce
- All custom JCL pages (employer login, job posting, etc.)

== Installation ==

1. Upload the `jcl-mobile-responsive` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it! All pages are now mobile-responsive

== Frequently Asked Questions ==

= Does this work with my theme? =
Yes! This plugin adds universal mobile CSS that works with any WordPress theme.

= Will this affect desktop users? =
No. The CSS only applies on screens 768px and smaller.

= Do I need to configure anything? =
No. Just activate the plugin and all pages become mobile-friendly.

= What about the homepage? =
The homepage uses a custom static HTML file (index.html.mobile) for mobile phones, served via index.php redirect. All other pages use WordPress + this plugin.

== Changelog ==

= 1.0.0 =
* Initial release
* Universal mobile CSS for all WordPress pages
* Touch-friendly button sizing
* Mobile-optimized forms
* Responsive images and tables
* Mobile-friendly modals
