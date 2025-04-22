<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cinemark' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '9D|/,1.a$AHbQU$LOwX;%KW:hkR:li${7F{`1Ar;-BX%`DP>hRrN,1c% ZuAhj6)' );
define( 'SECURE_AUTH_KEY',  'fa-!ejTUiO}nJv #c(#,uk[CssQ/q>2i}HU&,eZRN/(^0:$a/i[3N+OaeQMhPF@,' );
define( 'LOGGED_IN_KEY',    'IF5$=MR5BgsWu!Kng %MUt R$-W$TNfT$n-RwFlQ4,43K]c$z[4yOWw*tWtZM;Q6' );
define( 'NONCE_KEY',        ')5t@/AEg%<K&|ouUou5xs}(~Z.ro6F:u,=E!Qb>^W;TTiLlXNJ~mKY|az7Ff6fUw' );
define( 'AUTH_SALT',        'F={c%jEv]3j RWbShrss8R(Y#k&e~XHV@}!3f]5%sp6j[oV[}X+91;IEXL(G*d~<' );
define( 'SECURE_AUTH_SALT', 'zy-7ofEnvmU$sb`!@A{~o:.,3Sh] +FiWqE0q$QlY*_@(xS.DD0_dnCJyat*07fM' );
define( 'LOGGED_IN_SALT',   'KcCOib:KkyWJ=StG&h0_y~6?&+bAjdH^Vtwrt+jglEuuW&+~?KS3rK-:~r{c5%7g' );
define( 'NONCE_SALT',       ')5na*~S_zLd_2unkSaT,Q4H&IIC^!e7OSy3k]s@3VZrRTdC^nanHl Y:h3zPceE>' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
