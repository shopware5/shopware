<?php if (!defined('isHOP')) die();?><?php
/*{{{Submit Action*/
if ($_POST['submit_action']){
  $action = key($_POST['submit_action']);
  $loginData = $_SESSION['loginData'];
  $trxData = $_POST[$action];
  if ($trxData['amount'] <= 0){
    $msg.= x('Amount needs to be greater than 0.').'<br>';
    $error = true;
  } 
  if (!in_array(strtoupper($trxData['currency']), $hp->allowedCcardCurrencies)){
    $msg.= x('Unknown currency.').'<br>';
    $error = true;
  } 
  if (empty($trxData['usage'])){
    $msg.= x('Usage is mandatory.').'<br>';
    $error = true;
  } 
  if (empty($trxData['txnid'])){
    $msg.= x('TXN ID is mandatory.').'<br>';
    $error = true;
  } 
  if (!$error){
    $res = $hp->doAction($action, $loginData, $trxData);
    #echo '<pre>'.print_r($res, 1).'</pre>';
    if ($res['PROCESSING_RESULT'] == 'ACK'){
      $msg.= preg_replace('/{SHORTID}/', $res['IDENTIFICATION_SHORTID'], x('Booking {SHORTID} was successfull.')).'<br>';
      $forceRelaod = true;
    } else {
      $msg.= x('Booking failed.').'<br>';
    }
    $msg.= x('Reason').': '.htmlentities(stripslashes($res['PROCESSING_RETURN']));
  }
}/*}}}*/
?>
