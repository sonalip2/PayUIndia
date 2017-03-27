<?php
/**
 * PayUCreditCard Class that are used to create the form of credit cart.
 *
 * @category Class
 * @package  PayUCreditCard
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUCreditCard extends WC_Payment_Gateway {

	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {

		$this->id               = 'payu_creditcard';
		$this->method_title = __( 'PayU Credit Card', 'payuindia' );
		$this->has_fields       = true;
		$this->liveurl          = 'https://secure.payu.in/_payment';
		$this->testurl          = 'https://test.payu.in/_payment';

		// Added common functon files.
		require_once( PAYUINDIA_PLUGIN_CLASSES_DIR . 'common-functions.php' );
		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Check if the currency is set to INR. If not we disable the plugin here.
		if ( 'INR' === get_option( 'woocommerce_currency' ) ) {
			$payu_in_enabled = $this->settings['enabled_cc'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_cc'];
			$this->description      = $this->settings['description_cc'];
			$this->merchantid       = $this->settings['merchantid_cc'];
			$this->salt             = $this->settings['salt_cc'];
			$this->testmode         = $this->settings['testmode_cc'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
				check_transaction_status( $this );
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payu_creditcard', array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}//end __construct()

	/**
	 * Add admin links.
	 *
	 * @var    string
	 * @return void
	 */
	function init_form_fields() {
		 $this->form_fields = array(
		 'enabled_cc' => array(
		 'title' => __( 'Enable/Disable', 'payuindia' ),
		 'type' => 'checkbox',
		 'label' => __( 'Enable PayU India', 'payuindia' ),
		 'default' => 'no',
		 ),
		 'title_cc' => array(
		 'title' => __( 'Title', 'payuindia' ),
		 'type' => 'text',
		 'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		 ),
		 'description_cc' => array(
		 'title' => __( 'Description', 'payuindia' ),
		 'type' => 'textarea',
		 'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		 ),
		 'merchantid_cc' => array(
		 'title' => __( 'Merchant ID', 'payuindia' ),
		 'type' => 'text',
		 'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		 'default' => '',
		 ),
		 'salt_cc' => array(
		 'title' => __( 'SALT', 'payuindia' ),
		 'type' => 'text',
		 'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		 'default' => '',
		 ),

		 'testmode_cc' => array(
		 'title' => __( 'Test Mode', 'payuindia' ),
		 'type' => 'checkbox',
		 'label' => __( 'Enable PayU Test Mode', 'payuindia' ),
		 'default' => 'no',
		 'description' => __( 'If you are not using live mode and using test credentials then click here.', 'payuindia' ),
		 ),
		 );
	}//end init_form_fields()

	/**
	 * Add admin links.
	 *
	 * @var    string
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'PayU Credit Card', 'payuindia' ); ?></h3>
		<p><?php _e( 'PayU Credit Card works with Indian Rupee.', 'payuindia' ); ?></p>
		<?php
		if ( 'INR' === get_option( 'woocommerce_currency' ) ) {
			?>
			<table class="form-table">
			<?php
			// Generate the HTML For the settings form.
			$this->generate_settings_html();
			?>
			</table>
			<?php
		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'payuindia' ); ?></strong> <?php echo sprintf( __( 'Choose Indian Rupee (₹) as your store currency in <a href="%s">Pricing Options</a> to enable the PayU Gateway.', 'payuindia' ), admin_url( '?page=woocommerce&tab=general' ) ); ?></p></div>
		<?php
		}
	}//end admin_options()


	/**
	 * Show Credit card form on the checkout page in front end.
	 *
	 * @var    form
	 * @return void
	 */
	public function payment_fields() {
		$billing_credircard = isset( $_REQUEST['billing_credircard'] ) ? esc_attr( $_REQUEST['billing_credircard'] ) : '';
		$billing_names = isset( $_REQUEST['billing_names'] ) ? esc_attr( $_REQUEST['billing_names'] ) : '';
		$billing_expdatemonth = isset( $_REQUEST['billing_expdatemonth'] ) ? esc_attr( $_REQUEST['billing_expdatemonth'] ) : '';
		$billing_expdateyear = isset( $_REQUEST['billing_expdateyear'] ) ? esc_attr( $_REQUEST['billing_expdateyear'] ) : '';
		$billing_ccvnumber = isset( $_REQUEST['billing_ccvnumber'] ) ? esc_attr( $_REQUEST['billing_ccvnumber'] ) : '';

		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' ); ?>
					<p class="form-row validate-required">
					  <label><?php esc_attr_e( 'Card Number', 'payuindia' ); ?>
					  <span class="required">*</span></label>
					  <input class="input-text" type="text" size="19" maxlength="19" name="billing_credircard" value="<?php echo $billing_credircard; ?>" />
					</p>
	 <p class="form-row validate-required">
	   <label><?php esc_attr_e( 'Name On Card', 'payuindia' ); ?>
	   <span class="required">*</span></label>
	   <input class="input-text" type="text" size="19" maxlength="19" name="billing_names" value="<?php echo $billing_names; ?>" />
	 </p>
					<p class="form-row form-row-first">
					  <label><?php esc_attr_e( 'Card Type', 'payuindia' ); ?>
					  <span class="required">*</span></label>
					  <select name="billing_cardtype" >
						<option value="Visa" selected="selected">Visa</option>
						<option value="MasterCard">MasterCard</option>
						<option value="DINR">Diners Club</option>
						<option value="AMEX">American Express</option>
					  </select>
					</p>
					<div class="clear"></div>
					<p class="form-row form-row-first">
					  <label><?php esc_attr_e( 'Expiration Date', 'payuindia' ); ?>
					  <span class="required">*</span></label>
					  <select name="billing_expdatemonth" value="<?php echo $billing_expdatemonth?>">
						<option value=01>01</option>
						<option value=02>02</option>
						<option value=03>03</option>
						<option value=04>04</option>
						<option value=05>05</option>
						<option value=06>06</option>
						<option value=07>07</option>
						<option value=08>08</option>
						<option value=09>09</option>
						<option value=10>10</option>
						<option value=11>11</option>
						<option value=12>12</option>
					  </select>
					  <select name="billing_expdateyear" value="<?php echo $billing_expdateyear;?>">
						<?php
						$today = (int) date( 'Y', time() );

						for ( $i = 0; $i < 8; $i++ ) {
							?>
						  <option value="<?php  echo $today; ?>">
						<?php echo $today; ?>
						</option>
						<?php
						$today++;
						}
					?>
					</select>
					</p>
					<div class="clear"></div>
					<p class="form-row form-row-first validate-required">
					<label><?php esc_attr_e( 'Card Verification Number (CVV)', 'payuindia' ); ?>
				<span class="required">*</span></label>
				<input class="input-text" type="password" size="4" maxlength="4" name="billing_ccvnumber" value="<?php echo $billing_ccvnumber?>" />
				</p>
				<div class="clear"></div>

				<?php
	}//end payment_fields()


	/**
	 * Woocoomerce validate fields.
	 *
	 * @var    Validate all the fields
	 * @return void
	 */
	public function validate_fields() {
		global $woocommerce;

		if ( wp_verify_nonce( sanitize_key( $_POST['checkout_nonce_payment'] ), $woocommerce->cart->get_checkout_url() ) ) {
			$creditcard = array(
			'Visa'       => '/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/',
			'MasterCard' => '/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/',
			'DINR'       => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
			'AMEX'       => '/^3\d{3}[ \-]?\d{6}[ \-]?\d{5}$/',
			);
			$match      = false;
			foreach ( $creditcard as $type => $pattern ) {
				if ( $type === $_POST['billing_cardtype'] ) {
					if ( preg_match( $pattern, str_replace( ' ', '',$_POST['billing_credircard'] ) ) ) {
						$match = true;
					}
				}
			}
			$number = str_replace( ' ', '',$_POST['billing_credircard'] );
			$type = $_POST['billing_cardtype'];
			if ( ! valid_card( $number, $type ) ) {
				wc_add_notice( esc_attr__( 'Credit card number you entered is invalid.', 'payuindia' ), 'error' );
			}
			if ( ! $match ) {
				wc_add_notice( esc_attr__( 'Card type is not valid.', 'payuindia' ), 'error' );
			}

			if ( ! WC_PAYU_INDIA_UTILITY::is_valid_expiry( $_POST['billing_expdatemonth'], $_POST['billing_expdateyear'] ) ) {
					wc_add_notice( esc_attr__( 'Card expiration date is not valid.', 'payuindia' ), 'error' );
			}

			$count = ('AMEX' === $_POST['billing_cardtype'] ) ? 4 : 3;
			if ( ! preg_match( '/^[0-9]{' . $count . '}$/', $_POST['billing_ccvnumber'] ) ) {
					wc_add_notice( esc_attr__( 'Card verification number (CVV) is not valid. You can find this number on your credit card.', 'payuindia' ), 'error' );
			}
		}//end if()
	}//end validate_fields()



	/**
	 *  Process the payment and return the result.
	 *
	 * @var    Validate all the fields.
	 * @param  string $order_id will pass the current order id.
	 * @return boolean
	 */
	function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );

		if ( wp_verify_nonce( sanitize_key( $_POST['checkout_nonce_payment'] ), $woocommerce->cart->get_checkout_url() ) ) {
			if ( 'MasterCard' === $_POST['billing_cardtype'] ) {
				$billing_cardtype = 'Visa';
			} else {
				$billing_cardtype = $_POST['billing_cardtype'];
			}
			$array = array( $billing_cardtype,str_replace( ' ', '',$_POST['billing_credircard'] ), $_POST['billing_expdatemonth'], $_POST['billing_expdateyear'],$_POST['billing_ccvnumber'] ,$_POST['billing_names'] );
			WC()->session->set( 'payment_data', $array );
			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'CC', $order->get_checkout_payment_url( true ) ),
			);
		} else {
			return false;
		}
	}//end process_payment()

	/**
	 * Woocoomerce receipt page.
	 *
	 * @var    integer $order Will pass the current order details.
	 * @param  string $order Will pass the current order details.
	 * @return void
	 */
	function receipt_page( $order ) {
		echo generate_payu_in_form( $order, $this );
	}//end receipt_page()
}//end class
