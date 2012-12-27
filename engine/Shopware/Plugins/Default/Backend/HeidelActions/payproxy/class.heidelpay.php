<?php
class heidelpay
{
  /*{{{Variables*/
  var $response   = '';
  var $error      = '';
  var $httpstatus = '';

  #var $live_url = 'https://ctpe.net/frontend/payment.prc';
  #var $demo_url = 'https://test.ctpe.net/frontend/payment.prc';
  var $live_url = 'https://heidelpay.hpcgw.net/sgw/gtwu';
  var $demo_url = 'https://test-heidelpay.hpcgw.net/sgw/gtwu';
  #var $xml_demo_url = 'https://test.ctpe.io/payment/ctpe';
  #var $xml_live_url = 'https://ctpe.io/payment/ctpe';
  var $xml_demo_url = 'https://test-heidelpay.hpcgw.net/TransactionCore/xml';
  var $xml_live_url = 'https://heidelpay.hpcgw.net/TransactionCore/xml';

  var $allowedCcardCurrencies = array('AED','AFA','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZM','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CUP','CVE','CYP','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ERN','ETB','EUR','FJD','FKP','GBP','GEL','GGP','GHC','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','IMP','INR','IQD','IRR','ISK','JEP','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MTL','MUR','MVR','MWK','MXN','MYR','MZM','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PTS','PYG','QAR','RON','RUB','RWF','SAR','SBD','SCR','SDD','SEK','SGD','SHP','SIT','SKK','SLL','SOS','SPL','SRD','STD','SVC','SYP','SZL','THB','TJS','TMM','TND','TOP','TRL','TRY','TTD','TVD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VEF','VND','VUV','WST','XAF','XAG','XAU','XCD','XDR','XOF','XPD','XPF','XPT','YER','ZAR','ZMK','ZWD');
  var $availablePayments = array('CC','DD','DC','VA','OT','IV','PP','UA');
  var $pageURL = '';
  var $actualPaymethod = 'CC';

  var $db;
  var $dbhost = '' ; //'localhost';
  var $dbname = '' ; //'demoshops_sw4';
  var $dbuser = '' ; //'demoshops';
  var $dbpass = '' ; //'CKfFSxBDQxanWeBx';

  var $dbtable = 's_plugin_heidelpay_requests';
  var $table_config = 's_plugin_heidelpay_config';
  var $table_rates = 's_plugin_heidelpay_rates';
  
  var $sql = array();

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

  var $duration2days = array(
    'day'   => '1',
    'week'  => '7',
    'month' => '30',
    'year'  => '365',
  );
  var $baseURL = '';
  var $protokoll = 'http://';
  var $allKinds = array(
    'abo'     => '1',
    'rate'    => '2',
    'deposit' => '3',
  );
  /*}}}*/

  function heidelpay()/*{{{*/
  {

    $configFilepath = dirname(__FILE__).'/../../../../../../../config.php';
    $config =  include $configFilepath;
    if (!is_array($config)) {
      print 'Invalid configuration file provided; PHP file does not return array value';
      exit();
    };

    $this->dbhost = $config['db']['host'];
    $this->dbuser = $config['db']['username'];
    $this->dbpass = $config['db']['password'];
    $this->dbname = $config['db']['dbname'];

    if ($this->db = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass)){
      mysql_select_db($this->dbname, $this->db);
    } else {
      $this->error = 'MySQL Connection failed.';
    } 

    // load protokoll for shopware config (sUSESSL)
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
      $this->protokoll = "https://" ;
    } else {
      $this->protokoll = "http://" ;
    }
    $serverHost = $_SERVER['HTTP_HOST'];

    // Get default shop 
    $sql = 'SELECT base_path FROM `s_core_shops`WHERE `default` = 1 LIMIT 1';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);

    $exists = mysql_num_rows($res)>0;
    if (!$exists) {
      print 'No Settings Config found for sBASEPATH';
      exit();
    }
    $row = mysql_fetch_assoc($res);
    $this->baseURL = $this->protokoll.$serverHost.$row['base_path'];

  }/*}}}*/

  function saveReq($data, $xml)/*{{{*/
  {
    // Double Check
    if (!empty($data['IDENTIFICATION_UNIQUEID'])){
      $sql = 'SELECT `id` FROM `'.$this->dbtable.'` 
              WHERE `IDENTIFICATION_UNIQUEID`= "'.addslashes($data['IDENTIFICATION_UNIQUEID']).'" ';
      $this->sql[__FUNCTION__][] = $sql;
      $res = mysql_query($sql, $this->db);
      $row = mysql_fetch_assoc($res);
      if ($row['id'] > 0) return $row['id'];
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
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $lastID = mysql_insert_id($this->db);
    // Im Fall von CP die PA Zeile als gecaptured markieren
    if (!empty($data['IDENTIFICATION_REFERENCEID']) && $tmp[1] == 'CP'){
      $sql = 'UPDATE `'.$this->dbtable.'` 
              SET `CAPTURED` = 1 
              WHERE `IDENTIFICATION_UNIQUEID` = "'.addslashes($data['IDENTIFICATION_REFERENCEID']).'"';
      $this->sql[__FUNCTION__][] = $sql;
      mysql_query($sql, $this->db);
    }
    return $lastID;
  }/*}}}*/

  function saveRes2Req($uniqueId, $response)/*{{{*/
  {
    $sql = 'UPDATE `'.$this->dbtable.'` SET ';
    $sql.= '`RESPONSE` = "'.addslashes($response).'" ';
    $sql.= 'WHERE `IDENTIFICATION_UNIQUEID` = "'.addslashes($uniqueId).'" ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    return mysql_query($sql, $this->db);
  }/*}}}*/

  function doRequest($url, $data, $xml = NULL)/*{{{*/
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

      $this->response     = curl_exec($ch);
      $this->error        = curl_error($ch);
      $this->httpstatus   = curl_getinfo($ch,CURLINFO_HTTP_CODE);

      #echo '<pre>'.print_r($this->response, 1).'</pre>';
      #echo '<pre>'.print_r($this->error, 1).'</pre>';
      #echo '<pre>'.print_r($this->httpstatus, 1).'</pre>';

      curl_close($ch);

      $res = $this->response;
      if (!$this->response && $this->error){
        $msg = urlencode('Curl Fehler...');
        $res = 'status=FAIL&msg='.$this->error;
      }

    } else {
      $msg = urlencode('Curl Fehler..');
      $res = 'status=FAIL&msg='.$msg;
    }

    return $res;
  }/*}}}*/

  function parseResult($curlresultURL)/*{{{*/
  {
    $r_arr=explode("&",$curlresultURL);
    foreach($r_arr AS $buf) {
      $temp=urldecode($buf);
      $temp=split("=",$temp,2);
      $postatt=$temp[0];
      $postvar=$temp[1];
      $returnvalue[$postatt]=$postvar;
    }
    $processingresult = $returnvalue['PROCESSING.RESULT'];
    if (empty($processingresult)) $processingresult = $returnvalue['POST.VALIDATION'];
    $redirectURL = $returnvalue['FRONTEND.REDIRECT_URL'];
    if (!isset($returnvalue['PROCESSING.RETURN']) && $returnvalue['POST.VALIDATION'] > 0){
      $returnvalue['PROCESSING.RETURN'] = 'Errorcode: '.$returnvalue['POST.VALIDATION'];
    }
    ksort($returnvalue);
    return array('result' => $processingresult, 'url' => $redirectURL, 'all' => $returnvalue);
  }/*}}}*/

  function checkHPCParams($params)/*{{{*/
  {
    if (empty($params)) return array();
    $tmp = array();
    foreach($params AS $k => $v){
      $k = preg_replace('/_/', '.', $k, 1);
      $tmp[$k] = $v;
    }
    return $tmp;
  }/*}}}*/

  function doDeposit($amount, $currency, $usage, $depositName, $duration, $payCode, $uniqueId, $loginData)/*{{{*/
  {
    if ($duration < 1) return false;
    if (!in_array($payCode, array('DD', 'DC', 'CC'))) return false;

    $transMode = $loginData['TRANSACTION_MODE'];
    // Schedule auf Test System muï¿½ mit CONNECTOR_TEST durchgefï¿½hrt werden.
    if ($transMode == 'INTEGRATOR_TEST') $transMode = 'CONNECTOR_TEST';
    // Nicht jeder Monat hat mehr als 28 Tage. Daher immer den Letzten Tag des Monats nehmen wenn > 28
    $dayOfMonth = date('d');
    if ($dayOfMonth > 28) $dayOfMonth = 'L';
    $hour = date('H') - 3;
    $minute = date('i') - 1;

    // zum Testen in 1 Minute
    #$hour = date('H') - 2;
    #$minute = date('i') + 1;

    #$startTime = $res['all']['PROCESSING.TIMESTAMP'];
    $startTime = date('Y-m-d ').$hour.':'.date('i').date(':s');
    $nextMonth = date('m') + $duration; // Buchung erst zum bestimmten Zeitpunkt
    if ($nextMonth > 12) $nextMonth = $nextMonth - 12; // Wenn Dezember, dann ist nï¿½chster Monat 1 und nicht 13
    if ($dayOfMonth == 'L') $startTime = date('Y').'-'.$nextMonth.'-'.date('d').' '.$hour.':'.date('i').date(':s'); // Startdatum muï¿½ bei "L" in Zukunft sein.
    $entTime = '';
    $endTime = date('Y-m-d H:i:s', mktime($hour,date('i'),date('s'),date('m')+$duration,date('d'),date('Y')));
    # Nur in dem einen Monat
    $month = $nextMonth;

    $xml = '<Request version="1.0">
    <Header> 
      <Security sender="'.$loginData['SECURITY_SENDER'].'"/> 
    </Header> 
    <Transaction mode="'.$transMode.'" response="SYNC" channel="'.$loginData['TRANSACTION_CHANNEL'].'"> 
      <User login="'.$loginData['USER_LOGIN'].'" pwd="'.$loginData['USER_PWD'].'"/>
      <Identification> 
        <TransactionID>'.$depositName.'</TransactionID> 
      </Identification> 
      <Payment code="'.$payCode.'.SD"> 
        <Presentation> 
          <Amount>'.$amount.'</Amount> 
          <Currency>'.$currency.'</Currency> 
          <Usage>'.$usage.'</Usage> 
        </Presentation> 
      </Payment> 
      <Job name="'.$depositName.'" start="'.$startTime.'" end="'.$endTime.'"> 
        <Action type="DB"/> 
        <Execution>
          <DayOfMonth>'.$dayOfMonth.'</DayOfMonth>
          <Month>'.$month.'</Month>
          <Minute>'.$minute.'</Minute>
          <Hour>'.$hour.'</Hour>
        </Execution> 
        <Notice> 
          <Callable>ANYTIME</Callable> 
        </Notice> 
        <Duration>
          <Number>'.$duration.'</Number>
          <Unit>MONTH</Unit>
        </Duration>
      </Job> 
      <Analysis><Criterion name="sales_cycle">RN</Criterion></Analysis> 
      <Account registration="'.$uniqueId.'" /> 
    </Transaction> 
    </Request>';
    #echo $xml;
    return $xml;

    $res = $this->doRequest(array(), $xml);
    $res = $this->parseResult($res);
    #echo '<pre>'.print_r($res, 1).'</pre>';
    return $res;
  }/*}}}*/

  function doRates($amount, $currency, $usage, $rateName, $freq, $duration, $payCode, $uniqueId, $loginData)/*{{{*/
  {
    if ($freq < 1) return false;
    if (!in_array($payCode, array('DD', 'DC', 'CC'))) return false;

    $transMode = $loginData['TRANSACTION_MODE'];
    // Schedule auf Test System muï¿½ mit CONNECTOR_TEST durchgefï¿½hrt werden.
    if ($transMode == 'INTEGRATOR_TEST') $transMode = 'CONNECTOR_TEST';
    // Nicht jeder Monat hat mehr als 28 Tage. Daher immer den Letzten Tag des Monats nehmen wenn > 28
    $dayOfMonth = date('d');
    if ($dayOfMonth > 28) $dayOfMonth = 'L';
    $hour = date('H') - 3;
    $minute = date('i') - 1;

    // zum Testen in 1 Minute
    #$hour = date('H') - 2;
    #$minute = date('i') + 1;

    #$startTime = $res['all']['PROCESSING.TIMESTAMP'];
    $startTime = date('Y-m-d ').$hour.':'.date('i').date(':s');
    $nextMonth = date('m') + 1; // Erste Buchung erst im nï¿½chsten Monat
    if ($nextMonth > 12) $nextMonth = $nextMonth - 12; // Wenn Dezember, dann ist nï¿½chster Monat 1 und nicht 13
    if ($dayOfMonth == 'L') $startTime = date('Y').'-'.$nextMonth.'-'.date('d').' '.$hour.':'.date('i').date(':s'); // Startdatum muï¿½ bei "L" in Zukunft sein.
    $entTime = '';
    $endTime = date('Y-m-d H:i:s', mktime($hour,date('i'),date('s'),date('m')+$duration,date('d'),date('Y')));
    # Jeden Monat
    $month = '*';    
    if ($freq == 2){ # Sechsteljährlich
      $m = (int)date('m');
      $month = $m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 3){ # Vierteljährlich
      $m = (int)date('m');
      $month = $m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 4){ # Dritteljährlich
      $m = (int)date('m');
      $month = $m;
      $m+= 4;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 4;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 6){ # Halbjährlich
      $m = date('m');
      $month = $m;
      $m+= 6;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 12){ # Jährlich
      $m = date('m');
      $month = $m;
    }

    $xml = '<Request version="1.0">
    <Header> 
      <Security sender="'.$loginData['SECURITY_SENDER'].'"/> 
    </Header> 
    <Transaction mode="'.$transMode.'" response="SYNC" channel="'.$loginData['TRANSACTION_CHANNEL'].'"> 
      <User login="'.$loginData['USER_LOGIN'].'" pwd="'.$loginData['USER_PWD'].'"/>
      <Identification> 
        <TransactionID>'.$rateName.'</TransactionID> 
      </Identification> 
      <Payment code="'.$payCode.'.SD"> 
        <Presentation> 
          <Amount>'.$amount.'</Amount> 
          <Currency>'.$currency.'</Currency> 
          <Usage>'.$usage.'</Usage> 
        </Presentation> 
      </Payment> 
      <Job name="'.$rateName.'" start="'.$startTime.'" end="'.$endTime.'"> 
        <Action type="DB"/> 
        <Execution>
          <DayOfMonth>'.$dayOfMonth.'</DayOfMonth>
          <Month>'.$month.'</Month>
          <Minute>'.$minute.'</Minute>
          <Hour>'.$hour.'</Hour>
        </Execution> 
        <Notice> 
          <Callable>ANYTIME</Callable> 
        </Notice> 
        <Duration>
          <Number>'.$duration.'</Number>
          <Unit>MONTH</Unit>
        </Duration>
      </Job> 
      <Analysis><Criterion name="sales_cycle">RN</Criterion></Analysis> 
      <Account registration="'.$uniqueId.'" /> 
    </Transaction> 
    </Request>';
    #echo $xml;
    return $xml;

    $res = $this->doRequest(array(), $xml);
    $res = $this->parseResult($res);
    #echo '<pre>'.print_r($res, 1).'</pre>';
    return $res;
  }/*}}}*/

  function doSubscription($amount, $currency, $usage, $aboName, $freq, $payCode, $uniqueId, $loginData)/*{{{*/
  {
    if ($freq < 1) return false;
    if (!in_array($payCode, array('DD', 'DC', 'CC'))) return false;

    $transMode = $loginData['TRANSACTION_MODE'];
    // Schedule auf Test System muï¿½ mit CONNECTOR_TEST durchgefï¿½hrt werden.
    if ($transMode == 'INTEGRATOR_TEST') $transMode = 'CONNECTOR_TEST';
    // Nicht jeder Monat hat mehr als 28 Tage. Daher immer den Letzten Tag des Monats nehmen wenn > 28
    $dayOfMonth = date('d');
    if ($dayOfMonth > 28) $dayOfMonth = 'L';
    $hour = date('H') - 3;
    $minute = date('i') - 1;

    // zum Testen in 1 Minute
    #$hour = date('H') - 2;
    #$minute = date('i') + 1;

    #$startTime = $res['all']['PROCESSING.TIMESTAMP'];
    $startTime = date('Y-m-d ').$hour.':'.date('i').date(':s');
    $entTime = '';
    #$endTime = date('Y-m-d H:i:s', mktime($hour,date('i'),date('s'),date('m'),date('d')+($freq*30),date('Y')));
    # Jeden Monat
    $month = '*';    
    if ($freq == 2){ # Sechsteljï¿½hrlich
      $m = (int)date('m');
      $month = $m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 2;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 3){ # Vierteljï¿½hrlich
      $m = (int)date('m');
      $month = $m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 3;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 4){ # Dritteljï¿½hrlich
      $m = (int)date('m');
      $month = $m;
      $m+= 4;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
      $m+= 4;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 6){ # Halbjï¿½hrlich
      $m = date('m');
      $month = $m;
      $m+= 6;
      if ($m > 12) $m-= 12;
      $month.= ','.$m;
    } else if ($freq == 12){ # Jï¿½hrlich
      $m = date('m');
      $month = $m;
    }

    $xml = '<Request version="1.0">
    <Header> 
      <Security sender="'.$loginData['SECURITY_SENDER'].'"/> 
    </Header> 
    <Transaction mode="'.$transMode.'" response="SYNC" channel="'.$loginData['TRANSACTION_CHANNEL'].'"> 
      <User login="'.$loginData['USER_LOGIN'].'" pwd="'.$loginData['USER_PWD'].'"/>
      <Identification> 
        <TransactionID>'.$aboName.'</TransactionID> 
      </Identification> 
      <Payment code="'.$payCode.'.SD"> 
        <Presentation> 
          <Amount>'.$amount.'</Amount> 
          <Currency>'.$currency.'</Currency> 
          <Usage>'.$usage.'</Usage> 
        </Presentation> 
      </Payment> 
      <Job name="'.$aboName.'" start="'.$startTime.'" end="'.$endTime.'"> 
        <Action type="DB"/> 
        <Execution>
          <DayOfMonth>'.$dayOfMonth.'</DayOfMonth>
          <Month>'.$month.'</Month>
          <Minute>'.$minute.'</Minute>
          <Hour>'.$hour.'</Hour>
        </Execution> 
        <Notice> 
          <Callable>ANYTIME</Callable> 
        </Notice> 
        <Duration>
          <Number>'.$freq.'</Number>
          <Unit>DAY</Unit>
        </Duration>
      </Job> 
      <Analysis><Criterion name="sales_cycle">RN</Criterion></Analysis> 
      <Account registration="'.$uniqueId.'" /> 
    </Transaction> 
    </Request>';
    #echo $xml;
    return $xml;

    $res = $this->doRequest(array(), $xml);
    $res = $this->parseResult($res);
    #echo '<pre>'.print_r($res, 1).'</pre>';
    return $res;
  }/*}}}*/

  function getPostFromXML($xml)/*{{{*/
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

  function checkLogin($dat)/*{{{*/
  {
    $data = array(
      'SECURITY.SENDER'         => $dat['SECURITY_SENDER'],
      'USER.LOGIN'              => $dat['USER_LOGIN'],
      'USER.PWD'                => $dat['USER_PWD'],
      'TRANSACTION.CHANNEL'     => $dat['TRANSACTION_CHANNEL'],
      'TRANSACTION.MODE'        => $dat['TRANSACTION_MODE'],
      'PAYMENT.CODE'            => 'CC.RG',
      'FRONTEND.ENABLED'        => 'true',
      'FRONTEND.RESPONSE_URL'   => 'http://www.google.de',
    );
    $url = $this->demo_url;
    if ($dat['TRANSATION.MODE'] == 'LIVE') $url = $this->live_url;
    // echo '<pre>'.print_r($data, 1).'</pre>';
    $res = trim($this->doRequest($url, $data));
    parse_str($res, $ret);
    // echo '<pre>'.print_r($ret, 1).'</pre>';
    return !empty($ret['FRONTEND_REDIRECT_URL']);
  }/*}}}*/

  function getUniqueId($uniqueId)/*{{{*/
  {
    if (empty($uniqueId)) return array();
    $sql = 'SELECT `SERIAL` FROM `'.$this->dbtable.'` ';
    $sql.= 'WHERE `IDENTIFICATION_UNIQUEID` = "'.addslashes($uniqueId).'" ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    if (mysql_errno($this->db) > 0) return array();
    if (mysql_num_rows($res) <= 0) return array();
    $tmp = mysql_fetch_assoc($res);
    if (empty($tmp)) return array();
    return unserialize($tmp['SERIAL']);
  }/*}}}*/

  function getShortId($shortId)/*{{{*/
  {
    if (empty($shortId)) return array();
    $sql = 'SELECT `SERIAL` FROM `'.$this->dbtable.'` ';
    $sql.= 'WHERE `IDENTIFICATION_SHORTID` = "'.addslashes($shortId).'" ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[] = unserialize($row['SERIAL']);
    }
    return $tmp;
  }/*}}}*/

  function getRefId($refId)/*{{{*/
  {
    if (empty($refId)) return array();
    $sql = 'SELECT `SERIAL` FROM `'.$this->dbtable.'` ';
    $sql.= 'WHERE `IDENTIFICATION_REFERENCEID` = "'.addslashes($refId).'" ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    if (mysql_errno($this->db) > 0) return array();
    if (mysql_num_rows($res) <= 0) return array();
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[] = unserialize($row['SERIAL']);
    }    
    return $tmp;
  }/*}}}*/

  function getOpenPA()/*{{{*/
  {
    // Wenig perfomant
    /*
    $sql = 'SELECT a.`SERIAL` FROM `'.$this->dbtable.'` a
            LEFT JOIN `'.$this->dbtable.'` b 
            ON a.`IDENTIFICATION_REFERENCEID` = b.`IDENTIFICATION_UNIQUEID`
            AND a.`PROCESSING_CODE` LIKE "%.CP.%" 
            WHERE a.`PROCESSING_CODE` LIKE "%.PA.%" 
            AND a.`PROCESSING_RESULT` = "ACK" 
            AND a.`PROCESSING_CODE` NOT LIKE "OT.%" 
            ';
     */
    // besser so
    /*
    $sql = 'SELECT a.`SERIAL`, b.`id` FROM `'.$this->dbtable.'` a
            LEFT JOIN `'.$this->dbtable.'` b 
            ON b.`IDENTIFICATION_REFERENCEID` = a.`IDENTIFICATION_UNIQUEID`
            AND b.`typ` = "CP" 
            WHERE a.`typ` = "PA" 
            AND a.`PROCESSING_RESULT` = "ACK" 
            AND a.`meth` IN ("CC", "DC", "DD", "VA")
            ';
     */
    // viel besser
    $sql = 'SELECT `SERIAL` FROM `'.$this->dbtable.'` 
            WHERE `CAPTURED` = 0
            AND `typ` = "PA"
            AND `PROCESSING_RESULT` = "ACK" 
            AND `meth` IN ("CC", "DC", "DD", "VA")
            '; 
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $tmp = array();
    if (mysql_num_rows($res) <= 0) return $tmp;
    while($row = mysql_fetch_assoc($res)){
      if (!empty($row['id'])) continue;
      $tmp[] = unserialize($row['SERIAL']);
    }
    return $tmp;
  }/*}}}*/

  function doSearch($params)/*{{{*/
  {
    if (empty($params)) return array();
    $sql = 'SELECT `SERIAL` FROM `'.$this->dbtable.'`
            WHERE 1=1 ';
    foreach($params AS $k => $v){
      if (empty($v)) continue;
      $sql.= ' AND `'.addslashes($k).'` LIKE "%'.addslashes($v).'%" ';
    }
    #echo $sql;
    $res = mysql_query($sql, $this->db);
    if (mysql_num_rows($res) <= 0) return array();
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[] = unserialize($row['SERIAL']);
    }
    return $tmp;
  }/*}}}*/

  function convertXML2SERIAL()/*{{{*/
  {
    $sql = 'SELECT `id`,`xml` FROM `'.$this->dbtable.'` ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    while($row = mysql_fetch_assoc($res)){
      $xml = simplexml_load_string($row['xml']);
      $data = $this->getPostFromXML($xml);
      $this->saveSERIAL($row['id'], $data);
    }
    return true;
  }/*}}}*/

  function convertOpenPA()/*{{{*/
  {
    $sql = 'SELECT `IDENTIFICATION_REFERENCEID` FROM `'.$this->dbtable.'` 
            WHERE `typ` = "CP"
            AND `PROCESSING_RESULT` = "ACK" 
            AND `meth` IN ("CC", "DC", "DD", "VA")';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    while($row = mysql_fetch_assoc($res)){
      $sql = 'UPDATE `'.$this->dbtable.'` SET ';
      $sql.= '`CAPTURED` = "1" '; 
      $sql.= 'WHERE `IDENTIFICATION_UNIQUEID` = "'.$row['IDENTIFICATION_REFERENCEID'].'" '; 
      $sql.= 'AND `typ` = "PA" ';
      #echo $sql.'<br>';
      mysql_query($sql, $this->db);
    }
    return true;
  }/*}}}*/

  function convertProcessingCode()/*{{{*/
  {
    $sql = 'SELECT `id`,`PROCESSING_CODE` FROM `'.$this->dbtable.'` ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    while($row = mysql_fetch_assoc($res)){
      $tmp = explode('.', $row['PROCESSING_CODE']);
      $sql = 'UPDATE `'.$this->dbtable.'` SET ';
      $sql.= '`meth` = "'.addslashes($tmp[0]).'", '; 
      $sql.= '`typ` = "'.addslashes($tmp[1]).'" '; 
      $sql.= 'WHERE `id` = "'.$row['id'].'" '; 
      mysql_query($sql, $this->db);
    }
    return true;
  }/*}}}*/

  function saveSERIAL($id, $data)/*{{{*/
  {
    $serial = serialize($data);
    $sql = 'UPDATE `'.$this->dbtable.'` 
            SET `SERIAL` = "'.addslashes($serial).'" 
            WHERE `id` = '.(int)$id;
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    return mysql_query($sql, $this->db);
  }/*}}}*/

  function getSenderByChannel($channel)/*{{{*/
  {
    if (empty($channel)) return false;
    $sql = 'SELECT `SECURITY_SENDER` FROM `'.$this->table_config.'` ';
    $sql.= 'WHERE `TRANSACTION_CHANNEL` = "'.addslashes($channel).'" ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    if (mysql_errno($this->db) > 0) return false;
    if (mysql_num_rows($res) <= 0) return false;
    $tmp = mysql_fetch_assoc($res);
    #echo '<pre>'.print_r($tmp, 1).'</pre>';
    if (empty($tmp)) return array();
    return $tmp['SECURITY_SENDER'];
  }/*}}}*/

  function checkTable($table)/*{{{*/
  {
    $sql = 'SHOW TABLES LIKE "'.$table.'"';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $exists = mysql_num_rows($res)>0;
    if ($exists) $this->setActiveTable($table); // Aktuelle Tabelle wï¿½hlen
    return $exists;
  }/*}}}*/ 

  function createTable($table)/*{{{*/
  {
    $sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `meth` char(2) NOT NULL,
      `typ` char(2) NOT NULL,
      `IDENTIFICATION_UNIQUEID` varchar(32) NOT NULL,
      `IDENTIFICATION_SHORTID` varchar(14) NOT NULL,
      `IDENTIFICATION_TRANSACTIONID` varchar(255) NOT NULL,
      `IDENTIFICATION_REFERENCEID` varchar(32) NOT NULL,
      `PROCESSING_RESULT` varchar(20) NOT NULL,
      `PROCESSING_RETURN_CODE` varchar(11) NOT NULL,
      `PROCESSING_CODE` varchar(11) NOT NULL,
      `TRANSACTION_SOURCE` varchar(10) NOT NULL,
      `TRANSACTION_CHANNEL` varchar(32) NOT NULL,
      `TRANSACTION_RESPONSE` varchar(5) NOT NULL,
      `TRANSACTION_MODE` varchar(15) NOT NULL,
      `CRITERION_RESPONSE_URL` varchar(255) NOT NULL,
      `created` datetime NOT NULL,
      `SERIAL` mediumtext NOT NULL,
      `XML` mediumtext NOT NULL,
      `RESPONSE` mediumtext NOT NULL,
      `CAPTURED` int(1) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `typ` (`typ`),
      KEY `meth` (`meth`),
      KEY `IDENTIFICATION_UNIQUEID` (`IDENTIFICATION_UNIQUEID`),
      KEY `IDENTIFICATION_SHORTID` (`IDENTIFICATION_SHORTID`),
      KEY `IDENTIFICATION_TRANSACTIONID` (`IDENTIFICATION_TRANSACTIONID`),
      KEY `IDENTIFICATION_REFERENCEID` (`IDENTIFICATION_REFERENCEID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    return mysql_query($sql, $this->db);
  }/*}}}*/ 

  function setActiveTable($table)/*{{{*/
  {
    $this->dbtable = $table;
    return $table;
  }/*}}}*/ 

  function doAction($action, $loginData, $trxData)/*{{{*/
  {
    $data = array(
      'SECURITY_SENDER'               => $loginData['SECURITY_SENDER'],
      'TRANSACTION_MODE'              => $loginData['TRANSACTION_MODE'],
      'TRANSACTION_CHANNEL'           => $loginData['TRANSACTION_CHANNEL'],
      'USER_LOGIN'                    => $loginData['USER_LOGIN'],
      'USER_PWD'                      => $loginData['USER_PWD'],
      'IDENTIFICATION_TRANSACTIONID'  => $trxData['txnid'],
      'IDENTIFICATION_REFERENCEID'    => $trxData['uniqueid'],
      'PAYMETHOD'                     => $trxData['paymethod'],
      'AMOUNT'                        => $trxData['amount'],
      'CURRENCY'                      => $trxData['currency'],
      'USAGE'                         => $trxData['usage'], 
      'COMMENT'                       => $trxData['comment'],
      'FRONTEND_LANGUAGE'             => $loginData['FRONTEND_LANGUAGE'],
    );
    if (!empty($trxData['regid'])) $data['REGID'] = $trxData['regid'];
    $reqData = $this->getRequestData($action, $data);
    #echo '<pre>'.print_r($reqData, 1).'</pre>';
    $url = $this->demo_url;
    if ($dat['TRANSATION.MODE'] == 'LIVE') $url = $this->live_url;
    #echo $url;
    $res = $this->doRequest($url, $reqData);
    parse_str($res, $output);
    #echo '<pre>'.print_r($res, 1).'</pre>';
    return $output;
  }/*}}}*/ 

  function getRequestData($action, $dat)/*{{{*/
  {
    $data = array(
      'SECURITY.SENDER'               => $dat['SECURITY_SENDER'],
      'TRANSACTION.MODE'              => $dat['TRANSACTION_MODE'],
      'TRANSACTION.RESPONSE'          => 'SYNC',
      'TRANSACTION.CHANNEL'           => $dat['TRANSACTION_CHANNEL'],
      'USER.LOGIN'                    => $dat['USER_LOGIN'],
      'USER.PWD'                      => $dat['USER_PWD'],
      'IDENTIFICATION.TRANSACTIONID'  => $dat['IDENTIFICATION_TRANSACTIONID'],
      'IDENTIFICATION.REFERENCEID'    => $dat['IDENTIFICATION_REFERENCEID'],
      'CRITERION.COMMENT'             => $dat['COMMENT'],
      'PRESENTATION.AMOUNT'           => $dat['AMOUNT'],
      'PRESENTATION.CURRENCY'         => $dat['CURRENCY'],
      'PRESENTATION.USAGE'            => $dat['USAGE'],
      'FRONTEND.LANGUAGE'             => $dat['FRONTEND_LANGUAGE'],
    );
    switch($action){
    case 'refund':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.RF',
      ));
      break;
    case 'rebill':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.RB',
      ));
      break;
    case 'capture':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.CP',
      ));
      break;
    case 'reservation':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.PA',
        'ACCOUNT.REGISTRATION'          => $dat['REGID'],
      ));
      break;
    case 'debit':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.DB',
        'ACCOUNT.REGISTRATION'          => $dat['REGID'],
      ));
      break;
    case 'reversal':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.RV',
      ));
      break;
    case 'schedule':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.SD',
      ));
      break;
    case 'deschedule':
      $data = array_merge($data, array(
        'PAYMENT.CODE'                  => $dat['PAYMETHOD'].'.DS',
      ));
      break;
    }
    return $data;
  }/*}}}*/ 

  function getLanguage($lang, $external = false, $extPath = NULL)/*{{{*/
  {
    if ($external){
      if (empty($extPath)){
        $this->error = 'Could not load external Language File '.$filename.' from '.$extPath;
        return false;
      } else {
        $extCSV = $this->doRequest($extPath, array());
        #echo '<pre>'.print_r($extCSV, 1).'</pre>';
        if (empty($extCSV) || $this->httpstatus != 200 || strpos($extCSV, '#')===false){
          $this->error = 'External Language File '.$filename.' from '.$extPath.' was empty';
          return false;
        } else {
          $tmp = array();
          $data = explode("\n", $extCSV);
          #$data = str_getcsv($extCSV, '#');
          #echo '<pre>'.print_r($data, 1).'</pre>';
          foreach($data AS $k => $v){
            $p = explode('#', trim($v));
            $tmp[$p[0]] = $p[1];
          }
          return $tmp;
        }
      }
    }
    $filename = dirname(__FILE__).'/lang_'.strtolower($lang).'.csv';
    if (!file_exists($filename)){
      $this->error = 'Could not find Language File '.$filename;
      return false;
    }
    if (($handle = fopen($filename, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, "#")) !== FALSE) {
        #$data = trim($data);
        $tmp[$data[0]] = $data[1];
      }
      fclose($handle);
    } else {
      $this->error = 'Could not load Language File '.$filename;
      return false;
    }

    return $tmp;
  }/*}}}*/ 

  function setSettings($merID, $settings, $channel)/*{{{*/
  {
    if (empty($merID)) {
      $this->error = __FUNCTION__.': No Merchant ID';
      return false;
    }
    if (empty($settings)) {
      $this->error = __FUNCTION__.': No Settings to save';
      return false;
    }
    if (empty($channel)) {
      $this->error = __FUNCTION__.': No Channel to save';
      return false;
    }
    $sql = 'UPDATE `'.$this->table_config.'` SET ';
    foreach($settings AS $k => $v){
      $sql.= ' `'.addslashes($k).'` = "'.addslashes($v).'", ';
    }
    $sql.= '`lastChanged` = NOW() ';
    $sql.= 'WHERE `SECURITY_SENDER` = "'.addslashes($merID).'" 
            AND `TRANSACTION_CHANNEL` = "'.addslashes($channel).'" ';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    return mysql_errno($this->db)==0;
  }/*}}}*/ 

  function newSettings($merID, $channel)/*{{{*/
  {
    if (empty($merID)) {
      $this->error = __FUNCTION__.': No Merchant ID';
      return false;
    }
    if (empty($channel)) {
      $this->error = __FUNCTION__.': No Channel to save';
      return false;
    }
    $sql = 'INSERT INTO `'.$this->table_config.'` SET 
            `created` = NOW(),
            `SECURITY_SENDER` = "'.addslashes($merID).'", 
            `TRANSACTION_CHANNEL` = "'.addslashes($channel).'" 
            ';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    return mysql_errno($this->db)==0;
  }/*}}}*/ 

  function getSettings($merID)/*{{{*/
  {
    if (empty($merID)) {
      $this->error = __FUNCTION__.': No Merchant ID';
      return false;
    }
    $sql = 'SELECT * FROM `'.$this->table_config.'` 
            WHERE `SECURITY_SENDER` = "'.addslashes($merID).'" 
            ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $exists = mysql_num_rows($res)>0;
    if (!$exists) {
      $this->error = 'No Settings Config found for '.$merID;
      return false;
    }
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[$row['TRANSACTION_CHANNEL']] = $row;
      $tmp[$row['TRANSACTION_CHANNEL']]['rates'] = $this->getRatesByOwner($row['id']);
    }
    return $tmp;
  }/*}}}*/ 

  function setRate($owner, $id, $rate)/*{{{*/
  {
    if (empty($owner)) {
      $this->error = __FUNCTION__.': No Owner ID';
      return false;
    }
    if (empty($id)) {
      $this->error = __FUNCTION__.': No ID';
      return false;
    }
    if (empty($rate)) {
      $this->error = __FUNCTION__.': No rate to save';
      return false;
    }
    $sql = 'UPDATE `'.$this->table_rates.'` SET ';
    foreach($rate AS $k => $v){
      $sql.= ' `'.addslashes($k).'` = "'.addslashes($v).'", ';
    }
    $sql.= '`owner` = '.addslashes($owner).' ';
    $sql.= 'WHERE `owner` = "'.addslashes($owner).'" AND `id` = "'.(int)$id.'" ';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    return mysql_errno($this->db)==0;
  }/*}}}*/ 

  function removeRate($owner, $id)/*{{{*/
  {
    if (empty($owner)) {
      $this->error = __FUNCTION__.': No Owner ID';
      return false;
    }
    if (empty($id)) {
      $this->error = __FUNCTION__.': No ID';
      return false;
    }
    $sql = 'DELETE FROM `'.$this->table_rates.'` ';
    $sql.= 'WHERE `owner` = "'.addslashes($owner).'" 
            AND `id` = "'.(int)$id.'" ';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    return mysql_errno($this->db)==0;
  }/*}}}*/ 

  function addRate($owner, $rate)/*{{{*/
  {
    if (empty($owner)) {
      $this->error = __FUNCTION__.': No Owner ID';
      return false;
    }
    if (empty($rate)) {
      $this->error = __FUNCTION__.': No rate to save';
      return false;
    }
    $sql = 'INSERT `'.$this->table_rates.'` SET ';
    foreach($rate AS $k => $v){
      $sql.= ' `'.addslashes($k).'` = "'.addslashes($v).'", ';
    }
    $sql.= '`owner` = '.addslashes($owner).' ';
    #echo $sql.'<br>';
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    return mysql_insert_id($this->db)>0;
  }/*}}}*/ 

  function getRatesByOwner($owner)/*{{{*/
  {
    $sql = 'SELECT * FROM `'.$this->table_rates.'` 
            WHERE `owner` = "'.$owner.'"
            ORDER BY `kind`,`sortorder` ASC
            ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[$row['kind']][$row['id']] = $row;  
    }
    return $tmp;
  }/*}}}*/

  function getConfig($merID, $channel)/*{{{*/
  {
    $sql = 'SELECT * FROM `'.$this->table_config.'` 
            WHERE `SECURITY_SENDER` = "'.addslashes($merID).'" 
            AND `TRANSACTION_CHANNEL` = "'.addslashes($channel).'"
            ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $exists = mysql_num_rows($res)>0;
    if (!$exists) {
      $sql = 'INSERT INTO `'.$this->table_config.'` 
              SET `SECURITY_SENDER` = "'.addslashes($merID).'",
              `TRANSACTION_CHANNEL` = "'.addslashes($channel).'",
              `allowABO` = "0",
              `allowRATE` = "0",
              `created` = NOW()
              ';
      mysql_query($sql, $this->db);
      return $this->getConfig($merID, $channel);
    }
    $row = mysql_fetch_assoc($res);
    return $row;
  }/*}}}*/ 

  function getRates($merID, $channel, $amount = NULL, $currency = 'EUR')/*{{{*/
  {
    if (empty($merID)) {
      $this->error = __FUNCTION__.': Missing merID!';
      return array();
    }
    if (empty($channel)) {
      $this->error = __FUNCTION__.': Missing channel!';
      return array();
    }
    $config = $this->getConfig($merID, $channel);
    $sql = 'SELECT * FROM `'.$this->table_rates.'` 
            WHERE `owner` = "'.$config['id'].'"
            ORDER BY `kind`,`sortorder` ASC
            ';
    #echo $sql;
    $this->sql[__FUNCTION__][] = $sql;
    $res = mysql_query($sql, $this->db);
    $tmp = array();
    while($row = mysql_fetch_assoc($res)){
      $tmp[$row['kind']][$row['id']] = $row;
      if ($amount > 0){

        // Ratenzahlung
        if ($row['kind'] == 'rate'){ 
          $duraInDays = $this->duration2days[$row['durationtype']] * $row['duration'];
          $freqInDays = $this->duration2days[$row['freqtype']] * $row['freq'];
          $efDuration = floor($duraInDays / $freqInDays) + 1; // Plus 1 wegen der Initrate
          $tmp[$row['kind']][$row['id']]['duraInDays'] = $duraInDays;
          $tmp[$row['kind']][$row['id']]['freqInDays'] = $freqInDays;
          #echo $duraInDays.' duraInDays '.$freqInDays.' freqInDays '.$efDuration.'<br>';
          if ($row['feetype'] == 'percent'){
            $rate_amount = $amount + ($amount / 100) * ($row['fee'] / 100);
          } else {
            $rate_amount = $amount + ($row['fee'] / 100);
          }
          $rate_amount = sprintf('%1.2f', $rate_amount); // Da sonst zu viele Nachkommastellen entstehen kï¿½nnen
          $piece = floor($rate_amount / $efDuration);
          $rest = $piece + ($rate_amount - ($piece * $efDuration));
          #echo $rate_amount.' % '.$efDuration.'='.$piece.' rest: '.$rest.'<br>';

        // Anzahlung
        } else if ($row['kind'] == 'deposit'){
          $duraInDays = $this->duration2days[$row['durationtype']] * $row['duration'];
          $tmp[$row['kind']][$row['id']]['duraInDays'] = $duraInDays;
          $tmp[$row['kind']][$row['id']]['freqInDays'] = 0;
          #echo $duraInDays.' duraInDays '.$freqInDays.' freqInDays '.$efDuration.'<br>';
          if ($row['feetype'] == 'percent'){
            $rest = ($amount / 100) * ($row['fee'] / 100); // Erste Zahlung
            $rest = sprintf('%1.2f', $rest); // Da sonst zu viele Nachkommastellen entstehen kï¿½nnen
            $piece = $amount - $rest; // Zweite Zahlung
          } else {
            $rest = ($row['fee'] / 100); // Erste Zahlung
            $rest = sprintf('%1.2f', $rest); // Da sonst zu viele Nachkommastellen entstehen kï¿½nnen
            $piece = $amount - $rest; // Zweite Zahlung
          }
          $rate_amount = $amount;
          #echo $rate_amount.' % '.$efDuration.'='.$piece.' rest: '.$rest.'<br>';
        // Abozahlung
        } else if ($row['kind'] == 'abo'){
          $freqInDays = $this->duration2days[$row['freqtype']] * $row['freq'];
          $tmp[$row['kind']][$row['id']]['duraInDays'] = 0;
          $tmp[$row['kind']][$row['id']]['freqInDays'] = $freqInDays;
          if ($row['feetype'] == 'percent'){
            $rate_amount = $amount + ($amount / 100) * ($row['fee'] / 100);
          } else {
            $rate_amount = $amount + ($row['fee'] / 100);
          }
          $rate_amount = sprintf('%1.2f', $rate_amount); // Da sonst zu viele Nachkommastellen entstehen kï¿½nnen
          #echo $rate_amount.'<br>';
        }
        $tmp[$row['kind']][$row['id']]['rate_amount'] = $piece;
        $tmp[$row['kind']][$row['id']]['rate_first'] = $rest;
        $tmp[$row['kind']][$row['id']]['currency'] = $currency;
        $tmp[$row['kind']][$row['id']]['amount'] = $amount;
        $tmp[$row['kind']][$row['id']]['newamount'] = $rate_amount;
      }
    }
    return $tmp;
  }/*}}}*/ 
}

function x($text)/*{{{*/
{
  if (!empty($_SESSION['language_cache'][$text])){
    return $_SESSION['language_cache'][$text];
  }
  if (isset($_SESSION['language_cache'][$text])) return $text; // Da schon Eintrag vorhanden, braucht es nicht neu in die Datei
  $filename = 'lang_'.$_SESSION['actual_language'].'.csv';
  if ($handle = fopen($filename, 'a')) {
    if ($_SESSION['actual_language'] == 'de'){
      $somecontent = $text.'#'."\n";
    } else {
      $somecontent = '#'.$text."\n";
    }
    fwrite($handle, $somecontent);
    fclose($handle);
  }  
  return $text;
}/*}}}*/
?>
