<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:	  modifier
 * Name:	  phone_format
 * Purpose:	  Format a phone number according to the country's format
 * @author	  Philippe Jausions/11abacus.com (Philippe.Jausions-at-11abacus.com) (Lead)
 * @author	  Mark Thompson/burodefunk.com (mark-at-burodefunk.com)
 * @copyright (c) 2003-2006 by Philippe Jausions/11abacus.com
 * @date	  2003-11-04
 * @version	  0.1.1 (2006-02-28) - MT
 *
 * Known issues:
 *	- Phone extensions information could be handled better
 *		 (by not modifying them at all)
 *
 * Change history:
 *	- v0.1: First release (PhJ - 2003-11-04)
 *	- v0.1.1: Added UK support (MT - 2006-02-28)
 *
 * Supported country phone formats:
 *	- CA: Canada   (v0.1) 
 *	- FR: France   (v0.1)
 *	- AI: Anguilla (v0.1)
 *	- AG: Antigua/Barbuda (v0.1)
 *	- BS: Bahamas  (v0.1)
 *	- BB: Barbados (v0.1)
 *	- BM: Bermuda  (v0.1)
 *	- CA: Canada   (v0.1)
 *	- KY: Cayman Islands (v0.1)
 *	- DM: Dominica (v0.1)
 *	- DO: Dominican Republic (v0.1)
 *	- GD: Grenada	 (v0.1)
 *	- GU: Guam		 (v0.1)
 *	- JM: Jamaica	 (v0.1)
 *	- MS: Montserrat (v0.1)
 *	- MP: Northern Mariana Islands (v0.1)
 *	- PR: Puerto Rico (v0.1)
 *	- KN: Saint Kitts and Nevis (v0.1)
 *	- LC: Saint Lucia (v0.1)
 *	- VC: Saint Vincent and the Grenadines (v0.1)
 *	- TT: Trinidad and Tobago	   (v0.1)
 *	- TC: Turks and Caicos Islands (v0.1)
 *	- US: United States of America (v0.1)
 *	- VG: Virgin Islands (British) (v0.1)
 *	- VI: Virgin Islands (U.S.)	   (v0.1)
 *	- UK: United Kingdom (v0.1)
 * -------------------------------------------------------------
 */
function smarty_modifier_phone_format($sPhone, $sCountry = 'US', $bInternationalFormat = false)
{
	if (empty($sPhone)
	|| !trim($sPhone)) {
		return $sPhone;
	}

	// Supported list of country phone format
	// Country code => International phone code
	$aCountries = array(
		'CA' => '1',						// Canada
		'FR' => '33',						// France
		'AI' => '1-264',					// Anguilla
		'AG' => '1-268',					// Antigua/Barbuda
		'BS' => '1-242',					// Bahamas
		'BB' => '1-246',					// Barbados
		'BM' => '1-441',					// Bermuda
		'CA' => '1',						// Canada
		'KY' => '1-345',					// Cayman Islands
		'DM' => '1-767',					// Dominica
		'DO' => '1-809',					// Dominican Republic
		'GD' => '1-473',					// Grenada
		'GU' => '1-671',					// Guam
		'JM' => '1-876',					// Jamaica
		'MS' => '1-664',					// Montserrat
		'MP' => '1-670',					// Northern Mariana Islands
		'PR' => array('1-787', '1-939'),	// Puerto Rico
		'KN' => '1-869',					// Saint Kitts and Nevis
		'LC' => '1-758',					// Saint Lucia
		'VC' => '1-784',					// Saint Vincent and the Grenadines
		'TT' => '1-868',					// Trinidad and Tobago
		'TC' => '1-649',					// Turks and Caicos Islands
		'US' => '1',						// United States of America
		'VG' => '1-284',					// Virgin Islands (British)
		'VI' => '1-340',					// Virgin Islands (U.S.)
		'UK' => '44'						// United Kingdom
	);

	if (!isset($aCountries[$sCountry])) {
		return $sPhone;
	}

	// Get rid of parenthesis, dashes, plus and dot signs,
	// then remove any spaces before numbers,
	// and remove duplicate "white spaces".
	$sFormatted = str_replace(array('+', '(', ')', '-', '.', '/'), '', trim($sPhone));
	$sFormatted = preg_replace(array('/\s+([0-9])/', '/\s+/'), array('\1', ' '), $sFormatted);
	list($sFormatted, $sExt) = explode(' ', $sFormatted, 2);

	$iLen = strlen($sFormatted);
	$iCountryCode = $aCountries[$sCountry];

	// Deal with the primary phone number part based on the country
	switch ($sCountry) {
	/* case 'CA': See 'US' */

	case 'FR':
		// International format: +33 (0)1 23 45 67 89
		// National format: (0)1 23 45 67 89
		// Toll number format: 0800 12 34 56
		//						08 36 12 34 56
		switch ($iLen) {
			case 10:
				// Numeros Vert, Azur & Indigo
				$aNumerosSpeciaux = array('0800', '0801', '0802', '0803');
				$sIndicatif = substr($sFormatted, 0, 4);
				if (in_array($sIndicatif, $aNumerosSpeciaux)) {
				// Appels internationaux impossible (?)
					$bInternationalFormat = false;
					$sFormatted = $sIndicatif . ' ' . substr($sFormatted, 4, 2) . ' ' . substr($sFormatted, 6, 2) . ' ' . substr($sFormatted, -2);

				} elseif ($sIndicatif == '0836' && !$bInternationalFormat) {
				// Numeros Kiosque sont traites normalement a
				// l'international, mais en France the zero n'est pas mis
				// entre parentheses
					$sFormatted = substr($sFormatted, 0, 2) . ' ' . substr($sFormatted, 2, 2) . ' ' .
					substr($sFormatted, 4, 2) . ' ' . substr($sFormatted, 6, 2) . ' ' . substr($sFormatted, -2);

				} else {
					$sFormatted = '(' . substr($sFormatted, 0, 1) . ')' . substr($sFormatted, 1, 1) . ' ' .
					substr($sFormatted, 2, 2) . ' ' . substr($sFormatted, 4, 2) . ' ' . substr($sFormatted, 6, 2) . ' ' . substr($sFormatted, -2);
				}
				break;

			case 9:
				$sFormatted = '(0)' . substr($sFormatted, 0, 1) . ' ' . substr($sFormatted, 1, 2) . ' ' .
				substr($sFormatted, 3, 2) . ' ' . substr($sFormatted, 5, 2) . ' ' . substr($sFormatted, -2);

			default:
				// Any other unrecognized phone numbers are return as
				// they were passed.
				return $sPhone;
		}
		break;
		 // End [CASE] FR / France

		// The following countries are folded into the US numbering plan
		case 'AI':  // Anguilla
		case 'AG':  // Antigua/Barbuda
		case 'BS':  // Bahamas
		case 'BB':  // Barbados
		case 'BM':  // Bermuda
		case 'CA':  // Canada
		case 'KY':  // Cayman Islands
		case 'DM':  // Dominica
		case 'DO':  // Dominican Republic
		case 'GD':  // Grenada
		case 'GU':  // Guam
		case 'JM':  // Jamaica
		case 'MS':  // Montserrat
		case 'MP':  // Northern Mariana Islands
		case 'PR':  // Puerto Rico
		case 'KN':  // Saint Kitts and Nevis
		case 'LC':  // Saint Lucia
		case 'VC':  // Saint Vincent and the Grenadines
		case 'TT':  // Trinidad and Tobago
		case 'TC':  // Turks and Caicos Islands
		case 'VG':  // Virgin Islands (British)
		case 'VI':  // Virgin Islands (U.S.)

		case 'US':  // United States of America
		// National format: (123) 456-7890
		// International format: +1 (1) 123-456-7890
		// Toll number format: 1-800-123-4567
		if ($iLen == 11) {
			$sFormatted = substr($sFormatted, 1);
			$iLen = 10;
		}
		 switch ($iLen) {
			case 7:
				// Local number
				// Note: International number format cannot
				//be used for US and Canada
				$sFormatted = substr($sFormatted, 0, 3) . '-' . substr($sFormatted, -4);
				$bInternationalFormat &= ($sCountry != 'US' && $sCountry != 'CA');
				break;

			case 10:
				// Full number
				// Toll phone area codes
				$aTollAreaCodes = array(800, 866, 877, 888, 855, 844, 833, 822, 900, 880, 881, 882, 883);
				$sAreaCode = substr($sFormatted, 0, 3);

				// Countries using the US phone numbering system
				// use the code area as country code, so we "reset"
				// the country code to "1" for phone numbers already including
				// the area code.
				$iCountryCode = '1';

				if (in_array((int) $sAreaCode, $aTollAreaCodes)) {
				// Note: International format cannot be supported here
				//   for toll numbers.
					$sFormatted = '1-' . $sAreaCode . '-' . substr($sFormatted, 3, 3) . '-' . substr($sFormatted, -4);
					$bInternationalFormat = false;

				} elseif ($bInternationalFormat) {
					$sFormatted = '(1) ' . $sAreaCode . '-' . substr($sFormatted, 3, 3) . '-' . substr($sFormatted, -4);

				} else {
					$sFormatted = '(' . $sAreaCode . ') ' . substr($sFormatted, 3, 3) . '-' . substr($sFormatted, -4);
				}
				break;

			default:
				// Any other unrecognized phone numbers are return as
				// they were passed.
				return $sPhone;

		} // End [SWITCH] on length of number
		break;
		// End [CASE] US & Canada (CA)
		
		case 'UK':	// United Kingdom
			// National format: (020) 1234 5678
			// International format: +44 (20) 1234-5678
			// Toll number format: 0800-123-456
			$iLen = strlen($sPhone);
			
			switch ($iLen) {
				case 8:
					// Local number
					// Note: International number format cannot
					// be used for UK
					$sFormatted = substr($sFormatted, 0, 3) . ' ' . substr($sFormatted, -4);
					$bInternationalFormat &= $sCountry != 'UK';
					break;

				case (10 || 11):
					// Full number
					// Toll phone area codes
					$aTollAreaCodes = array(800, 844, 845, 870, 871, 90, 91);

					if (in_array((int) substr($sFormatted, 1, 3), $aTollAreaCodes)) {
					// Note: International format cannot be supported here
					//   for toll numbers.
						$sFormatted = substr($sFormatted, 0, 4) . '-' . substr($sFormatted, 4, 3) . '-' . substr($sFormatted, 7);
						$bInternationalFormat = false;

					} elseif ($bInternationalFormat) {
						$sFormatted = '(' . substr($sFormatted, 1, 2) . ') '. substr($sFormatted, 3, 4) . '-' . substr($sFormatted, 7);

					} else {
						$sFormatted = substr($sFormatted, 0, 3) . ' ' . substr($sFormatted, 3, 4) . ' ' . substr($sFormatted, 7);
					}
					break;

				default:
					// Any other unrecognized phone numbers are return as
					// they were passed.
					return $sPhone;

			} // End [SWITCH] on length of number
			break;
			// End [CASE] UK

	} // End [SWITCH] on country code

	// Prepend with the country code and append extension if needed.
	return (($bInternationalFormat) ? '+' . $iCountryCode . ' ' : '') . $sFormatted . (($sExt) ? ' ' . $sExt : '');
}
?>
