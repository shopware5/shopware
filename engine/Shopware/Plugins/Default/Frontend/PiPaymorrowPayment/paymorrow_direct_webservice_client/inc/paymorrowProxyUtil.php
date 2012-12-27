<?php
Shopware()->Session()->soapBody = new DOMDocument('1.0', 'UTF-8');

//PHP proxy classes
require_once 'PaymorrowServiceClasses.inc.php';
require_once 'log.inc.php';

//require for send the HTTP request to Server
require_once 'communication.php';

// Util Functions for handling Paymorrow request/response.
require_once 'UtilFunctions.php';

// Functions for final normalize of xml string (remove invalid characters)
require_once 'NormalizeXML.php';

/**
 * This method takes the Paymorrow Order Request object and Authorization Key
 * and sends it to Paymorrow Server as soap message.
 * URL and Protocol needs to adjust according to envoirnment.
 */
function sendRequestToPaymorrow($paymorrowOrderRequest, $authorizationKey)
{
	$soapBody = Shopware()->Session()->soapBody;
	$soapBodyEle = $soapBody->createElement("S:Body");
	$soapBody->appendChild($soapBodyEle);
	$paymorrowEle = $soapBody->createElement("paymorrow");
	$soapBodyEle->appendChild($paymorrowEle);

	$orderRequestEle = appendIfNotNull("notempty", 'paymorrowOrderRequest', null, $paymorrowEle, "1", false);
	$merchantIdEle = appendIfNotNull($paymorrowOrderRequest->requestMerchantId, "requestMerchantId", $paymorrowOrderRequest->requestMerchantId, $orderRequestEle, "1", false);
	$requestIdEle = appendIfNotNull($paymorrowOrderRequest->requestId, "requestId", $paymorrowOrderRequest->requestId, $orderRequestEle, "1", false);
	$requestTimestampEle = appendIfNotNull($paymorrowOrderRequest->requestTimestamp, "requestTimestamp", $paymorrowOrderRequest->requestTimestamp, $orderRequestEle, "1", false);
	$requestLanguageCodeEle = appendIfNotNull($paymorrowOrderRequest->requestLanguageCode, "requestLanguageCode", $paymorrowOrderRequest->requestLanguageCode, $orderRequestEle, "1", false);

	$orderEle = appendIfNotNull($paymorrowOrderRequest->order, "order", $paymorrowOrderRequest->order, $orderRequestEle, "0", false);
	$orderIdEle = appendIfNotNull($paymorrowOrderRequest->order->orderId, "orderId", $paymorrowOrderRequest->order->orderId, $orderEle, "1", false);
	$orderTimestampEle = appendIfNotNull($paymorrowOrderRequest->order->orderTimestamp, "orderTimestamp", $paymorrowOrderRequest->order->orderTimestamp, $orderEle, "1", false);
	$orderShoppingDurationEle = appendIfNotNull($paymorrowOrderRequest->order->orderShoppingDuration, "orderShoppingDuration", $paymorrowOrderRequest->order->orderShoppingDuration, $orderEle, "1", false);
	$orderCheckoutDurationEle = appendIfNotNull($paymorrowOrderRequest->order->orderCheckoutDuration, "orderCheckoutDuration", $paymorrowOrderRequest->order->orderCheckoutDuration, $orderEle, "1", false);


	$orderCustomerEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer, "orderCustomer", $paymorrowOrderRequest->order->orderCustomer, $orderEle, "0", false);
	$customerTypeEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer->orderCustomerType, "orderCustomerType", $paymorrowOrderRequest->order->orderCustomer->orderCustomerType, $orderCustomerEle, "1", true);

	$orderCustomerIdEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer->customerId, "customerId", $paymorrowOrderRequest->order->orderCustomer->customerId, $orderCustomerEle, "1", false);
	$customerGroupIdEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer->customerGroupId, "customerGroupId", $paymorrowOrderRequest->order->orderCustomer->customerGroupId, $orderCustomerEle, "1", false);
	$customerPreferredLanguageEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer->customerPreferredLanguage, "customerPreferredLanguage", $paymorrowOrderRequest->order->orderCustomer->customerPreferredLanguage, $orderCustomerEle, "1", false);
	$customerIPAddressEle = appendIfNotNull($paymorrowOrderRequest->order->orderCustomer->customerIPAddress, "customerIPAddress", $paymorrowOrderRequest->order->orderCustomer->customerIPAddress, $orderCustomerEle, "1", false);


	$customerPersonalDetails = $paymorrowOrderRequest->order->orderCustomer->customerPersonalDetails;
	$customerPersonalDetailsEle = appendIfNotNull($customerPersonalDetails, "customerPersonalDetails", $customerPersonalDetails, $orderCustomerEle, "0", false);
	appendIfNotNull($customerPersonalDetails->customerSalutation, "customerSalutation", $customerPersonalDetails->customerSalutation, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerNamePrefix, "customerNamePrefix", $customerPersonalDetails->customerNamePrefix, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerGivenName, "customerGivenName", $customerPersonalDetails->customerGivenName, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerMiddleName, "customerMiddleName", $customerPersonalDetails->customerMiddleName, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerSurname, "customerSurname", $customerPersonalDetails->customerSurname, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerNameSuffix, "customerNameSuffix", $customerPersonalDetails->customerNameSuffix, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerOrganizationName, "customerOrganizationName", $customerPersonalDetails->customerOrganizationName, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerPhoneNo, "customerPhoneNo", $customerPersonalDetails->customerPhoneNo, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerFaxNo, "customerFaxNo", $customerPersonalDetails->customerFaxNo, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerMobileNo, "customerMobileNo", $customerPersonalDetails->customerMobileNo, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerEmail, "customerEmail", $customerPersonalDetails->customerEmail, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerGender, "customerGender", $customerPersonalDetails->customerGender, $customerPersonalDetailsEle, "1", false);
	appendIfNotNull($customerPersonalDetails->customerDateOfBirth, "customerDateOfBirth", $customerPersonalDetails->customerDateOfBirth, $customerPersonalDetailsEle, "1", false);


	$orderCustomerAddress = $paymorrowOrderRequest->order->orderCustomer->customerAddress;
	$orderCustomerAddressEle = appendIfNotNull($orderCustomerAddress, "customerAddress", $orderCustomerAddress, $orderCustomerEle, "0", false);

	appendIfNotNull($orderCustomerAddress->addressContact, "addressContact", $orderCustomerAddress->addressContact, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressOrganizationName, "addressOrganizationName", $orderCustomerAddress->addressOrganizationName, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressDepartment, "addressDepartment", $orderCustomerAddress->addressDepartment, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressBuilding, "addressBuilding", $orderCustomerAddress->addressBuilding, $orderCustomerAddressEle, "1", false);

	appendIfNotNull($orderCustomerAddress->addressStreet, "addressStreet", $orderCustomerAddress->addressStreet, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressHouseNo, "addressHouseNo", $orderCustomerAddress->addressHouseNo, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressPostalCode, "addressPostalCode", $orderCustomerAddress->addressPostalCode, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressLocality, "addressLocality", $orderCustomerAddress->addressLocality, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressProvince, "addressProvince", $orderCustomerAddress->addressProvince, $orderCustomerAddressEle, "1", false);
	appendIfNotNull($orderCustomerAddress->addressCountryCode, "addressCountryCode", $orderCustomerAddress->addressCountryCode, $orderCustomerAddressEle, "1", false);

	//Set Customer History

	$customerHistory = $paymorrowOrderRequest->order->orderCustomer->customerHistory;

	if ($customerHistory != null) {
		$customerHistoryEle = appendIfNotNull($customerHistory, "customerHistory", $customerHistory, $orderCustomerEle, "0", false);
		appendIfNotNull($customerHistory->customerSince, "customerSince", $customerHistory->customerSince, $customerHistoryEle, "1", false);

		$customerOrderType = $customerHistory->customerOrders;
		if ($customerOrderType != null) {
			$customerOrderTypeEle = appendIfNotNull($customerOrderType, "customerOrders", $customerOrderType, $customerHistoryEle, "0", false);
			$customerFirstOrder = $customerOrderType->customerFirstOrder;
			if ($customerFirstOrder != null) {
				$customerFirstOrderEle = appendIfNotNull($customerFirstOrder, "customerFirstOrder", $customerFirstOrder, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerFirstOrder->orderDate, "orderDate", $customerFirstOrder->orderDate, $customerFirstOrderEle, "1", false);
				appendIfNotNull($customerFirstOrder->orderTotalAmount, "orderTotalAmount", $customerFirstOrder->orderTotalAmount, $customerFirstOrderEle, "1", false);
				appendIfNotNull($customerFirstOrder->orderPaidAmount, "orderPaidAmount", $customerFirstOrder->orderPaidAmount, $customerFirstOrderEle, "1", false);
				appendIfNotNull($customerFirstOrder->orderPaymentMethod, "orderPaymentMethod", $customerFirstOrder->orderPaymentMethod, $customerFirstOrderEle, "1", false);
				appendIfNotNull($customerFirstOrder->orderPaymentStatus, "orderPaymentStatus", $customerFirstOrder->orderPaymentStatus, $customerFirstOrderEle, "1", false);
			}

			$customerLastOrder = $customerOrderType->customerLastOrder;
			if ($customerLastOrder != null) {
				$customerLastOrderEle = appendIfNotNull($customerLastOrder, "customerLastOrder", $customerLastOrder, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerLastOrder->orderDate, "orderDate", $customerLastOrder->orderDate, $customerLastOrderEle, "1", false);
				appendIfNotNull($customerLastOrder->orderTotalAmount, "orderTotalAmount", $customerLastOrder->orderTotalAmount, $customerLastOrderEle, "1", false);
				appendIfNotNull($customerLastOrder->orderPaidAmount, "orderPaidAmount", $customerLastOrder->orderPaidAmount, $customerLastOrderEle, "1", false);
				appendIfNotNull($customerLastOrder->orderPaymentMethod, "orderPaymentMethod", $customerLastOrder->orderPaymentMethod, $customerLastOrderEle, "1", false);
				appendIfNotNull($customerLastOrder->orderPaymentStatus, "orderPaymentStatus", $customerLastOrder->orderPaymentStatus, $customerLastOrderEle, "1", false);
			}

			$customerOrdersLast7Days = $customerOrderType->customerOrdersLast7Days;
			if ($customerOrdersLast7Days != null) {
				$customerOrdersLast7DaysEle = appendIfNotNull($customerOrdersLast7Days, "customerOrdersLast7Days", $customerOrdersLast7Days, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerOrdersLast7Days->totalNoOfCustomerOrders, "totalNoOfCustomerOrders", $customerOrdersLast7Days->totalNoOfCustomerOrders, $customerOrdersLast7DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast7Days->totalAmountOfCustomerOrders, "totalAmountOfCustomerOrders", $customerOrdersLast7Days->totalAmountOfCustomerOrders, $customerOrdersLast7DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast7Days->totalAmountOfCustomerOrdersPaid, "totalAmountOfCustomerOrdersPaid", $customerOrdersLast7Days->totalAmountOfCustomerOrdersPaid, $customerOrdersLast7DaysEle, "1", false);
			}

			$customerOrdersLast30Days = $customerOrderType->customerOrdersLast30Days;
			if ($customerOrdersLast30Days != null) {
				$customerOrdersLast30DaysEle = appendIfNotNull($customerOrdersLast30Days, "customerOrdersLast30Days", $customerOrdersLast30Days, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerOrdersLast30Days->totalNoOfCustomerOrders, "totalNoOfCustomerOrders", $customerOrdersLast30Days->totalNoOfCustomerOrders, $customerOrdersLast30DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast30Days->totalAmountOfCustomerOrders, "totalAmountOfCustomerOrders", $customerOrdersLast30Days->totalAmountOfCustomerOrders, $customerOrdersLast30DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast30Days->totalAmountOfCustomerOrdersPaid, "totalAmountOfCustomerOrdersPaid", $customerOrdersLast30Days->totalAmountOfCustomerOrdersPaid, $customerOrdersLast30DaysEle, "1", false);
			}

			$customerOrdersLast180Days = $customerOrderType->customerOrdersLast180Days;
			if ($customerOrdersLast180Days != null) {
				$customerOrdersLast180DaysEle = appendIfNotNull($customerOrdersLast180Days, "customerOrdersLast180Days", $customerOrdersLast180Days, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerOrdersLast180Days->totalNoOfCustomerOrders, "totalNoOfCustomerOrders", $customerOrdersLast180Days->totalNoOfCustomerOrders, $customerOrdersLast180DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast180Days->totalAmountOfCustomerOrders, "totalAmountOfCustomerOrders", $customerOrdersLast180Days->totalAmountOfCustomerOrders, $customerOrdersLast180DaysEle, "1", false);
				appendIfNotNull($customerOrdersLast180Days->totalAmountOfCustomerOrdersPaid, "totalAmountOfCustomerOrdersPaid", $customerOrdersLast180Days->totalAmountOfCustomerOrdersPaid, $customerOrdersLast180DaysEle, "1", false);
			}

			$customerOrdersForEver = $customerOrderType->customerOrdersEver;
			if ($customerOrdersForEver != null) {
				$customerOrdersForEverEle = appendIfNotNull($customerOrdersForEver, "customerOrdersEver", $customerOrdersForEver, $customerOrderTypeEle, "0", false);
				appendIfNotNull($customerOrdersForEver->totalNoOfCustomerOrders, "totalNoOfCustomerOrders", $customerOrdersForEver->totalNoOfCustomerOrders, $customerOrdersForEverEle, "1", false);
				appendIfNotNull($customerOrdersForEver->totalAmountOfCustomerOrders, "totalAmountOfCustomerOrders", $customerOrdersForEver->totalAmountOfCustomerOrders, $customerOrdersForEverEle, "1", false);
				appendIfNotNull($customerOrdersForEver->totalAmountOfCustomerOrdersPaid, "totalAmountOfCustomerOrdersPaid", $customerOrdersForEver->totalAmountOfCustomerOrdersPaid, $customerOrdersForEverEle, "1", false);
			}

		}
	}
	// History Ends

	$addressShipmentType = $paymorrowOrderRequest->order->orderShippingAddress;
	$addressShipmentTypeEle = appendIfNotNull($addressShipmentType, "orderShippingAddress", $addressShipmentType, $orderEle, "0", false);
	appendIfNotNull($addressShipmentType->addressContact, "addressContact", $addressShipmentType->addressContact, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressOrganizationName, "addressOrganizationName", $addressShipmentType->addressOrganizationName, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressDepartment, "addressDepartment", $addressShipmentType->addressDepartment, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressBuilding, "addressBuilding", $addressShipmentType->addressBuilding, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressStreet, "addressStreet", $addressShipmentType->addressStreet, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressHouseNo, "addressHouseNo", $addressShipmentType->addressHouseNo, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressPostalCode, "addressPostalCode", $addressShipmentType->addressPostalCode, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressLocality, "addressLocality", $addressShipmentType->addressLocality, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressProvince, "addressProvince", $addressShipmentType->addressProvince, $addressShipmentTypeEle, "1", false);
	appendIfNotNull($addressShipmentType->addressCountryCode, "addressCountryCode", $addressShipmentType->addressCountryCode, $addressShipmentTypeEle, "1", false);

	$orderShipmentDetails = $paymorrowOrderRequest->order->orderShipmentDetails;
	$orderShipmentDetailsEle = appendIfNotNull($orderShipmentDetails, "orderShipmentDetails", $orderShipmentDetails, $orderEle, "0", false);
	appendIfNotNull($orderShipmentDetails->shipmentProvider, "shipmentProvider", $orderShipmentDetails->shipmentProvider, $orderShipmentDetailsEle, "1", false);
	appendIfNotNull($orderShipmentDetails->shipmentMethod, "shipmentMethod", $orderShipmentDetails->shipmentMethod, $orderShipmentDetailsEle, "1", false);

	$order = $paymorrowOrderRequest->order;
	appendIfNotNull($order->orderExpectedDeliveryDate, "orderExpectedDeliveryDate", $order->orderExpectedDeliveryDate, $orderEle, "1", false);
	appendIfNotNull($order->orderAmountNet, "orderAmountNet", $order->orderAmountNet, $orderEle, "1", false);

	$orderAmountVAT = $order->orderAmountVAT;
	$orderAmountVATEle = appendIfNotNull($orderAmountVAT, "orderAmountVAT", $orderAmountVAT, $orderEle, "0", false);

	$orderVATAmountsArray = $orderAmountVAT->orderVatRate;
	// Loop and Get data
	foreach ($orderVATAmountsArray as $orderVatRates) {
		$orderVatRatesEle = appendIfNotNull($orderVatRates, "orderVatRate", $orderVatRates, $orderAmountVATEle, "0", false);
		appendIfNotNull($orderVatRates->vatRate, "vatRate", $orderVatRates->vatRate, $orderVatRatesEle, "1", true);
		appendIfNotNull($orderVatRates->orderVatAmount, "orderVatAmount", $orderVatRates->orderVatAmount, $orderVatRatesEle, "1", false);
	}
	appendIfNotNull($order->orderAmountVATTotal, "orderAmountVATTotal", $order->orderAmountVATTotal, $orderEle, "1", false);
	appendIfNotNull($order->orderAmountGross, "orderAmountGross", $order->orderAmountGross, $orderEle, "1", false);
	appendIfNotNull($order->orderCurrencyCode, "orderCurrencyCode", $order->orderCurrencyCode, $orderEle, "1", false);

	$orderItemsArray = $order->orderItems;
	$orderItemsEle = appendIfNotNull($orderItemsArray, "orderItems", $orderItemsArray, $orderEle, "0", false);


	foreach ($orderItemsArray as $orderItem) {
		$orderItemEle = appendIfNotNull($orderItem, "orderItem", $orderItem, $orderItemsEle, "0", false);
		appendIfNotNull($orderItem->itemId, "itemId", $orderItem->itemId, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemQuantity, "itemQuantity", $orderItem->itemQuantity, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemUOM, "itemUOM", $orderItem->itemUOM, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemArticleId, "itemArticleId", $orderItem->itemArticleId, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemDescription, "itemDescription", $orderItem->itemDescription, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemCategory, "itemCategory", $orderItem->itemCategory, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemUnitPrice, "itemUnitPrice", $orderItem->itemUnitPrice, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemCurrencyCode, "itemCurrencyCode", $orderItem->itemCurrencyCode, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemVatRate, "itemVatRate", $orderItem->itemVatRate, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemExtendedAmount, "itemExtendedAmount", $orderItem->itemExtendedAmount, $orderItemEle, "1", false);

		$amtVatInc = "";

		if ($orderItem->itemAmountInclusiveVAT == 1) {
			$amtVatInc = "true";
		} else {
			$amtVatInc = "false";
		}
		appendIfNotNull($orderItem->itemAmountInclusiveVAT, "itemAmountInclusiveVAT", $amtVatInc, $orderItemEle, "1", false);
		appendIfNotNull($orderItem->itemComments, "itemComments", $orderItem->itemComments, $orderItemEle, "1", false);
	}

	$requestMerchantUrls = $paymorrowOrderRequest->requestMerchantUrls;
	$requestMerchantUrlsEle = appendIfNotNull($requestMerchantUrls, "requestMerchantUrls", $requestMerchantUrls, $orderRequestEle, "0", false);
	appendIfNotNull($requestMerchantUrls->merchantSuccessUrl, "merchantSuccessUrl", $requestMerchantUrls->merchantSuccessUrl, $requestMerchantUrlsEle, "1", false);
	appendIfNotNull($requestMerchantUrls->merchantErrorUrl, "merchantErrorUrl", $requestMerchantUrls->merchantErrorUrl, $requestMerchantUrlsEle, "1", false);
	appendIfNotNull($requestMerchantUrls->merchantPaymentMethodChangeUrl, "merchantPaymentMethodChangeUrl", $requestMerchantUrls->merchantPaymentMethodChangeUrl, $requestMerchantUrlsEle, "1", false);
	appendIfNotNull($requestMerchantUrls->merchantNotificationUrl, "merchantNotificationUrl", $requestMerchantUrls->merchantNotificationUrl, $requestMerchantUrlsEle, "1", false);

	// ------------------------------------------------------------
	// Make SOAP Message
	// invoke web service
	// ------------------------------------------------------------
	$soapEnvStart = "<?xml version=\"1.0\"?><S:Envelope xmlns:S=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
	$soapEnvEnd = "</S:Envelope>";

	$soapBody->normalize();
	$body = $soapBody->saveXML();

	$body = $body . "";

	$body = substr($body, strpos($body, '"?>') + 3);
	$body = str_replace("<paymorrowOrderRequest>", "<ns2:paymorrowOrderRequest>", $body);
	$body = str_replace("</paymorrowOrderRequest>", "</ns2:paymorrowOrderRequest>", $body);
	$body = str_replace("<paymorrow>", "<ns2:paymorrow xmlns:ns2=\"http://paymorrow.com/integration/paymorrowservice\">", $body);
	$body = str_replace("</paymorrow>", "</ns2:paymorrow>", $body);

	$pos1 = strpos($body, '<ns2:paymorrow');
	$tempreqBody = substr($body, $pos1);
	$pos1 = strpos($tempreqBody, '</ns2:paymorrow>');
	$tempreqBody = substr($tempreqBody, 0, $pos1 + strlen('</ns2:paymorrow>'));

	// removes invalid characters from xml
	$tempreqBody = normalizeXML($tempreqBody);

	$messageSignature = sha1($tempreqBody . $authorizationKey); // create Data signature for soap Header.
	$myHeader = "*** " . date(DATE_ATOM, time()) . " ******************************************************\n";


	log_output('paymorrow_client_raw_signature_log.txt', $myHeader . $messageSignature . "\n");

	#$messageSignature = sha1($tempreqBody.$key); // create Data signature for soap Header.
	//@todo aukommentiert
	//echo "SIGNATURE: ".$messageSignature;
	//@todo aukommentiert
	//echo "SIGNATURE: ".$authorizationKey;

	$soapHeader = "<S:Header><S:messageSignature S:mustUnderstand=\"0\">" . $messageSignature . "</S:messageSignature></S:Header>";

	//echo str_replace(">","&gt;", str_replace("<","&lt;", $body))."<p/>";

	// Complete request
	$reqBody = $soapEnvStart . $soapHeader . $body . $soapEnvEnd;

	log_output('paymorrow_client_sent_xml.txt', $reqBody);


	$res = sendHTTP($reqBody);

	if ($res == "") {
		return null;
	}

	//echo "Response from server:".$res;
	return preparePaymorrowResponse($res);
}