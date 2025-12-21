<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pluto_jericho');
/** MySQL database username */
define('DB_USER', 'pluto_jericho');
/** MySQL database password */
define('DB_PASSWORD', 'gkWEsrcMIjwW');
/** MySQL hostname */
define('DB_HOST', 'mysql.jerichocreations.com');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '0p2`sH)y_4VT?iF|gs^AI%$(#r`zPS;@k&093CqxEsYTEEIp0xKvCE(YncLX/|tu');
define('SECURE_AUTH_KEY',  'nR`|%XlatdxA"CK5jp;uD9:wUAmxIx@uoc7xwexOE*L1|4QeMfsTS+|TWp"H&a0a');
define('LOGGED_IN_KEY',    '&twek?8O"tLbgwIajPclS(vcSMKyQ9f!IiqpN|%MfmAoZL:Tz2g5Z~JP9!UI`WZZ');
define('NONCE_KEY',        '(8~aj3BwYr:@%dsvwhEwmkrXTq:b+z?A1D"XH;)?"1rF2#!p!@sWezHk7_%Doz(b');
define('AUTH_SALT',        'QKP9u(+@e4sm!aJd!)fvMaRqH`gA;3d);t%*8d@H8Vxg:S6jqSA%onWvE$Dz9@U8');
define('SECURE_AUTH_SALT', '8#6Bwj1*fU1VYx7/zYW|BHJgPkrJ$1A$wY?DOU#vx?Q!g#toUBX$yZ;Is(fD8|lV');
define('LOGGED_IN_SALT',   'FLz:O!64qeKyVxw`l/#SaDfWf9UTN%h_&J`oXxq4`aBp#KlW6na/4ciDy5coO&L#');
define('NONCE_SALT',       'RN#uGwmeB~|7K4_Y:ca_1)^;!Yg6crWABAmL`kWw&moeY9|~"(HdNJGsP""Zbkal');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_ga47q5_';
/**
 * Limits total Post Revisions saved per Post/Page.
 * Change or comment this line out if you would like to increase or remove the limit.
 */
define('WP_POST_REVISIONS',  10);
/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);
/**
 * Removing this could cause issues with your experience in the DreamHost panel
 */
if (isset($_SERVER['HTTP_HOST']) && preg_match("/^(.*)\.dream\.website$/", $_SERVER['HTTP_HOST'])) {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        define('WP_SITEURL', $proto . '://' . $_SERVER['HTTP_HOST']);
        define('WP_HOME',    $proto . '://' . $_SERVER['HTTP_HOST']);
        define('JETPACK_STAGING_MODE', true);
}
/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');