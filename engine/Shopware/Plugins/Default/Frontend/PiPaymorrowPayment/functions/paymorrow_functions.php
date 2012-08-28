<?php
/**
 * get language pack
 *
 * @param    Enlight_Event_EventArgs $args    Arguments
 */
function piPaymorrowGetLanguage($args)
{
    
        $language = 'de_DE';
    
        $filename = dirname(__FILE__) . '/../local/Paymorrow_lang_' . $language . '.php';;
    
        if (file_exists($filename)) {
            require $filename;
        }
        else {
            require dirname(__FILE__) . '/../local/Paymorrow_lang_de_DE.php'; 
        };
    
	return $pi_Paymorrow_lang;
}

/**
 * Calculates Userage
 *
 * @param    Int $day      Birthday
 * @param    Int $month    Birthmonth
 * @param    Int $year     Birthyear
 *
 * @return      INT $calc_year  Age in years
 */
function piPaymorrowAgeCalculator($day, $month, $year)
{

	if (!checkdate($month, $day, $year)) return 0;
	$currentDay = date("d");
	$currentMonth = date("m");
	$currentYear = date("Y");
	$calculateYear = $currentYear - $year;
	if ($month > $currentMonth) return $calculateYear - 1;
	elseif ($month == $currentMonth && $day > $currentDay) return $calculateYear - 1;
	else return $calculateYear;
}

/**
 * Saves userdata from payment field
 *
 * @param    Enlight_Event_EventArgs $piPaymorrowArgs    Arguments
 */
function piPaymorrowSaveNewUserdata(Enlight_Event_EventArgs $piPaymorrowArgs)
{
	$piPaymorrowView = $piPaymorrowArgs->getSubject()->View();
	$piPaymorrowResponse = $piPaymorrowArgs->getSubject()->Response();
	$piPaymorrowGetPost = $piPaymorrowArgs->getSubject()->Request()->getPost();
        $piPaymorrowUserdata = array();
	$piPaymorrowUserdata = $piPaymorrowArgs->getSubject()->View()->sUserData;
        $piPaymorrowInvoiceId = "";
        $piPaymorrowBirthday = "";
	if ($piPaymorrowGetPost['pi_Paymorrow_saveBirthday']) {
            $piPaymorrowInvoiceId = piPaymorrowGetInvoicePaymentId();
	}
	else {
            $piPaymorrowInvoiceId = piPaymorrowGetRatePaymentId();
	}
	if ($piPaymorrowGetPost['register']['personal']['phone']) {
            $sql = "UPDATE s_user_billingaddress SET phone= ? WHERE ID= ?";
            Shopware()->Db()->query($sql, array($piPaymorrowGetPost['register']['personal']['phone'], (int)$piPaymorrowUserdata['billingaddress']['id']));
	}
	if ($piPaymorrowGetPost['register']['personal']['birthday_rate']) {
            $piPaymorrowBirthday = $piPaymorrowGetPost['register']['personal']['birthyear_rate'] . "-"
                    . $piPaymorrowGetPost['register']['personal']['birthmonth_rate'] . "-"
                    . $piPaymorrowGetPost['register']['personal']['birthday_rate'];
	}
	else {
            $piPaymorrowBirthday = $piPaymorrowGetPost['register']['personal']['birthyear'] . "-"
                    . $piPaymorrowGetPost['register']['personal']['birthmonth'] . "-"
                    . $piPaymorrowGetPost['register']['personal']['birthday'];
	}
	if ($piPaymorrowUserdata["billingaddress"]["birthday"] == "0000-00-00") {
            $sql = "UPDATE s_user_billingaddress SET birthday= ? WHERE id= ?";
		Shopware()->Db()->query($sql, array($piPaymorrowBirthday, (int)$piPaymorrowUserdata['billingaddress']['id']));
		$piPaymorrowView->pi_Paymorrow_birthdayflag = true;
	}
        $sql = "SELECT birthday FROM s_user_billingaddress WHERE id= ?";
	$piPaymorrowNewBirthday = Shopware()->Db()->fetchOne($sql, array((int)$piPaymorrowUserdata['billingaddress']['id']));
        $sql = "SELECT phone FROM s_user_billingaddress WHERE id= ?";
	$piPaymorrowNewphone = Shopware()->Db()->fetchOne($sql, array((int)$piPaymorrowUserdata['billingaddress']['id']));
	if ($piPaymorrowNewBirthday != "0000-00-00" && $piPaymorrowNewphone && !$piPaymorrowUserdata["billingaddress"]["company"] 
            && !$piPaymorrowUserdata["shippingaddress"]["company"]
        ) {
            $sql = "UPDATE s_user SET paymentID = ? WHERE id = ?";
            Shopware()->Db()->query($sql, array((int)$piPaymorrowInvoiceId,(int)$piPaymorrowUserdata['billingaddress']['userID']));
	}
	else {
		$piPaymorrowHeader = $piPaymorrowResponse->getHeaders();
		$piPaymorrowUrl = $piPaymorrowHeader[1][value];
		$piPaymorrowNewUrl = str_replace("/sViewport,account/success,payment", "/sViewport,account/sAction,payment", $piPaymorrowUrl);
		$piPaymorrowNewUrl = str_replace("/sViewport,checkout/success,payment", "/sViewport,account/sAction,payment/sTarget,checkout", $piPaymorrowNewUrl);
		$piPaymorrowResponse->setHeader('Location', $piPaymorrowNewUrl, 2);
	}
}

/**
 * Checks userdata and sets errormessage if neccessary
 *
 * @param    Array    $piPaymorrowUserdata     Current Userdata
 * @param    Object   $piPaymorrowView         Current View
 *
 */
function piPaymorrowCheckUserdata($piPaymorrowUserdata, $piPaymorrowView)
{
	if ($piPaymorrowUserdata["billingaddress"]["birthday"] == "0000-00-00") {
		if (!$piPaymorrowUserdata["billingaddress"]["phone"]) {
			$piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang['warning']['both'];
		}
		else {
			$piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang['warning']['birthday'];
		}
	}
	else {
		if (!$piPaymorrowUserdata["billingaddress"]["phone"]) {
			$piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang['warning']['phone'];
		}
	}
	if (Shopware()->Session()->pi_Paymorrow_no_Paymorrow) {
		$piPaymorrowView->pi_Paymorrow_paymentWarningText = $piPaymorrowView->pi_Paymorrow_lang['warning']['notaccepted'];
		$piPaymorrowView->pi_Paymorrow_no_Paymorrow = true;
	}
}

/**
 * Sets template vars
 *
 * @param    Array       $piPaymorrowUserdata          Current userdata
 * @param    Object      $piPaymorrowConfig            Paymorrow config
 * @param    Object      $piPaymorrowView              Current view
 * @param    Object      $piPaymorrowRequest           Current request
 *
 */
function piPaymorrowSetTemplateVars($piPaymorrowView, $piPaymorrowRequest, $piPaymorrowConfig, $piPaymorrowUserdata)
{
	if ($piPaymorrowUserdata["additional"]["payment"]["name"] == "PaymorrowInvoice") {
		$piPaymorrowView->pi_Paymorrow_basket_min = $piPaymorrowConfig->basket_min;
		$piPaymorrowView->pi_Paymorrow_basket_max = $piPaymorrowConfig->basket_max;
	}
	else {
		$piPaymorrowView->pi_Paymorrow_basket_min = $piPaymorrowConfig->basket_min_rate;
		$piPaymorrowView->pi_Paymorrow_basket_max = $piPaymorrowConfig->basket_max_rate;
	}
	$piPaymorrowView->pi_Paymorrow_actions = $piPaymorrowRequest->getActionName();
}

/**
 * get Surcharge from current payment method
 *
 * @params  String $payment     Payment method
 * @params  String $basket      Current basket
 *
 * @return  float  $surcharge   Surcharge for payment method
 */
function piPaymorrowGetSurcharge($payment, $basket)
{
        $sql = "SELECT surcharge FROM s_core_paymentmeans  WHERE name = ?";
	$surcharge = Shopware()->Db()->fetchOne($sql, array($payment));
	if (!$surcharge) {
            $sql = "SELECT debit_percent FROM s_core_paymentmeans WHERE name = ?";
            $surcharge = Shopware()->Db()->fetchOne($sql, array($payment));
            if ($surcharge) {
                    $surcharge = ($basket['totalAmount'] / 100) * $surcharge;
            }
	}
	return number_format($surcharge, 2, ',', '.');
	;
}

/**
* Gets ID of Paymorrow invoice payment
*
* @return Int $piPaymorrowInvoiceId  ID
*/
function piPaymorrowGetInvoicePaymentId() {
    $sql = "SELECT id FROM s_core_paymentmeans WHERE name LIKE 'PaymorrowInvoice'";
    $piPaymorrowInvoiceId = Shopware()->Db()->fetchOne($sql);
    return $piPaymorrowInvoiceId;
}

/**
* Gets ID of Paymorrow rate payment
*
* @return Int $piPaymorrowRateId        ID
*/
function piPaymorrowGetRatePaymentId() {
    $sql = "SELECT id FROM s_core_paymentmeans WHERE name LIKE 'PaymorrowRate'";
    $piPaymorrowRateId = Shopware()->Db()->fetchOne($sql);
    return $piPaymorrowRateId;
}


/**
* Gets ID of Paymorrow plugin
*
* @return Int $piPaymorrowPluginId         Plugin ID
*/
function piPaymorrowGetPluginId() {
    $sql = "SELECT id FROM s_core_plugins WHERE name LIKE 'PiPaymorrowPayment'";
    $piPaymorrowPluginId = Shopware()->Db()->fetchOne($sql);
    return $piPaymorrowPluginId;
}