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
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/dh_w5amcg/jerichocaselogs.com/wp-content/plugins/wp-super-cache/' );
define('DB_NAME', 'jerichocaselogs_com');

/** MySQL database username */
define('DB_USER', 'jerichocaselogsc');

/** MySQL database password */
define('DB_PASSWORD', 'esvp**5p');

/** MySQL hostname */
define('DB_HOST', 'mysql.jerichocaselogs.com');

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
define('AUTH_KEY',         'TPgLei%GlyKTez@4u_Evp8x6eeyLt5|ZR2#/)r&q^ocRtmvQu~@Q&GZC&EmfqOPo');
define('SECURE_AUTH_KEY',  'g:f$5C4P&AuvqLB@tLBb|q4%"62xgJSC~wEcUh$~QOfCxT+*C(`k$Gq1aVlM+PoZ');
define('LOGGED_IN_KEY',    'orf7V9YdJx^(@NPfWYbuuMp;_%sQtb/"@XxcPt5GyT^t^giN7I#)QS0Tx)VPXH0o');
define('NONCE_KEY',        'FTMGJ(ism6Eq(7!2~~c)(1%*gv/*0SNqUVewPoQp5VrRh`A+&Fo_4aexdYTHSF8#');
define('AUTH_SALT',        'QxsBeNkNs$1:I/#x0DThDT13eCfWVB9(hD8xSII^o^A*#Ohu(!Zql$qo$D(r1CzY');
define('SECURE_AUTH_SALT', 'Eue&bsmRIN0$6emkSbCRt6fHZLGT5tZo?tSr$bnfvRr)l#Hj(;~__XHY&F~hZVTK');
define('LOGGED_IN_SALT',   '7xkmvY_2j(uPD0^$$4hSjO;T`Z^vom+BjH2q/q;sddblk_ZTjln$)`K/P_@MPSwm');
define('NONCE_SALT',       '7^Nh"7%yC@"9N60+v8hnE~socSJ40/JKk+1q@2GlaQ!(1u_MM%naUJw!c~e+r?;j');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_jzk8hw_';

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


/**
 * Set memory limit on shared
 */
define('WP_MEMORY_LIMIT', '128M');


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
