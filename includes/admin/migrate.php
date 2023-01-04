<?php

if ( !class_exists( 'WWCPSM_Migrate' ) ) {
	
	/**
	 * Handles Migrate feature.
	*/

	class WWCPSM_Migrate {
		
		private $data = array();
		private $file = false;
		private $mode = '';
		private $error;
					
	    public function __construct() {}
	    
	    // Initialize wizard form
		public function initialize() {

			$this->get_data_file();

			echo '<div class="wrap">';
			echo '<h1>' . __( 'Subscription Migration', 'wc-wcpay-subscriptions-migration' ) . '</h1>';
			if ( $this->error ) {
				echo '<p>' . $this->error . '</p>';
			}
									
			if ( !$this->data ) {
				$this->get_page_1();
			} else if ( $this->file ) {
				$this->get_page_2();
			} else {
				if ( !$this->mode ) {
					$this->get_page_3_migrate();
				} else {
					$this->get_page_3_rollback();
				}
			}

			echo '</div>';
		}
		
		public function get_page_3_rollback() {
			
			$html   = '<div class="wcpsm_migrate_page container mt-3" id="wcpsm_migrate_page_3">';
			$html  .= '<div>';
			$html  .= '<h4>' . __( 'Step 3/3 - Rollback Result', 'wc-wcpay-subscriptions-migration' ) . '</h4>';
			
			if ( $this->data ) {
				
				$html        .= '<table>';
				$html .= '<tr>
					<th>' . __( 'Subscription', 'wc-wcpay-subscriptions-migration' ) . '</th>
					<th>' . __( 'Result', 'wc-wcpay-subscriptions-migration' ) . '</th>
				</tr>';
				
				foreach ( $this->data as $subscription ) {
					
					$customer_id     = $subscription['customer_id'];
					$old_id          = $subscription['old_id'];
					$new_id          = $subscription['new_id'];					
					$subscription    = $this->get_old_subscription( $customer_id, $old_id );
										
					if ( $subscription ) {
						$subscription_id   = $subscription->get_id();
						$status            = $subscription->get_status();
						$subscription_edit = get_edit_post_link( $subscription_id );
						$subscription_span = '<a target="_blank" href="' . $subscription_edit . '">' . __( "Subscription #$subscription_id - $status", 'wc-wcpay-subscriptions-migration' ) . '</a>';
						$result            = $this->rollback_subscription( $subscription, $old_id, $new_id, $customer_id );
						$message           = $result ? '<span class="wcpsm-success">' . __( 'Rollback success', 'wc-wcpay-subscriptions-migration' ) . '</span>' : '<span class="wcpsm-error">' . __( 'Rollback error', 'wc-wcpay-subscriptions-migration' ) . '</span>';
						$subscription_id   = $subscription->get_id();						
						$html .= '<tr>
							<td>' . $subscription_span . '</td>
							<td>' . $message . '</td>	
						</tr>';
					}
				}
				
				$html  .= '</table>';
				
			}
			$html  .= '</div>';
			$html  .= '</div>';
			
			echo $html;
		}
		
		public function get_page_3_migrate() {
			
			$html   = '<div class="wcpsm_migrate_page container mt-3" id="wcpsm_migrate_page_3">';
			$html  .= '<div>';
			$html  .= '<h4>' . __( 'Step 3/3 - Migration Result', 'wc-wcpay-subscriptions-migration' ) . '</h4>';
			
			if ( $this->data ) {
				
				$html        .= '<table>';
				$html .= '<tr>
					<th>' . __( 'Subscription', 'wc-wcpay-subscriptions-migration' ) . '</th>
					<th>' . __( 'Result', 'wc-wcpay-subscriptions-migration' ) . '</th>
				</tr>';
				
				foreach ( $this->data as $subscription ) {
					
					$customer_id  = $subscription['customer_id'];
					$old_id       = $subscription['old_id'];
					$new_id       = $subscription['new_id'];					
					$subscription = $this->get_subscription( $customer_id, $old_id );
										
					if ( $subscription ) {
						$subscription_id   = $subscription->get_id();
						$status            = $subscription->get_status();
						$subscription_edit = get_edit_post_link( $subscription_id );
						$subscription_span = '<a target="_blank" href="' . $subscription_edit . '">' . __( "Subscription #$subscription_id - $status", 'wc-wcpay-subscriptions-migration' ) . '</a>';
						$result          = $this->migrate_subscription( $subscription, $old_id, $new_id, $customer_id );
						$message         = $result ? '<span class="wcpsm-success">' . __( 'Migration success', 'wc-wcpay-subscriptions-migration' ) . '</span>' : '<span class="wcpsm-error">' . __( 'Migration error', 'wc-wcpay-subscriptions-migration' ) . '</span>';
						$subscription_id = $subscription->get_id();						
						$html .= '<tr>
							<td>' . $subscription_span . '</td>
							<td>' . $message . '</td>	
						</tr>';
					}
				}
				
				$html  .= '</table>';
			}
			
			$html  .= '</div>';
			$html  .= '</div>';
			
			echo $html;
		}
		
		public function get_page_2() {
			
			$html   = '<div class="wcpsm_migrate_page container mt-3" id="wcpsm_migrate_page_2">';
			$html  .= '<div>';
			$html  .= '<h4>' . __( 'Step 2/3 - Validate Associations', 'wc-wcpay-subscriptions-migration' ) . '</h4>';
			
			if ( $this->data && is_array( $this->data ) ) {
				$html  .= '<table>';
				$sub_exists = false;
				$html .= '<tr>
						<th>' . __( 'Customer ID', 'wc-wcpay-subscriptions-migration' ) . '</th>
						<th>' . __( 'Old ID', 'wc-wcpay-subscriptions-migration' ) . '</th>
						<th>' . __( 'New ID', 'wc-wcpay-subscriptions-migration' ) . '</th>
						<th>' . __( 'Status', 'wc-wcpay-subscriptions-migration' ) . '</th>
					</tr>';
					
				foreach ( $this->data as $subscription ) {
					$customer_id = $subscription['customer_id'];
					$old_id      = $subscription['old_id'];
					$new_id      = $subscription['new_id'];
					
					if ( $this->mode ) {
						$status      = $this->get_old_subscription_status( $customer_id, $old_id );
					} else {							
						$status      = $this->get_subscription_status( $customer_id, $old_id );
					}
					
					if ( !$status ) {
						$status = __( 'No subscription was found', 'wc-wcpay-subscriptions-migration' );
					} else {
						$sub_exists = true;
					}
					$html .= '<tr>
						<td>' . $customer_id . '</td>
						<td>' . $old_id . '</td>
						<td>' . $new_id . '</td>
						<td>' . $status . '</td>	
					</tr>';
				}
				$html  .= '</table>';
				
				if ( $sub_exists ) {
					$json_data = json_encode( $this->data );
					$mode      = $this->mode;
					$action    = menu_page_url( 'pl-wcpsm-migration', false );
					$btn_label = $this->mode ? __( 'Rollback Subscriptions', 'wc-wcpay-subscriptions-migration' ) : __( 'Migrate Subscriptions', 'wc-wcpay-subscriptions-migration' );
					$html     .= "<form action='$action' method='post'>
					  <input type='hidden' name='wcpsm_data' value='$json_data'>
					  <input type='hidden' name='wcpsm_mode' value='$mode'>
					  <div class='wcpsm-clear-top'><input type='submit' class='button-primary' value='$btn_label' name='submit'></div>
					</form>";
				} else {
					$html .= '<p>' . __( '0 associated subscriptions were found.', 'wc-wcpay-subscriptions-migration' ) . '</p>';
				}
			}
			
			$html  .= '</div>';
			$html  .= '</div>';
			
			echo $html;
		}
		
		public function get_page_1() {
			
			$action = menu_page_url( 'pl-wcpsm-migration', false );
			$html   = '<div class="wcpsm_migrate_page container mt-3" id="wcpsm_migrate_page_1">';
			$html  .= '<div>';
			$html  .= '<h4>' . __( 'Step 1/3 - Upload CSV File', 'wc-wcpay-subscriptions-migration' ) . '</h4>';
			$html  .= '<form action="' . $action . '" method="post" enctype="multipart/form-data">';
			$html  .= '<div class="wcpsm_mode_div"><select name="wcpsm_mode"><option value="">' . __( 'Migrate', 'wc-wcpay-subscriptions-migration') . '</option><option value="rollback">' . __( 'Rollback Migration', 'wc-wcpay-subscriptions-migration') . '</option></select></div>';
			$html  .= '<input accept=".csv" type="file" name="wcpsm_file" id="wcpsm_file">
			  <div class="wcpsm-clear-top"><input type="submit" class="button-primary" value="' . __( 'Upload File', 'wc-wcpay-subscriptions-migration' ) . '" name="submit"></div>
			</form>';
			$html  .= '</div>';
			$html  .= '</div>';
			
			echo $html;
		}
		
		public function get_old_subscription( $customer_id, $old_id ) {
			
			$args = array ( 
				'subscription_status'    => array( 'any' ),
				'subscriptions_per_page' => 1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
					   'key'     => '_stripe_customer_id',
					   'value'   => $customer_id,
					   'compare' => '=',
					),
					array(
					   'key'     => '_pl_old_payment_method_id',
					   'value'   => $old_id,
					   'compare' => '=',
					)
			    )
			);
			
			$subscriptions = wcs_get_subscriptions( $args );
			$subscription  = $subscriptions ? reset( $subscriptions ) : array(); 
			return $subscription;
		}
		
		public function get_subscription( $customer_id, $old_id ) {
			
			$args = array ( 
				'subscription_status'    => array( 'any' ),
				'subscriptions_per_page' => 1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
					   'key'     => '_stripe_customer_id',
					   'value'   => $customer_id,
					   'compare' => '=',
					),
					array(
					   'key'     => '_payment_method_id',
					   'value'   => $old_id,
					   'compare' => '=',
					)
			    )
			);
			
			$subscriptions = wcs_get_subscriptions( $args );
			$subscription  = $subscriptions ? reset( $subscriptions ) : array(); 
			return $subscription;
		}
		
		public function get_old_subscription_status( $customer_id, $old_id ) {
			
			$status = '';
			$args = array ( 
				'subscription_status' => array( 'any' ),
				'subscriptions_per_page' => 1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
					   'key'     => '_stripe_customer_id',
					   'value'   => $customer_id,
					   'compare' => '=',
					),
					array(
					   'key'     => '_pl_old_payment_method_id',
					   'value'   => $old_id,
					   'compare' => '=',
					)
			    )
			);
			
			$subscriptions = wcs_get_subscriptions( $args );
			$subscription  = $subscriptions ? reset( $subscriptions ) : array(); 
			
			if ( $subscription ) {
				$subscription_id   = $subscription->get_id();
				$status            = $subscription->get_status();
				$subscription_edit = get_edit_post_link( $subscription_id );
				
				$status = '<a target="_blank" href="' . $subscription_edit . '">' . __( "Subscription #$subscription_id - $status", 'wc-wcpay-subscriptions-migration' ) . '</a>';
			} else {
				$status = '';
			}
						
			return $status;
		}
		
		public function get_subscription_status( $customer_id, $old_id ) {
						
			$status = '';
			$args = array ( 
				'subscription_status' => array( 'any' ),
				'subscriptions_per_page' => 1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
					   'key'     => '_stripe_customer_id',
					   'value'   => $customer_id,
					   'compare' => '=',
					),
					array(
					   'key'     => '_payment_method_id',
					   'value'   => $old_id,
					   'compare' => '=',
					)
			    )
			);
			
			$subscriptions = wcs_get_subscriptions( $args );
			$subscription  = $subscriptions ? reset( $subscriptions ) : array(); 
			
			if ( $subscription ) {
				$subscription_id   = $subscription->get_id();
				$status            = $subscription->get_status();
				$subscription_edit = get_edit_post_link( $subscription_id );
				
				$status = '<a target="_blank" href="' . $subscription_edit . '">' . __( "Subscription #$subscription_id - $status", 'wc-wcpay-subscriptions-migration' ) . '</a>';
			} else {
				$status = '';
			}
						
			return $status;
		}
		
		private function get_customer_id_option() {
			
			return WC_Payments::get_gateway()->is_in_test_mode()
				? WC_Payments_Customer_Service::WCPAY_TEST_CUSTOMER_ID_OPTION
				: WC_Payments_Customer_Service::WCPAY_LIVE_CUSTOMER_ID_OPTION;
		}
		
		public function rollback_subscription( $subscription, $old_id, $new_id, $customer_id ) {
			$result = false;
						
			try {
				$tokens = WC_Payment_Tokens::get_customer_tokens( $subscription->get_customer_id(), 'woocommerce_payments' );									foreach ( $tokens as $tokn ) {
					if ( $tokn->get_token() == $old_id ) {							
						$subscription->add_payment_token( $tokn );
						$subscription->save();
						$result = true;			
						delete_post_meta( $subscription->get_id(), '_pl_old_payment_method_id' );
						break;	
					}
				}
				
			} catch ( Exception $e ) {}
			
			return $result;
		}
			
		public function migrate_subscription( $subscription, $old_token, $new_token, $customer_id ) {
			
			$result = false;
			try {
				
				$wcpayments_id = 'woocommerce_payments';
				$tokens    = WC_Payment_Tokens::get_customer_tokens( $subscription->get_customer_id(), $wcpayments_id );								
				if ( $tokens ) {
					$token     = array();
					foreach ( $tokens as $tokn ) {
						if ( $tokn->get_token() == $new_token ) {
							$token = $tokn;
						}
					}
										
					if ( $token ) {
												
						$subscription->add_payment_token( $token );
						$subscription->save();								
						
						$global = WC_Payments::is_network_saved_cards_enabled();
						update_user_option( $subscription->get_customer_id(), $this->get_customer_id_option(), $customer_id, $global );
						update_post_meta( $subscription->get_id(), '_pl_old_payment_method_id', $old_token );								
						$result = true;
					}
				}
			} catch ( Exception $e ) {}
			
			return $result;
		}
		
		public function get_data_file() {
						
			if ( isset( $_POST['wcpsm_mode'] ) ) {
				$this->mode = $_POST['wcpsm_mode'];
			}
			
			if ( isset( $_FILES['wcpsm_file'] ) ) {
				
				$this->file = true;
				if ( isset( $_FILES['wcpsm_file']['error'] ) && $_FILES['wcpsm_file']['error'] ) {
					$this->error = __( 'Error uploading file.', 'wc-wcpay-subscriptions-migration' );
				} else {
					
					$file = $_FILES['wcpsm_file']['tmp_name'];
					$row = 0;
					if ( ( $handle = fopen( $file, "r" ) ) !== FALSE) {
					    while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
					        
					        if ( $row ) {
						        $customer_id  = isset( $data[0] ) ? $data[0] : '';
						        $old_id       = isset( $data[1] ) ? $data[1] : '';
						        $new_id       = isset( $data[2] ) ? $data[2] : '';
						        if ( $customer_id && $old_id && $new_id ) {
							        $this->data[] = array( 'customer_id' => $customer_id, 'old_id' => $old_id, 'new_id' => $new_id );
						        }
					        }
					        $row++;
					    }
					    
					    fclose( $handle );
					}					
				}
			} else if ( isset( $_POST['wcpsm_data'] ) ) {
				
				$raw_data   = str_replace( "\\", "", $_POST['wcpsm_data'] );
				$data       = json_decode( $raw_data, true );
				if ( $data ) {
					$this->data = $data;
				}
			}
		}
	}
}