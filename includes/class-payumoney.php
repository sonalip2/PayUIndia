<?php
/**
 * PayUMoney Class that are used to create the form of netbanking.
 *
 * @category Class
 * @package  PayUMoney
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUMoney extends WC_Payment_Gateway {


	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payuindia_payumoney';
		$this->method_title = __( 'PayU Money', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_payumoney'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_payumoney'];
			$this->description      = $this->settings['description_payumoney'];
			$this->merchantid       = $this->settings['merchantid_payumoney'];
			$this->salt             = $this->settings['salt_payumoney'];
			$this->testmode         = $this->settings['testmode_payumoney'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'WALLET' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payuindia_payumoney', array( $this, 'receipt_page' ) );
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
		'enabled_payumoney' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_payumoney' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_payumoney' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_payumoney' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_payumoney' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_payumoney' => array(
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
		<h3><?php esc_attr_e( 'PayU Money', 'payuindia' ); ?></h3>
		<p><?php    esc_attr_e( 'Allows PayU Money Payments.', 'payuindia' ); ?></p>
		<table class="form-table">
			<?php
			$this->generate_settings_html();
			?>
		</table>
		<?php
	}



	/**
	 * Show Net Banking form on the checkout page in front end.
	 *
	 * @var    form
	 * @return void
	 */
	public function payment_fields() {
		?>
		<p class="form-row form-row-first">
		Supports all Credit Cards, Debit Cards and Netbanking options
		<div class="clear"></div>
		<span style="color:#00AEEF">NOTE:</span>  <label>In the next step you will be redirected to PayUmoney site. Please enter card or netbanking details after sign-in.</label>

		</p><div class="clear"></div><?php
		global $woocommerce;
		$checkout_url = $woocommerce->cart->get_checkout_url();
		wp_nonce_field( $checkout_url, 'checkout_nonce_payment' );

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

			$array = array( 'PAYUW','','','','','' );
			WC()->session->set( 'payment_data', $array );

			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'WALLET', $order->get_checkout_payment_url( true ) ),
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
function pay_u_money() {
	global $pay_u_money;

	if ( ! isset( $pay_u_money ) ) {
		$pay_u_money = new PayUMoney;
	}

	return $pay_u_money;
}//end pay_u_money()


pay_u_money();
