<?php
function sendHTTP($reqBody, $host, $port, $wsPath)
{
	$res = "";

	try {
		//Paymorrow Server URL

		$pi_paymorrow_config = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();
		if ($pi_paymorrow_config->sandbox_mode) {
			$host = $pi_paymorrow_config->server_url_sandbox;
		}
		else {
			$host = $pi_paymorrow_config->server_url;
		}
		$port = $pi_paymorrow_config->server_port;
		$wsPath = $pi_paymorrow_config->server_path;

		//-->only for debuging
		$h = fopen('send.log', 'a+');
		fputs($h, date('Y-m-d H:j:s') . "\r\n");
		fputs($h, $reqBody);
		fputs($h, "\r\n------------------------------------------------------------------------------------------------------------------------------------------------------------\r\n\r\n");
		fclose($h);
		//<--

		// HTTP Protocol settings for sending request
		$req = "POST " . $wsPath . " HTTP/1.1\r\n"
			. "Host: $host\r\n"
			. "Content-Type: text/xml\r\n"
			. "Content-Length: " . strlen($reqBody) . "\r\n"
			. "Connection: close\r\n\r\n"
			. $reqBody;

		//@todo aukommentiert
		//echo "<pre>";
		//@todo aukommentiert
		//var_dump($req);
		//@todo aukommentiert
		//echo "</pre>";

		#echo "Connecting: ".$host.":".$port.", path=".$wsPath."...";

		$myHeader = "*** " . date(DATE_ATOM, time()) . " ******************************************************\n";

		#log_output("paymorrow_client_http_socket_log.txt", $myHeader.$req);

		if (!($fp = fsockopen("ssl://" . $host, $port, $errNo, $errStr))) {
			echo "Cannot open:" . $errStr;
			return false;
		}

		// data placed on Stream
		fwrite($fp, $req, strlen($req));

		//Read data from Stream
		while ($data = fread($fp, 32768)) {
			$res .= $data;
		}

		#log_output("paymorrow_client_http_socket_log.txt", "\n".$myHeader."Response:\n".$res."\n");

		fclose($fp);
		//@todo aukommentiert
		//echo "Succeeded.<hr>";

	} catch (Exception $e) {
		echo $e->getMessage();
	}

	return $res;

}