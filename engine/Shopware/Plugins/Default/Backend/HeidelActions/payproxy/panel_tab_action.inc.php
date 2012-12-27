<?php if (!defined('isHOP')) die();?><?php
/*{{{Tabs Action */
$tabIds = array(
  'refund'      => '0',
  'rebill'      => '1',
  'capture'     => '2',
  'reservation' => '3',
  'debit'       => '4',
  'reversal'    => '5',
  'deschedule'  => '6',
  'schedule'    => '7',
);

$booking = $refbookings = array();
if (!empty($_SESSION['case']['uid'])){
  $booking = $hp->getUniqueId($_SESSION['case']['uid']);
  $tmp = explode('.', $booking['PAYMENT_CODE']);
  $booking['CODES']['method'] = $tmp[0];
  $booking['CODES']['type'] = $tmp[1];
  #echo '<pre>'.print_r($booking, 1).'</pre>';
  $refbookings = $hp->getRefId($_SESSION['case']['uid']);
  #echo '<pre>'.print_r($refbookings, 1).'</pre>';
  
  #echo '<pre>'.print_r($hp, 1).'</pre>';
}

// Welche Buttons dürfen wann angezeigt werden?
$allowedTabs = array();
switch($booking['CODES']['type']){
  case 'DB':
    $allowedTabs[] = '0'; // refund
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '1'; // rebill
    if (in_array($booking['CODES']['method'], array('DD'))) $allowedTabs[] = '5'; // reversal
    break;
  case 'PA':
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD', 'VA'))) $allowedTabs[] = '2'; // capture
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '1'; // rebill
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '5'; // reversal
    break;
  case 'RG':
    $allowedTabs[] = '3'; // reservation
    $allowedTabs[] = '4'; // debit
    break;
  case 'RC':
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '0'; // refund
    break;
  case 'RB':
    $allowedTabs[] = '0'; // refund
    break;
  case 'RF':
    break;
  case 'CP':
    $allowedTabs[] = '0'; // refund
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '1'; // rebill
    break;
  case 'SD':
    if (in_array($booking['CODES']['method'], array('CC', 'DC', 'DD'))) $allowedTabs[] = '6'; // Endschedule
    break;
  case 'DS':
    break;
}
sort($allowedTabs);

// Tabs deaktivieren die nicht passen
$disallowedTabs = array();
$tabHeads = '';
foreach($tabIds AS $k => $v){
  if (!in_array($v, $allowedTabs)) {
    $disallowedTabs[] = $v;
    $tabHeads.= '$("#tabshead-'.$v.'").addClass("ui-state-disabled");'."\n";
  }
}
// Wenn Buchung nicht ACK dann keine Action
if ($booking['PROCESSING_RESULT'] != 'ACK'){
  $disallowedTabs = $tabIds; // Alle nicht erlauben
}

$displayTabs = 'block';
$tabDisabled = 'disabled: ';
if (count($disallowedTabs) < count($tabIds)){
  $tabDisabled.= '['.implode(',', $disallowedTabs).']';
} else {
  $tabDisabled.= 'true';
  $displayTabs = 'none';
}
$tabDisabled.= ',';
#echo '<pre>'.print_r($disallowedTabs, 1).'</pre>';

// Ersten aktiven Tab auswählen
$i=0;
while(!in_array($i, $allowedTabs) && $i<10) $i++;
$tabSelected = 'active: '.$i.',';

// zuletzt abgeschickten Tab wieder herstellen
$displayActions = 'none';
if (!empty($_POST['submit_action'])){
  $displayActions = 'block';
  $tabSelected = 'active: '.$tabIds[key($_POST['submit_action'])].',';
}

$dialogColor = '000';
if ($error) $dialogColor = 'f00';

$confirm = 1;
/*}}}*/
?>
