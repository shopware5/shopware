<?php
error_reporting(0);
ini_set('display_errors', 0);

if(empty($_GET['file'])) {
	return;
}

$file = (string) $_GET['file'];
$file = dirname($file).'/'.basename($file);
$file = preg_replace('#\.+/#', '', $file);
$file = dirname(dirname(dirname(dirname(__FILE__)))).'/'.$file;

if(preg_match('/[^a-z0-9\\/\\\\_. :-]/i', $file)) {
	return;
}
if(!file_exists($file)) {
	return;
}

$extension = pathinfo($file, PATHINFO_EXTENSION);

switch ($extension) {
	case 'js':
		$typ = 'text/js';
		break;
	case 'css':
		$typ = 'text/css';
		break;
	case 'gif':
		$typ = 'image/gif';
		break;
	case 'png':
		$typ = 'image/png';
		break;
	case 'jpg':
	case 'jpeg':
		$typ = 'image/jpeg';
		break;
	default:
		return;
}

$last_modified_time = filemtime($file); 
$etag = md5($file.$last_modified_time);

header('Content-Type: '.$typ);
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT'); 
header('Etag: '.$etag); 

if ((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])
  && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time)
    || (!empty($_SERVER['HTTP_IF_NONE_MATCH'])
    && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag))
{
	if (php_sapi_name()=='cgi') {
		header('Status: 304');
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
	}
} elseif (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && !ini_get('zlib.output_compression')) {
	ob_start('ob_gzhandler');
	echo file_get_contents($file);
	ob_end_flush();
} else {
	echo file_get_contents($file);
}