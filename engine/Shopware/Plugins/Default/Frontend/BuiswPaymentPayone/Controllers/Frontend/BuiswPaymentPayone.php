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

class Shopware_Controllers_Frontend_BuiswPaymentPayone extends Enlight_Controller_Action {

	public function logClientAPICallAction($setNoRender) {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

		echo 'ok'; // no logging here anymore
	}

	public function appendHashAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

		$cardType = $this->Request()->getParam('cardtype');

		$signparams = array ();
		$signparams['mid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('merchant_id'); // $merchentId;
		$signparams['aid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('sub_account_id');
		$signparams['portalid'] = Shopware_Controllers_Backend_BuiswPaymentPayone::Config('portal_id'); //$portalId;
		$signparams['mode'] = $this->getModeFromCardType($cardType);
		$signparams['request'] = 'creditcardcheck';
		$signparams['responsetype'] = 'JSON';
		$signparams['storecarddata'] = 'yes';


		ksort($signparams);
		$hash = md5(join('', $signparams) . Shopware_Controllers_Backend_BuiswPaymentPayone::Config('portal_key'));



		$user = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();
        $languangeSql = 'SELECT `locale` FROM s_core_locales WHERE id = ?' ;
        $userLanguage = Shopware()->Db()->fetchOne($languangeSql,array((int) $user['additional']['user']['language']));
        $tmpLang = explode('_', $userLanguage);
        $userLanguage = $tmpLang[0];
        
		$signparams['language'] = $userLanguage;

		$signparams['hash'] = $hash;

		echo json_encode($signparams);
	}

	protected function getModeFromCardType($t) {
		// $t is Shopware_Controllers_Backend_BuiswPayOne::$creditcards [value]
		foreach (Shopware_Controllers_Backend_BuiswPaymentPayone::$creditcards as $c) {
			if ($c['value'] == $t) {
				$key = $c['key'] . '_mode';
				return Shopware_Controllers_Backend_BuiswPaymentPayone::Config($key);
			}
		}
		return 'test';
	}

}

?>
