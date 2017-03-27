<?php
/**
 * WC_PAYU_INDIA_UTILITY Class that are used to add functionality.
 *
 * @category Class
 * @package  WC_PAYU_INDIA_UTILITY
 * @author   Tailored Solutions Pvt. Ltd.
 * @license  http://tasolglobal.com
 * @link     http://tasolglobal.com
 */
class WC_PAYU_INDIA_UTILITY {

	/**
	 * Payment method names.
	 *
	 * @var array
	 * @param array $acceptable_cards array of caard methods.
	 * @return array
	 */
	public static $acceptable_cards = array(
	'Visa',
	'MasterCard',
	'DINR',
	'Amex',
	);

	/**
	 * Valid card number
	 *
	 * @var string
	 * @param array $to_check card number validation returned.
	 * @return $to_check
	 */
	static function is_valid_card_number( $to_check ) {
		if ( ! is_numeric( $to_check ) ) {
			return false;
		}

		$number = preg_replace( '/[^0-9]+/', '', $to_check );
		$strlen = strlen( $number );
		$sum = 0;

		if ( $strlen < 13 ) {
			return false;
		}

		for ( $i = 0; $i < $strlen; $i++ ) {
			$digit = substr( $number, $strlen - $i - 1, 1 );
			if ( 1 === $i % 2 ) {
				$sub_total = $digit * 2;
				if ( $sub_total > 9 ) {
					$sub_total = 1 + ($sub_total - 10);
				}
			} else {
				$sub_total = $digit;
			}
			$sum += $sub_total;
		}

		if ( 0 === $sum > 0 and $sum % 10 ) {
			return true;
		}

		return false;
	}

	/**
	 * Valid card type.
	 *
	 * @var string
	 * @param array $to_check  cardtype validation returned.
	 * @return $to_check
	 */
	static function is_valid_card_type( $to_check ) {
		return $to_check and in_array( $to_check, self::$acceptable_cards, true );
	}

	/**
	 * Valid card expiry date.
	 *
	 * @var string
	 * @param string $month  month validation returned.
	 * @param string $year  year validation returned.
	 * @return $month,$year
	 */
	static function is_valid_expiry( $month, $year ) {
		$now = time();
		$this_year = (int) date( 'Y', $now );
		$this_month = (int) date( 'm', $now );

		if ( is_numeric( $year ) && is_numeric( $month ) ) {
			$this_date = mktime( 0, 0, 0, $this_month, 1, $this_year );
			$expire_date = mktime( 0, 0, 0, $month, 1, $year );

			return $this_date <= $expire_date;
		}

		return false;
	}

	/**
	 * Valid card number.
	 *
	 * @var string
	 * @param string $to_check  cvv number validation returned.
	 * @return $to_check
	 */
	static function is_valid_cvv_number( $to_check ) {
		$length = strlen( $to_check );

		return is_numeric( $to_check ) and $length > 2 and $length < 5;
	}
}
