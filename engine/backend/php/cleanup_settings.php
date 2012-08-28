<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("PERMISSION DENIED");
}

include("cleanup.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../backend/js/mootools.js"></script>
<link href="../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
</head>
<style>
td {
font-size:10px
}
</style>
<body>
<?php
if ($_POST["sStartCleanup"]){
?>
<script>
parent.parent.Growl("Datenbank-Bereinigung wurde durchgeführt");
</script>
<?php
}
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]?>">
<input type="hidden" name="sStartCleanup" value="1">
<fieldset>
<legend>Datenbank-Bereinigung</legend>
Sie können die Bereinigungsfunktion in regelmäßigen Abständen ausführen um sicherzustellen, dass alle Datenbank-Verknüpfungen weiterhin aktuell sind.
Neben der reinen Datenbank-Überprüfung und Optimierung, werden auch überflüssige Artikelbilder entfernt.
<strong>
Bevor Sie fehlerhafte Einträge löschen lassen, sollten Sie in jedem Fall ein Datenbank-Backup durchführen.
</strong>
Das Bereinigungsscript kann auch via Cronjob automatisch durchgeführt werden. Weitere Informationen dazu erhalten Sie im Wiki.<br /><br />
<ul>
<li>
<label>Bericht an eMail verschicken:</label>
<input class="w200" style="height:25px;width:280px" maxlength="40" value="<?php echo $sCore->sCONFIG["sMAIL"] ?>" name="sMailReport">
</li>
<li class="clear"></li>
<li>
<label>Fehlerhafte Einträge löschen:</label>
<input type="checkbox" value="1" name="sDelete">
</li>
<li class="clear"></li>
<li>
		<div class="buttons" id="div">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Ausführen</div>
	        </button>
	       </li>
	      </ul>
		</div>
		</li>
</ul>
</fieldset>
</form>
		
<?php
if ($_POST["sStartCleanup"]){
// Shopware Cleanup-Functions
$sCleanup = new sCleaner($sCore);
if (!empty($_POST["sDelete"])){
	$sCleanup->sRealDelete = true;
}
$sCleanup->sCheckRelations();
$sCleanup->sCheckCategories();
$sCleanup->sCheckPrices();
$sCleanup->sCheckImages();
$sCleanup->sCheckTranslations();
$sCleanup->sClear();
$sCleanup->sOptimize();

foreach ($sCleanup->reporting as $message){
	$body .= $message."\n";
}
if (!empty($_POST["sMailReport"])){
	mail($_POST["sMailReport"],"{$_SERVER['HTTP_HOST']} - Datenbank-Bereinigung Log",$body);
}


echo "<table border=1 cellpadding=2 cellspacing=3>";
foreach ($sCleanup->reporting as $message){
	echo "<tr><td>$message</td></tr>";
}
echo "</table>";
}
?>
</body>
</html>