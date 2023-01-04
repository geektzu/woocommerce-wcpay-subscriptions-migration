<?php

if ( !class_exists( 'WCPSM_Admin' ) ) {
	
	/**
	 * Handles Admin Menu page and scripts.
	*/

	class WWCPSM_Admin {
					
	    public function __construct() {
		    
		    // Add settings menu
		    add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );	
		    
		    // Enqueue migration scripts
		    add_action( 'admin_enqueue_scripts', array( $this, 'migration_scripts' ) );	    
	    }
	    
	    /**
		 * Add settings option page.
		*/
	    
	    public function add_settings_menu() {
		    
		    add_submenu_page( 'woocommerce', __( 'WCPay Subscription Migration', 'wc-wcpay-subscriptions-migration' ), __( 'WCPay Subscription Migration', 'pl-gis-helper' ), 'manage_woocommerce', 'pl-wc-wcpsm-migration', array( $this, 'migration_page' ) );
	    }
	    
	    /**
		 * Include migration page.
		*/
	    
	    public function migration_page() {
		    		    
		    $migrate = new WWCPSM_Migrate();
			$migrate->initialize();
	    }
	    
	    public function migration_scripts( $hook ) {
			
			if ( $hook == 'woocommerce_page_pl-wc-wcpsm-migration' ) {
				wp_enqueue_style( 'wwcpsm-migrate-css', plugins_url( 'assets/css/migrate.css', WWCPSM_PLUGIN_FILE ), array(), WWCPSM_PLUGIN_VERSION, false );
			}
		}
	}
}