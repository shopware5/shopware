<?php
class Shopware_Controllers_Backend_HeidelBooking extends Enlight_Controller_Action
{

  /*{{{Variables*/
  var $curl_response   = '';
  var $curl_error      = '';

  var $live_url_old = 'https://ctpe.net/frontend/payment.prc';
  var $demo_url_old = 'https://test.ctpe.net/frontend/payment.prc';
  var $live_url_new = 'https://heidelpay.hpcgw.net/sgw/gtw';
  var $demo_url_new = 'https://test-heidelpay.hpcgw.net/sgw/gtw';
  // var $payproxyURL = 'http://demoshops.heidelpay.de/sw4/engine/Shopware/Plugins/Community/Backend/HeidelActions/payproxy/';
  var $payproxyURL = '';

  var $availablePayments = array('CC','DD','DC','VA','OT','IV','PP','UA');
  var $pageURL = '';
  var $actualPaymethod = 'CC';
  /*}}}*/

	public function init(){/*{{{*/
		$this->View()->addTemplateDir(dirname(__FILE__)."/Views/");
	}/*}}}*/

  public function indexAction()/*{{{*/
  {
    if (  $this->Request()->isSecure() ) {
      $protokoll = "https://";
    } else {
      $protokoll = "http://";
    };

    // Get default shop 
    $sql = 'SELECT base_path FROM `s_core_shops`WHERE `default` = 1 LIMIT 1';
    $res = current(Shopware()->Db()->fetchAll($sql));
    $exists = count($res)>0;
    if (!$exists) {
      print 'No Settings Config found for sBASEPATH';
      exit();
    }
    $serverHost = $this->Request()->getHttpHost();
    $baseURL = $protokoll.$serverHost.$res['base_path'];

    //$this->payproxyURL = $protokoll . $this->Request()->getHttpHost() ."/engine/Shopware/Plugins/Default/Backend/HeidelActions/payproxy/" ;
    $this->payproxyURL = $baseURL."/backend/HeidelBooking/executeSubScript/file/" ; 
    
    if ($this->Request()->getParam('cid') != $this->FrontendConfig()->HEIDELPAY_CC_CHANNEL){
      define('TRANSACTION_CHANNEL', $this->Request()->getParam('cid'));
    } else {
      define('TRANSACTION_CHANNEL', $this->FrontendConfig()->HEIDELPAY_CC_CHANNEL);
    }
    define('SECURITY_SENDER', $this->FrontendConfig()->HEIDELPAY_SECURITY_SENDER);
    define('USER_LOGIN', $this->FrontendConfig()->HEIDELPAY_USER_LOGIN);
    define('USER_PWD', $this->FrontendConfig()->HEIDELPAY_USER_PW);
    define('TRANSACTION_MODE', $this->FrontendConfig()->HEIDELPAY_TRANSACTION_MODE);

    // check if table exists
    if (!Shopware()->Plugins()->Frontend()->HeidelPayment()->checkTable(SECURITY_SENDER)){
      Shopware()->Plugins()->Frontend()->HeidelPayment()->createSenderTable(SECURITY_SENDER);
    }

    /*
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
    // Übergabe CSS aus Shop an PayProxy für eigenes Design -> Siehe style.tpl
    
    $params['FRONTEND_CSS_PATH']      = 'http://'.$_SERVER['HTTP_HOST'].$this->Front()->Router()->assemble(array(
      'forceSecure' => 1,
      'action' => 'style',
    ));
     */
    $params = array();
    $params['FRONTEND_LANGUAGE']      = Shopware()->Locale()->getLanguage();
    $params['SECURITY_SENDER']        = SECURITY_SENDER;
    $params['USER_LOGIN']             = USER_LOGIN;
    $params['USER_PWD']               = USER_PWD;
    $params['TRANSACTION_CHANNEL']    = TRANSACTION_CHANNEL;
    $params['TRANSACTION_MODE']       = TRANSACTION_MODE;

    $params['FRONTEND_CSS_PATH']      = 'style_shopware.css'; // Style liegt nun beim HOP
    
    $user = Shopware()->Auth()->getIdentity();
    /** @var $locale \Shopware\Models\Shop\Locale */
    $locId = $user->locale;
    /*
    if (empty($params['SECURITY.SENDER'])){
      $this->View()->loadTemplate("backend/plugins/HeidelBooking/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingSender', $locId);
      return false;
    }
    if (empty($params['TRANSACTION.CHANNEL'])){
      $this->View()->loadTemplate("backend/plugins/HeidelBooking/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingChannel', $locId);
      return false;
    }
    if (empty($params['USER.LOGIN'])){
      $this->View()->loadTemplate("backend/plugins/HeidelBooking/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingUser', $locId);
      return false;
    }
    if (empty($params['USER.PWD'])){
      $this->View()->loadTemplate("backend/plugins/HeidelBooking/error.tpl");
      $this->View()->ERROR = $this->getSnippet('MissingPwd', $locId);
      return false;
    }
    */
    /*
    $url = $this->demo_url_old;
    if (substr(strtoupper(SECURITY_SENDER),0,3) == '31H') $url = $this->demo_url_new;
    if (constant('TRANSACTION_MODE') == 'LIVE'){
      $url = $this->live_url_old;
      if (substr(strtoupper(SECURITY_SENDER),0,3) == '31H') $url = $this->live_url_new;
    }

    $token = $this->doRequest($url, $params, false);
    */

    $_SESSION['loginData'] = $params;
    $token = session_id();

    $url = $this->payproxyURL.'panel.php?token='.$token.'&uid='.$this->Request()->getParam('uid');

    $this->View()->loadTemplate("backend/plugins/HeidelBooking/index.tpl");
    #$this->View()->assign('HPUrl', $url);
    $this->View()->HPUrl = $url;
  }/*}}}*/

	public function skeletonAction(){/*{{{*/
		$this->View()->loadTemplate("backend/plugins/HeidelBooking/skeleton.tpl");
  }/*}}}*/

  public function FrontendConfig()/*{{{*/
	{
		return Shopware()->Plugins()->Frontend()->HeidelPayment()->Config();
  }/*}}}*/

  public function BackendConfig()/*{{{*/
	{
		return Shopware()->Plugins()->Frontend()->HeidelBooking()->Config();
	}/*}}}*/

  /**
	 * Style action method
	 */
	public function styleAction()/*{{{*/
  {
    $this->View()->loadTemplate("backend/plugins/HeidelBooking/style.tpl");
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

  public function getSnippet($name, $localeId, $ns = 'backend/HeidelActions', $shopId = 1)/*{{{*/
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

  public function executeSubScriptAction(){ 
	  $dir = dirname(__FILE__)."/payproxy/"; 
	  $file = $this->Request()->getParam("file"); 
	  $file = basename($file); 
	  if (!in_array($file,array("panel.php"))){ 
		  return; 
	  } 
	  define("heidelpay_auth",true); 
	  
	  include($dir.$file); 
	  
	  $this->View()->setTemplate(); 
  } 

}
?>
