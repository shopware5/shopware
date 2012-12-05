<?php
$a = file_get_contents("test.csv");
$a = html_entity_decode($a);
$a = str_replace(";","",$a);
$a = explode(" ",$a);

foreach ($a as $p){
	$rand = rand(0,1);
	echo $p;
	if ($rand) echo "\n"; else echo " ";

	$i++;
	if ($i>=1000) break;
}