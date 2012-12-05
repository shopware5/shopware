<?php
/**
 * Heidelpay
 *
 * @link http://www.heidelpay.de
 * @copyright Copyright (c) 2011, Heidelberger Payment AG
 * @author Jens Richter und Tobias Eilers
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_PaymentHeidelpay extends Shopware_Controllers_Frontend_Payment
{
	 var $reqFields = array(
    'IDENTIFICATION_UNIQUEID',
    'IDENTIFICATION_SHORTID',
    'IDENTIFICATION_TRANSACTIONID',
    'IDENTIFICATION_REFERENCEID',
    'PROCESSING_RESULT',
    'PROCESSING_RETURN_CODE',
    'PROCESSING_CODE',
    'TRANSACTION_SOURCE',
    'TRANSACTION_CHANNEL',
    'TRANSACTION_RESPONSE',
    'TRANSACTION_MODE',
    'CRITERION_RESPONSE_URL',
  	);
	
	
	var $dbtable = '';
	var $curl_response   = '';
  	var $error      = '';
    var $httpstatus = '';
  /**
   * Index action method
   */
  public function indexAction()/*{{{*/
  {
    if ( $this->Config()->HEIDELPAY_DEBUG == "Ja" )
    {
      print "<h1>Heidelpay Controller</h1><br />";
      print "<h2>Debug Mode</h2><br />";
      print "PaymentShortName: ".$this->getPaymentShortName() ;
      print '<br /><a href="'.$this->Front()->Router()->assemble(array(
        'forceSecure' => 1,
        'action' => 'gateway'
      )).'">Weiter zum IFrame</a>';
      die();

    }

    $avaliblePayment  = Shopware()->Plugins()->Frontend()->HeidelPayment()->paymentMethod();
    $Payment = array();
    foreach ($avaliblePayment as $key => $value) {
      $Payment[] = $avaliblePayment[$key]['name'];
    }
    $activePayment	=  preg_replace('/heidelpay_/', '', $this->getPaymentShortName());

    if (in_array($activePayment, $Payment , true))
    {
      //return $this->forward('gateway');
      return $this->redirect(array('controller' => 'PaymentHeidelpay', 'action' => 'gateway', 'forceSecure' => 1)) ;
    }
    else
    {
      return $this->forward('index', 'checkout');
    }

  }/*}}}*/

  /**
   * Pre dispatch action method
   */
  public function preDispatch()/*{{{*/
  {
    if(in_array($this->Request()->getActionName(), array('notify', 'book', 'refresh', 'memo'))) {
      Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }
  }/*}}}*/

  /**
   * Style action method
   */
  public function styleAction()/*{{{*/
  {
    $this->Response()->setHeader('Content-Type', 'text/css');
  }/*}}}*/

  /**
   * Api call hco
   */
  public function gatewayAction()/*{{{*/
  {

    $user = $this->getUser();
    $router = $this->Front()->Router();
    $request = $this->Request();
		unset(Shopware()->Session()->HPError);

    $params = array();
    $params['PRESENTATION.AMOUNT'] = $this->formatNumber($this->getAmount());
    $params['PRESENTATION.CURRENCY'] = Shopware()->Currency()->getShortName();
    $params['FRONTEND.LANGUAGE'] = Shopware()->Locale()->getLanguage();
    $params['IDENTIFICATION.TRANSACTIONID']= $this->createPaymentUniqueId();
    Shopware()->Session()->HPOrderID = $params['IDENTIFICATION.TRANSACTIONID'] ; 

    /* PaymentMethode */
    $activePayment	=  preg_replace('/heidelpay_/', '', $this->getPaymentShortName());

    switch ($activePayment) {
      case 'sue':
      case 'gir':
      case 'ide':
      case 'eps':
            $params['PAYMENT.CODE'] = "OT.PA" ;
        break;
      case 'pay';
            $params['PAYMENT.CODE'] = "VA.DB" ;
            $params['ACCOUNT.BRAND'] = "PAYPAL" ;
            $params['FRONTEND.PM.DEFAULT_DISABLE_ALL'] = "true";
            $params['FRONTEND.PM.0.ENABLED'] = "true";
            $params['FRONTEND.PM.0.METHOD'] = "VA";
            $params['FRONTEND.PM.0.SUBTYPES'] = "PAYPAL" ;
        break;
      case 'pp' :
      case 'iv' :
            $params['PAYMENT.CODE'] = strtoupper($activePayment).".PA";
        break;
      default:
            $params['PAYMENT.CODE'] = strtoupper($activePayment).".DB";
        break;
    }

    $bookingMode = array( 'cc','dc','dd');

    if (in_array($activePayment, $bookingMode))
    {
      $booking = 	'HEIDELPAY_'.strtoupper($activePayment).'_BOOKING_MODE';
      if ( $this->Config()->$booking == "Reservierung" ) {
        $params['PAYMENT.CODE'] = strtoupper($activePayment).".PA";
      }
    }


    $channel = 'HEIDELPAY_'.strtoupper($activePayment).'_CHANNEL';
    $params['TRANSACTION.CHANNEL'] = $this->Config()->$channel;
    //$params['TRANSACTION.CHANNEL'] = "31HA07BC81A71E2A47DA94B6ADC524D8";

    $countryISO = Shopware()->Db()->fetchOne("
      SELECT `countryiso` FROM `s_core_countries`
      WHERE `id` = ?
      ", array( $user['billingaddress']['countryID'] ));

    /* billing informations */
    //$params['NAME.SALUTATION ']		=	$user['billingaddress']['salutation'];
    $params['ACCOUNT.HOLDER'] 		= $user['billingaddress']['firstname'].' '.$user['billingaddress']['lastname'];
    $params['NAME.GIVEN'] 			= 	$user['billingaddress']['firstname'];
    $params['NAME.FAMILY'] 			= 	$user['billingaddress']['lastname'];
    $params['ADDRESS.STREET']		=	$user['billingaddress']['street']." ".$user['shippingaddress']['streetnumber'];
    $params['ADDRESS.ZIP']			=	$user['billingaddress']['zipcode'];
    $params['ADDRESS.CITY']			=	$user['billingaddress']['city'];
    $params['ADDRESS.COUNTRY']		=	$countryISO;
    $params['CONTACT.EMAIL']		=	$user['additional']['user']['email'];
    $params['CONTACT.IP']			=	$_SERVER['REMOTE_ADDR'] ;


    $params['SHOP.TYPE']        	  = "Shopware - ".  Shopware()->Config()->Version;
    $params['SHOPMODUL.VERSION']      = Shopware()->Plugins()->Frontend()->HeidelPayment()->modulType ." ".
      Shopware()->Plugins()->Frontend()->HeidelPayment()->version ;


    /* api settings */
    $params['TRANSACTION.MODE']			=  	$this->Config()->HEIDELPAY_TRANSACTION_MODE ;
    $params['FRONTEND.MODE']			=	"DEFAULT";

    $params['FRONTEND.ENABLED']			=	"true";
    // Rechnung und Vorkasse direkt ohne Frame buchen // 05.07.2012
    if (in_array($activePayment, array('pp', 'iv'))){
      $params['FRONTEND.ENABLED']			=	"false";
    }
    $params['FRONTEND.POPUP']			=	"false";
    $params['FRONTEND.REDIRECT_TIME']	=	"0";
    $params['REQUEST.VERSION']			=	"1.0";
    $params['FRONTEND.NEXTTARGET']		=	"top.location.href";

    $params['FRONTEND.RESPONSE_URL']	=	$this->Front()->Router()->assemble(array(
      'forceSecure' => 1,
      'action' => 'response',
      'appendSession' => 'SESSION_ID'
    ));
    $params['FRONTEND.CSS_PATH']	=	$this->Front()->Router()->assemble(array(
      'forceSecure' => 1,
      'action' => 'style'
    ));


    if($this->Config()->HEIDELPAY_TRANSACTION_MODE == 'LIVE') {
      $requestUrl = $this->Config()->HEIDELPAY_LIVE_URL ;
      $params['SECURITY.SENDER'] = $this->Config()->HEIDELPAY_SECURITY_SENDER ;
      $params['USER.LOGIN'] = $this->Config()->HEIDELPAY_USER_LOGIN ;
      $params['USER.PWD'] = $this->Config()->HEIDELPAY_USER_PW ;
    } else {
      $requestUrl = $this->Config()->HEIDELPAY_TEST_URL ;
      $params['SECURITY.SENDER'] = $this->Config()->HEIDELPAY_SECURITY_SENDER ;
      $params['USER.LOGIN'] = $this->Config()->HEIDELPAY_USER_LOGIN ;
      $params['USER.PWD'] = $this->Config()->HEIDELPAY_USER_PW ;
    }
    $params['CRITERION.MERCHANTID'] = $params['SECURITY.SENDER'];
    $params['CRITERION.SECRET'] = $this->createSecretHash( Shopware()->Session()->HPOrderID );

 
    // Neue Paramater für HOP
    $params['CRITERION.RESPONSE_URL'] = $this->Front()->Router()->assemble(array(
      'forceSecure' => 1,
      'action' => 'notify',
      'appendSession' => 'SESSION_ID'
    ));
    
    $respone = $this->doRequest($requestUrl, $params);

    if ($this->Config()->HEIDELPAY_DEBUG == "Ja" )
    {
      print "<h1>Heidelpay Controler</h1><br />";
      print "<h2>Debug Mode</h2><br />";
      print "Request:<br /> ";
      foreach ($params as $key => $value) {
        print "&nbsp; $key => $value <br />";
      }
      print "<br /><br />";

      print "Response:<br /> ";
      foreach ($respone as $key => $value) {
        print "&nbsp; $key => $value <br />";
      }
      print "<br /><br />";
      if($respone['PROCESSING_RESULT'] == "ACK" || $respone['POST_VALIDATION'] == "ACK" ) {
        print '<center><iframe id="payment_frame" frameborder="0"
          border="0" src="'.$respone['FRONTEND_REDIRECT_URL'].'"
          style="width: 450px; border: 1px solid #000; height: 600px;"></iframe></center>';
      }
      die();
    }

    if( $respone['POST_VALIDATION'] == "NOK") {
      Shopware()->Plugins()->Frontend()->HeidelPayment()->Logging(
        $respone['PROCESSING_RETURN'] .
        " -> please verify plugin configuration.", "ERROR" ) ;
      return $this->forward('error');
    }



    if($respone['PROCESSING_RESULT'] == "ACK" || $respone['POST_VALIDATION'] == "ACK" ) {
  
      if (in_array($activePayment, array('pp', 'iv'))){
        $transactionId = $respone['IDENTIFICATION_TRANSACTIONID'];
        $paymentUniqueId = $respone['IDENTIFICATION_UNIQUEID'];
        #echo '<pre>'.print_r($respone, 1).'</pre>'; exit();
        $locId = Shopware()->Shop()->getLocale()->getId();
        $repl = array(
          '{AMOUNT}'                    => sprintf('%1.2f', $this->getAmount()),
          '{CURRENCY}'                  => $this->getCurrencyShortName(),
          '{CONNECTOR_ACCOUNT_COUNTRY}' => $respone['CONNECTOR_ACCOUNT_COUNTRY']."\n",
          '{CONNECTOR_ACCOUNT_HOLDER}'  => $respone['CONNECTOR_ACCOUNT_HOLDER']."\n",
          '{CONNECTOR_ACCOUNT_NUMBER}'  => $respone['CONNECTOR_ACCOUNT_NUMBER']."\n",
          '{CONNECTOR_ACCOUNT_BANK}'    => $respone['CONNECTOR_ACCOUNT_BANK']."\n",
          '{CONNECTOR_ACCOUNT_IBAN}'    => $respone['CONNECTOR_ACCOUNT_IBAN']."\n",
          '{CONNECTOR_ACCOUNT_BIC}'     => $respone['CONNECTOR_ACCOUNT_BIC']."\n",
          '{IDENTIFICATION_SHORTID}'    => "\n\n".$respone['IDENTIFICATION_SHORTID']."\n\n",
        );
        if ($activePayment == 'iv'){
          $comment = $this->getSnippet('InvoiceHeader', $locId)."\n";
          $comment.= strtr($this->getSnippet('PrepaymentText', $locId), $repl);
        } else {
          $comment = strtr($this->getSnippet('PrepaymentText', $locId), $repl);
        }
        /*
         * Basket to order
         */
        $paymentStatus = "21";
        Shopware()->Session()->HPTrans = $paymentUniqueId;
        $this->saveOrder($transactionId, $paymentUniqueId, $paymentStatus);
        // Add Infos to Order
        $params = array(
          'o_attr1' => $respone['IDENTIFICATION_SHORTID'],
          'o_attr2' => $respone['IDENTIFICATION_UNIQUEID'],
          'o_attr5' => $respone['TRANSACTION_CHANNEL'],
          'comment' => $comment,
          'internalcomment' => '',
        );
        $this->addOrderInfos($transactionId, $params);

        $comment = preg_replace('/:/', ':<br><br>', $comment, 1);
        $comment = nl2br($comment);
        Shopware()->Session()->sOrderVariables['sTransactionumber'] = $transactionId.'<br><br>'.$comment;

        return $this->redirect(array(
          'forceSecure' => 1,
          'action' => 'success',
          'txnID'	=> $transactionId,
           'sUniqueID' => $transactionId,
          #'sComment' => urlencode($comment)
        ));
      }

      $this->View()->PaymentShortName = $this->getPaymentShortName();
      $this->View()->PaymentUrl = $respone['FRONTEND_REDIRECT_URL'];

    }
  }/*}}}*/

  public function notifyAction()/*{{{*/
  {
    $internalcomment = '';
    $comment = "ShortID: ".$this->Request()->getParam('IDENTIFICATION_SHORTID');
    $status = $this->Request()->getParam('PROCESSING_RESULT');
    $transactionId = $this->Request()->getParam('IDENTIFICATION_TRANSACTIONID');
    $paymentUniqueId = $this->Request()->getParam('IDENTIFICATION_UNIQUEID');
    $errorMessage = $this->Request()->getParam('PROCESSING_RETURN');

    $order = $this->getOrder($transactionId);
    #echo '<pre>'.print_r($order, 1).'</pre>';

    Shopware()->Session()->HPOrderID = $transactionId; 

    // Heidelpay function to verify the response
    $orgHash = $this->createSecretHash( $transactionId );
    $responseHash =	$this->Request()->getParam('CRITERION_SECRET');

    #$this->View()->loadTemplate("frontend/payment_heidelpay/notify.tpl");

    if ($responseHash != $orgHash ) {
      Shopware()->Plugins()->Frontend()->HeidelPayment()->Logging(
        "Hash verification error, suspecting manipulation.".
        " PaymentUniqeID: " . Shopware()->Session()->HPOrderID .
        " IP: " . $_SERVER['REMOTE_ADDR'] .
        " Hash: " . $orgHash .
        " ResponseHash: " . $responseHash 
        , "ERROR" );
      #$this->View()->URL = 'FAIL';
      echo 'FAIL';

    } else if ($this->Request()->getParam('PROCESSING_RESULT') == 'ACK') {

      $params = array();
      $tmp = explode('.', $this->Request()->getParam('PAYMENT_CODE'));
      $meth = $tmp[0];
      $type = $tmp[1];

      $amount = $this->Request()->getParam('PRESENTATION_AMOUNT');
      $currency = $this->Request()->getParam('PRESENTATION_CURRENCY');
      #$ori_amount = $this->formatNumber($this->getAmount());
      #$ori_currency = $this->getCurrencyShortName();
      $ori_amount = $this->formatNumber($order['invoice_amount']);
      $ori_currency = $order['currency'];

      if ($type == 'PA'){ 
        $params['cleared'] = 18; // Reserviert
        $params['internalcomment'] = 'Reservation '.$comment;
        $params['o_attr3'] = $this->Request()->getParam('IDENTIFICATION_SHORTID');
        $params['o_attr4'] = $this->Request()->getParam('IDENTIFICATION_UNIQUEID');
        $params['o_attr5'] = $this->Request()->getParam('TRANSACTION_CHANNEL');
      } else if ($type == 'CP' || $type == 'RC' || $type == 'DB'){ 
        $params['cleared'] = 12; // default payment status is "12 Komplett bezahl"
        if ($type == 'CP'){
          $params['internalcomment'] = 'Capture '.$comment;
        } else if ($type == 'DB'){
          $params['internalcomment'] = 'Debit '.$comment;
        } else {
          $params['internalcomment'] = 'Receipt '.$comment;
        }
        $params['cleareddate'] = date('Y-m-d H:i:s');
        $params['o_attr1'] = $this->Request()->getParam('IDENTIFICATION_SHORTID');
        $params['o_attr2'] = $this->Request()->getParam('IDENTIFICATION_UNIQUEID');
        $params['o_attr5'] = $this->Request()->getParam('TRANSACTION_CHANNEL');
      } else if ($type == 'RB'){ 
        $params['internalcomment'] = 'Rebill '.$comment;
      } else if ($type == 'RF'){ 
        $params['internalcomment'] = 'Refund '.$comment;
      } else if ($type == 'RV'){ 
        $params['internalcomment'] = 'Reversal '.$comment;
      } else if ($type == 'CB'){ 
        $params['internalcomment'] = 'Chargeback '.$comment;
      }
      // Amount mit in Kommentar
      $params['internalcomment'].= "\n".'Amount: '.$amount.' '.$currency."\n".'Original Amount: '.$ori_amount.' '.$ori_currency; // Amount in Kommentar
      // Amount prüfen
      if ($type == 'RC' && $amount > 0 && $ori_amount != $amount){
        $params['internalcomment'].= "\n".'!!! Amount mismatch !!!';
      }
      // Currency prüfen
      if (!empty($currency) && $ori_currency != $currency){
        $params['internalcomment'].= "\n".'!!! Currency mismatch !!!';
      }
      // Externes Kommentar mit speichern
      $externalcomment = $this->Request()->getParam('CRITERION_COMMENT');
      if (!empty($externalcomment)){
        $params['internalcomment'].= "\nExternal Comment: ".$externalcomment;
      }

      // Add Infos to Order
      $this->addOrderInfos($transactionId, $params);
      #$this->View()->URL = '<pre>'.print_r($params, 1).'</pre>';
      echo '<pre>'.print_r($params, 1).'</pre>';
    }
    unset(Shopware()->Session()->HPOrderID);
  }/*}}}*/

  /* Respose and redirct */

  public function responseAction()/*{{{*/
  {
    $internalcomment = '';
    $comment = '';
    $status = $this->Request()->getParam('PROCESSING_RESULT');
    $transactionId = $this->Request()->getParam('IDENTIFICATION_TRANSACTIONID');
    $paymentUniqueId = $this->Request()->getParam('IDENTIFICATION_UNIQUEID');
    $errorMessage = $this->Request()->getParam('PROCESSING_RETURN');

    // Payment Code zerlegen
    $tmp = explode('.', $this->Request()->getParam('PAYMENT_CODE'));
    $meth = $tmp[0];
    $type = $tmp[1];

    /*
     * Heidelpay function to verify the response
     * new feature since version 12.06
     */
//    $orgHash = $this->createSecretHash( Shopware()->Session()->HPOrderID, $this->formatNumber($this->getAmount()) );
    $orgHash = $this->createSecretHash( $transactionId );
    $responseHash =	$this->Request()->getParam('CRITERION_SECRET'); 

    if ($responseHash != $orgHash ) {
      Shopware()->Plugins()->Frontend()->HeidelPayment()->Logging(
        "Hash verification error, suspecting manipulation.".
        " PaymentUniqeID: " . Shopware()->Session()->HPOrderID .
        " IP: " . $_SERVER['REMOTE_ADDR'] .
        " Hash: " . $orgHash .
        " ResponseHash: " . $responseHash 
        , "ERROR" );
      $this->View()->URL = $this->Front()->Router()->assemble(array(
        'action' => 'error'
      ));

    } elseif ($this->Request()->getParam('PROCESSING_RESULT') == 'ACK' && $this->Request()->getParam('PROCESSING_REASON_CODE') == "00" ) {

      $paymentStatus = 12 ; // default payment status is "12 Komplett bezahl"
      if ($type == 'PA'){ 
        $paymentStatus = 18; // if booking type is set to reservation set payment status to "18 Reserviert"
      }
      $comment = "ShortID: ".$this->Request()->getParam('IDENTIFICATION_SHORTID') ;

      //$locId = Shopware()->Locale()->getId(); // Locale ID laden

      /*
       * Basket to order
       */
      Shopware()->Session()->HPTrans = $paymentUniqueId;
      $this->saveOrder( $transactionId, $paymentUniqueId, $paymentStatus  );
      // Add Infos to Order
      $params = array(
        'o_attr1' => $this->Request()->getParam('IDENTIFICATION_SHORTID'),
        'o_attr2' => $this->Request()->getParam('IDENTIFICATION_UNIQUEID'),
        'o_attr5' => $this->Request()->getParam('TRANSACTION_CHANNEL'),
        'comment' => "ShortID: ".$this->Request()->getParam('IDENTIFICATION_SHORTID'),
        'internalcomment' => $internalcomment,
      );
      if ($paymentStatus == 12){
        $params['cleareddate'] = date('Y-m-d H:i:s');
      }
      $this->addOrderInfos($transactionId, $params);

      $this->View()->URL =   $this->Front()->Router()->assemble(array(
        'forceSecure' => 1,
        'action' => 'success'
      ));

    /* 3D Secure Waiting */
    } elseif ($this->Request()->getParam('POST_VALIDATION') == 'ACK' && $this->Request()->getParam('PROCESSING_REASON_CODE') == 80 ) {

      $paymentStatus = 21;
      /* Basket to order */
      Shopware()->Session()->HPTrans = $transactionId;
      $this->saveOrder( $transactionId, $paymentUniqueId, $paymentStatus  );
      // Add Infos to Order
      $params = array(
        'o_attr1' => $this->Request()->getParam('IDENTIFICATION_SHORTID'),
        'o_attr2' => $this->Request()->getParam('IDENTIFICATION_UNIQUEID'),
        'o_attr5' => $this->Request()->getParam('TRANSACTION_CHANNEL'),
        'comment' => "ShortID: ".$this->Request()->getParam('IDENTIFICATION_SHORTID'),
        'internalcomment' => $internalcomment,
        );
      $this->addOrderInfos($transactionId, $params);

      $this->View()->URL =   $this->Front()->Router()->assemble(array(
        'forceSecure' => 1,
        'action' => 'success'
      ));

    } elseif ($this->Request()->getParam('FRONTEND_REQUEST_CANCELLED') == 'true') {

      //if ( $this->Config()->HEIDELPAY_CANCEL_ORDER == "Ja" )
      //{
      //	$this->saveOrder( $transactionId, $transactionId, "35", false );
      //}
      $this->View()->URL = $this->Front()->Router()->assemble(array(
        'forceSecure' => 1,
        'action' => 'cancel'
      ));

    } else {

      //if ( $this->Config()->HEIDELPAY_FAIL_ORDER == "Ja" )
      //{
      //	$this->saveOrder( $transactionId, $paymentUniqueId , "35", false );
      //}
      Shopware()->Session()->HPError = $errorMessage;
      $this->savePaymentStatus( $transactionId, $paymentUniqueId , "35, false" );
      $this->View()->ErrorMessage = $errorMessage;
      $this->View()->URL = $this->Front()->Router()->assemble(array(
        'action' => 'fail'
      ));
    }
    unset(Shopware()->Session()->HPOrderID);
  }/*}}}*/

    public function cancelAction()/*{{{*/
    {
    //if ( $this->Config()->HEIDELPAY_CANCEL_ORDER == "Ja" )
    //{

    //}
    //else
    //{
    //return $this->redirect(array('controller' => 'checkout', 'action' => 'cart', 'forceSecure' => 1)) ;
    //}

  }/*}}}*/

  public function failAction()/*{{{*/
  {
    Shopware()->Template()->addTemplateDir(dirname(__FILE__).'/Views/');
    ////if ( $this->Config()->HEIDELPAY_FAIL_ORDER == "Nein" )
    //{
    $this->View()->back2basket = 1 ;
    //}
    $this->View()->ErrorMessage = htmlentities(Shopware()->Session()->HPError);
    //unset(Shopware()->Session()->HPError);

  }/*}}}*/

  public function successAction()/*{{{*/
  {
    return $this->redirect(array('controller' => 'checkout', 'action' => 'finish', 'forceSecure' => 1, 'sUniqueID' =>  Shopware()->Session()->HPTrans)) ;
    unset(Shopware()->Session()->HPTrans);
  }/*}}}*/

  public function prepaymentAction()/*{{{*/
  {
    $this->View()->back2basket = 1 ;
    $bankInfo = $this->readComment($this->Request()->getParam('txnID'));
    if ($bankInfo == '' ) {
      $bankInfo = "Es konnten keine Daten zur Ihrer Transaktion ermittelt werden";
    }

    $this->View()->bankInfo = $bankInfo ;
    $this->View()->transID = Shopware()->Session()->HPTrans;
  }/*}}}*/

  public function errorAction()/*{{{*/
  {
  }/*}}}*/

  public function formatNumber($value)/*{{{*/
  {
    //$value = preg_replace(",", ".", $value);
    return  sprintf('%1.2f', $value);
  }/*}}}*/

  /**
   * Do request method
   *
   * @param string $url
   * @param array $params
   * @return array
   */
  public function doRequest($url, $params=array())/*{{{*/
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
    parse_str($respone, $result);
    return $result;
  }/*}}}*/


  /**
   * Returns payment plugin config
   *
   * @return unknown
   */
  public function Config()/*{{{*/
  {
    return Shopware()->Plugins()->Frontend()->HeidelPayment()->Config();
  }/*}}}*/

  function readComment($transactionID) {/*{{{*/
    $data = Shopware()->Db()->fetchAll("
      SELECT comment FROM s_order
      WHERE transactionID = '".$transactionID."' 
      ");

    return $data[0]['comment'] ;
  }/*}}}*/

  function createSecretHash($orderID) {/*{{{*/
    $secret = $this->Config()->HEIDELPAY_SECRET ;
      $hash = sha1( $orderID . $secret );
    return $hash;
  }/*}}}*/

  public function getSnippet($name, $localeId, $ns = 'frontend/payment_heidelpay/success', $shopId = 1)/*{{{*/
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

  public function getOrder($transactionId)/*{{{*/
  {
    $sql = 'SELECT * 
            FROM `s_order` 
            WHERE `transactionID` = "'.$transactionId.'" 
            ';
    $data = current(Shopware()->Db()->fetchAll($sql));
    return $data;
  }/*}}}*/

  function addOrderInfos($transactionID, $params) /*{{{*/
  {
    $orderModel = Shopware()
      ->Models()
      ->getRepository('Shopware\Models\Order\Order')
      ->findOneBy(array('transactionId' => $transactionID));
    // if internalComment is set, read old commment and add time stamp
    $alterWert = $orderModel->getInternalComment();
    if (!empty($params['internalcomment'])) {
      $params['internalcomment'] = date('d.m.Y H:i:s') . "\n" . $params['internalcomment'] . "\n \n" . $alterWert;
    } else {
      $params['internalcomment'] = $alterWert;
    }
    // Mapping database -> model
    $orderMappings = array('ordernumber' => 'number', 
                'userID' => 'customerId', 
                'invoice_amount' => 'invoiceAmount', 
                'invoice_amount_net' => 'invoiceAmountNet', 
                'invoice_shipping' => 'invoiceShipping', 
                'invoice_shipping_net' => 'invoiceShippingNet', 
                'ordertime' => 'orderTime', 
                'status' => 'status', 
                'cleared' => 'cleared', // Payment Status model
                'paymentID'  => 'paymentId', 
                'transactionID' => 'transactionId', 
                'comment' => 'comment', 
                'customercomment' => 'customerComment', 
                'internalcomment' => 'internalComment', 
                'net' => 'net', 
                'taxfree' => 'taxFree', 
                'partnerID' => 'partnerId', 
                'temporaryID' => 'temporaryId', 
                'referer' => 'referer', 
                'cleareddate' => 'clearedDate', 
                'trackingcode' => 'trackingCode', 
                'language' => 'languageIso', 
                'dispatchID' => 'dispatch', // dispatch model
                'currency'  => 'currency', 
                'currencyFactor' => 'currencyFactor', 
                'subshopID' => 'shopId', 
                'remote_addr' => 'remoteAddress');

    $attributeMapping = array(
      'o_attr1' => 'attribute1', 
      'o_attr2' => 'attribute2', 
      'o_attr3' => 'attribute3', 
      'o_attr4' => 'attribute4', 
      'o_attr5' => 'attribute5', 
      'o_attr6' => 'attribute6');

    /** @var $orderModel \Shopware\Models\Order\Order */

    $newData      = array();
    $attribute     = array();
    $params['o_attr6'] = "HEIDELPAY"; // Damit das Backend diese Zahlung als Heidelpay Zahlung erkennt
    //order mapping
    foreach ($orderMappings as $key => $mapping) {
      if (isset($params[$key])) {
        $newData[$mapping] = $params[$key];
      }
    }
    //attribute mapping
    foreach ($attributeMapping as $key => $mapping) {
      if (isset($params[$key])) {
        $attribute[$mapping] = $params[$key];
      }
    }
    if (!empty($attribute)) {
      $newData['attribute'] = $attribute;
    }
    //check if the cleared parameter is passed, if this is the case resolve the id with the status model .
    if (isset($params['cleared'])) {
      //$orderModel->getInternalComment();
      /*
			$newData['paymentStatus'] = Shopware()
				->Models()
        ->getRepository('\Shopware\Models\Order\Status')
        ->findOneBy(array('id' => $params['cleared']));
       */
      $sql = 'UPDATE `s_order` SET `cleared` = ? WHERE `transactionID` = ?';
      Shopware()->Db()->query($sql,array((int)$params['cleared'], $transactionID));
    }
    /*
		if (isset($params['status'])) {
			$newData['orderStatus'] = Shopware()
				->Models()
				->getRepository('\Shopware\Models\Order\Status')
				->find($params['status']);
    }
		if (isset($params['dispatch'])) {
			$newData['dispatch'] = Shopware()
				->Models()
				->getRepository('\Shopware\Models\Dispatch\Dispatch')
				->find($params['dispatch']);
    }
    */

		// populate Model with data
		$orderModel->fromArray($newData);
		Shopware()->Models()->persist($orderModel);
		// save to database
		Shopware()->Models()->flush();
		}
	/*}}}*/
	
	public function rawnotifyAction(){
		
		ini_set('session.use_cookies', 0); // Session Cookie unterbinden
		ob_start();
		
		$PaymentIP = explode(',', $this->Config()->HEIDELPAY_NOTIFY_IP);
		if (!in_array($_SERVER['REMOTE_ADDR'], $PaymentIP)) {
			 Shopware()->Plugins()->Frontend()->HeidelPayment()->Logging(
        "Notify call from an unauthorized ip-address".
        " IP: " . $_SERVER['REMOTE_ADDR'] .
        " allowed are : " . $this->Config()->HEIDELPAY_NOTIFY_IP
        , "ERROR" );
        $this->View()->MES = 'FAIL';
		exit();	
			
		}
		
		$HTTP_RAW_POST_DATA = $this->Request()->getRawBody();

		if (empty($HTTP_RAW_POST_DATA)) {
			$HTTP_RAW_POST_DATA = '';
			exit();
		};
		$mail = '<pre>RAW:'.print_r($HTTP_RAW_POST_DATA, 1).'</pre>';
		$xml = simplexml_load_string($HTTP_RAW_POST_DATA); // Raw Daten in XML Object laden
		$mail.= '<pre>XML:'.print_r($xml, 1).'</pre>';
		$postData = $this->getPostFromXML($xml); // XML in Post Array konvertieren
		$mail.= '<pre>Data:'.print_r($postData, 1).'</pre>';
		#echo '<pre>'.print_r($postData, 1).'</pre>';
		
		$table = $this->Config()->HEIDELPAY_SECURITY_SENDER;
		$res = Shopware()->Plugins()->Frontend()->HeidelPayment()->checkTable($table);
		if ($res) $this->setActiveTable($table); // Aktuelle Tabelle wï¿½hlen
		#var_dump($res);
		if (!$res){
			Shopware()->Plugins()->Frontend()->HeidelPayment()->createSenderTable($table);
			$this->setActiveTable($table); // Aktuelle Tabelle wählen
		}
		// Falls RefId nicht gefuellt aber AccountRegistration gesetzt, dann ueübernehmen
		if (empty($postData['IDENTIFICATION_REFERENCEID']) && !empty($postData['CRITERION_ACCOUNT_REGISTRATION'])){
			$postData['IDENTIFICATION_REFERENCEID'] = $postData['CRITERION_ACCOUNT_REGISTRATION'];
		}
		$lastId = $this->saveReq($postData, $HTTP_RAW_POST_DATA); // Request speichern
		if (!$lastId){
			// Buchung bereits gefunden

			header('HTTP/1.1 200 Not Found');
			exit();
		}
		$this->saveSERIAL($lastId, $postData); // Postdaten speichern
		
		$url = $postData['CRITERION_RESPONSE_URL'];
		if ($postData['PROCESSING_STATUS'] == 'WAITING') $url = 'NORESP'; // Wenn 3D Secure Waiting, dann keine Response an Shop.
		if ($url != 'NORESP' && !empty($url)){
			$res = $this->doNotify($url, $postData); // Post Response an Shop schicken

			if ($this->httpstatus != '200'){

				if ($postData['CRITERION_RESPONSE_PER_MAIL']){
					 //@mail($postData['CRITERION_RESPONSE_PER_MAIL'], 'RESPONSE PER MAIL', print_r($postData,1));
					header('HTTP/1.1 200 OK'); // Hier wird es eh keinen Erfolg mehr geben
					exit();
				} else {
					header('HTTP/1.1 403 Forbidden');
					exit();
				}
			}
			$this->saveRes2Req($postData['IDENTIFICATION_UNIQUEID'], $res); // Response speichern
			
			// 3D Secure
			if ($postData['PROCESSING_STATUS_CODE'] == '80' 
				&& $postData['PROCESSING_RETURN_CODE'] == '000.200.000' 
					&& $postData['PROCESSING_REASON_CODE'] == '00'){
				// Nix tun
				$mail.= 'Noch keine Aktion, da 3D Secure WAITING...'."\n";
			} 
		}
		
		$mail.= '<pre>Res:'.print_r($res, 1).'</pre>';
		
		
		
		header('HTTP/1.1 200 OK');
		$this->View()->MES = 'OK';
		
	}
	
	  private function getPostFromXML($xml)/*{{{*/
  {
    $tmp = array();
    if (empty($xml)) return array();

    foreach($xml AS $k => $v){
      $attribs = $v->attributes();
      #echo '<pre>'.print_r($attribs, 1).'</pre>';
      foreach($attribs AS $ak => $av){
        #echo $ak.' -> '.$av.'<br>';
        $tmp[strtoupper($k).'_'.strtoupper($ak)] = (string)$av;
      }
      foreach($v AS $kk => $vv){
        $attribs = $vv->attributes();
        if (!empty($attribs)){
          foreach($attribs AS $ak => $av){
            #echo $ak.' -> '.$av.'<br>';
            $tmp[strtoupper($kk).'_'.strtoupper($ak)] = (string)$av;
          }
        }# else {
        foreach($vv AS $kkk => $vvv){
          $attribs = $vvv->attributes();
          if (!empty($attribs)){
            foreach($attribs AS $ak => $av){
              if ($kk == 'Analysis') continue;
              #echo $ak.' -> '.$av.'<br>';
              $tmp[strtoupper($kk).'_'.strtoupper($kkk).'_'.strtoupper($ak)] = (string)$av;
            }
          }# else {
          if ($kk == 'Customer'){
            foreach($vvv AS $kkkk => $vvvv){
              #echo $ak.' -> '.$av.'<br>';
              $tmp[strtoupper($kkk).'_'.strtoupper($kkkk)] = (string)$vvvv;
            }
          } else if ($kk == 'Payment'){
            foreach($vvv AS $kkkk => $vvvv){
              #echo $ak.' -> '.$av.'<br>';
              $tmp[strtoupper($kkk).'_'.strtoupper($kkkk)] = (string)$vvvv;
            }
          } else if ($kk == 'Analysis'){
            $attribs = $vvv->attributes();
            if (!empty($attribs)){
              #echo (string)$attribs->name;
              #echo (string)$vvv;
              $tmp[strtoupper($kkk).'_'.strtoupper((string)$attribs->name)] = (string)$vvv;
            }
            foreach($vvv AS $kkkk => $vvvv){
              #echo $kkkk.' -> '.$vvvv.'<br>';
              #$tmp[strtoupper($kkkk).'_'.strtoupper((string)$attribs->name)] = (string)$vvvv;
            }
          } else {
            if ($kkk == 'Expiry') continue;
            $tmp[strtoupper($kk).'_'.strtoupper($kkk)] = (string)$vvv;
            #echo $kkk.' -> '.$vvv.'<br>';
          }
          #}
        }
        #}
      }
    }
    return $tmp;
  }/*}}}*/
  
  
    private function saveReq($data, $xml)/*{{{*/
  {
    // Double Check
    if (!empty($data['IDENTIFICATION_UNIQUEID'])){
      $sql = 'SELECT `id` FROM `'.$this->dbtable.'` 
              WHERE `IDENTIFICATION_UNIQUEID`= "'.addslashes($data['IDENTIFICATION_UNIQUEID']).'" ';
      $row = Shopware()->Db()->fetchAll($sql);
      if ($row[0]['id'] > 0) return $row[0]['id'];
    }
    $sql = 'INSERT INTO `'.$this->dbtable.'` SET ';
    foreach($this->reqFields AS $key){
      $sql.= '`'.$key.'` = "'.addslashes($data[$key]).'", ';
    }
    $tmp = explode('.', $data['PROCESSING_CODE']);
    $sql.= '`meth` = "'.addslashes($tmp[0]).'", '; 
    $sql.= '`typ` = "'.addslashes($tmp[1]).'", '; 
    #$sql.= '`XML` = "'.addslashes($xml).'", '; // Raw Post Data
    $sql.= '`created` = NOW() ';
    #echo $sql;
    $res = Shopware()->Db()->query($sql);
    $lastID = Shopware()->Db()->lastInsertId();
    // Im Fall von CP die PA Zeile als gecaptured markieren
    if (!empty($data['IDENTIFICATION_REFERENCEID']) && $tmp[1] == 'CP'){
      $sql = 'UPDATE `'.$this->dbtable.'` 
              SET `CAPTURED` = 1 
              WHERE `IDENTIFICATION_UNIQUEID` = "'.addslashes($data['IDENTIFICATION_REFERENCEID']).'"';
      Shopware()->Db()->query($sql);
    }
    return $lastID;
  }/*}}}*/
  
    private function saveSERIAL($id, $data)/*{{{*/
  {
  	foreach ($data AS $key => $value) {
  		$data[$key] = utf8_decode($value);
  	}
    $serial = serialize($data);
    $sql = 'UPDATE `'.$this->dbtable.'` 
            SET `SERIAL` = "'.addslashes($serial).'" 
            WHERE `id` = '.(int)$id;
    return Shopware()->Db()->query($sql);
  }/*}}}*/
  
    private function doNotify($url, $data, $xml = NULL)/*{{{*/
  {
    $strPOST = '';
    foreach($data AS $k => $v) {
      $strPOST.= $k.'='.$v.'&';
    }
    if (!empty($xml)) $strPOST = 'load='.urlencode($xml);

    #echo '<pre>'.print_r($strPOST, 1).'</pre>';

    if (function_exists('curl_init')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 8);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $strPOST);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
      #curl_setopt($ch, CURLOPT_FOLLLOW_LOCATION,1);
      curl_setopt($ch, CURLOPT_USERAGENT, "php ctpepost");

      $this->curl_response     = curl_exec($ch);
      $this->error        = curl_error($ch);
      $this->httpstatus   = curl_getinfo($ch,CURLINFO_HTTP_CODE);

      #echo '<pre>'.print_r($this->curl_response, 1).'</pre>';
      #echo '<pre>'.print_r($this->error, 1).'</pre>';
      #echo '<pre>'.print_r($this->httpstatus, 1).'</pre>';

      curl_close($ch);

      $res = $this->curl_response;
      if (!$this->curl_response && $this->error){
        $msg = urlencode('Curl Fehler...');
        $res = 'status=FAIL&msg='.$this->error;
      }

    } else {
      $msg = urlencode('Curl Fehler..');
      $res = 'status=FAIL&msg='.$msg;
    }

    return $res;
  }/*}}}*/
  
  private  function saveRes2Req($uniqueId, $response)/*{{{*/
  {
    $sql = 'UPDATE `'.$this->dbtable.'` SET ';
    $sql.= '`RESPONSE` = "'.addslashes($response).'" ';
    $sql.= 'WHERE `IDENTIFICATION_UNIQUEID` = "'.addslashes($uniqueId).'" ';
    return Shopware()->Db()->query($sql);
  }/*}}}*/
  public function setActiveTable($table)/*{{{*/
	  {
	  $this->dbtable = $table;
	  return $table;
	  }/*}}}*/ 
	
}