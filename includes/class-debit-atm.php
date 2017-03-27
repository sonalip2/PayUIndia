<?php
/**
 * PayUDebitCardATM Class that are used to create the form of pay_u_debit_atm.
 *
 * @category Class
 * @package  PayUDebitCardATM
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUDebitCardATM extends WC_Payment_Gateway {
	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payu_debit_atm';
		$this->method_title = __( 'PayU Debit Card ATM', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_dc_atm'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_dc_atm'];
			$this->description      = $this->settings['description_dc_atm'];
			$this->merchantid       = $this->settings['merchantid_dc_atm'];
			$this->salt             = $this->settings['salt_dc_atm'];
			$this->testmode             = $this->settings['testmode_dc_atm'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'DC' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payu_debit_atm', array( $this, 'receipt_page' ) );
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
		'enabled_dc_atm' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_dc_atm' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_dc_atm' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_dc_atm' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_dc_atm' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_dc_atm' => array(
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
			if ( empty( $_POST['debit_card_atm'] ) ) {
				wc_add_notice( esc_attr__( 'Please select ATM Card. ', 'payuindia' ), 'error' );
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

		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' ); ?>

		<p class="form-row form-row-first de">
			<label><?php esc_attr_e( 'Select Debit Card', 'payuindia' ); ?>
			<span class="required">*</span></label>
			<select name="debit_card_atm" class="debit_card_atm" id="debit_card_atm">
				<option  selected="selected" value="">Select ATM Card</option>
				<option value="PNDB">Punjab National Bank Debit Card</option>
			</select>
		</p>
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
			$array = array( $_POST['debit_card_atm'] ,'' , '', '','','' );
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
 *  Instance of PayUDebitCardATM.
 *
 * @var    Validate all the fields.
 * @return object
 */
function pay_u_debit_atm() {
	global $pay_u_debit_atm;

	if ( ! isset( $pay_u_debit_atm ) ) {
		$pay_u_debit_atm = new PayUDebitCardATM;
	}

	return $pay_u_debit_atm;
}//end pay_u_debit_atm()


pay_u_debit_atm();
