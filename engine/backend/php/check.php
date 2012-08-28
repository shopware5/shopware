<?php
if(empty($_SESSION)) {
	session_start();
}

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

header('Content-Type: text/html; charset=iso-8859-1', true);

include('functions.php');

$sCore = new sFunctions;
$sCore->sInitDb();
$sCore->sInitConfig();
$sCore->sInitTranslation();
$sCore->sLoadHookPoints();
$sCore->sGetLicenseData();

class checkLogin
{
	function checkUser()
    {
        return $this->report('SUCCESS');
	}
	
	function renew()
    {
		return $this->report('SUCCESS');
	}

	function report($code)
    {
		if (defined('login')){
			die ($code);	
		} else {
			return $code;
		}
	}	
}

if (!defined('sAuthFile') && !defined('logout')){
    define('login', true);
}
if (defined('login') || !defined('sAuthFile')){
	$checkLogin = new checkLogin();
	$checkLogin->checkUser();
}