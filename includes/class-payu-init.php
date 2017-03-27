<?php
/**
 * PayUInit Class that are used to add functionality.
 *
 * @category Class
 * @package  PayUInit
 * @author   Tailored Solutions Pvt. Ltd.
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class PayUInit {



	/**
	 * Construct function.
	 *
	 * @var string
	 */
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'load_payment_classes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}
	/**
	 * Add scripts.
	 *
	 * @var string
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_script( 'jquery.payuindia_onsite', WP_PLUGIN_URL . '/PayUIndia/assets/js/payuindia_onsite.js', array( 'jquery' ), false, true );
		wp_localize_script( 'jquery.payuindia_onsite', 'my_plugin_script_vars', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}


	/**
	 * Load all classes
	 *
	 * @var string
	 * @return void
	 */
	public function load_payment_classes() {
		/**
		 * PayUCreditCard File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-credit-card.php';

		/**
		 * PayUDebitCard File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-debit-card.php';

		/**
		 * PayUDebitCardATM File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-debit-atm.php';

		/**
		 * WC_PAYU_INDIA_UTILITY File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-utility.php';
		/**
		 * PayUNetBanking File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-net-banking.php';

		/**
		 * PayUMoney File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-payumoney.php';

		/**
		 * PayUAmexEzeClick File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-amex-eze-click.php';

		/**
		 * PayEMI File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-emi.php';

		/**
		 * PayUWallet File added.
		 */
		require_once PAYUINDIA_PLUGIN_CLASSES_DIR . 'class-wallet.php';

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		};

		/* Adding payment gateways to woocommerce.*/
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payuindiapaymentgateway' ) );
	}
	/**
	 * Register Payu India methods.
	 *
	 * @var method
	 * @param array $methods credit card method send.
	 * @return $methods
	 */
	public function add_payuindiapaymentgateway( $methods ) {
		$methods[] .= 'PayUCreditCard';
		$methods[] .= 'PayUDebitCard';
		$methods[] .= 'PayUDebitCardATM';
		$methods[] .= 'PayUNetBanking';
		$methods[] .= 'PayUMoney';
		$methods[] .= 'PayUAmexEzeClick';
		$methods[] .= 'PayEMI';
		$methods[] .= 'PayUWallet';

		return $methods;
	}
}

/**
 *  Generate array of the payment options for payubiz.
 *
 * @var Validate all the fields.
 * @return object
 */
function pay_u_init() {
	global $pay_u_init;

	if ( ! isset( $pay_u_init ) ) {
		$pay_u_init = new PayUInit;
	}

	return $pay_u_init;
}

pay_u_init();
