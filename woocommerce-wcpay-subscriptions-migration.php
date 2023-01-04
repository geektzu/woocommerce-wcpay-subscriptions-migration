<?php
/*
Plugin Name: WooCommerce WCPay Subscriptions Migration
Description: Migrates stripe subscriptions to WooCommerce Payments via CSV file.
Version: 1.0
Author: Marcel Schmitz
Text Domain: wc-wcpay-subscriptions-migration
Domain Path: /languages
Author URI: https://app.codeable.io/tasks/new?preferredContractor=22877
License: GPLv2
*/

if ( !class_exists( 'wc_wcpay_subscriptions_migration' ) ) {

	class wc_wcpay_subscriptions_migration {
		
		private static $instance = null;
		private static $data     = array();		
		public static function get_instance() {
			
			if ( self::$instance == null ) {
		      self::$instance = new wc_wcpay_subscriptions_migration();
		    }
		 
		    return self::$instance;
		}
					
	    public function __construct() {
		    
		    // Include required files
		    add_action( 'plugins_loaded', array( $this, 'includes' ), 12 );
		   		    
		    // Add plugin internationalization
		    add_action( 'init', array( $this, 'load_textdomain' ) );
	    }
	    	    
	    // Show notice if WooCommerce is not active
	    public function woocommerce_error_activation_notice() {
		    
		    $notice = __( 'You need WooCommerce active in order to use WooCommerce Payments Subscriptions Migration.', 'wc-wcpay-subscriptions-migration' );
	  		echo "<div class='error'><p><strong>$notice</strong></p></div>";
	    }
	    
	     // Show notice if WCPay is not active
	    public function wcpay_error_activation_notice() {
		    
		    $notice = __( 'You need WooCommerce Payments active in order to use WooCommerce Payments Subscriptions Migration.', 'wc-wcpay-subscriptions-migration' );
	  		echo "<div class='error'><p><strong>$notice</strong></p></div>";
	    }
	    
	    // Include needed files and classes
	    public function includes() {
		    		    
		    if ( !class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_error_activation_notice' ) );
		    } else if ( !class_exists( 'WC_Payments' ) ) {
			    add_action( 'admin_notices', array( $this, 'wcpay_error_activation_notice' ) );
		    } else {
			    
			    if ( is_admin() ) {
				    
					include_once WWCPSM_DIR_PATH . 'includes/admin/admin.php';
					include_once WWCPSM_DIR_PATH . 'includes/admin/migrate.php';
	
					new WWCPSM_Admin();
			    }
		    }
	    }
	    
	    // Load plugin textdomain
	    public function load_textdomain() {
		    load_plugin_textdomain( 'wc-wcpay-subscriptions-migration', false, dirname( WWCPSM_PLUGIN_BASENAME ) . '/languages' ); 
		}	    
	}
}

if ( class_exists( 'wc_wcpay_subscriptions_migration' ) ) {
	
	if ( ! defined( 'ABSPATH' ) ) {
	    exit; // Exit if accessed directly
	}
			
	define( 'WWCPSM_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'WWCPSM_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'WWCPSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'WWCPSM_PLUGIN_FILE', __FILE__ );
	define( 'WWCPSM_PLUGIN_VERSION', '1.0' );
	
	wc_wcpay_subscriptions_migration::get_instance();
}
