<?php
/**
 * Common-function File that contains common functions that are used in all payu methods.
 *
 * @author   Tailored Solutions Pvt. Ltd.
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */

/**
 * Calculate hash before transaction for security & credit card and net banking common functions starts from here.
 *
 * @var     integer $hash_data will pass the current order details.
 * @param   string $hash_data will pass the current order details.
 * @param   array  $obj  will used in all payment methods.
 * @return  $hash_data,$obj
 */
function calculate_hash_before_transaction( $hash_data, $obj ) {

	$hash_sequence = 'key|txnid|amount|productinfo|firstname|email|order_id|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10';
	$hash_vars_seq = explode( '|', $hash_sequence );
	$hash_string = '';

	foreach ( $hash_vars_seq as $hash_var ) {
		$hash_string .= isset( $hash_data[ $hash_var ] ) ? $hash_data[ $hash_var ] : '';
		$hash_string .= '|';
	}

	$hash_string .= $obj->salt;
	$hash_data['hash'] = strtolower( hash( 'sha512', $hash_string ) );

	return $hash_data['hash'];
}//end calculate_hash_before_transaction()

	/**
	 * Woocoomerce check if payment is success.
	 *
	 * @var     integer $txnid Will pass the current order details.
	 * @param   string $txnid Will pass the current order details.
	 * @param   array  $obj  will used in all payment methods.
	 * @return  boolean
	 */
function payment_success( $txnid, $obj ) {
	?><style type="text/css">
		.entry-title {
			visibility: hidden;
		}

		.entry-title:before {
			content: 'Order Success';
			visibility:visible;
		}
	</style>
	<?php
	if ( 'success' === payu_in_transaction_verification( $txnid, $obj ) ) {
		global $woocommerce;

		$order = new WC_Order( $_GET['orders_id'] );
		$current_post = get_post( $order->id );
		$current_post->post_status = 'wc-processing';
		wp_update_post( $current_post );

		$woocommerce->cart->empty_cart();

		return true;
	} else {
		die( 'PayU verification failed!' );
	}
}//end payment_success()


	/**
	 * Woocoomerce check if payment is pending.
	 *
	 * @var     integer $txnid Will pass the current order details.
	 * @param   string $txnid Will pass the current order details.
	 * @param   array  $obj  will used in all payment methods.
	 * @return  boolean
	 */
function payment_pending( $txnid, $obj ) {
	?><style type="text/css">
		.entry-title {
			visibility: hidden;
		}

		.entry-title:before {
			content: 'Order success';
			visibility:visible;
		}
	</style>
	<?php
	if ( 'pending' === payu_in_transaction_verification( $txnid, $obj ) ) {
		global $woocommerce;

		$order = new WC_Order( $_GET['orders_id'] );
		$current_post = get_post( $order->id );
		$current_post->post_status = 'wc-pending';
		wp_update_post( $current_post );
		$woocommerce->cart->empty_cart();

		return false;
	} else {
		die( 'PayU verification failed!' );
	}
}//end payment_pending()

	/**
	 * Woocoomerce check if payment is failed.
	 *
	 * @var     integer $txnid Will pass the current order details.
	 * @param   string $txnid Will pass the current order details.
	 * @param   array  $obj  will used in all payment methods.
	 * @return  boolean
	 */
function payment_failure( $txnid, $obj ) {
	?><style type="text/css">
		.entry-title {
			visibility: hidden;
		}

		.entry-title:before {
			content: 'Order Failed';
			visibility:visible;
		}
	</style>
	<?php
	$order = new WC_Order( $_GET['orders_id'] );
	$current_post = get_post( $order->id );
	$current_post->post_status = 'wc-failed';
	wp_update_post( $current_post );

	return false;
}//end payment_failure()

	/**
	 * Payu transaction verification .
	 *
	 * @var     integer $txnid Will pass the current order details.
	 * @param   string $txnid Will pass the current order details.
	 * @param   array  $obj  will used in all payment methods.
	 * @return  $response
	 */
function payu_in_transaction_verification( $txnid, $obj ) {

	$obj->verification_liveurl     = 'https://info.payu.in/merchant/postservice';
	$obj->verification_testurl     = 'https://test.payu.in/merchant/postservice';

	$host = $obj->verification_liveurl;
	if ( 'yes' === $obj->testmode ) {
		$host = $obj->verification_testurl;
	}

	$hash_data['key'] = $obj->merchantid;
	$hash_data['command'] = 'verify_payment';
	$hash_data['var1'] = $txnid;
	$hash_data['hash'] = calculate_hash_before_verification( $hash_data, $obj );

	// Call the PayU, and verify the status.
	$response = send_request( $host, $hash_data );

	$response = maybe_unserialize( $response );

	return $response['transaction_details'][ $txnid ]['status'];
}//end payu_in_transaction_verification()

	/**
	 * Calculate hash before varification for security.
	 *
	 * @var     integer $hash_data Will pass the current order details.
	 * @param   string $hash_data Will pass the current order details.
	 * @param   array  $obj  will used in all payment methods.
	 * @return  $hash_data
	 */
function calculate_hash_before_verification( $hash_data, $obj ) {

	$hash_sequence = 'key|command|var1';
	$hash_vars_seq = explode( '|', $hash_sequence );
	$hash_string = '';

	foreach ( $hash_vars_seq as $hash_var ) {
		$hash_string .= isset( $hash_data[ $hash_var ] ) ? $hash_data[ $hash_var ] : '';
		$hash_string .= '|';
	}

	$hash_string .= $obj->salt;
	$hash_data['hash'] = strtolower( hash( 'sha512', $hash_string ) );

	return $hash_data['hash'];
}//end calculate_hash_before_verification()

	/**
	 * Check transaction status.
	 *
	 * @var    integer $hash_data Will pass the current order details.
	 *
	 * @param  array $obj will used in all payment methods.
	 * @return void
	 */
function check_transaction_status( $obj ) {

	$salt = $obj->salt;

	if ( ! empty( $_REQUEST ) ) {
		foreach ( $_REQUEST as $key => $value ) {
			$txn_rs[ $key ] = htmlentities( $value, ENT_QUOTES );
		}
	} else {
		die( 'No transaction data was passed!' );
	}

		$txnid = $txn_rs['txnid'];

		/* Checking hash / true or false */
	if ( check_hash_after_transaction( $salt, $txn_rs ) ) {
		if ( 'success' === $txn_rs['status'] ) {
			payment_success( $txnid, $obj );
		}

		if ( 'pending' === $txn_rs['status'] ) {
			payment_pending( $txnid, $obj );
		}

		if ( 'failure' === $txn_rs['status'] ) {
			payment_failure( $txnid, $obj );
		}
	} else {
		die( 'Hash incorrect!' );
	}
}//end check_transaction_status()

	/**
	 * Connect to the payu payment method.
	 *
	 * @var    integer $host,$data will pass the current order details.
	 * @param  string $host will pass the current order details.
	 * @param  string $data will pass the current order details.
	 * @throws Exception There was a problem connecting to the payment gateway.
	 * @throws Exception Empty PayU response.
	 * @return $parsed_response
	 */
function send_request( $host, $data ) {

	$response = wp_remote_post($host, array(
		'method' => 'POST',
		'body' => $data,
		'timeout' => 70,
		'sslverify' => false,
	));

	// Error occured with payment gateway connection.
	if ( is_wp_error( $response ) ) {
		throw new Exception( __( 'There was a problem connecting to the payment gateway.', 'payuindia' ) );
	}

	// Error occured with empty response.
	if ( empty( $response['body'] ) ) {
		throw new Exception( __( 'Empty PayU response.', 'payuindia' ) );
	}

	$parsed_response = $response['body'];

	return $parsed_response;
}//end send_request()


	/**
	 * Calculate hash after transaction for security.
	 *
	 * @var    integer $salt,$txn_rs Will pass the current order details.
	 * @param  string $salt Will pass the current order details.
	 * @param  string $txn_rs Will pass the current order details.
	 * @return boolean
	 */
function check_hash_after_transaction( $salt, $txn_rs ) {
	$hash_sequence = 'key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10';
	$hash_vars_seq = explode( '|', $hash_sequence );
	// generation of hash after transaction is = salt + status + reverse order of variables.
	$hash_vars_seq = array_reverse( $hash_vars_seq );

	$merc_hash_string = $salt . '|' . $txn_rs['status'];

	foreach ( $hash_vars_seq as $merc_hash_var ) {
		$merc_hash_string .= '|';
		$merc_hash_string .= isset( $txn_rs[ $merc_hash_var ] ) ? $txn_rs[ $merc_hash_var ] : '';
	}

	$merc_hash = strtolower( hash( 'sha512', $merc_hash_string ) );

	/* The hash is valid */
	if ( $merc_hash === $txn_rs['hash'] ) {
		return true;
	} else {
		return false;
	}
}//end check_hash_after_transaction()


	/**
	 *  Process payment method.
	 *
	 * @var      Process payment method.
	 * @param    int   $order_id   link to the payubiz.
	 * @param    array $obj   will used in all payment methods.
	 * @return   string
	 */
function generate_payu_in_form( $order_id, $obj ) {
	global $woocommerce;
	$order = new WC_Order( $order_id );
	/*shipping state values */
	switch ( $order->shipping_state ) {
		case 'AP':$shipping_state = 'Andhra Pradesh';
			break;
		case 'AR':$shipping_state = 'Arunachal Pradesh';
			break;
		case 'AS':$shipping_state = 'Assam';
			break;
		case 'BR':$shipping_state = 'Bihar';
			break;
		case 'CT':$shipping_state = 'Chhattisgarh';
			break;

		case 'GA':$shipping_state = 'Goa';
			break;
		case 'GJ':$shipping_state = 'Gujarat';
			break;
		case 'HR':$shipping_state = 'Haryana';
			break;
		case 'HP':$shipping_state = 'Himachal Pradesh';
			break;
		case 'JK':$shipping_state = 'Jammu and Kashmir';
			break;

		case 'JH':$shipping_state = 'Jharkhand';
			break;
		case 'KA':$shipping_state = 'Karnataka';
			break;
		case 'KL':$shipping_state = 'Kerala';
			break;
		case 'MP':$shipping_state = 'Madhya Pradesh';
			break;
		case 'MH':$shipping_state = 'Maharashtra';
			break;

		case 'MN':$shipping_state = 'Manipur';
			break;
		case 'ML':$shipping_state = 'Meghalaya';
			break;
		case 'MZ':$shipping_state = 'Mizoram';
			break;
		case 'NL':$shipping_state = 'Nagaland';
			break;
		case 'OR':$shipping_state = 'Orissa';
			break;

		case 'PB':$shipping_state = 'Punjab';
			break;
		case 'RJ':$shipping_state = 'Rajasthan';
			break;
		case 'SK':$shipping_state = 'Sikkim';
			break;
		case 'TN':$shipping_state = 'Tamil Nadu';
			break;
		case 'TS':$shipping_state = 'Telangana';
			break;

		case 'TR':$shipping_state = 'Tripura';
			break;
		case 'UK':$shipping_state = 'Uttarakhand';
			break;
		case 'UP':$shipping_state = 'Uttar Pradesh';
			break;
		case 'WB':$shipping_state = 'West Bengal';
			break;
		case 'AN':$shipping_state = 'Andaman and Nicobar Islands';
			break;

		case 'CH':$shipping_state = 'Chandigarh';
			break;
		case 'DN':$shipping_state = 'Dadar and Nagar Haveli';
			break;
		case 'DD':$shipping_state = 'Daman and Diu';
			break;
		case 'DL':$shipping_state = 'Delhi';
			break;
		case 'LD':$shipping_state = 'Lakshadeep';
			break;
		case 'PY':$shipping_state = 'Pondicherry (Puducherry)';
			break;
	}//end switch()
	$payment_data = WC()->session->get( 'payment_data' );
	$productinfo = "Order $order_id";

	$hash_data['key']                = $obj->merchantid;
	$hash_data['txnid']              = substr( hash( 'sha256', mt_rand() . microtime() ), 0, 20 ); // Unique alphanumeric Transaction ID.
	$hash_data['amount']             = $order->order_total;
	$hash_data['productinfo']        = $productinfo;
	$hash_data['firstname']          = isset( $payment_data[5] )?( ! empty( $payment_data[5] )?$payment_data[5]:$order->billing_first_name):'';
	$hash_data['email']              = $order->billing_email;
	$hash_data['hash'] = calculate_hash_before_transaction( $hash_data, $obj );
	// PayU Args.
	$payu_in_args = array(

	// Merchant details.
	'key'                           => $hash_data['key'],
	'amount'                        => $order->order_total,
	'firstname'                     => $hash_data['firstname'],
	'email'                         => $order->billing_email,
	'phone'                         => $order->billing_phone,
	'productinfo'                   => $hash_data['productinfo'],
	'surl'                          => add_query_arg( array( 'payuindia_callback' => 1, 'orders_id' => $order_id, 'pg' => $_GET['pg'] ), $obj->get_return_url( $order ) ),
	'furl'                          => add_query_arg( array( 'payuindia_callback' => 1, 'orders_id' => $order_id, 'pg' => $_GET['pg'] ), $obj->get_return_url( $order ) ),
	'curl'                          => add_query_arg( array( 'payuindia_callback' => 1, 'orders_id' => $order_id, 'pg' => $_GET['pg'] ), $obj->get_return_url( $order ) ),
	'lastname'                      => $order->billing_last_name,
	'address1'                      => $order->billing_address_1,
	'address2'                      => $order->billing_address_2,
	'city'                          => $order->billing_city,
	'state'                         => $order->billing_state,
	'zipcode'                       => $order->billing_postcode,
	'country'                       => $order->billing_country,
	'pg'                            => $_GET['pg'],
	'bankcode'                      => isset( $payment_data[0] )?$payment_data[0]:'',
	'ccnum'                         => isset( $payment_data[1] )?$payment_data[1]:'',
	'ccname'                        => $hash_data['firstname'],
	'ccvv'                          => isset( $payment_data[4] )?$payment_data[4]:'',
	'ccexpmon'                      => isset( $payment_data[2] )?$payment_data[2]:'',
	'ccexpyr'                       => isset( $payment_data[3] )?$payment_data[3]:'',
	'service_provider'              => '',
	'shipping_firstname'            => $order->shipping_first_name,
	'shipping_lastname'             => $order->shipping_last_name,
	'shipping_address1'             => $order->shipping_address_1,
	'shipping_address2'             => $order->shipping_address_2,
	'shipping_city'                 => $order->shipping_city,
	'shipping_state'                => $shipping_state,
	'shipping_country'              => $order->shipping_country,
	'shipping_zipcode'              => $order->shipping_postcode,
	'shipping_phone'                => $order->billing_phone,

	);
	$payuform = '';

	foreach ( $payu_in_args as $key => $value ) {
		if ( $value ) {
			$payuform .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
		}
	}

	$payuform .= '<input type="hidden" name="txnid" value="' . $hash_data['txnid'] . '" />' . "\n";
	$payuform .= '<input type="hidden" name="hash" value="' . $hash_data['hash'] . '" />' . "\n";

	// Live url .
	$posturl = $obj->liveurl;
	if ( 'yes' === $obj->testmode ) {
		$posturl = $obj->testurl;
	}
	if ( isset( $_GET['payuindia_callback'] ) ) {
		unset( WC()->session->payment_data );
	}
	// The form.
	return '<form action="' . $posturl . '" method="POST" name="payform" id="payform">
        ' . $payuform . '
        <input type="submit" class="button" id="submit_payu_in_payment_form" value=""/>
        <script type="text/javascript">
          jQuery(function(){

            jQuery("body").block(
							{
								message: "<img src=\"' . $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />' . __( 'Thank you for your order. We are now redirecting you to PayU to make payment.', 'woothemes' ) . '",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
							        padding:        20,
							        textAlign:      "center",
							        color:          "#555",
							        border:         "3px solid #aaa",
							        backgroundColor:"#fff",
							        cursor:         "wait"
							    }
							});
						jQuery("#submit_payu_in_payment_form").click();

          });
        </script>
      </form>';
}//end generate_payu_in_form()


	/**
	 * Get the plugin URL
	 *
	 * @since 1.0.0
	 */
function plugin_url() {
	if ( isset( $obj->plugin_url ) ) {
		return $obj->plugin_url;
	}

	if ( is_ssl() ) {
		return $obj->plugin_url = str_replace( 'http://', 'https://', WP_PLUGIN_URL ) . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
	} else {
		return $obj->plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) );
	}
} // End plugin_url()

	/**
	 * Woocoomerce validate card.
	 *
	 * @var    Validate all the fields
	 * @param  string $number will pass the current order id.
	 * @param  string $type will pass the current order id.
	 * @return $number,$type
	 */
function valid_card( $number, $type ) {
	return valid_luhn( $number, $type );
}

	/**
	 * Woocoomerce validate luhn formula.
	 *
	 * @var    Validate all the fields
	 * @param  string $number will pass the current order id.
	 * @param  string $type will pass the current order id.
	 * @return $number,$type
	 */
function valid_luhn( $number, $type ) {
	return luhn_check( $number );
}

	/**
	 * Woocoomerce validate check.
	 *
	 * @var    Validate all the fields
	 * @param  string $number will pass the current order id.
	 * @return $number,$type
	 */
function luhn_check( $number ) {
	$checksum = 0;
	$range_val = (2 -(strlen( $number ) % 2));
	$strlen = strlen( $number );
	for ( $i = $range_val; $i <= $strlen; $i += 2 ) {
		$checksum += (int) ($number{$i -1});
	}
	// Analyze odd digits in even length strings or even digits in odd length strings.
	$range_val1 = (strlen( $number ) % 2) + 1;
	for ( $i = $range_val1; $i < $strlen; $i += 2 ) {
		$digit = (int) ($number{$i -1}) * 2;
		if ( $digit < 10 ) {
			$checksum += $digit;
		} else {
			$checksum += ($digit -9);
		}
	}
	$chk = $checksum % 10;
	if ( 0 === $chk ) {
		return true;
	} else {
		return false;
	}
}
