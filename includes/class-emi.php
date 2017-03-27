<?php
/**
 * PayEMI Class that are used to create the form of pay_emi.
 *
 * @category Class
 * @package  PayEMI
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayEMI extends WC_Payment_Gateway {

	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payu_emi';
		$this->method_title = __( 'PayU EMI', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_emi'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_emi'];
			$this->description      = $this->settings['description_emi'];
			$this->merchantid       = $this->settings['merchantid_emi'];
			$this->salt             = $this->settings['salt_emi'];
			$this->testmode         = $this->settings['testmode_emi'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'EMI' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payu_emi', array( $this, 'receipt_page' ) );
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
		'enabled_emi' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_emi' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_emi' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_emi' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_emi' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_emi' => array(
		'title' => __( 'Test Mode', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU Test Mode', 'payuindia' ),
		'default' => 'no',
		'description' => __( 'If you are not using live mode and using test credentials then click here.', 'payuindia' ),
		),
			);
	}//end init_form_fields()


	/**
	 * Show Credit card form on the checkout page in front end.
	 *
	 * @var    form
	 * @return void
	 */
	public function payment_fields() {

		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' );
		$emi_number = isset( $_REQUEST['emi_number'] )?$_REQUEST['emi_number']:'';
		$billing_names_emi = isset( $_REQUEST['billing_names_emi'] ) ? esc_attr( $_REQUEST['billing_names_emi'] ) : '';

			?>

		<p class="form-row form-row-first de">
		  	<label><?php esc_attr_e( 'Select Bank', 'payuindia' ); ?>
		  	<span class="required">*</span></label>
		  	<select name="select_bank" class="select_bank" id="select_bank">
				<option  selected="selected">Select</option>
				<option value="21">ICICI Credit Card</option>
				<option value="INDUS">INDUSIND Credit Card</option>
				<option value="HSBC">HSBC Credit Card</option>
				<option value="KOTAK">KOTAK Credit Card</option>
				<option value="AXIS">AXIS Credit Card</option>
				<option value="15">HDFC Credit Card</option>
				<option value="ICICID">ICICI Debit Card</option>
				<option value="SBI">SBI Credit Card</option>
				<option value="20">CITIBANK Credit Card</option>
				<?php // Exra for liveurl. ?>
				<option value="AMEX">American Express</option>
		  	</select>
		</p>
		<div class="clear"></div>


		<p class="form-row validate-required">
		 	<label><?php esc_attr_e( 'Select duration', 'payuindia' ); ?>
		 	 <span class="required"></span></label>
			<select name="select_duration" class="select_duration" id="select_duration">
			<option  selected="selected">Select</option>
		  </select>
		</p>



		<div class="clear"></div>
		<div class="emi_card_form" style="display:none">
		<div class="clear"></div>
		<p class="form-row validate-required">
		  <label><?php esc_attr_e( 'Card Number', 'payuindia' ); ?>
		  <span class="required">*</span></label>
		  <input class="input-text" type="text"  name="emi_number" value="<?php echo $emi_number; ?>" />
		</p>
		<p class="form-row validate-required">
		   <label><?php esc_attr_e( 'Name On Card', 'payuindia' ); ?>
		   <span class="required">*</span></label>
		   <input class="input-text" type="text" size="19" maxlength="19" name="billing_names_emi" value="<?php echo $billing_names_emi; ?>" />
		 </p>
		<p class="form-row form-row-first">
		  <label><?php esc_attr_e( 'Expiration Date', 'payuindia' ); ?>
		  <span class="required">*</span></label>
		  <select name="emi_exp_datemon">
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
		  <select name="emi_exp_dateyr">
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
			<input class="input-text" type="password" size="4" maxlength="4" name="emi_cvv" value="" />
		</p>
		<div class="clear"></div>
		<p>An additional fee of 1%  (Bank processing charges) will be applicable.</p>
		</div>

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

			$number = str_replace( ' ','', $_POST['emi_number'] );

			if ( ! luhn_check( $number ) ) {
					wc_add_notice( esc_attr__( 'Card number you entered is invalid.', 'payuindia' ), 'error' );
			}
			if ( ! WC_PAYU_INDIA_UTILITY::is_valid_expiry( $_POST['emi_exp_datemon'], $_POST['emi_exp_dateyr'] ) ) {
					wc_add_notice( esc_attr__( 'Card expiration date is not valid.', 'payuindia' ), 'error' );
			}
			if ( ! WC_PAYU_INDIA_UTILITY::is_valid_cvv_number( $_POST['emi_cvv'] ) ) {
					wc_add_notice( esc_attr__( 'Card verification number (CVV) is not valid. You can find this number on your credit card.', 'payuindia' ), 'error' );
			}
		}
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

			$array = array( $_POST['select_duration'] ,str_replace( ' ','', $_POST['emi_number'] ) , $_POST['emi_exp_datemon'], $_POST['emi_exp_dateyr'],$_POST['emi_cvv'],isset( $_POST['billing_names_emi'] )?$_POST['billing_names_emi']:' ' );
			WC()->session->set( 'payment_data', $array );

			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'EMI', $order->get_checkout_payment_url( true ) ),
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
		echo generate_payu_in_form( $order,$this );
	}//end receipt_page()
}
