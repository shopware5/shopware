<?php
define('WORKSPACE', dirname(__FILE__).'/workspace');

$arguments = getopt('t:d:');

if (empty($arguments['t'])){
	throw new Exception('Missing tag parameter.');
}

require_once(dirname(__FILE__).'/Shopware_Deployment.php');

try {
	$deployment = new Shopware_Deployment();
	$deployment->setConfig(include(dirname(__FILE__) . '/deploy_config.php'));
	$deployment->setWorkspace(WORKSPACE);
	$deployment->setTag($arguments['t']);
	$deployment->setProject($deployment->getWorkspace() . '/' . $deployment->getTag());
	
	$deployment->checkEncoding('deployment/install-ioncube');
	$deployment->checkEncoding('deployment/install-ioncube-demo');
	$deployment->checkEncoding('deployment/install-zend');
	$deployment->checkEncoding('deployment/install-zend-demo');
	$deployment->checkEncoding('deployment/patch-ioncube');
	$deployment->checkEncoding('deployment/patch-zend');
	
} catch (Exception $e) {
	echo $e->getMessage() . '\n';
	exit(1);
}