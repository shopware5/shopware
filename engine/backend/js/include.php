<?php
if(empty($_REQUEST['module'])) {
	exit();
}
$base_path = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://';
$base_path .= $_SERVER['HTTP_HOST'];
if(!empty($_SERVER['REQUEST_URI'])) {
	$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$base_path .= dirname(dirname(dirname($request_path)));
} else {
	$base_path .= dirname(dirname(dirname($_SERVER['PHP_SELF'])));
}
$base_dir = dirname(dirname(dirname(__FILE__))).'/';
$module = basename($_REQUEST['module']);
$module = preg_replace('/[^a-z0-9_.:-]/i', '', $module);
$include = empty($_REQUEST['include']) ? 'skeleton.php' : (string) $_REQUEST['include'];
$query = parse_url($include, PHP_URL_QUERY);
$include = parse_url($include, PHP_URL_PATH);
$include = preg_replace('/[^a-z0-9\\/\\\\_.:-]/i', '', $include);

if(file_exists($base_dir.'local_old/modules/'.$module.'/'.$include)) {
	$location = $base_path.'/local_old/modules/'.$module.'/'.$include;
} elseif(file_exists($base_dir.'backend/modules/'.$module.'/'.$include)) {
	$location = $base_path.'/backend/modules/'.$module.'/'.$include;
}

if(!empty($location)) {
	if(!empty($query)) {
		$location .= '?'.$query;
	} elseif(!empty($_POST)) {
		$location .= '?'.http_build_query($_POST, '', '&');
	}
	header('Location: '.$location);
} else {
	header('x', true, 404);
}