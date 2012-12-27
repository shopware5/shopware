<?php
$originalReportLevel = error_reporting();
error_reporting(0);

/*{{{Validierung*/
/*
if (!empty($_GET['token'])){
  $res = preg_match('#[^a-zA-Z0-9]#', $_GET['token']);
  #var_dump($res);
  if ($res === false || $res > 0){
    echo 'INVALID TOKEN!!';
    exit(); 
  }
  session_id($_GET['token']);
}
session_start();
*/

if ($_SESSION['Shopware']['Auth']->active != 1 || !defined('heidelpay_auth')){
  die('Invalid request'); // Kein User eingeloggt.
}

if (empty($_SESSION['loginData'])){
  echo 'TOKEN EXPIRED!';
  exit();
}

if (empty($_GET['show']) && !empty($_GET['uid'])){
  if (empty($_GET['uid'])){
    echo 'INVALID UID!';
    exit();  
  }
  $res = preg_match('#[^a-fhA-FH0-9]#', $_GET['uid']);
  #var_dump($res);
  if ($res === false || $res > 0){
    echo 'INVALID UID!!';
    exit();  
  } 
  if (strlen($_GET['uid']) != 32){
    echo 'INVALID UID!!!';
    exit();  
  } 
  $_SESSION['case']['uid'] = $_GET['uid'];
}
/*}}}*/

#print_r($_SESSION);
#echo $_SESSION['Shopware']['Auth']->active;

// Security Define for no direct access do all .inc files
define('isHOP', 1);

#echo '<pre>'.print_r($_SESSION, 1).'</pre>';
require_once('class.heidelpay.php');
$hp = new heidelpay();
$hp->setActiveTable($_SESSION['loginData']['SECURITY_SENDER']); // Aktuelle Tabelle w�hlen
/*{{{Sprach Cache*/
#unset($_SESSION['language_cache']);
if (empty($_SESSION['language_cache']) || (!empty($_GET['lang']) && $_GET['lang'] != $_SESSION['actual_language']) || !empty($_SESSION['loginData']['FRONTEND_LANGUAGE_PATH'])){
  #echo 'Lang loaded...';
  $lang = 'de';
  if (!empty($_SESSION['loginData']['FRONTEND_LANGUAGE']) && empty($_GET['lang'])) {
    $lang = strtolower($_SESSION['loginData']['FRONTEND_LANGUAGE']);
  }
  if (!empty($_GET['lang'])) $lang = $_GET['lang'];
  $externalLang = false;
  /*
  if (!empty($_SESSION['loginData']['FRONTEND_LANGUAGE_PATH'])){
    $externalLang = true;
    $extPath = $_SESSION['loginData']['FRONTEND_LANGUAGE_PATH'];
  }
  */
  $lg = $hp->getLanguage($lang, $externalLang, $extPath);
  if (!$lg){
    $externalLang = false;
    unset($_SESSION['loginData']['FRONTEND_LANGUAGE_PATH']);
    $lang = 'de';
    $lg = $hp->getLanguage($lang); // Default Language
  } 
  $_SESSION['language_cache'] = $lg;
  $_SESSION['actual_language'] = $lang;
  $_SESSION['loginData']['FRONTEND_LANGUAGE'] = $_SESSION['actual_language'];
  #echo $hp->error;  
  #echo '<pre>LG:'.print_r($_SESSION['language_cache'], 1).'</pre>';
}
if (empty($_SESSION['loginData']['FRONTEND_LANGUAGE'])){
  $_SESSION['loginData']['FRONTEND_LANGUAGE'] = $_SESSION['actual_language'];
}
/*}}}*/
/*{{{Link Query*/
$query = '?token='.$_GET['token'];
$queryform = $query.'&uid='.$_GET['uid'];
$queryshow = $query.'&uid='.$_GET['uid'];
if (!empty($_GET['act'])){
  $query.= '&act='.$_GET['act'];
  $queryform.= '&act='.$_GET['act'];
  $queryshow.= '&act='.$_GET['act'];
}
if (!empty($_GET['show'])){
  $queryshow.= '&show='.$_GET['show'];
}
$query.= '&';
$queryform.= '&';
$queryshow.= '&';
/*}}}*/
$msg = '';
$error = false;
$forceRelaod = false;
$openPAs = $searchResult = array();

#echo '<pre>'.print_r($_SESSION, 1).'</pre>';
#echo '<pre>'.print_r($_POST, 1).'</pre>';
$extStyle = '';
if (!empty($_SESSION['loginData']['FRONTEND_CSS_PATH'])) $extStyle = $_SESSION['loginData']['FRONTEND_CSS_PATH'];

if ($_GET['show'] == 'open_pa'){
  // Offene PAs ermitteln, die noch keinen CP haben
  $openPAs = $hp->getOpenPA();
  #echo '<pre>'.print_r($openPAs, 1).'</pre>';
}

require_once('panel_settings_action.inc.php');

if ($_GET['show'] == 'search'){
  // Suche
  if (!empty($_POST['submit_search'])) $_SESSION['lastSearch'] = array();
  #echo '<pre>'.print_r($_POST, 1).'</pre>';
  if (!empty($_POST['search'])) $_SESSION['lastSearch'] = $_POST['search'];
  #echo '<pre>'.print_r($_SESSION['lastSearch'], 1).'</pre>';
  $searchResult = $hp->doSearch($_SESSION['lastSearch']);
  #echo '<pre>'.print_r($searchResult, 1).'</pre>';
}

$imgReload = base64_encode(file_get_contents(dirname(__FILE__) . '/reload.png'));
$imgDE = base64_encode(file_get_contents(dirname(__FILE__) . '/de.jpg'));
$imgEN = base64_encode(file_get_contents(dirname(__FILE__) . '/en.jpg'));

require_once('panel_submit_action.inc.php');
require_once('panel_tab_action.inc.php');
?><html>
<head>

<meta charset="utf-8">

<?php
require_once('panel_javascripts.inc.php');
require_once('panel_styles.inc.php');
?>

</head>
<body marginheight="0" marginwidth="0" topmargin="0" leftmargin="0">

<?php if (empty($_GET['act'])){?>
<div class="togglerMenu">
  <div id="effectMenu" class="ui-state-content ui-corner">
    <a href="#" id="button_search" class="ui-state-default ui-corner-all" style="float: left;"><?php echo x('Search')?></a>    
    <a href="<?php echo $query?>show=open_pa" id="button_openpa" class="ui-state-default ui-corner-all" style="float: left;"><?php echo x('Open PA')?></a>
    <!--<a href="<?php echo $query?>show=settings" id="button_settings" class="ui-state-default ui-corner-all" style="float: left;"><?php echo x('Settings')?></a>-->
  </div>
  
  <div id="langswitch">
  <a href="#" id="button_menu" class="ui-state-default ui-corner-bottom" xstyle="width: 30px"><?php echo x('Menu')?></a>
  <a href="<?php echo $queryshow?>" class="ui-state-default ui-corner-bottom" style="height: 22px; width: 30px; float: right; text-align: center; padding-top: 3px" title="<?php echo x('Reload')?>"><img src="data:image/png;base64,<?php echo $imgReload?>"></a>
  <?php if (!$externalLang){?>
  <a href="<?php echo $queryshow?>lang=de" class="ui-state-default ui-corner-bottom" style="height: 22px; width: 30px; float: right; text-align: center; padding-top: 3px"><img src="data:image/png;base64,<?php echo $imgDE?>"></a>
  <a href="<?php echo $queryshow?>lang=en" class="ui-state-default ui-corner-bottom" style="height: 22px; width: 30px; float: right; text-align: center; padding-top: 3px"><img src="data:image/png;base64,<?php echo $imgEN?>"></a>
  <?php }?>
  </div>
</div>
<?php }?>

<div id="dialog" title="<?php echo x('Message')?>"><p><?php echo $msg;?></p></div>
<div id="confirmdialog" title="<?php echo x('Alert!')?>"><p><?php echo $confirmdialogmsg;?><br><?php echo x('Are you sure ?')?></p></div>

<div class="togglerSearch">
  <div id="effectSearch" class="ui-widget-content ui-corner-all">
      <table id="search"><form method="post" action="<?php echo $queryform?>show=search">
      <tr><td class="td_title"><?php echo x('Name')?>:</td><td><input type="text" name="search[SERIAL]" value="" size="38"></td></tr>
      <tr><td class="td_title"><?php echo x('SHORT-ID')?>:</td><td><input type="text" name="search[IDENTIFICATION_SHORTID]" value="" size="38"></td></tr>
      <tr><td class="td_title"><?php echo x('Unique ID')?>:</td><td><input type="text" name="search[IDENTIFICATION_UNIQUEID]" value="" size="38"></td></tr>
      <tr><td class="td_title"><?php echo x('TXN-ID')?>:</td><td><input type="text" name="search[IDENTIFICATION_TRANSACTIONID]" value="" size="38"></td></tr>
      <tr><td class="td_title"><?php echo x('Result')?>:</td><td><input type="text" name="search[PROCESSING_RESULT]" value="" size="5" maxlength="3"></td></tr>
      <tr><td class="td_title"><?php echo x('Meth')?>. / <?php echo x('Type')?>:</td><td><input type="text" name="search[meth]" value="" size="5" maxlength="2">.<input type="text" name="search[typ]" value="" size="5" maxlength="2"></td></tr>
      <tr><td class="td_title"><?php echo x('Action')?>:</td><td><input type="submit" name="submit_search" value="<?php echo x('Search')?>"></td></tr>
      </form></table>
  </div>
</div>

<?php 
if (!empty($settings)){ 
  require_once('panel_settings.inc.php');
} else if (!empty($searchResult)){ 
  require_once('panel_searchresults.inc.php');
} else if (!empty($openPAs)){
  require_once('panel_openpas.inc.php');
} else {
  require_once('panel_show.inc.php');
}
?>
<br>
</body>
</html>
<?php 
error_reporting($originalReportLevel);
?>
