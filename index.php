<?php
/*
Plugin Name: PayPing dashboard
Version: 1.0.0
Description:  افزونه داشبورد پی‌پینگ در وردپرس.
Plugin URI: https://www.payping.ir/
Author: Mahdi Sarani
Author URI: https://mahdisarani.ir
*/
if (!defined('ABSPATH'))
	exit;
define( 'PPD_GPPDIR', plugin_dir_path( __FILE__ ) );
define( 'PPD_GPPDIRU', plugin_dir_url(__FILE__) );

/**
 * Register and enqueue a custom stylesheet in the WordPress admin.
 */
function ppd_enqueue_assets_admin_style() {
	wp_register_style( 'ppd_report_detail_wp_admin', PPD_GPPDIRU . 'assets/css/reposrt-detail.css', false, null );
	if( isset( $_GET['page'] ) && $_GET['page'] == 'payping-transactions' && isset( $_GET['code'] ) ){
		wp_enqueue_style( 'ppd_report_detail_wp_admin' );
	}
}
add_action( 'admin_enqueue_scripts', 'ppd_enqueue_assets_admin_style' );

include_once( PPD_GPPDIR . "includes/PayPing.php" );
include_once( PPD_GPPDIR . "admin/admin.php" );
new PayPingAdminPage();