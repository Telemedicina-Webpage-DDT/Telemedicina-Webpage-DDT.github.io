<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '8_(L/b]XOG0]#KU20p2T:lJVYa3zNy!xhym9dlqv/~a^t.WNfxKi95)0&vN14KJO');
define('SECURE_AUTH_KEY',  '[4^m}]xMO]mfyC-Di5Kfvp_}L{2ste3AwyZ* EVh0~9U?ki6A)Jg3+_R~qTYJM-:');
define('LOGGED_IN_KEY',    'SaOqe9{@dO|A`w{#Nz)@zQK7U!J`-Hb5sNf.i7YcTF*u$-DX%7#o=x{rEb+CCdD+');
define('NONCE_KEY',        'S #X=s(QvY^^Mi>3xOL;|6}k]h!?%e;Y-j@nz?zsin9rXa>iU-$Bu~+4~:eOKRc1');
define('AUTH_SALT',        'XgMNH3CXGSlyM9Kq$NuF[Q|}JJWKse]=#53P*4M2ob]^!3`GZkkghn!T#~ou[RV%');
define('SECURE_AUTH_SALT', ')U*ih_8;_0FgwRf3VC5U_7#n(%+Dv#=(TNHreP;[J>-;|;22 q8#]U5]5J./$h^+');
define('LOGGED_IN_SALT',   '_QmeM&S-W1SsMkk4giHlWKvHZfA3v9l6XY5sC0i]TONcuktXGY)!E0n@Yc}?!Z)z');
define('NONCE_SALT',       ':;,zYY/Tx.4&/=07ggO#E5exkLM!%?$yy~Ud+#$&2c?5<m*FT|<eMl$Kz+`|t!#Y');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
