<?php
/**
 * PayUWallet Class that are used to create the form of netbanking.
 *
 * @category Class
 * @package  PayUWallet
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUWallet extends WC_Payment_Gateway {

	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {
		// Back end view  at checkout page.
		$this->id               = 'payuindiawallet';
		$this->method_title = __( 'PayU Wallet', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_wallet'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_wallet'];
			$this->description      = $this->settings['description_wallet'];
			$this->merchantid       = $this->settings['merchantid_wallet'];
			$this->salt             = $this->settings['salt_wallet'];
			$this->testmode         = $this->settings['testmode_wallet'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'CASH' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payuindiawallet', array( $this, 'receipt_page' ) );
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
		'enabled_wallet' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_wallet' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_wallet' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_wallet' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_wallet' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_wallet' => array(
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
		<h3><?php esc_attr_e( 'PayU Wallet', 'payuindia' ); ?></h3>
		<p><?php    esc_attr_e( 'Allows Wallet Payments.', 'payuindia' ); ?></p>
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
			if ( empty( $_POST['wallet_billing_cardtype'] ) ) {
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
			<select name="wallet_billing_cardtype" >
			 <option value="" selected="selected">Select Wallet Type</option>
				<option value="ITZC">ItzCash</option>
				<option value="CPMC">Citibank Reward Points</option>
				<option value="AMON">Airtel Money</option>
				<option value="YPAY">YPay Cash</option>
				<option value="DONE">DONE Cash Card</option>
				<option value="ICASH">ICash Card</option>
				<option value="ZIPCASH">ZIPcash card</option>
				<option value="OXICASH">Oxicash card</option>
				<option value="PAYZ">HDFC Bank - PayZapp</option>
				<option value="YESW">YES PAY Wallet</option>
				<option value="FREC">FreeCharge</option>
				<option value="OLAM">OLA Money</option>
				<option value="IDM">Idea Money</option>
				<option value="AMEXZ">Amex easy click</option>
				<!--option value="cashcard_PAYCASH_0">PAYCASH CARD</option>-->

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
				  sanitize_text_field( wp_unslash( $_POST['wallet_billing_cardtype'] ) ),
			'',
			'',
			'',
			'',
			'',
				  );
			WC()->session->set( 'payment_data', $array );

			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'CASH', $order->get_checkout_payment_url( true ) ),
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
		echo generate_payu_in_form( $order, $this );
	}
}//end class
