<?php
/*
Plugin Name: WPMU DEV SEO Lite
Plugin URI: http://premium.wpmudev.org/
Description: Every SEO option that a site requires, in one easy bundle.
Version: 1.0.9.5
Network: true
Text Domain: wds
Author: Ulrich Sossou (Incsub)
Author URI: http://ulrichsossou.com/
WDP ID: 222
*/

/* Copyright 2010-2011 Incsub (http://incsub.com/)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

/**
 * Autolinks module contains code from SEO Smart Links plugin
 * (http://wordpress.org/extend/plugins/seo-automatic-links/ and http://www.prelovac.com/products/seo-smart-links/)
 * by Vladimir Prelovac (http://www.prelovac.com/).
 */

if (is_multisite()) wp_die(
	'WPMU DEV SEO Lite plugin is not multisite compatible. ' .
	'<div class="error below-h2"><p><a title="Upgrade Now" href="http://premium.wpmudev.org/project/wpmu-dev-seo">Upgrade to WPMU DEV SEO Pro</a></p></div>'
);

//you can override this in wp-config.php to enable blog-by-blog settings in multisite
if (!defined('WDS_SITEWIDE'))
	define( 'WDS_SITEWIDE', true );

define( 'WDS_VERSION', '1.0.9.4' );

/**
 * Setup plugin path and url.
 */
define( 'WDS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) . 'wds-files/' );
define( 'WDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) . 'wds-files/' );

/**
 * Load textdomain.
 */
if ( defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/wpmu-dev-seo.php' ) ) {
	load_muplugin_textdomain( 'wds', 'wds-files/languages' );
} else {
	load_plugin_textdomain( 'wds', false, WDS_PLUGIN_DIR . 'wds-files/languages' );
}

require_once ( WDS_PLUGIN_DIR . 'wds-core/wds-core-wpabstraction.php' );
require_once ( WDS_PLUGIN_DIR . 'wds-core/wds-core.php' );
$wds_options = get_wds_options();

if ( is_admin() ) {
	require_once ( WDS_PLUGIN_DIR . 'wds-core/admin/wds-core-admin.php' );
	require_once ( WDS_PLUGIN_DIR . 'wds-core/admin/wds-core-config.php' );

	require_once ( WDS_PLUGIN_DIR . 'wds-autolinks/wds-autolinks-settings.php' );
	require_once ( WDS_PLUGIN_DIR . 'wds-seomoz/wds-seomoz-settings.php' );
	require_once ( WDS_PLUGIN_DIR . 'wds-sitemaps/wds-sitemaps-settings.php' );
	require_once ( WDS_PLUGIN_DIR . 'wds-onpage/wds-onpage-settings.php' );

	if( isset( $wds_options['seomoz'] ) && $wds_options['seomoz'] == 'on' ) { // Changed '=' to '=='
		require_once ( WDS_PLUGIN_DIR . 'wds-seomoz/wds-seomoz-results.php' );
	}

	if( isset( $wds_options['onpage'] ) && $wds_options['onpage'] == 'on' ) { // Changed '=' to '=='
		require_once ( WDS_PLUGIN_DIR . 'wds-core/admin/wds-core-metabox.php' );
		require_once ( WDS_PLUGIN_DIR . 'wds-core/admin/wds-core-taxonomy.php' );
	}
} else {

	if( isset( $wds_options['autolinks'] ) && $wds_options['autolinks'] == 'on' ) { // Changed '=' to '=='
		require_once ( WDS_PLUGIN_DIR . 'wds-autolinks/wds-autolinks.php' );
	}
	if( isset( $wds_options['sitemap'] ) && $wds_options['sitemap'] == 'on' ) { // Changed '=' to '=='. Also, changed plural to singular.
		require_once ( WDS_PLUGIN_DIR . 'wds-sitemaps/wds-sitemaps-settings.php' ); // This is to propagate defaults without admin visiting the dashboard.
		require_once ( WDS_PLUGIN_DIR . 'wds-sitemaps/wds-sitemaps.php' );
	}
	if( isset( $wds_options['onpage'] ) && $wds_options['onpage'] == 'on' ) { // Changed '=' to '=='
		require_once ( WDS_PLUGIN_DIR . 'wds-onpage/wds-onpage.php' );
	}

}