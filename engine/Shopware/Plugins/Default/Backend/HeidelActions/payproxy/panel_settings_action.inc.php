<?php if (!defined('isHOP')) die();?><?php
$channel = '';
if (!empty($_GET['ch'])) $channel = $_GET['ch'];
if ($_GET['show'] == 'settings'){
  if (isset($_POST['submit_settings'])){
    $settings = array(
      'allowABO'      => $_POST['settings']['allowABO'],
      'allowRATE'     => $_POST['settings']['allowRATE'],
      'allowDEPOSIT'  => $_POST['settings']['allowDEPOSIT'],
    );
    $res = $hp->setSettings($_SESSION['loginData']['SECURITY_SENDER'], $settings, $channel);
    if ($res){
      $owner = $_POST['oid'];
      foreach(array('abo', 'rate', 'deposit') AS $kind){
        if (!empty($_POST['settings'][$kind])){
          foreach($_POST['settings'][$kind] AS $k => $v){
            $v['fee'] = $v['fee_sign'].$v['fee_euro'].$v['fee_cent'];
            unset($v['fee_sign']);
            unset($v['fee_euro']);
            unset($v['fee_cent']);
            if ($v['delete']==1){
              $res = $hp->removeRate($owner, $k);
            } else {
              $res = $hp->setRate($owner, $k, $v);
            }
            if (!$res){
              $error = true;
              $msg.= 'Error saving '.$kind.' SET '.$k.'<br>';
              #echo $hp->error.'<br>';
            }
          }
        }
        if (!empty($_POST['new_settings'][$kind])){
          if (!empty($_POST['new_settings']['activate']) && in_array($kind, $_POST['new_settings']['activate'])){
            $v = $_POST['new_settings'][$kind];
            $v['fee'] = $v['fee_sign'].$v['fee_euro'].$v['fee_cent'];
            $v['kind'] = $kind;
            unset($v['fee_sign']);
            unset($v['fee_euro']);
            unset($v['fee_cent']);
            $res = $hp->addRate($owner, $v);
            if (!$res){
              $error = true;
              $msg.= 'Error adding '.$kind.' SET<br>';
              #echo $hp->error.'<br>';
            }
          }
        }
      }
      if (!$error){
        $msg = x('Setting have been saved successful.');
      }
    } else {
      $msg.= 'Error saving settings.';
    }
  }
  // Einstellungen laden
  $settings = $hp->getSettings($_SESSION['loginData']['SECURITY_SENDER']);
  #echo '<pre>'.print_r($settings, 1).'</pre>';
  if (empty($settings)){ // Wenn noch keine Settings, dann neu anlegen
    $res = $hp->newSettings($_SESSION['loginData']['SECURITY_SENDER'], $_SESSION['loginData']['TRANSACTION_CHANNEL']);
    if ($res){
      $settings = $hp->getSettings($_SESSION['loginData']['SECURITY_SENDER']);
    }
  }
}
?>
