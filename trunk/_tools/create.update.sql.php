<?php
$sql = file_get_contents("update.sql");
$replace = array(
	"INSERT INTO" => "INSERT IGNORE INTO",
	"`sth`." => "",
	"`trunk_sth`." => "",
	"TINYINT." => "INT( 1 )",
	"CREATE TABLE `" => "CREATE TABLE IF NOT EXISTS `",
	"DROP TABLE `" => "DROP TABLE IF EXISTS `",
	"info@shopware2.de" => "info@example.com",
	"demo@shopware2.de" => "info@example.com",
	"meine@domain.de" => "info@example.com",
	"info@ihreshopdomain.de" => "info@example.com",
	"AUTO_INCREMENT=[0-9]+ " => ""
);
$sql = str_replace(array_keys($replace),array_values($replace),$sql);
$sql = preg_replace("/\s*$/","",$sql);
//$sql = preg_replace("/\n\s*[-+\/].*/","",$sql);
file_put_contents("update.sql",$sql);
?>