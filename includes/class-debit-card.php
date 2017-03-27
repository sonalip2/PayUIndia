<?php
/**
 * PayUDebitCard Class that are used to create the form of pay_u_debit_card.
 *
 * @category Class
 * @package  PayUDebitCard
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUDebitCard extends WC_Payment_Gateway {
	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payu_debit_card';
		$this->method_title = __( 'PayU Debit Card', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_dc'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_dc'];
			$this->description      = $this->settings['description_dc'];
			$this->merchantid       = $this->settings['merchantid_dc'];
			$this->salt             = $this->settings['salt_dc'];
			$this->testmode             = $this->settings['testmode_dc'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'DC' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payu_debit_card', array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Add admin links.
	 *
	 * @var    string
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
		'enabled_dc' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_dc' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_dc' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_dc' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_dc' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_dc' => array(
		'title' => __( 'Test Mode', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU Test Mode', 'payuindia' ),
		'default' => 'no',
		'description' => __( 'If you are not using live mode and using test credentials then click here.', 'payuindia' ),
		),
			);
	}//end init_form_fields()




	/**
	 * Woocoomerce validate fields.
	 *
	 * @var    Validate all the fields
	 * @return void
	 */
	public function validate_fields() {
		global $woocommerce;
		if ( wp_verify_nonce( sanitize_key( $_POST['checkout_nonce_payment'] ), $woocommerce->cart->get_checkout_url() ) ) {
			$debitcard = array(
			'MAST'       => '/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/',
			'MAES'       => '/^(5018|5020|5038|6304|6759|6761|6763)[0-9]{8,15}$/',
			'RUPAY'      => '/^6[0-9]{15}$/',
			'VISA'       => '/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/',
			'SMAE'       => '/^(5018|5020|5038|6304|6759|6761|6763)[0-9]{8,15}$/',
			 );

			$match      = false;
			foreach ( $debitcard as $type => $pattern ) {
				if ( $type === $_POST['debit_card'] ) {
					if ( preg_match( $pattern, str_replace( ' ', '', $_POST['billing_debitcard'] ) ) ) {
						$match = true;
					}
				}
			}
			 $number = str_replace( ' ', '', $_POST['billing_debitcard'] );
			 $type = $_POST['debit_card'];
			if ( ! valid_card( $number, $type ) ) {
				   wc_add_notice( esc_attr__( 'Debit card number you entered is invalid.', 'payuindia' ), 'error' );
			}
			if ( ! $match ) {
				   wc_add_notice( esc_attr__( 'Card type is not valid.', 'payuindia' ), 'error' );
			}
			if ( ! WC_PAYU_INDIA_UTILITY::is_valid_expiry( $_POST['billing_exp_datemon'], $_POST['billing_exp_dateyr'] ) ) {
					   wc_add_notice( esc_attr__( 'Card expiration date is not valid.', 'payuindia' ), 'error' );
			}

			 $count = 3;
			if ( ! preg_match( '/^[0-9]{' . $count . '}$/', $_POST['billing_cvv'] ) ) {
					   wc_add_notice( esc_attr__( 'Card verification number (CVV) is not valid. You can find this number on your credit card.', 'payuindia' ), 'error' );
			}
		}//end if()
	}//end validate_fields()

	/**
	 * Show Credit card form on the checkout page in front end.
	 *
	 * @var    form
	 * @return void
	 */
	public function payment_fields() {
		$billing_debitcard = isset( $_REQUEST['billing_debitcard'] ) ? esc_attr( $_REQUEST['billing_debitcard'] ) : '';
		$billing_names_dc = isset( $_REQUEST['billing_names_dc'] ) ? esc_attr( $_REQUEST['billing_names_dc'] ) : '';
		$billing_exp_datemon = isset( $_REQUEST['billing_exp_datemon'] ) ? esc_attr( $_REQUEST['billing_exp_datemon'] ) : '';
		$billing_exp_dateyr = isset( $_REQUEST['billing_exp_dateyr'] ) ? esc_attr( $_REQUEST['billing_exp_dateyr'] ) : '';
		$billing_cvv = isset( $_REQUEST['billing_cvv'] ) ? esc_attr( $_REQUEST['billing_cvv'] ) : '';

		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' ); ?>

		<p class="form-row form-row-first de">
			<label><?php esc_attr_e( 'Select Debit Card', 'payuindia' ); ?>
			<span class="required">*</span></label>
			<select name="debit_card" class="debit_card" id="debit_card1">
				<option  selected="selected" value="">Select Debit Card Type</option>
				<option value="MAST">MasterCard Debit Cards (All Banks)</option>
				<option value="MAES">Other Maestro Cards</option>
				<option value="RUPAY">Rupay Debit Card</option>
				<option value="SMAE">State Bank Maestro Cards</option>
				<option value="VISA">Visa Debit Cards (All Banks)</option>
			</select>
		</p>
		<div class="clear"></div>
		<div class="debit_card_form" style="display:none;">
		<p class="form-row validate-required">
		  <label><?php esc_attr_e( 'Card Number', 'payuindia' ); ?>
		  <span class="required">*</span></label>
		  <input class="input-text" type="text"  name="billing_debitcard" value="<?php echo $billing_debitcard; ?>" />
		</p>
		<p class="form-row validate-required">
	   <label><?php esc_attr_e( 'Name On Card', 'payuindia' ); ?>
	   <span class="required">*</span></label>
	   <input class="input-text" type="text" size="19" maxlength="19" name="billing_names_dc" value="<?php echo $billing_names_dc; ?>" />
	 </p>
		<div class="clear"></div>
		<p class="form-row form-row-first">
		  <label><?php esc_attr_e( 'Expiration Date', 'payuindia' ); ?>
		  <span class="required">*</span></label>
		  <select name="billing_exp_datemon" value="<?php echo $billing_exp_datemon?>">
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
		  <select name="billing_exp_dateyr" value="<?php echo $billing_exp_dateyr?>" >
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
			<input class="input-text" type="password" size="4" maxlength="4" name="billing_cvv" value="<?php echo $billing_cvv?>" />
		</p>
		</div>
		<div class="clear"></div>

				<?php
	}//end payment_fields()

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
			$array = array( $_POST['debit_card'] ,str_replace( ' ', '', $_POST['billing_debitcard'] ) , $_POST['billing_exp_datemon'], $_POST['billing_exp_dateyr'],$_POST['billing_cvv'],$_POST['billing_names_dc'] );
			WC()->session->set( 'payment_data', $array );
			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'DC', $order->get_checkout_payment_url( true ) ),
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
		echo generate_payu_in_form( $order ,$this );
	}//end receipt_page()
}

/**
 *  Instance of PayUDebitCard.
 *
 * @var    Validate all the fields.
 * @return object
 */
function pay_u_debit_card() {
	global $pay_u_debit_card;

	if ( ! isset( $pay_u_debit_card ) ) {
		$pay_u_debit_card = new PayUDebitCard;
	}

	return $pay_u_debit_card;
}//end pay_u_debit_card()


pay_u_debit_card();
