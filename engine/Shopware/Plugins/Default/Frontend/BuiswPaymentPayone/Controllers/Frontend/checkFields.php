<?php
/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id$
  # @copyright:   found in /lic/copyright.txt
  #
  ##############################################################################
 */

class sPaymentMean{
		var $sSYSTEM;

		function sInit(){
			switch($this->sSYSTEM->_POST['payonesubpay']) {
				case "lastschrift":
					if($this->sSYSTEM->_POST["payonesubpay_directdebit_bankcode"] == "")
						$sErrorFlag["payonesubpay_directdebit_bankcode"] = true;

					if($this->sSYSTEM->_POST["payonesubpay_directdebit_accountnumber"] == "")
						$sErrorFlag["payonesubpay_directdebit_accountnumber"] = true;

					if($this->sSYSTEM->_POST["payonesubpay_directdebit_depositor"] == "")
						$sErrorFlag["payonesubpay_directdebit_depositor"] = true;

					break;
				case "onlinepay":
					if($this->sSYSTEM->_POST['payonesubpay_onlinepay_provider'] != "EPS" && $this->sSYSTEM->_POST['payonesubpay_onlinepay_provider'] != "IDL")
						if($this->sSYSTEM->_POST["payonesubpay_onlinepay_bankcode"] == "")
							$sErrorFlag["payonesubpay_onlinepay_bankcode"] = true;

					if($this->sSYSTEM->_POST["payonesubpay_onlinepay_accountnumber"] == "")
							$sErrorFlag["payonesubpay_onlinepay_accountnumber"] = true;

					break;
				case "creditcard":
					if($this->sSYSTEM->_POST["payonesubpay_creditcard_pseudonumber"] == "")
						if($this->sSYSTEM->_POST["payonesubpay_creditcard_checkdigit"] == "")
							$sErrorFlag["payonesubpay_creditcard_checkdigit"] = true;

					if($this->sSYSTEM->_POST["payonesubpay_creditcard_number"] == "")
							$sErrorFlag["payonesubpay_creditcard_number"] = true;

					if($this->sSYSTEM->_POST["payonesubpay_creditcard_depositor"] == "")
							$sErrorFlag["payonesubpay_creditcard_depositor"] = true;

					break;
				default:
					break;
			}

			if (count($sErrorFlag)) $error = true;

			if ($error){
				$sErrorMessages[] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorBillingAdress'];

				return array("sErrorFlag"=>$sErrorFlag,"sErrorMessages"=>$sErrorMessages);
			} else {
				return true;
			}
		}
	}
?>
