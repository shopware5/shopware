<?php
class Shopware_Controllers_Backend_HeidelActions extends Enlight_Controller_Action
{
  /*{{{Variables*/
  var $curl_response   = '';
  var $curl_error      = '';

  var $live_url_old = 'https://ctpe.net/frontend/payment.prc';
  var $demo_url_old = 'https://test.ctpe.net/frontend/payment.prc';
  var $live_url_new = 'https://heidelpay.hpcgw.net/sgw/gtw';
  var $demo_url_new = 'https://test-heidelpay.hpcgw.net/sgw/gtw';
  var $payproxyURL = '';

  var $availablePayments = array('CC','DD','DC','VA','OT','IV','PP','UA');
  var $pageURL = '';
  var $actualPaymethod = 'CC';
  /*}}}*/

	public function init(){/*{{{*/
		$this->View()->addTemplateDir(dirname(__FILE__)."/Views/");
	}/*}}}*/

  public function indexAction(){/*{{{*/
    if (  $this->Request()->isSecure() ) {
      $protokoll = "https://";
    } else {
      $protokoll = "http://";
    };
    // Get default shop 
    $sql      = 'SELECT base_path FROM `s_core_shops`WHERE `default` = 1 LIMIT 1';
    $basepath = Shopware()->Db()->fetchOne($sql);
    $this->payproxyURL = $protokoll . $this->Request()->getHttpHost() . $basepath ."/engine/Shopware/Plugins/Default/Backend/HeidelActions/payproxy/";
      
    define('TRANSACTION_CHANNEL', $this->FrontendConfig()->HEIDELPAY_CC_CHANNEL);
    define('SECURITY_SENDER', $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER);
    define('USER_LOGIN', $this->FrontendConfig()->HEIDELPAY_USER_LOGIN);
    define('USER_PWD', $this->FrontendConfig()->HEIDELPAY_USER_PW);
    define('TRANSACTION_MODE', $this->FrontendConfig()->HEIDELPAY_TRANSACTION_MODE);


    // add new channels to database
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_CC_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_DC_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_DD_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_PP_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_IV_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_SUE_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_GIR_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_PAY_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_IDE_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->addChannelToSettingDB($this->FrontendConfig()->HEIDELPAY_EPS_CHANNEL , $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER );
    $this->live_url_new = $this->FrontendConfig()->HEIDELPAY_LIVE_URL;
    $this->demo_url_new = $this->FrontendConfig()->HEIDELPAY_TEST_URL;
    
    $this->demo_url_new = $this->payproxyURL.'login.php'; // Demo Login

    $params = array();
    $params['FRONTEND.LANGUAGE']      = Shopware()->Locale()->getLanguage();
    $params['SECURITY.SENDER']        = SECURITY_SENDER;
    $params['USER.LOGIN']             = USER_LOGIN;
    $params['USER.PWD']               = USER_PWD;
    $params['TRANSACTION.CHANNEL']    = TRANSACTION_CHANNEL;
    $params['TRANSACTION.MODE']       = TRANSACTION_MODE;
    // Wie auch immer man die richtige URL rausbekommt im Backend...
    // �bergabe CSS aus Shop an PayProxy f�r eigenes Design -> Siehe style.tpl
    /*
    $params['FRONTEND_CSS_PATH']      = 'http://'.$_SERVER['HTTP_HOST'].$this->Front()->Router()->assemble(array(
		  'forceSecure' => 1,
      'action' => 'style',
    ));
     */
    $user = Shopware()->Auth()->getIdentity();
    /** @var $locale \Shopware\Models\Shop\Locale */
     $locId = $user->locale;
      

    if (empty($params['SECURITY.SENDER'])){
      $this->View()->loadTemplate("backend/plugins/HeidelActions/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingSender', $locId);
      return false;
    }
    if (empty($params['TRANSACTION.CHANNEL'])){
      $this->View()->loadTemplate("backend/plugins/HeidelActions/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingChannel', $locId);
      return false;
    }
    if (empty($params['USER.LOGIN'])){
      $this->View()->loadTemplate("backend/plugins/HeidelActions/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingUser', $locId);
      return false;
    }
    if (empty($params['USER.PWD'])){
      $this->View()->loadTemplate("backend/plugins/HeidelActions/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingPwd', $locId);
      return false;
    }

    $url = $this->demo_url_old;
      
    if (substr(strtoupper(SECURITY_SENDER),0,3) == '31H') $url = $this->demo_url_new;
    if (constant('TRANSACTION_MODE') == 'LIVE'){
      $url = $this->live_url_old;
      if (substr(strtoupper(SECURITY_SENDER),0,3) == '31H') $url = $this->live_url_new;
    }
    
    $token = $this->doRequest($url, $params, false);


    #$this->response = 1;
    #$this->request = 1;

    $url = $this->payproxyURL.'panel.php?token='.$token.'&show=settings';
    #echo $url;
    
    $this->View()->loadTemplate("backend/plugins/HeidelActions/index.tpl");
    #$this->View()->assign('HPUrl', $url);
    $this->View()->HPUrl = $url;
  }/*}}}*/

	public function skeletonAction(){/*{{{*/
		$this->View()->loadTemplate("backend/plugins/HeidelActions/skeleton.tpl");
  }/*}}}*/

  public function FrontendConfig()/*{{{*/
	{
		return Shopware()->Plugins()->Frontend()->HeidelPayment()->Config();
  }/*}}}*/

  public function BackendConfig()/*{{{*/
	{
		return Shopware()->Plugins()->Frontend()->HeidelActions()->Config();
	}/*}}}*/

  /**
	 * Style action method
	 */
	public function styleAction()/*{{{*/
  {
    $this->View()->loadTemplate("backend/plugins/HeidelActions/style.tpl");
    $this->Response()->setHeader('Content-Type', 'text/css');
  }/*}}}*/

  /**
	 * Do request method
	 *
	 * @param string $url
	 * @param array $params
	 * @return array
	 */
	public function doRequest($url, $params=array(), $doParse = true)/*{{{*/
	{
		$client = new Zend_Http_Client($url, array(
			'useragent' => 'Shopware/' . Shopware()->Config()->Version
		));
		$client->setParameterPost($params);
		if (extension_loaded('curl')) {
			$adapter = new Zend_Http_Client_Adapter_Curl();
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
			$client->setAdapter($adapter);
		}
		$respone = $client->request('POST');
		$respone = $respone->getBody();
		//$respone = file_get_contents($url . '?' . http_build_query($params, '', '&'));

		$result = null;
    //$respone = str_replace('&#37;2B' , ' ', $respone);
    if ($doParse){
      parse_str($respone, $result);
    } else {
      $result = $respone;
    }
		return $result;
	}/*}}}*/

  public function getSnippet($name, $localeId = null, $ns = 'backend/HeidelActions', $shopId = 1)/*{{{*/
  {
    $sql = 'SELECT `value` 
            FROM `s_core_snippets` 
            WHERE `namespace` = "'.$ns.'" 
            AND `shopID` = "'.$shopId.'" 
            AND `localeID` = "'.$localeId.'" 
            AND `name` = "'.$name.'" ';
    $data = current(Shopware()->Db()->fetchAll($sql));
    return $data['value'];
  }/*}}}*/

  public function addChannelToSettingDB($Channel, $Sender) {
	if (!empty($Channel)) {
		$sql = 'SELECT TRANSACTION_CHANNEL, SECURITY_SENDER  FROM  s_plugin_heidelpay_config WHERE TRANSACTION_CHANNEL = "'.$Channel.'";';
	 	$data = Shopware()->Db()->fetchAll($sql);
		if (empty($data[0]['TRANSACTION_CHANNEL'])) {
	 		$insert = 'INSERT s_plugin_heidelpay_config SET SECURITY_SENDER="'.$Sender.'" , TRANSACTION_CHANNEL="'.$Channel.'" ;';
			Shopware()->Db()->query($insert);
		}
	 }
  }
}
?>
