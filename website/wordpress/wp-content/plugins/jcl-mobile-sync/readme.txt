=== JCL Mobile Sync ===
Contributors: Jericho Case Logs
Tags: mobile, sync, responsive
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later

Automatically syncs mobile version (index.html.mobile) with the primary WordPress homepage.

== Description ==

JCL Mobile Sync automatically keeps your mobile HTML file in sync with your WordPress homepage. Whenever you update your homepage content, the plugin automatically generates a fresh mobile version.

**Features:**

* ✅ Automatic sync when homepage is saved/updated
* ✅ Works with Elementor, Gutenberg, and custom page templates
* ✅ Manual sync button in admin bar for quick updates
* ✅ Admin page to monitor sync status
* ✅ Extracts content sections and styles from live site
* ✅ Generates clean, standalone mobile HTML

**How It Works:**

1. Plugin hooks into WordPress save events
2. When homepage is updated, it fetches the current HTML
3. Extracts relevant content sections (hero, features, etc.)
4. Generates a standalone mobile HTML file
5. Saves to index.html.mobile in your WordPress root directory

**Mobile Redirect:**

The plugin works in conjunction with your index.php redirect logic that serves index.html.mobile to mobile phone visitors.

== Installation ==

1. Upload the `jcl-mobile-sync` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure `index.html.mobile` in your WordPress root is writable (chmod 666)
4. Plugin will automatically sync when you update your homepage
5. You can also manually sync using the "📱 Sync Mobile" button in the admin bar

== Usage ==

**Automatic Sync:**
Simply edit and save your homepage. The mobile version will automatically update.

**Manual Sync:**
Click the "📱 Sync Mobile" button in the WordPress admin bar at the top of any page.

**Admin Page:**
Go to Tools → Mobile Sync to view sync status and manually trigger a sync.

== Frequently Asked Questions ==

= Does this work with Elementor? =
Yes! The plugin works with Elementor, Gutenberg, and any page builder.

= How do I know if the sync worked? =
Check the error log or go to Tools → Mobile Sync to see the last sync time.

= What if the mobile file isn't updating? =
Make sure the file permissions allow writing (chmod 666) and check your error logs.

== Changelog ==

= 1.0.0 =
* Initial release
* Auto-sync on page save
* Manual sync button in admin bar
* Admin page for monitoring
