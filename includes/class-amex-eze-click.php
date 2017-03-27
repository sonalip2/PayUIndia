<?php
/**
 * PayUAmexEzeClick Class that are used to create the form of PayUAmexEzeClick.
 *
 * @category Class
 * @package  PayUAmexEzeClick
 * @author   Tasolglobal
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUAmexEzeClick extends WC_Payment_Gateway {
	/**
	 * CONSTRUCT FUNCTION.
	 *
	 * @var string
	 */
	public function __construct() {

		$this->id               = 'payu_amex_ezeclick';
		$this->method_title = __( 'PayU Amex ezeClick', 'payuindia' );
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
			$payu_in_enabled = $this->settings['enabled_eze'];
		} else {
			$payu_in_enabled = 'no';
		}

			$this->enabled          = $payu_in_enabled;
			$this->title            = $this->settings['title_eze'];
			$this->description      = $this->settings['description_eze'];
			$this->merchantid       = $this->settings['merchantid_eze'];
			$this->salt             = $this->settings['salt_eze'];
			$this->testmode             = $this->settings['testmode_eze'];

		if ( isset( $_GET['payuindia_callback'] ) &&  '1' === esc_attr( $_GET['payuindia_callback'] ) ) {
			if ( 'CASH' === $_GET['pg'] ) {
				check_transaction_status( $this );
			}
		}

			// Receipt page action.
			add_action( 'woocommerce_receipt_payu_amex_ezeclick', array( $this, 'receipt_page' ) );
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
		'enabled_eze' => array(
		'title' => __( 'Enable/Disable', 'payuindia' ),
		'type' => 'checkbox',
		'label' => __( 'Enable PayU India', 'payuindia' ),
		'default' => 'no',
		),
		'title_eze' => array(
		'title' => __( 'Title', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This text show at frontend during checkout page.', 'payuindia' ),
		),
		'description_eze' => array(
		'title' => __( 'Description', 'payuindia' ),
		'type' => 'textarea',
		'description' => __( 'This text show at frontend during during checkout page.', 'payuindia' ),
		),
		'merchantid_eze' => array(
		'title' => __( 'Merchant ID', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'This ID is generated at the time of activation of your site and helps to uniquely identify you to PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),
		'salt_eze' => array(
		'title' => __( 'SALT', 'payuindia' ),
		'type' => 'text',
		'description' => __( 'SALT provided by PayU Credit Card.', 'payuindia' ),
		'default' => '',
		),

		'testmode_eze' => array(
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
		<h3><?php _e( 'PayU Amex ezeClick', 'payuindia' ); ?></h3>
		<p><?php _e( 'PayU Amex ezeClick works with Indian Rupee.', 'payuindia' ); ?></p>
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
		?>
		<p class="form-row form-row-first">
		American Express ezeClick is a one ID online payment solution for you. It's a solution that replaces the need to enter Credit Card details with just a single user ID. It also provides you an opportunity to sync multiple American Express Cards to this unique ID.
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
	 * @return boolean
	 */
	function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );
			$array = array( 'AMEXZ' ,'' ,'','','','' );
			WC()->session->set( 'payment_data', $array );
			return array(
			'result'    => 'success',
			'redirect'  => add_query_arg( 'pg', 'CASH', $order->get_checkout_payment_url( true ) ),
		);

	}


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
}//end class

/**
 *  Generate array of the payment options for payubiz.
 *
 * @var    Validate all the fields.
 * @return object
 */
function amex_ezeclick() {
	global $amex_ezeclick;

	if ( ! isset( $amex_ezeclick ) ) {
		$amex_ezeclick = new PayUAmexEzeClick;
	}

	return $amex_ezeclick;
}//end amex_ezeclick()


amex_ezeclick();
