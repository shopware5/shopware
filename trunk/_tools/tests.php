<?php


//phpinfo();

//print_r($test->parse());
/*
$blocks = array();
$file = '$temp=split("=",$temp,2);';
$file .= "\n"." preg_split(c);";
preg_match_all("/(call_user_method|call_user_method_array|define_syslos_variables|dl|ereg|ereg_replace|eregi|eregi_replace|session_register|session_unregister|session_is_registered|set_socking_blocking|[^\_]split|spliti)\((.*)\)/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
print_r($result);exit;					
*/
?>
<html>
<head>
<style>
body {
font-family: Arial;
font-size: 14px; 
}
</style>
<title>Shopware Template Testsuite</title>
</head>
<body>

<div style="width:350px;height:100%;border:1px solid;float:left;padding:0 0 0 0">
<form method="GET">
<input type="hidden" name="task" value="freeSearch">
<input type="hidden" name="mode" value="storefront">
<input type="text" name="searchTerm" value="<?php echo $_REQUEST["searchTerm"] ?>">
<input type="submit" value="LOS">
</form>
<h2>Tasks Template</h2>
	<ul>
	<li><a href="?task=changes">Finde offene Tasks</a></li>
	<li><a href="?task=superglobals">Finde Templates mit Verwendung von Super_Globals</a></li>
	<li><a href="?task=config">Alte $_CONFIG Syntax</a></li>
	<li><a href="?task=script">Finde Javascript Tags</a></li>
	<li><a href="?task=literal">Finde Literal Tags</a></li>
	<li><a href="?task=css">Finde Inline-CSS</a></li>
	<li><a href="?task=style">Finde Style Tags</a></li>
	<li><a href="?task=links">Finde alte Linksyntax</a></li>
	<li><a href="?task=includes">Prüfe Includes</a></li>
	<li><a href="?task=img">Finde IMG-Tags</a></li>
	<li><a href="?task=block">Prüfe Block-Konsistenz</a></li>
	<li><a href="?task=snippet">Prüfe Snippet-Konsistenz</a></li>
	<li><a href="?task=htmldoc">Prüfe HTML-Kommentare</a></li>
	<li><a href="?task=#">Suche Snippets ohne DB-Eintrag</a></li>
	<li><a href="?task=snippetcheck">Suche nicht mehr benötigte Snippets</a></li>
	<li><a href="?task=#">Suche leere Snippets</a></li>
	<li><a href="?task=css_1">Zeige alle definierten Klassen/IDs</a></li>
	<li><a href="?task=css_2">Show CSS Selectors which are not defined</a></li>
	<li><a href="?task=css_3">Show CSS Selectors which are not used in template</a></li>
	</ul>
	<h2>Build Template/Wiki-Doku</h2>
	<ul>
	<li><a href="?task=#">Start</a></li>
	</ul>
	<h2>Tasks Code</h2>
	<ul>
	<li><a href="?task=deprecated&mode=storefront">Suche PHP 5.3 deprecated Syntax</a></li>
	<li><a href="?task=mysql&mode=storefront">Suche mysql_query / mysql_real_escape_string Frontend</a></li>
	<li><a href="?task=addslashes&mode=storefront">Suche addslashes Frontend</a></li>
	<li><a href="?task=queries&mode=storefront">Suche potentiell unsichere Queries</a></li>
	<li><a href="?task=#">Suche Backend-Dateien ohne Auth-Prüfung</a></li>
	<li><a href="?task=oldlinks&mode=storefront">Alte Links</a></li>
	<li><a href="?task=oldsystem&mode=storefront">Alte sSystem Syntax</a></li>
	<li><a href="?task=oldlinks2&mode=storefront">Falsche Pfade</a></li>
	<li><a href="?task=oldsmarty&mode=storefront">Alte Smarty Resourcen</a></li>
	</ul>
</div>
<div style="margin-left:350px;width:950px;height:100%;border:1px solid;overflow:auto">
<?php

if ($_GET["mode"]=="storefront"){
	storefrontTask($_GET["task"] ? $_GET["task"] : "mysql"); 
}elseif ($_GET["mode"]=="backend"){
	
}else {
	templateTask($_GET["task"] ? $_GET["task"] : "changes") ;
}
?>
</div>
</body>
</html>
<?php
function storefrontTask($task=""){
	if (!empty($_REQUEST["searchTerm"])){
		$ite=new RecursiveDirectoryIterator(".");
	}else {
		$ite=new RecursiveDirectoryIterator("engine/");
	}
	$bytestotal=0;
	$nbfiles=0;
	if ($task=="deprecated"){
		$ignore = array("engine\/Enlight\/Vendor","engine\/tabs","engine\/core\/js","engine\/core\/img","engine\/core\/css","engine\/core\/elements","engine\/core\/css","engine\/plugins","engine\/tabs");
	}else {
		$ignore = array("adodb","engine\/Enlight\/Vendor","engine\/index.php","engine\/auth.php","engine\/modules","engine\/tabs","engine\/core\/ajax","engine\/core\/php","engine\/core\/js","engine\/core\/img","engine\/core\/css","engine\/core\/elements","engine\/core\/css","engine\/plugins","engine\/tabs");
	}
	if ($task=="oldlinks" || $task=="oldsystem" || $task=="oldsmarty" || $task = "freeSearch"){
		$ignore = array();
	}
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
		$found = false;
		if (preg_match("/\.svn/",$filename)){
			continue;
		}
		if (!preg_match("/\.php|\.tpl/",$filename)){
			continue;
		}
		foreach ($ignore as $v){
			if (preg_match("/$v/",$filename)){
				$found = true;break;
			}
		}
		if ($found) continue;
		// Investigate file
		$file = file_get_contents($filename);
		switch ($task){
			case "freeSearch";
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/{$_REQUEST["searchTerm"]}/Us",$file,$blocks);
					
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
			case "oldsmarty";
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(.*)fetch\(\"db\:(.*);/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "oldlinks2";
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/core\/elements(.*)\n/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "oldlinks";
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/\/sViewport=(.*)\,/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "oldsystem":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/class\/sSystem.php(.*)\n/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
					
					break;
				case "deprecated":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(call_user_method|call_user_method_array|define_syslos_variables|dl|ereg|ereg_replace|eregi|eregi_replace|session_register|session_unregister|session_is_registered|set_socking_blocking|[^\_]split|spliti)\((.*)\)/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "mysql":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(mysql_query|mysql_real_escape_string)\((.*)\)/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "addslashes":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(addslashes)\((.*)\)/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
				case "queries":
					$blocks = array();
					$result = array();
					//$/file = "\"SELECT * FROM A WHERE B = \$A \"";
					//$file = "{\$_POST.test} {\$_GET.te}";
					$file = str_replace("\n","",$file);$file = str_replace("\r","",$file);
					$file = str_replace("	","",$file);
					
					preg_match_all("/\"(SELECT)(.*)\\$(.*)\";?/U",$file,$blocks);
					
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					//print_r($blocks);
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
				break;
		}
	}
}
function templateTask($task=""){
	$ite=new RecursiveDirectoryIterator("templates/_default/frontend");
	$bytestotal=0;
	$nbfiles=0;
	if ($task=="css_1" || $task=="css_2" || $task == "css_3"){
		foreach (glob("templates/default/resources/styles/*.css") as $filename){
			$data = file_get_contents($filename);
			$result = array();
			preg_match_all("/(\.|\#)([A-Za-z_0-9]{2,})( |{)/U",$data,$result);
			
			foreach ($result[0] as $style){
				$style = str_replace("{","",$style);
				$stylesheets[trim($style)]["files"][$filename] = 1;
			}
						
		}
		
		if ($task=="css_1"){
			echo "
			<strong>CSS-Info</strong>
			<ul>
			";
			
			
			foreach ($stylesheets as $key => $files){
				echo "<li>".$key."</li><ul>";
				foreach ($files["files"] as $file => $v){
					echo "<li>$file</li>";
				}
				echo "</ul>";
			}
			echo "</ul>";
		}

	}
	
	foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	    /*$filesize=$cur->getSize();
	    $bytestotal+=$filesize;
	    $nbfiles++;
	    echo "$filename => $filesize\n";
		*/
	    $file = file_get_contents($filename);
		if (preg_match("/\.svn/",$filename)){
			
		}else {
			
			switch ($task){
				case "changes":
					$blocks = array();
					$result = array();
					preg_match_all("/(\{\* c?hange(.*) \*\})/U",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
					break;
				case "superglobals":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(\_POST|\_SERVER|\_GET|\_COOKIE)(.*)\}/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
					break;
				case "config":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					
					preg_match_all("/sConfig(.*)\}/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					if (!empty($result)){
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".$row."</li>";
						}
						echo "</ul>";
					}
					break;
				case "script":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/\<script(.*)\>(.*)\<\/script\>/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "literal":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/\{literal(.*)\}(.*)\{\/literal\}/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "css":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/style\=\"(.*)\"/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case $task == "css_2" || $task == "css_3":
					
					$blocks = array();
					if ($task != "css_3"){
						$result = array();
					}
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/class\=\"(.*)\"/Us",$file,$blocks);
					
					foreach ($blocks[1] as $block){
						if (!empty($block)){
							$temp = explode(" ",$block);
							if (!empty($temp) && is_array($temp)){
								foreach ($temp as $block) $result[".".$block] = 1;
							}else {
								$result[".".$block] = 1;
							}
						}
					}
					preg_match_all("/id\=\"(.*)\"/Us",$file,$blocks);
					
					foreach ($blocks[1] as $block){
						if (!empty($block)){
							$temp = explode(" ",$block);
							if (!empty($temp) && is_array($temp)){
								foreach ($temp as $block) $result["#".$block] = 1;
							}else {
								$result["#".$block] = 1;
							}
						}
					}
					
					if ($task=="css_2"){
						if (!empty($result)){
							//print_r($result);
							echo "
							<strong>$filename</strong>
							<ul>
							";
							
							foreach ($result as $row => $no){
								if (!isset($stylesheets[$row])){
									$style = "style='color:#F00'";
								}else {
									$style = "";
								}
								echo "<li $style>".nl2br(htmlspecialchars(($row)))."</li>";
							}
							echo "</ul>";
						}
					}
					
					break;
				case "style":
					
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/\<style(.*)\>(.*)\<\/style\>/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "links":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/(sViewport\=(.*)\")|(sBase(.*)\")/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "includes":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/{include file=('|\")(.*)('|\")(.*)}/U",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[2] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							
							$style = !is_file("templates/default/".$row) ? "style=\"color:#F00\"" : "";
							
							echo "<li $style>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "img":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/\<img (.*)\>/Us",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "block":
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/{block name=(.*) ?}/U",$file,$blocks);
					//print_r($blocks);
					foreach ($blocks[1] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						foreach ($result as $row){
							echo "<li>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				case "snippet":
					
					$blocks = array();
					$result = array();
					//$file = "{\$_POST.test} {\$_GET.te}";
					preg_match_all("/{(s|se) name=(.*)}(.*){\/(s|se)}/U",$file,$blocks);
					
					
					foreach ($blocks[0] as $block){
						if (!empty($block))	$result[] = $block;
					}
					
					if (!empty($result)){
						//print_r($result);
						echo "
						<strong>$filename</strong>
						<ul>
						";
						
						
						foreach ($result as $row){
							echo "<li $style>".nl2br(htmlspecialchars(($row)))."</li>";
						}
						echo "</ul>";
					}
					break;
				
			}
		}
		
	    /*if (!strpos(".svn",$filename)){
	    	echo $filename."\n";
	    }*/
	}
	if ($task=="css_3"){
		//echo count($stylesheets);
		//print_r($result);
		
		foreach ($stylesheets as $selector => $no){
			//echo $selector."\n"."(".$result[$selector].")"; 
			if (isset($result[$selector])){
				unset($stylesheets[$selector]);
			}
		}
		//echo "#".count($stylesheets);
		echo "<strong>Selectors not found in template</strong><ul>";
		foreach ($stylesheets as $selector => $no){
			echo "<li>$selector</li>";
			echo "<ul>";
			foreach ($no["files"] as  $file => $n){
				echo "<li>$file</li>";
			}
			echo "</ul>";
		}echo "</ul>";
	}
}
?>