<?php
/**
 * PayUNetBanking Class that are used to create the form of netbanking.
 *
 * @category Class
 * @package  PayUNetBanking
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUNetBanking extends WC_Payment_Gateway {

	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payuindianetbanking';
		$this->method_title = __( 'PayU Net Banking', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_net'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_net'];
			$this->description      = $this->settings['description_net'];
			$this->merchantid       = $this->settings['merchantid_net'];
			$this->salt             = $this->settings['salt_net'];
			$this->testmode         = $this->settings['testmode_net'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'NB' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payuindianetbanking', array( $this, 'receipt_page' ) );
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
		'enabled_net' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_net' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_net' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_net' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_net' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_net' => array(
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
		<h3><?php esc_attr_e( 'PayU Net Banking', 'payuindia' ); ?></h3>
		<p><?php    esc_attr_e( 'Allows Net Banking Payments.', 'payuindia' ); ?></p>
		<table class="form-table">
			<?php
			$this->generate_settings_html();
			?>
		</table>
		<?php
	}

	/**
	 * Woocoomerce validate fields.
	 *
	 * @var    Validate all the fields
	 * @return void
	 */
	public function validate_fields() {
		global $woocommerce;
		if ( wp_verify_nonce( sanitize_key( $_POST['checkout_nonce_payment'] ), $woocommerce->cart->get_checkout_url() ) ) {
			if ( empty( $_POST['net_billing_cardtype'] ) ) {
				wc_add_notice( esc_attr__( 'Please select bank for further process of payment. ', 'payuindia' ), 'error' );
			}
		}
	}//end validate_fields()

	/**
	 * Show Net Banking form on the checkout page in front end.
	 *
	 * @var    form
	 * @return void
	 */
	public function payment_fields() {
		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' ); ?>
		<p class="form-row form-row-first">
			<label><?php esc_attr_e( 'Select one of the popular banks:', 'payuindia' ); ?>
			<span class="required">*</span></label>
			<select name="net_billing_cardtype" >
			 <option value="" selected="selected">Select Bank</option>
				<option value="ICIB">Industrial Central For International Business</option>
				<option value="SBBJB">State Bank of Bikaner and Jaipur</option>
				<option value="SBHB">State Bank of Hyderabad</option>
				<option value="SOIB">South Indian Bank</option>
				<option value="162B">Kotak Mahindra Bank</option>
				<option value="KRVB">Karur Vysya</option>
				<option value="SBIB">State Bank of India</option>
				<option value="SBMB">State Bank of Mysore</option>
				<option value="VJYB">Vijaya Bank</option>
				<option value="UNIB">United Bank Of India</option>
				<option value="UBIB">Union Bank of India</option>
				<option value="SBTB">State Bank of Travancore</option>
				<option value="KRKB">Karnataka Bank</option>
				<option value="JAKB">Jammu and Kashmir Bank</option>
				<option value="CABB">Canara Bank</option>
				<option value="BOIB">Bank of India</option>
				<option value="BBRB">Bank of Baroda - Retail Banking</option>
				<option value="BBCB">Bank of Baroda - Corporate Banking</option>
				<option value="CBIB">Central Bank of India</option>
				<option value="CITNB">Citibank Netbanking</option>
				<option value="INIB">IndusInd Bank</option>
				<option value="ICIB">ICICI Netbanking</option>
				<option value="HDFB">HDFC Bank</option>
				<option value="DSHB">Deutsche Bank</option>
				<option value="AXIB">AXIS Bank NetBanking</option>
			</select>
			<div class="clear"></div>
			<span style="color:#00AEEF">NOTE:</span> <label>In the next step you will be redirected to your bank's website to verify yourself.</label>
		</p>
		<div class="clear"></div>
		<?php
	}

	/**
	 *  Process the payment and return the result.
	 *
	 * @var    Validate all the fields.
	 * @param  string $order_id will pass the current order id.
	 * @return array
	 */
	function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );
		if ( wp_verify_nonce( sanitize_key( $_POST['checkout_nonce_payment'] ), $woocommerce->cart->get_checkout_url() ) ) {

			$array = array(
				  sanitize_text_field( wp_unslash( $_POST['net_billing_cardtype'] ) ),
			'',
			'',
			'',
			'',
			'',
				  );
			WC()->session->set( 'payment_data', $array );

			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'NB', $order->get_checkout_payment_url( true ) ),
			);
		} else {
			return false;
		}
	}



	/**
	 * Woocoomerce receipt.
	 *
	 * @var    integer $order Will pass the current order details.
	 * @param  string $order Will pass the current order details.
	 * @return void
	 */
	function receipt_page( $order ) {
		echo generate_payu_in_form( $order,$this );
	}



}//end class

/**
 *  Generate array of the payment options for payubiz.
 *
 * @var    Validate all the fields.
 * @return object
 */
function pay_u_net_banking() {
	global $pay_u_net_banking;

	if ( ! isset( $pay_u_net_banking ) ) {
		$pay_u_net_banking = new PayUNetBanking;
	}

	return $pay_u_net_banking;
}//end pay_u_net_banking()


pay_u_net_banking();
