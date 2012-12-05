<?php
define("WORKSPACE", dirname(__FILE__)."/workspace");

$arguments = getopt("t:d:");

if (empty($arguments["t"])){
	throw new Exception('Missing tag parameter.');
}

require_once(dirname(__FILE__)."/Shopware_Deployment.php");

try {
	$deployment = new Shopware_Deployment();
	$deployment->setConfig(include(dirname(__FILE__)."/deploy_config.php"));
	$deployment->setTag($arguments["t"]);
	if (!empty($arguments["d"])){
		$deployment->setDiffTag($arguments["d"]);
	}
	if (!is_dir(WORKSPACE)){
		$deployment->initWorkspace(WORKSPACE);
	}
	
	$deployment->setWorkspace(WORKSPACE);
	$deployment->initProject();
} catch (Exception $e) {
	echo $e->getMessage() . "\n";
	exit(1);
}
