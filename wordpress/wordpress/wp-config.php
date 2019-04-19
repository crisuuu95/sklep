<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

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
define('AUTH_KEY',         '6*](4.&_!3ay~R3Lj.S8fmfMb).gKT}/mEN~M.@H35PM?Zpd=>m(2(futWl3v1N$');
define('SECURE_AUTH_KEY',  'RO.][o5.3aRc^~`)P=>4+{a&W@D32[QtHCHE_X%MHj[mR}NoL<;6=Bh1[/dfBdO;');
define('LOGGED_IN_KEY',    '?*a~*n]aAdl!**Ev=;9u!fXUVq0(aBrs>,v9^f@B#@]oC% *R>:0D+W@Uv7{Ee7J');
define('NONCE_KEY',        '=RwH-M#vI^.d_^I |R<Qv;}Z.o7Bi)2J}%?d`Ic|r}9ISCFu1c:B0_(]E!_~<>H%');
define('AUTH_SALT',        'GRHzbyhB1)bC=o613I[%|42]f2FW#iC`@PGsN}Nks+.QXF4|L4CThD(:SJ[5!5M[');
define('SECURE_AUTH_SALT', 'UNKA<d{MOUm^% v]dDu5Jj3PbUzCwQgeqc?nR yB%ZSu]=Qe,w}dbxl{goI[Km+u');
define('LOGGED_IN_SALT',   'Jxg|7Uz9v&K7gQDg=]T-8:?oF2_]D#w<Kn6PkWj>Vkq,jx8C-3ZUi0o7:%^6^^VI');
define('NONCE_SALT',       '9`so2&LTWB;%3>Kb>3$B?s?Tz^O9^+JlvOZvqq<KCr?}B:J!cWOJ)Vcgq([PDT:S');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
