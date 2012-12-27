<?php
function register_paymorrow($ordernumber, $invoiceNumber)
{
	$pi_Paymorrow_config = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();
	if ($pi_Paymorrow_config->sandbox_mode) {
		$merchant_id = $pi_Paymorrow_config->merchant_id_sandbox;
		$merchant_key = $pi_Paymorrow_config->security_code_sandbox;
		$host = $pi_Paymorrow_config->server_url_sandbox;
	}
	else {
		$merchant_id = $pi_Paymorrow_config->merchant_id;
		$merchant_key = $pi_Paymorrow_config->security_code;
		$host = $pi_Paymorrow_config->server_url;
	}
	$port = $pi_Paymorrow_config->server_port;
	$wsPath = "/perthMerchantPortalWS/services/MerchantService.Merchant";


//Register Transaction

	$hash = sha1('<mer:createInvoice><createInvoiceRequest><orderId>' . $ordernumber . '</orderId><createFullInvoice>true</createFullInvoice><invoiceId>' . $invoiceNumber . '</invoiceId></createInvoiceRequest></mer:createInvoice>' . $merchant_key);

	$reqBody = '<?xml version="1.0" encoding="UTF-8"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mer="http://paymorrow.com/integration/merchantservice">
       <soapenv:Header>
          <mer:messageContext>
             <merchantId>' . $merchant_id . '</merchantId>
             <signature>' . $hash . '</signature>
          </mer:messageContext>
       </soapenv:Header>
       <soapenv:Body><mer:createInvoice><createInvoiceRequest><orderId>' . $ordernumber . '</orderId><createFullInvoice>true</createFullInvoice><invoiceId>' . $invoiceNumber . '</invoiceId></createInvoiceRequest></mer:createInvoice></soapenv:Body></soapenv:Envelope>';


	// HTTP Protocol settings for sending request
	$req = "POST " . $wsPath . " HTTP/1.1\r\n"
		. "Host: $host\r\n"
		. "Content-Type: text/xml\r\n"
		. "Content-Length: " . strlen($reqBody) . "\r\n"
		. "Connection: close\r\n\r\n"
		. $reqBody;


	$myHeader = "*** " . date(DATE_ATOM, time()) . " ******************************************************\n";

	if (!($fp = fsockopen("ssl://" . $host, $port, $errNo, $errStr))) {
		echo "Cannot open:" . $errStr;
		return false;
	}

	fwrite($fp, $req, strlen($req));
	$res = '';
	while ($data = fread($fp, 32768)) {
		$res .= $data;
	}

	if (strpos($res, 'HTTP/1.1 200 OK') === false) {
		return false;
	}

	//@todo aukommentiert
	//print_r($res);
	fclose($fp);

// Register Shipment
	$shipment_time = date("Y-m-d\TH:i:s.840\Z");
	$shipment_trackno = 'x' . date('Ymd');
	$OrderTrackingCode = Shopware()->Db()->fetchOne("SELECT trackingcode from s_order WHERE ordernumber ='" . $ordernumber . "'");
	if ($OrderTrackingCode) {
		$shipment_trackno = $OrderTrackingCode;
	}
	else {
		$shipment_trackno = 'x' . date('Ymd');
	}
	$selectedShipping = $pi_Paymorrow_config->versand;
	$selectedShippingPart = explode(" ", $selectedShipping);
	//Define your Shipment_Provider And Shipment_Methode
	$shipment_provider = $selectedShippingPart[0];
	$shipment_type = $selectedShippingPart[1];

	//@todo aukommentiert
	//echo "<br/><br/>";

	$hash = sha1('<mer:registerShipment><shippingRegistration><invoiceId>' . $invoiceNumber . '</invoiceId><shipmentProvider>' . $shipment_provider . '</shipmentProvider><shipmentMethod>' . $shipment_type . '</shipmentMethod><shippingDate>' . $shipment_time . '</shippingDate><trackingNo>' . $shipment_trackno . '</trackingNo></shippingRegistration></mer:registerShipment>' . $merchant_key);
	$reqBody = '<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mer="http://paymorrow.com/integration/merchantservice">
       <soapenv:Header><mer:messageContext><merchantId>' . $merchant_id . '</merchantId><signature>' . $hash . '</signature></mer:messageContext></soapenv:Header><soapenv:Body><mer:registerShipment><shippingRegistration><invoiceId>' . $invoiceNumber . '</invoiceId><shipmentProvider>' . $shipment_provider . '</shipmentProvider><shipmentMethod>' . $shipment_type . '</shipmentMethod><shippingDate>' . $shipment_time . '</shippingDate><trackingNo>' . $shipment_trackno . '</trackingNo></shippingRegistration></mer:registerShipment></soapenv:Body></soapenv:Envelope>';


	// HTTP Protocol settings for sending request
	$req = "POST " . $wsPath . " HTTP/1.1\r\n"
		. "Host: $host\r\n"
		. "Content-Type: text/xml\r\n"
		. "Content-Length: " . strlen($reqBody) . "\r\n"
		. "Connection: close\r\n\r\n"
		. $reqBody;


	$myHeader = "*** " . date(DATE_ATOM, time()) . " ******************************************************\n";

	if (!($fp = fsockopen("ssl://" . $host, $port, $errNo, $errStr))) {
		echo "Cannot open:" . $errStr;
		return false;
	}

	fwrite($fp, $req, strlen($req));
	$res = '';
	while ($data = fread($fp, 32768)) {
		$res .= $data;
	}

	if (strpos($res, 'HTTP/1.1 200 OK') === false) {
		return false;
	}

	fclose($fp);

	return true;
}
