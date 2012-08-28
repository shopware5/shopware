<?php

require_once 'inc/paymorrowProxyUtil.php';

$todayDateTime = date("c");
$todayDate = date("1980-m-d");
$piPaymorrowConfig = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();

$request = new paymorrowOrderRequestType(); //t.XMLtoObject("");
if ($piPaymorrowConfig->sandbox_mode) {
	$request->requestMerchantId = $piPaymorrowConfig->merchant_id_sandbox;
}
else {
	$request->requestMerchantId = $piPaymorrowConfig->merchant_id;
}

$request->requestId = time();
$request->requestTimestamp = $todayDateTime;
$request->requestLanguageCode = strtolower(Shopware()->Session()->sOrderVariables['sCountry']['countryiso']);

$order = new OrderType();
$order->orderId = Shopware()->Session()->orderNumber;
$order->orderTimestamp = $todayDateTime;


$customer = new customerType();
$customer->customerId = $piPaymorrowUser['user']['id'];

$customer->customerIPAddress = $_SERVER['REMOTE_ADDR'];
if (!$piPaymorrowUser['billingaddress']['company']) {
	$customer->orderCustomerType = "PERSON";
}
else {
	$customer->orderCustomerType = "COMPANY";
}
$languangeSql = 'SELECT `locale` FROM s_core_locales WHERE id = ' .(int) $piPaymorrowUser['additional']['user']['language'];
$userLanguage = Shopware()->Db()->fetchOne($languangeSql);
$tmpLang = explode('_', $userLanguage);
$piPaymorrowUser['additional']['user']['language'] = $tmpLang[0];
$customer->customerPreferredLanguage = $piPaymorrowUser['additional']['user']['language'];

$customerPersonalDetails = new customerPersonalDetailsType();
$customerPersonalDetails->customerGivenName = trim($piPaymorrowUser['billingaddress']['firstname']);
$customerPersonalDetails->customerSurname = trim($piPaymorrowUser['billingaddress']['lastname']);
$customerPersonalDetails->customerEmail = trim($piPaymorrowUser['additional']['user']['email']);
if ($piPaymorrowUser['billingaddress']['salutation'] == 'mr') {
	$customerPersonalDetails->customerGender = "M";
}
else {
	$customerPersonalDetails->customerGender = "F";
}
$customerPersonalDetails->customerDateOfBirth = $piPaymorrowUser['billingaddress']['birthday'];
$customerPersonalDetails->customerPhoneNo = trim($piPaymorrowUser['billingaddress']['phone']);
$customerPersonalDetails->customerOrganizationName = trim($piPaymorrowUser['billingaddress']['company']);

$customer->customerPersonalDetails = $customerPersonalDetails;

$customerAddress = new AddressType();
$customerAddress->addressStreet = trim($piPaymorrowUser['billingaddress']['street']);
$customerAddress->addressHouseNo = trim($piPaymorrowUser['billingaddress']['streetnumber']);
//activate the following line to get declined transaction
//$customerAddress->addressDepartment="decline";
$customerAddress->addressPostalCode = trim($piPaymorrowUser['billingaddress']['zipcode']);
$customerAddress->addressLocality = trim($piPaymorrowUser['billingaddress']['city']);
$customerAddress->addressCountryCode = strtolower(trim($piPaymorrowUser['additional']['country']['countryiso']));

$customer->customerAddress = $customerAddress;

$addressShipmentType = new AddressType();
$addressShipmentType->addressStreet = trim($piPaymorrowUser['shippingaddress']['street']);
$addressShipmentType->addressHouseNo = trim($piPaymorrowUser['shippingaddress']['streetnumber']);
$addressShipmentType->addressPostalCode = trim($piPaymorrowUser['shippingaddress']['zipcode']);
$addressShipmentType->addressLocality = trim($piPaymorrowUser['shippingaddress']['city']);
$addressShipmentType->addressCountryCode = strtolower(trim($piPaymorrowUser['additional']['countryShipping']['countryiso']));

$order->orderShippingAddress = $addressShipmentType;
$selectedShipping = $piPaymorrowConfig->versand;
$selectedShipping = explode(" ", $selectedShipping);
$orderShipmentDetails = new OrderShipmentDetailType();
$orderShipmentDetails->shipmentMethod = $selectedShipping[1];
$orderShipmentDetails->shipmentProvider = $selectedShipping[0];
$order->orderShipmentDetails = $orderShipmentDetails;

$articles = $piPaymorrowBasket['content'];
$ordercode = Shopware()->Db()->fetchAll("SELECT * from s_emarketing_vouchers");
foreach ($articles as $article) {
	$voucherflag = false;
	if (!$article['taxPercent']) $article['taxPercent'] = 0;
	for ($i = 0; $i < count($ordercode); $i++) {
		if ($article['ordernumber'] == $ordercode[$i]['ordercode']) $voucherflag = true;
	}
	if ($voucherflag == true) $itemCategory = 'VOUCHER';
	else $itemCategory = $piPaymorrowConfig->katergorie;
    $piPaymorrowTax = $article['tax_rate'];
    
	$itemsArray[] = array(
		"itemQuantitiy"          => $article['quantity'],
		"itemArticleId"          => trim($article['ordernumber']),
		"itemDescription"        => str_replace('%', 'prozent', trim($article['articlename'])),
		"itemCategory"           => $itemCategory,
		"itemUnitPrice"          => number_format($article['priceNumeric'], 2, '.', ''),
		"itemVatRate"            => $piPaymorrowTax,
		"itemAmountInclusiveVAT" => true
	);
}
if ($piPaymorrowBasket['sShippingcostsNet'] > 0) {
	$itemsArray[] = array(
		"itemQuantitiy"          => 1,
		"itemArticleId"          => 'versand',
		"itemDescription"        => 'Versandkosten',
		"itemCategory"           => 'SHIPPING',
		"itemUnitPrice"          => round($piPaymorrowBasket['sShippingcostsWithTax'], 2),
		"itemVatRate"            => $piPaymorrowBasket['sShippingcostsTax'],
		"itemAmountInclusiveVAT" => true
	);
}
$vatAmount19 = 0;
$vatAmount7 = 0;
$order->orderAmountVATTotal = 0;
$j = 0;
$orderItemsArray = array();
$orderAmountVATArray = array();
while ($itemsArray[$j] != NULL) {
	//compilation of ordered items (orderItems)
	$orderItems = new orderItemType();
	$orderItems->itemId = $j + 1;
	$orderItems->itemQuantity = $itemsArray[$j]['itemQuantitiy']; // quantity
	$orderItems->itemArticleId = trim($itemsArray[$j]['itemArticleId']); // product id
	$orderItems->itemDescription = trim($itemsArray[$j]['itemDescription']); // product description
	$orderItems->itemCategory = $itemsArray[$j]['itemCategory']; // paymorrow category type
	$orderItems->itemUnitPrice = $itemsArray[$j]['itemUnitPrice']; // product unit price
	$orderItems->itemCurrencyCode = "EUR"; // currency
	$orderItems->itemVatRate = $itemsArray[$j]['itemVatRate']; // vat rate
	$orderItems->itemExtendedAmount = number_format($itemsArray[$j]['itemQuantitiy'] * $itemsArray[$j]['itemUnitPrice'], 2, '.', ''); // total price of the same article
	$orderItems->itemAmountInclusiveVAT = $itemsArray[$j]['itemAmountInclusiveVAT']; // product incl. oder excl. vat?
	$orderItemsArray[$j] = $orderItems;
	//calculation of VAT values (orderVatRate)
	if ($orderItems->itemVatRate == 19) {
		$order->orderAmountVATTotal += round((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2);
		$vatAmount19 += round((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2);
	}
	elseif ($orderItems->itemVatRate == 7) {
		$order->orderAmountVATTotal += round((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2);
		$vatAmount7 += round((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2);
	}
	elseif ($orderItems->itemVatRate == 0) {
		$prozentFlag = true;
		$order->orderAmountVATTotal += number_format((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2, '.', '');
		$vatAmount0 += number_format((($orderItems->itemExtendedAmount * $orderItems->itemVatRate) / (100 + $orderItems->itemVatRate)), 2, '.', '');
	}
	//calculation of gross and net order value
	$order->orderAmountNet += round((($orderItems->itemExtendedAmount * 100) / (100 + $orderItems->itemVatRate)), 2); // Nettopreis der gesammten Bestellung
	$order->orderAmountGross += $orderItems->itemExtendedAmount; // Bruttopreis der gesammten Bestellung
	$j++;
}
$order->orderItems = $orderItemsArray;

//compilation of VAT values (orderVatRate)
$k = 0;
if ($vatAmount19 != 0) {
	$orderVatRateType19 = new orderVatRate();
	$orderVatRateType19->vatRate = 19;
	$orderVatRateType19->orderVatAmount = $vatAmount19;
	$orderAmountVATArray[$k] = $orderVatRateType19;
	$k++;
}
if ($vatAmount7 != 0) {
	$orderVatRateType7 = new orderVatRate();
	$orderVatRateType7->vatRate = 7;
	$orderVatRateType7->orderVatAmount = $vatAmount7;
	$orderAmountVATArray[$k] = $orderVatRateType7;
	$k++;
}
if ($prozentFlag) {
	$orderVatRateType0 = new orderVatRate();
	$orderVatRateType0->vatRate = 0;
	$orderVatRateType0->orderVatAmount = 0;
	$orderAmountVATArray[$k] = $orderVatRateType0;
	$k++;
}
$orderAmountVAT = new orderAmountVatType();
$orderAmountVAT->orderVatRate = $orderAmountVATArray;
$order->orderAmountVAT = $orderAmountVAT;

$order->orderCurrencyCode = "EUR";

$hash = md5(time());
$basepath = Shopware()->Config()->basepath;
$basefile = Shopware()->Config()->basefile;

$requestMerchantUrls = new requestMerchantUrlType();
if ($_SERVER["SERVER_PORT"] == 80)
	$ShopPort = "http://";
else if ($_SERVER["SERVER_PORT"] == 443)
	$ShopPort = "https://";
else
	$ShopPort = "http://";
$requestMerchantUrls->merchantSuccessUrl             = $ShopPort . $basepath . '/' . $basefile . '?sViewport=PiPaymentPaymorrow&sAction=end';
$requestMerchantUrls->merchantErrorUrl               = $ShopPort . $basepath . '/' . $basefile . '?sViewport=PiPaymentPaymorrow&sAction=cancel';
$requestMerchantUrls->merchantPaymentMethodChangeUrl = $ShopPort . $basepath . '/' . $basefile . '?sViewport=PiPaymentPaymorrow&sAction=changePayment';
$requestMerchantUrls->merchantNotificationUrl        = $ShopPort . $basepath . '/' . $basefile . '?sViewport=PiPaymentPaymorrow&sAction=notify';
$request->requestMerchantUrls = $requestMerchantUrls;
$order->orderCustomer = $customer;

$request->order = $order;
if ($piPaymorrowConfig->sandbox_mode) {
	$pi_paymorrow_key = $piPaymorrowConfig->security_code_sandbox;
}
else {
	$pi_paymorrow_key = $piPaymorrowConfig->security_code;
}
$paymorrowOrderResponse = sendRequestToPaymorrow($request, $pi_paymorrow_key);
