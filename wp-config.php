<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'children' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'Db7`hCtAjO?`.$hE?zB1@Ft{OV+3mxNc[B_(GT:<kT~q&FS&g?F8})^ho;;8dzUy' );
define( 'SECURE_AUTH_KEY',  'X_RsFxOjC]=LAI$Lz%|kqKN*2fxm%t.;]HxVzXtPa4}6jsOlkm8&lPl4(XBOhzH`' );
define( 'LOGGED_IN_KEY',    '/@$W#u*L6>87;&4!q#)VEN75>-^sl+lA)%DV,C$uHoyGVJaiYxm =F9_U]u}9D}u' );
define( 'NONCE_KEY',        'Z#k_==grqe$d+}7DUZkKRa6eyO=j,+G2}g_(nU{TMiWzY%q#)wMSX9}Y(QQZGq6V' );
define( 'AUTH_SALT',        'U5Qxq7FB8T 2E! Sq20jV%<q+?PJ uIvNHMR-o>S7$}Ig5xL[ X1<nt*bk/jWJ*N' );
define( 'SECURE_AUTH_SALT', 'i>MJpr~S0I%Rta5DcT!p_8bkXUp}VnfpH.<O0E3@@a|&$8dqTXb{=$8/3xBn Bhb' );
define( 'LOGGED_IN_SALT',   '~*@Ev_o^GG;UcCvK X!iZ$OA1uC8a!v_]H7yfnEWiEpELanW$be<o@HWZQ2gTYR2' );
define( 'NONCE_SALT',       '9^.QkTK-=QFSZQ4;[Fc-&$E8H|*>(7$FjOnxd{_|#=oIyOR}An`XG&$C/[X=;^VC' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
