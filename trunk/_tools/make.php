#!/usr/bin/php
<?php
/**
 * Make svn checkout runable
 * @link http://www.shopware.de
 * @package tools
 * @subpackage make-tool
 * @copyright (C) Shopware AG 2010-2011
 * @version Shopware 3.5.0
 */

class
	Dashboard_Components_LicenceShopware
{
	/**
	 * Copied without description
	 *
	 */
	public function sLicense ($host,$modules)
	{
		$sql = "";
		foreach ($modules as $module){

			$licence = $this->sBuildLicense($host,$module);
			//$sql.= "INSERT INTO s_core_licences (module,hash) VALUES ('$module','".$this->sBuildLicense($host,$module)."');\n";
			$sql[] = array("module"=>$module,"licence"=>$licence);
		}
		return $sql;
	}
	/**
	 * Copied without description
	 *
	 */
	public static function sBuildLicense($host,$module){

		$moduleInfo = base64_encode($host.$module);
		$len = strlen($moduleInfo);
		$secretKey = "A#l#a#b#a#m#a#f#o#x#n#o#r#t#h#s#o#u#t#h#e#l#e#v#e#n#A#t#a#p#r#o#t#o#k#o#l#l#1#9#2#1#9#2#4#V#a#s#y#y#x#j#k#r#e#3#2#4#d#x#m#d#v#ö#l#e#d#s#o#4#m#l#d#x#ö#l#m#s#a#m#s#a#n#x#v#v#j#i#o#e#i#o#j#k#m#d#k#m#v#x#k#l#c#m#v#k#l#x#v#v#a#s#d#l#s#d#j#g#b#8#8#4#4#2#n#m#,#x#c#m#v#n#k#l#j#s#d#f#k#j#4#e#l#k#s#d#f#j#a#k#s#d#l#f#k#J#S#D#K#L#f#k#l#n#f##s#a#d#j#x#v#i#x#c#(#s#d#a#i#f#n#k#l#x#n#v#k#l#s#k#j#e#k#l#m#k#s#d#m#v#k#s#d#j#f#s#d#f#s#d#j#f#k#l#s#d#f#j#k";

		$secretKey = explode("#",$secretKey);

		for ($i = 6; $i<= $len - 7;$i+=6){
			$secretKey[$i] = substr($moduleInfo,$i,1);
		}

		$result = implode("",$secretKey);

		$checksum = self::crc64($moduleInfo);
		//echo $checksum;
		$result = base64_encode(self::crc64($result).$result.self::crc64($result));

		$resultLen = strlen($result);
		$blocks = intval((intval($resultLen / 5)-1)/10);
		for ($i=1;$i<=$blocks;$i++){
			$serial[] = strtoupper(substr(md5(substr($result,$i*10*5,5).$checksum),1,5));
		}

		return "".implode("-",$serial)."-#$module#";
	}
	/**
	 * Copied without description
	 *
	 */
	private static function crc64($num){
		$crc = crc32($num);
		if($crc & 0x80000000){
			$crc ^= 0xffffffff;
			$crc += 1;
			$crc = -$crc;
		}
		return $crc;
	}
	//+++++ NEUE LIZENZSCHLÜSSEL ++++++
	/**
	 * Copied without description
	 *
	 */
	public function createSecret($module)
	{
		$b = base64_decode(str_rot13('AoKTV39CMyYdBOeK7T7uNodr9pMgg7pBnSbQEEHQzUMyGvnI'));
		$s = base64_encode(md5($b.$module, true).sha1($module.$b, true));
		$s = substr($s, 6, 12).substr($s, 30, 12);
		return $s;
	}
	/**
	 * Copied without description
	 *
	 */
	public function createLicense($module, $host, $expiry=null)
	{
		if(empty($module)||empty($host)){
			return false;
		}

		$s = $this->createSecret($module);
		$b = base64_decode(str_rot13('onma+yR3cp9Qjia2aOwBTxpIOVl7j9+Z2gPIeUJsnq83Esbe'));

		if(isset($expiry)) {
			$expiry = date('Ymd', strtotime($expiry));
			$expiry = strtoupper(base_convert($expiry, 10, 32));
			$m = $host.$b.$s.$expiry;
			$h = strtoupper(substr(md5($m), 10, 15).substr(sha1($m), 8, 10)).$expiry;
		} else {
			$m = $host.$b.$s;
			$h = strtoupper(substr(md5($m), 10, 15).substr(sha1($m), 8, 15));
		}

		$h = wordwrap($h, 5, '-', true).'-#'.$module.'#';

		return $h;
	}
}

class
	Shopware_Components_Dump_Import implements SeekableIterator, Countable
{
	protected $length;
	protected $count;
	protected $stream;
	protected $position;
	protected $current;
	
	public function __construct($filename)
	{
		$this->stream = @fopen($filename, 'rb');
		$this->length = 65535;
		if(!$this->stream || !$this->length) {
			throw new Exception('Dump can\'t open failure');
		}
		$this->position = 0;
		$this->count = 0;
		while (!feof($this->stream)) {
			$this->fetch();
			$this->count++;
		}
		$this->rewind();
	}
	
	public function fetch()
	{
		$this->current = '';
		while (!feof($this->stream)) {
			$this->current .= fgets($this->stream, $this->length);
			if(substr(rtrim($this->current), -1) == ';') {
				break;
			}
		}
		$this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
	}
	
	public function seek($position)
	{
		while($this->position < $position) {
			$this->next();
		}
	}
	
	public function count()
	{
		return $this->count;
	}
	
	public function rewind()
	{
		rewind($this->stream);
		$this->next();
		$this->position = 0;
	}

	public function current()
	{
		return $this->current;
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->fetch();
		++$this->position;
	}

	public function valid()
	{
		return !feof($this->stream);
	}
	
	public function each()
	{
		if(!$this->valid()) {
			return false;
		}
		$result = array($this->key(), $this->current());
		$this->next();
		return $result;
	}
}

$baseDir = dirname(dirname(__FILE__));
if (is_file($baseDir."/local.properties.php")){
	$config = include($baseDir."/local.properties.php");
}elseif (is_file("/var/local.properties.php")){
	$config = include("/var/local.properties.php");
}
else {
	$config = array(
		"defaultRoot"=>"/var/www",
		"db"=> array("%host%"=>"localhost","%user%"=>"root","%password%"=>"root","%database%"=>"shopware"),
	);
}


// Get Host & basepath
if (!empty($config["host"])){
	$host = $config["host"];
}else {
	$result = array();
	exec('echo $(ifconfig eth0 | head -n 2 | tail -n 1 | cut -d: -f2 | cut -d" " -f 1)',$result);
	$host = count(explode(".",$result[0]))==4 ? $result[0] : "";
	if (empty($host)){
		file_put_contents('php://stderr',PHP_EOL." No valid host found... Could not create bootable environment ".PHP_EOL);
		exit(1);
	}
}

if (strpos($baseDir,$config["defaultRoot"])!==false){
	$basePath = $host.str_replace($config["defaultRoot"],"",$baseDir);
}
elseif (!empty($config["basepath"])){
	$basePath = $config["basepath"];
}
else {
	$basePath = $host;
}

if (!is_writeable($baseDir)){
	file_put_contents('php://stderr',PHP_EOL." $baseDir is not writeable - could not proceed ".PHP_EOL);
	exit(1);
}

// Create directotires
$directories = array (
	"cache",
	"cache/database",
	"cache/templates",
	"images",
	"images/articles",
	"images/banner",
	"images/cms",
	"images/supplier",
	"uploads",
	"files",
	"files/552211cce724117c3178e3d22bec532ec",
	"files/cms",
	"files/documents",
	"files/downloads",
	"_tests/Shopware/TempFiles"
);
foreach ($directories as $directory){
	if (!is_dir($baseDir."/".$directory)){
		mkdir($baseDir."/".$directory, 0777);
	} else {
		chmod($baseDir."/".$directory, 0777);
	}
}

// Check database connection
if (!mysql_connect($config["db"]["%host%"], $config["db"]["%user%"], $config["db"]["%password%"])){
	file_put_contents('php://stderr',PHP_EOL."Could not establish database connection with ".print_r($config["db"],true). " ###END###".PHP_EOL);
	exit(1);
}
// Create database if not available
mysql_query("CREATE DATABASE IF NOT EXISTS {$config["db"]["%database%"]}");
if (mysql_error()){
	file_put_contents('php://stderr',PHP_EOL."Fatal Database error\n".mysql_error().PHP_EOL);
	exit(1);
}
if (!mysql_select_db($config["db"]["%database%"])){
	file_put_contents('php://stderr',PHP_EOL."Could not select database with ".print_r($config["db"],true). " ###END###".PHP_EOL);
	exit(1);
}

// Create configuration files
$config_php = file_get_contents(dirname(__FILE__)."/config.php.template");
$config_php = str_replace(array_keys($config["db"]),array_values($config["db"]),$config_php);
file_put_contents($baseDir."/config.php",$config_php);

/*
$Application = file_get_contents(dirname(__FILE__)."/Application.php.template");
$ApplicationTest = file_get_contents(dirname(__FILE__)."/TestApplication.php.template");

$Application = str_replace(array_keys($config["db"]),array_values($config["db"]),$Application);
$Application = str_replace(array("%server%","%basepath%"),array($host,$basePath),$Application);

$ApplicationTest = str_replace(array_keys($config["db"]),array_values($config["db"]),$ApplicationTest);
$ApplicationTest = str_replace(array("%server%","%basepath%"),array($host,$basePath),$ApplicationTest);

// Licences
$licence = new Dashboard_Components_LicenceShopware();
$licences = array("sARTICLECONF","sGROUPS","sFUZZY","sPRICESEARCH","sCORE","sMAILCAMPAIGNSADV","sLANGUAGEPACK2","sTICKET","sPREMIUM","sBUNDLE","sLIVE");
$getLicenceArray = $licence->sLicense($host,$licences);

$licences ="";
foreach ($getLicenceArray as $v){
	$licences .= "'{$v["module"]}' => '{$v["licence"]}',\n";
}

$Application = str_replace("%licences%",$licences,$Application);
$ApplicationTest = str_replace("%licences%",$licences,$ApplicationTest);

// Doing replacements
file_put_contents($baseDir."/Application.php",$Application);
file_put_contents($baseDir."/_tests/Shopware/TestApplication.php",$ApplicationTest);

// Checking sql state
$getValue = mysql_result(mysql_query("SELECT value FROM s_core_config WHERE name='sVERSION'"),0,"value");

// Do initial import
if (empty($getValue)){
	// New database ?	
	$result = array();
	$database = $config["schema"] == "test" ? "defaultTestdata" : "default";
	$command = "mysql -u{$config["db"]["%user%"]} -p{$config["db"]["%password%"]} {$config["db"]["%database%"]} < ".escapeshellarg($baseDir."/_sql/$database.sql")." 2>&1";
	exec($command,$result);
}

// Do patch imports
$command = "mysql -u{$config["db"]["%user%"]} -p{$config["db"]["%password%"]} {$config["db"]["%database%"]} < ".escapeshellarg($baseDir."/_sql/release.sql")." 2>&1";
exec($command, $result);
*/

$dump = new Shopware_Components_Dump_Import($baseDir."/_sql/release.sql");
foreach ($dump as $line) {
	if(!mysql_query($line)) {
		if(in_array(mysql_errno(), array(1060, 1061, 1062, 1091))) {
    		continue;
    	}
    	$msg = "Es ist ein Fehler beim Import der Datenbank aufgetreten:<br />\n";
    	$msg .= mysql_error()."<br />\n";
    	$msg .= htmlentities($line);
    	echo $msg;
		exit(1);
    }
}

$sql = "TRUNCATE TABLE `s_core_licences`;";
mysql_query($sql);

$licences = array(
	'sARTICLECONF',
	'sGROUPS',
	'sFUZZY',
	'sPRICESEARCH',
	'sCORE',
	'sMAILCAMPAIGNSADV',
	'sLANGUAGEPACK5',
	'sTICKET',
	'sPREMIUM',
	'sBUNDLE',
	'sLIVE'
);
foreach ($licences as $module) {
	$license = Dashboard_Components_LicenceShopware::sBuildLicense($host, $module);
	
	$sql = "INSERT IGNORE INTO s_core_licences (module, hash) VALUES ('$module', '$license');";
	mysql_query($sql);
}

$sql = "UPDATE `s_core_config` SET `value` = '$host' WHERE `name` = 'sHOST';";
mysql_query($sql);

$sql = "
	INSERT INTO `s_core_config`
		(`group`, `name`, `value`, `description`, `required`, `warning`)
	VALUES
		(6, 'sHOSTORGINAL', '$host', 'Shophost', 1, 1)
	ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);
";
mysql_query($sql);

$sql = "UPDATE `s_core_config` SET `value` = '$basePath' WHERE `name` = 'sBASEPATH';";
mysql_query($sql);

$sql = "UPDATE `s_core_multilanguage` SET `domainaliase` = '$host' WHERE `id` =1;";
mysql_query($sql);

exit(0);