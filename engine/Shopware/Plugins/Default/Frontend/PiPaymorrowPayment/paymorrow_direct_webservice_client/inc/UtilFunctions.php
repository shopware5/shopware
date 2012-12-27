<?php
/**
 * this method will create the Node in DOM tree with the given nodeName and Parent Node.
 */
function appendIfNotNull($testField, $nodeName, $nodeData, $parentNode, $nodeType, $attInd, $namespaceInd = false)
{
	$newNode = null;
	$soapBody = Shopware()->Session()->soapBody;
	#$nodeName == 'vatRate' OR $nodeName == 'orderVatAmount' OR $nodeName == 'itemVatRate'
	if (!empty($testField) || $testField === 0) {
		if (!$attInd) {
			if ($namespaceInd) {
				$newNode = $soapBody->createElementNS('http://paymorrow.com/integration/paymorrowservice', $nodeName);
				//'http://paymorrow.com/integration/paymorrowservice',
			} else {
				$newNode = $soapBody->createElement($nodeName);
			}

			$parentNode->appendChild($newNode);
		}

		// For parent nodes, Parent nodes not need to have text node.
		if ($nodeType == "0") {
			//        return $newNode;
		} else {
			if ($attInd) {
				// If current Element needs to append as Attribute.
				$attrNode = $soapBody->createAttribute($nodeName);
				$parentNode->appendChild($attrNode);
				$attrVal = $soapBody->createTextNode($nodeData);
				$attrNode->appendChild($attrVal);
			} else {
				// add the text node to newly created node.
				$newNode->appendChild($soapBody->createTextNode($nodeData));
			}

		}
	}
	// return newly created node.
	return $newNode;

}

/**
 * This function return the node data(Only data for first index)
 */
function getNodeValueByTagName($node, $nodeName)
{
	$tagList = $node->getElementsByTagName($nodeName);
	if ($tagList != null && $tagList->item(0)) {
		return $tagList->item(0)->nodeValue;
	}
}

/**
 * return the Node value, if node is not null in DOM tree.
 */
function getIfNotNull($resp, $nodeName)
{
	$tagList = $resp->getElementsByTagName($nodeName);
	if ($tagList && $tagList->item(0)) {
		//print_r($tagList);
		//echo ",";
		//print_r($tagList->item(0));
		//echo ",$nodeName,";
		//echo $tagList->item(0)->nodeName;
		//echo "<br>";
		return $tagList->item(0)->nodeValue;
	}
	//@todo aukommentiert
	//echo "Element ".$nodeName." not found.<br>";
	return null;
}


/**
 * This method takes the xml as String and then creates the PaymorrowOrderResponse from the
 * XML String(A part of response which sent by Paymorrow Server ).
 */
function preparePaymorrowResponse($res)
{

	$paymorrowOrderResponse = new paymorrowOrderResponse();

	try {
		// Ignore the HTTP related items, only SOAP Part is required.
		$pos = strpos($res, '<?xml');
		$respSoap = substr($res, $pos, strlen($res));

		// Create DOM Document out of SOAP Message.
		$paymorrowResp = new DOMDocument();
		$paymorrowResp->loadXML($respSoap);

		//print $paymorrowResp->saveXML();

		// Set all the elements from the XML to Object.

		$request = new PaymorrowOrderRequestType(); //t.XMLtoObject("");
		$request->requestMerchantId = getIfNotNull($paymorrowResp, "requestMerchantId");

		$request->requestId = getIfNotNull($paymorrowResp, "requestId");
		$request->requestTimestamp = getIfNotNull($paymorrowResp, "requestTimestamp");
		$request->requestLanguageCode = getIfNotNull($paymorrowResp, "requestLanguageCode");

		$requestMerchantUrls = new RequestMerchantUrlType();
		$requestMerchantUrls->merchantSuccessUrl = getIfNotNull($paymorrowResp, "merchantSuccessUrl");
		$requestMerchantUrls->merchantErrorUrl = getIfNotNull($paymorrowResp, "merchantErrorUrl");
		$requestMerchantUrls->merchantPaymentMethodChangeUrl = getIfNotNull($paymorrowResp, "merchantPaymentMethodChangeUrl");
		$requestMerchantUrls->merchantNotificationUrl = getIfNotNull($paymorrowResp, "merchantNotificationUrl");
		$request->requestMerchantUrls = $requestMerchantUrls;

		//Create Order Type
		$order = new OrderType();
		$order->orderId = getIfNotNull($paymorrowResp, "orderId");
		$order->orderTimestamp = getIfNotNull($paymorrowResp, "orderTimestamp");
		$order->orderShoppingDuration = getIfNotNull($paymorrowResp, "orderShoppingDuration");
		$order->orderCheckoutDuration = getIfNotNull($paymorrowResp, "orderCheckoutDuration");
		$order->orderSalesChannelId = getIfNotNull($paymorrowResp, "orderSalesChannelId");

		//Create Customer for the order
		$customer = new CustomerType();
		$customer->customerId = getIfNotNull($paymorrowResp, "customerId");
		$customer->customerGroupId = getIfNotNull($paymorrowResp, "customerGroupId");
		$customer->customerPreferredLanguage = getIfNotNull($paymorrowResp, "customerPreferredLanguage");
		$customer->customerIPAddress = getIfNotNull($paymorrowResp, "customerIPAddress");
		$customer->orderCustomerType = getIfNotNull($paymorrowResp, "orderCustomerType");

		// Set the customer personal details
		$customerPersonalDetails = new CustomerPersonalDetailsType();
		$customerPersonalDetails->customerSalutation = getIfNotNull($paymorrowResp, "customerSalutation");
		$customerPersonalDetails->customerNamePrefix = getIfNotNull($paymorrowResp, "customerNamePrefix");
		$customerPersonalDetails->customerGivenName = getIfNotNull($paymorrowResp, "customerGivenName");
		$customerPersonalDetails->customerMiddleName = getIfNotNull($paymorrowResp, "customerMiddleName");
		$customerPersonalDetails->customerSurname = getIfNotNull($paymorrowResp, "customerSurname");
		$customerPersonalDetails->customerNameSuffix = getIfNotNull($paymorrowResp, "customerNameSuffix");
		$customerPersonalDetails->customerOrganizationName = getIfNotNull($paymorrowResp, "customerOrganizationName");
		$customerPersonalDetails->customerPhoneNo = getIfNotNull($paymorrowResp, "customerPhoneNo");
		$customerPersonalDetails->customerFaxNo = getIfNotNull($paymorrowResp, "customerFaxNo");
		$customerPersonalDetails->customerMobileNo = getIfNotNull($paymorrowResp, "customerMobileNo");
		$customerPersonalDetails->customerEmail = getIfNotNull($paymorrowResp, "customerEmail");
		$customerPersonalDetails->customerGender = getIfNotNull($paymorrowResp, "customerGender");
		$customerPersonalDetails->customerDateOfBirth = getIfNotNull($paymorrowResp, "customerDateOfBirth");

		$customerAddress = new AddressType();
		$customerAddress->addressContact = getIfNotNull($paymorrowResp, "addressContact");
		$customerAddress->addressOrganizationName = getIfNotNull($paymorrowResp, "addressOrganizationName");
		$customerAddress->addressDepartment = getIfNotNull($paymorrowResp, "addressDepartment");
		$customerAddress->addressBuilding = getIfNotNull($paymorrowResp, "addressBuilding");
		$customerAddress->addressStreet = getIfNotNull($paymorrowResp, "addressStreet");
		$customerAddress->addressHouseNo = getIfNotNull($paymorrowResp, "addressHouseNo");
		$customerAddress->addressPostalCode = getIfNotNull($paymorrowResp, "addressPostalCode");
		$customerAddress->addressLocality = getIfNotNull($paymorrowResp, "addressLocality");
		$customerAddress->addressProvince = getIfNotNull($paymorrowResp, "addressProvince");
		$customerAddress->addressCountryCode = getIfNotNull($paymorrowResp, "addressCountryCode");
		$customer->customerAddress = $customerAddress;

		//Set Customer History
		// Set for first order
		// Set for Last order
		// Set orders for 7,30,180 and Forever days


		$customerHistory = getIfNotNull($paymorrowResp, "customerHistory");
		if ($customerHistory != null) {
			$customerLastOrder = new customerOrderType();
			$customerFirstOrder = new customerOrderType();
			$customerHistory = new customerHistoryType();
			$customerHistory->customerSince = getIfNotNull($paymorrowResp, "customerSince");

			$customerOrderType = getIfNotNull($paymorrowResp, "customerOrders");
			if ($customerOrderType != null) {
				$customerOrderType = new customerOrdersType();

				$customerFirstOrder->orderDate = getIfNotNull($paymorrowResp, "orderDate");
				$customerFirstOrder->orderTotalAmount = getIfNotNull($paymorrowResp, "orderTotalAmount");
				$customerFirstOrder->orderPaidAmount = getIfNotNull($paymorrowResp, "orderPaidAmount");
				$customerFirstOrder->orderPaymentMethod = getIfNotNull($paymorrowResp, "orderPaymentMethod");
				$customerFirstOrder->orderPaymentStatus = getIfNotNull($paymorrowResp, "orderPaymentStatus");

				$customerLastOrder->orderDate = getIfNotNull($paymorrowResp, "orderDate");
				$customerLastOrder->orderTotalAmount = getIfNotNull($paymorrowResp, "orderTotalAmount");
				$customerLastOrder->orderPaidAmount = getIfNotNull($paymorrowResp, "orderPaidAmount");
				$customerLastOrder->orderPaymentMethod = getIfNotNull($paymorrowResp, "orderPaymentMethod");
				$customerLastOrder->orderPaymentStatus = getIfNotNull($paymorrowResp, "orderPaymentStatus");

				$last7Orders = $paymorrowResp->getElementsByTagName("customerOrdersLast7Days");
				$customerOrdersLast7Days = new totalOrderType();
				if ((!empty($last7Orders)) && $last7Orders != null && $last7Orders->length > 0) {
					$last7OrdersNode = $last7Orders->item(0);
					$last7OrdersChilds = $last7OrdersNode->childNodes;
					$customerOrdersLast7Days->totalNoOfCustomerOrders = $last7OrdersChilds->item(0)->nodeValue;
					$customerOrdersLast7Days->totalAmountOfCustomerOrders = $last7OrdersChilds->item(1)->nodeValue;
					$customerOrdersLast7Days->totalAmountOfCustomerOrdersPaid = $last7OrdersChilds->item(2)->nodeValue;
				}

				$last30Orders = $paymorrowResp->getElementsByTagName("customerOrdersLast30Days");
				$customerOrdersLast30Days = new totalOrderType();
				if ((!empty($last30Orders)) && $last30Orders != null && $last30Orders->length > 0) {
					$last30OrdersNode = $last30Orders->item(0);
					$last30OrdersChilds = $last30OrdersNode->childNodes;
					$customerOrdersLast30Days->totalNoOfCustomerOrders = $last30OrdersChilds->item(0)->nodeValue;
					$customerOrdersLast30Days->totalAmountOfCustomerOrders = $last30OrdersChilds->item(1)->nodeValue;
					$customerOrdersLast30Days->totalAmountOfCustomerOrdersPaid = $last30OrdersChilds->item(2)->nodeValue;
				}

				$last180Orders = $paymorrowResp->getElementsByTagName("customerOrdersLast180Days");
				$customerOrdersLast180Days = new totalOrderType();
				if ((!empty($last180Orders)) && $last180Orders != null && $last180Orders->length > 0) {
					$last180OrdersNode = $last180Orders->item(0);
					$last180OrdersChilds = $last180OrdersNode->childNodes;
					$customerOrdersLast180Days->totalNoOfCustomerOrders = $last180OrdersChilds->item(0)->nodeValue;
					$customerOrdersLast180Days->totalAmountOfCustomerOrders = $last180OrdersChilds->item(1)->nodeValue;
					$customerOrdersLast180Days->totalAmountOfCustomerOrdersPaid = $last180OrdersChilds->item(2)->nodeValue;
				}

				$ordersEver = $paymorrowResp->getElementsByTagName("customerOrdersEver");
				$customerOrdersEver = new totalOrderType();
				if ((!empty($ordersEver)) && $ordersEver != null && $ordersEver->length > 0) {
					$ordersNode = $ordersEver->item(0);
					$ordersEverChilds = $ordersNode->childNodes;
					$customerOrdersEver->totalNoOfCustomerOrders = $ordersEverChilds->item(0)->nodeValue;
					$customerOrdersEver->totalAmountOfCustomerOrders = $ordersEverChilds->item(1)->nodeValue;
					$customerOrdersEver->totalAmountOfCustomerOrdersPaid = $ordersEverChilds->item(2)->nodeValue;
				}


				$customerOrderType->customerFirstOrder = $customerFirstOrder;
				$customerOrderType->customerLastOrder = $customerLastOrder;
				$customerOrderType->customerOrdersLast7Days = $customerOrdersLast7Days;
				$customerOrderType->customerOrdersLast30Days = $customerOrdersLast30Days;
				$customerOrderType->customerOrdersLast180Days = $customerOrdersLast180Days;
				$customerOrderType->customerOrdersEver = $customerOrdersEver;
				$customerHistory->customerOrders = $customerOrderType;
			}
		}

		$customer->customerHistory = $customerHistory;
		// History Ends

		//Set shipment address
		$addressShipmentType = new AddressType();
		$addressShipmentType->addressContact = getIfNotNull($paymorrowResp, "addressContact");
		$addressShipmentType->addressOrganizationName = getIfNotNull($paymorrowResp, "addressOrganizationName");
		$addressShipmentType->addressDepartment = getIfNotNull($paymorrowResp, "addressDepartment");
		$addressShipmentType->addressBuilding = getIfNotNull($paymorrowResp, "addressBuilding");
		$addressShipmentType->addressStreet = getIfNotNull($paymorrowResp, "addressStreet");
		$addressShipmentType->addressHouseNo = getIfNotNull($paymorrowResp, "addressHouseNo");
		$addressShipmentType->addressPostalCode = getIfNotNull($paymorrowResp, "addressPostalCode");
		$addressShipmentType->addressLocality = getIfNotNull($paymorrowResp, "addressLocality");
		$addressShipmentType->addressProvince = getIfNotNull($paymorrowResp, "addressProvince");
		$addressShipmentType->addressCountryCode = getIfNotNull($paymorrowResp, "addressCountryCode");

		$order->orderShippingAddress = $addressShipmentType;
		$orderShipmentDetails = new OrderShipmentDetailType();
		$orderShipmentDetails->shipmentMethod = getIfNotNull($paymorrowResp, "shipmentMethod");
		$orderShipmentDetails->shipmentProvider = getIfNotNull($paymorrowResp, "shipmentProvider");
		$order->orderShipmentDetails = $orderShipmentDetails;

		$order->orderExpectedDeliveryDate = getIfNotNull($paymorrowResp, "orderExpectedDeliveryDate");
		$order->orderAmountNet = getIfNotNull($paymorrowResp, "orderAmountNet");

		$orderAmountVAT = new orderAmountVatType();

		$domNodeList = $paymorrowResp->getElementsByTagName('orderVatRate');

		// Set VAT for the order
		$orderAmountVATArray = new ArrayObject();
		$i = 0;
		for ($i; $i < $domNodeList->length; $i++) {
			$vatNode = $domNodeList->item($i);
			$vatAmount = $vatNode->nodeValue;

			foreach ($vatNode->attributes as $attrNode) {
				$vatRate = $attrNode->value;
			}

			$orderVatRateType = new orderVatRate();
			$orderVatRateType->orderVatAmount = $vatAmount;
			$orderVatRateType->orderVatRate = $vatRate;

			//put in Array
			$orderAmountVATArray[$i] = $orderVatRateType;
		}

		$orderAmountVAT->orderVatRate = $orderAmountVATArray;
		$order->orderAmountVAT = $orderAmountVAT;

		$order->orderAmountVATTotal = getIfNotNull($paymorrowResp, "orderAmountVATTotal");

		$order->orderAmountGross = getIfNotNull($paymorrowResp, "orderAmountGross");
		$order->orderCurrencyCode = getIfNotNull($paymorrowResp, "orderCurrencyCode");

		//loop
		$orderItemsList = $paymorrowResp->getElementsByTagName('orderItem');

		// Set order items
		$orderItemsArray = new ArrayObject();
		$i = 0;
		for ($i; $i < $orderItemsList->length; $i++) {
			$nodeOrderItem = $orderItemsList->item($i);

			$orderItems = new OrderItemType();
			$orderItems->itemId = getNodeValueByTagName($nodeOrderItem, 'itemId');
			$orderItems->itemQuantity = getNodeValueByTagName($nodeOrderItem, 'itemQuantity');
			$orderItems->itemUOM = getNodeValueByTagName($nodeOrderItem, 'ItemUOM');
			$orderItems->itemArticleId = getNodeValueByTagName($nodeOrderItem, 'itemArticleId');
			$orderItems->itemDescription = getNodeValueByTagName($nodeOrderItem, 'itemDescription');
			$orderItems->itemCategory = getNodeValueByTagName($nodeOrderItem, 'itemCategory');
			$orderItems->itemUnitPrice = getNodeValueByTagName($nodeOrderItem, 'itemUnitPrice');
			$orderItems->itemCurrencyCode = getNodeValueByTagName($nodeOrderItem, 'itemCurrencyCode');
			$orderItems->itemVatRate = getNodeValueByTagName($nodeOrderItem, 'itemVatRate');
			$orderItems->itemExtendedAmount = getNodeValueByTagName($nodeOrderItem, 'itemExtendedAmount');
			$orderItems->itemAmountInclusiveVAT = getNodeValueByTagName($nodeOrderItem, 'itemAmountInclusiveVAT');
			$orderItems->itemComments = getNodeValueByTagName($nodeOrderItem, 'itemComments');

			$orderItemsArray[$i] = $orderItems;
		}

		$order->orderItems = $orderItemsArray;

		$customer->customerPersonalDetails = $customerPersonalDetails;

		$order->orderCustomer = $customer;

		$request->order = $order;

		$responsePaymorrowOrder = new responsePaymorrowOrder();
		$responsePaymorrowOrder->paymorrowOrderRequestModified = getIfNotNull($paymorrowResp, "paymorrowOrderRequestModified");
		$responsePaymorrowOrder->paymorrowOrderRequest = $request;

		$paymorrowOrderResponse->responsePaymorrowOrder = $responsePaymorrowOrder;

		$paymorrowOrderResponse->responseTimestamp = getIfNotNull($paymorrowResp, "responseTimestamp");
		$paymorrowOrderResponse->responseResultCode = getIfNotNull($paymorrowResp, "responseResultCode");
		$paymorrowOrderResponse->responseResultURL = getIfNotNull($paymorrowResp, "responseResultURL");

		// Set response Error (if any)
		$responseError = getIfNotNull($paymorrowResp, "responseError");

		if (!empty ($responseError)) {
			$respError = new responseErrorType();

			$respError->responseErrorType = getIfNotNull($paymorrowResp, "responseErrorType");
			$respError->responseErrorNo = getIfNotNull($paymorrowResp, "responseErrorNo");
			$respError->responseErrorMessage = getIfNotNull($paymorrowResp, "responseErrorMessage");
			$paymorrowOrderResponse->responseError = $respError;
		}

	} catch (Exception $e) {
		$e->getTraceAsString();
		$paymorrowOrderResponse = null;
	}
	return $paymorrowOrderResponse;
}