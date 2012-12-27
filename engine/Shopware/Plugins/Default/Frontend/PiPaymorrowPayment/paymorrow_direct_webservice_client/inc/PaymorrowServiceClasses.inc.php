<?php
class paymorrowOrderResponse
{

	/**
	 * @var dateTime
	 */
	public $responseTimestamp; // dateTime

	/**
	 * @var responsePaymorrowOrder
	 */
	public $responsePaymorrowOrder; // responsePaymorrowOrder

	/**
	 * @var codeTypes
	 */
	public $responseResultCode; // codeTypes

	/**
	 * @var string
	 */
	public $responseResultURL; // string

	/**
	 * @var responseErrorType
	 */
	public $responseError; // responseErrorType
}

class responsePaymorrowOrder
{

	/**
	 * @var boolean
	 */
	public $paymorrowOrderRequestModified; // boolean

	/**
	 * @var paymorrowOrderRequestType
	 */
	public $paymorrowOrderRequest; // paymorrowOrderRequestType
}

class paymorrow
{

	/**
	 * @var paymorrowOrderRequestType
	 */
	public $paymorrowOrderRequest; // paymorrowOrderRequestType
}

class paymorrowOrderRequestType
{

	/**
	 * @var stringType50
	 */
	public $requestMerchantId; // stringType50

	/**
	 * @var stringType50
	 */
	public $requestId; // stringType50

	/**
	 * @var dateTime
	 */
	public $requestTimestamp; // dateTime

	/**
	 * @var languageType
	 */
	public $requestLanguageCode; // languageType

	/**
	 * @var orderType
	 */
	public $order; // orderType

	/**
	 * @var requestMerchantUrlType
	 */
	public $requestMerchantUrls; // requestMerchantUrlType
}

class orderType
{

	/**
	 * @var stringType50
	 */
	public $orderId; // stringType50

	/**
	 * @var dateTime
	 */
	public $orderTimestamp; // dateTime

	/**
	 * @var int
	 */
	public $orderShoppingDuration; // int

	/**
	 * @var int
	 */
	public $orderCheckoutDuration; // int

	/**
	 * @var stringType50
	 */
	public $orderSalesChannelId; // stringType50

	/**
	 * @var customerType
	 */
	public $orderCustomer; // customerType

	/**
	 * @var addressType
	 */
	public $orderShippingAddress; // addressType

	/**
	 * @var orderShipmentDetailType
	 */
	public $orderShipmentDetails; // orderShipmentDetailType

	/**
	 * @var date
	 */
	public $orderExpectedDeliveryDate; // date

	/**
	 * @var decimal
	 */
	public $orderAmountNet; // decimal

	/**
	 * @var orderAmountVatType
	 */
	public $orderAmountVAT; // orderAmountVatType

	/**
	 * @var decimal
	 */
	public $orderAmountVATTotal; // decimal

	/**
	 * @var decimal
	 */
	public $orderAmountGross; // decimal

	/**
	 * @var currencyType
	 */
	public $orderCurrencyCode; // currencyType

	/**
	 * @var orderItems
	 */
	public $orderItems; // orderItems
}

class orderItems
{

	/**
	 * @var orderItemType
	 */
	public $orderItem; // orderItemType
}

class customerType
{

	/**
	 * @var stringType50
	 */
	public $customerId; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerGroupId; // stringType50

	/**
	 * @var languageType
	 */
	public $customerPreferredLanguage; // languageType

	/**
	 * @var string
	 */
	public $customerIPAddress; // string

	/**
	 * @var customerPersonalDetailsType
	 */
	public $customerPersonalDetails; // customerPersonalDetailsType

	/**
	 * @var addressType
	 */
	public $customerAddress; // addressType

	/**
	 * @var customerHistoryType
	 */
	public $customerHistory; // customerHistoryType

	/**
	 * @var customerTypes
	 */
	public $orderCustomerType; // customerTypes
}

class customerPersonalDetailsType
{

	/**
	 * @var salutationTypes
	 */
	public $customerSalutation; // salutationTypes

	/**
	 * @var stringType50
	 */
	public $customerNamePrefix; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerGivenName; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerMiddleName; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerSurname; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerNameSuffix; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerOrganizationName; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerPhoneNo; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerFaxNo; // stringType50

	/**
	 * @var stringType50
	 */
	public $customerMobileNo; // stringType50

	/**
	 * @var stringType320
	 */
	public $customerEmail; // stringType320

	/**
	 * @var stringType50
	 */
	public $customerGender; // stringType50

	/**
	 * @var date
	 */
	public $customerDateOfBirth; // date
}

class addressType
{

	/**
	 * @var stringType50
	 */
	public $addressContact; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressOrganizationName; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressDepartment; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressBuilding; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressStreet; // stringType50

	/**
	 * @var stringType10
	 */
	public $addressHouseNo; // stringType10

	/**
	 * @var stringType20
	 */
	public $addressPostalCode; // stringType20

	/**
	 * @var stringType50
	 */
	public $addressLocality; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressProvince; // stringType50

	/**
	 * @var stringType50
	 */
	public $addressCountryCode; // stringType50
}

class customerHistoryType
{

	/**
	 * @var dateTime
	 */
	public $customerSince; // dateTime

	/**
	 * @var customerOrdersType
	 */
	public $customerOrders; // customerOrdersType
}

class customerOrdersType
{

	/**
	 * @var customerOrderType
	 */
	public $customerFirstOrder; // customerOrderType

	/**
	 * @var customerOrderType
	 */
	public $customerLastOrder; // customerOrderType

	/**
	 * @var totalOrderType
	 */
	public $customerOrdersLast7Days; // totalOrderType

	/**
	 * @var totalOrderType
	 */
	public $customerOrdersLast30Days; // totalOrderType

	/**
	 * @var totalOrderType
	 */
	public $customerOrdersLast180Days; // totalOrderType

	/**
	 * @var totalOrderType
	 */
	public $customerOrdersEver; // totalOrderType
}

class customerOrderType
{

	/**
	 * @var dateTime
	 */
	public $orderDate; // dateTime

	/**
	 * @var decimal
	 */
	public $orderTotalAmount; // decimal

	/**
	 * @var decimal
	 */
	public $orderPaidAmount; // decimal

	/**
	 * @var stringType10
	 */
	public $orderPaymentMethod; // stringType10

	/**
	 * @var orderPaymentStatusType
	 */
	public $orderPaymentStatus; // orderPaymentStatusType
}

class totalOrderType
{

	/**
	 * @var int
	 */
	public $totalNoOfCustomerOrders; // int

	/**
	 * @var decimal
	 */
	public $totalAmountOfCustomerOrders; // decimal

	/**
	 * @var decimal
	 */
	public $totalAmountOfCustomerOrdersPaid; // decimal
}

class orderShipmentDetailType
{

	/**
	 * @var stringType255
	 */
	public $shipmentProvider; // stringType255

	/**
	 * @var stringType255
	 */
	public $shipmentMethod; // stringType255
}

class orderAmountVatType
{

	/**
	 * @var orderVatRate
	 */
	public $orderVatRate; // orderVatRate
}

class orderVatRate
{

	/**
	 * @var decimal
	 */
	public $orderVatAmount; // decimal

	/**
	 * @var decimal
	 */
	public $vatRate; // decimal
}

class orderItemType
{

	/**
	 * @var stringType50
	 */
	public $itemId; // stringType50

	/**
	 * @var decimal
	 */
	public $itemQuantity; // decimal

	/**
	 * @var stringType50
	 */
	public $itemUOM; // stringType50

	/**
	 * @var stringType50
	 */
	public $itemArticleId; // stringType50

	/**
	 * @var stringType255
	 */
	public $itemDescription; // stringType255

	/**
	 * @var stringType255
	 */
	public $itemCategory; // stringType255

	/**
	 * @var decimal
	 */
	public $itemUnitPrice; // decimal

	/**
	 * @var currencyType
	 */
	public $itemCurrencyCode; // currencyType

	/**
	 * @var decimal
	 */
	public $itemVatRate; // decimal

	/**
	 * @var decimal
	 */
	public $itemExtendedAmount; // decimal

	/**
	 * @var boolean
	 */
	public $itemAmountInclusiveVAT; // boolean

	/**
	 * @var stringType255
	 */
	public $itemComments; // stringType255
}

class requestMerchantUrlType
{

	/**
	 * @var string
	 */
	public $merchantSuccessUrl; // string

	/**
	 * @var string
	 */
	public $merchantErrorUrl; // string

	/**
	 * @var string
	 */
	public $merchantPaymentMethodChangeUrl; // string

	/**
	 * @var string
	 */
	public $merchantNotificationUrl; // string
}

class paymorrowResponse
{

	/**
	 * @var paymorrowOrderResponse
	 */
	public $paymorrowOrderResponse; // paymorrowOrderResponse
}

class responseErrorType
{

	/**
	 * @var responseErrorTypes
	 */
	public $responseErrorType; // responseErrorTypes

	/**
	 * @var int
	 */
	public $responseErrorNo; // int

	/**
	 * @var string
	 */
	public $responseErrorMessage; // string
}

class languageType
{
}

class salutationTypes
{
}

class customerTypes
{
}

class currencyType
{
}

class codeTypes
{
}

class responseErrorTypes
{
}

class orderPaymentStatusType
{
}

class stringType10
{
}

class stringType20
{
}

class stringType50
{
}

class stringType255
{
}

class stringType320
{
}


/**
 * PaymorrowService class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class PaymorrowService extends SoapClient
{

	private static $classmap = array(
		'paymorrowOrderResponse'      => 'paymorrowOrderResponse',
		'responsePaymorrowOrder'      => 'responsePaymorrowOrder',
		'paymorrow'                   => 'paymorrow',
		'paymorrowOrderRequestType'   => 'paymorrowOrderRequestType',
		'orderType'                   => 'orderType',
		'orderItems'                  => 'orderItems',
		'customerType'                => 'customerType',
		'customerPersonalDetailsType' => 'customerPersonalDetailsType',
		'addressType'                 => 'addressType',
		'customerHistoryType'         => 'customerHistoryType',
		'customerOrdersType'          => 'customerOrdersType',
		'customerOrderType'           => 'customerOrderType',
		'totalOrderType'              => 'totalOrderType',
		'orderShipmentDetailType'     => 'orderShipmentDetailType',
		'orderAmountVatType'          => 'orderAmountVatType',
		'orderVatRate'                => 'orderVatRate',
		'orderItemType'               => 'orderItemType',
		'requestMerchantUrlType'      => 'requestMerchantUrlType',
		'paymorrowResponse'           => 'paymorrowResponse',
		'responseErrorType'           => 'responseErrorType',
		'languageType'                => 'languageType',
		'salutationTypes'             => 'salutationTypes',
		'customerTypes'               => 'customerTypes',
		'currencyType'                => 'currencyType',
		'codeTypes'                   => 'codeTypes',
		'responseErrorTypes'          => 'responseErrorTypes',
		'orderPaymentStatusType'      => 'orderPaymentStatusType',
		'stringType10'                => 'stringType10',
		'stringType20'                => 'stringType20',
		'stringType50'                => 'stringType50',
		'stringType255'               => 'stringType255',
		'stringType320'               => 'stringType320',
	);

	public function PaymorrowService($wsdl = "PaymorrowService.wsdl", $options = array())
	{
		foreach (self::$classmap as $key => $value) {
			if (!isset($options['classmap'][$key])) {
				$options['classmap'][$key] = $value;
			}
		}
		parent::__construct($wsdl, $options);
	}

	/**
	 *
	 *
	 * @param paymorrow $parameters
	 * @return paymorrowResponse
	 */
	public function paymorrow(paymorrow $parameters)
	{
		$header = new SoapHeader('http://paymorrow.com/integration/paymorrowservice',
			'signature',
			'997f4684314a30db4c3849ab7b26b376');

		$this->__setSoapHeaders($header);
		return $this->__soapCall('paymorrow', array($parameters), array(
				'uri'        => 'http://paymorrow.com/integration/paymorrowservice',
				'soapaction' => ''
			)
		);
	}

}
