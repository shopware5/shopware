<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket7670 extends Enlight_Components_Test_Controller_TestCase
{
    private $orderId;

    /**
     * Set up test case
     */
    public function setUp()
    {
        parent::setUp();

        $this->orderId = Shopware()->Db()->fetchOne('SELECT id FROM s_order WHERE ordernumber = 20001');

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        $sql = "
            DELETE FROM `s_order_documents` WHERE `orderID` = :orderID;
        ";

        Shopware()->Db()->query($sql, array('orderID' => $this->orderId));
    }

    /**
     * Batch create documents for an order. Checks that the number of shops is not changed
     */
    public function testBatchProcessOrderDocument()
    {
        $postData = array (  'module' => 'backend',  'controller' => 'Order',  'action' => 'batchProcess',  'targetField' => 'orders',  '_dc' => '1391161752595',  'docType' => '1',  'mode' => '',  'forceTaxCheck' => '1',  'displayDate' => '2014-01-31T10:49:12',  'deliveryDate' => '2014-01-31T10:49:12',  'autoSend' => 'true',  'id' => 15,  'number' => '20001',  'customerId' => 2,  'invoiceAmountNet' => 839.13,  'invoiceShippingNet' => 0,  'status' => 0,  'cleared' => 17,  'paymentId' => 4,  'transactionId' => '',  'comment' => '',  'customerComment' => '',  'internalComment' => '',  'net' => 1,  'taxFree' => 0,  'partnerId' => '',  'temporaryId' => '',  'referer' => '',  'clearedDate' => '',  'trackingCode' => '',  'languageIso' => '1',  'dispatchId' => 9,  'currency' => 'EUR',  'currencyFactor' => 1,  'shopId' => 1,  'remoteAddress' => '217.86.205.141',  'invoiceAmount' => 998.56,  'invoiceShipping' => 0,  'orderTime' => '2012-08-30T16:15:54',  'invoiceShippingEuro' => 0,  'invoiceAmountEuro' => 998.56,  'remoteAddressConverted' => '217.86.205.xxx',  'customer' =>   array (    0 =>     array (      'id' => 2,      'groupKey' => 'H',      'email' => 'mustermann@b2b.de',      'active' => true,      'accountMode' => 0,      'confirmationKey' => '',      'paymentId' => 4,      'firstLogin' => '2012-08-30T00:00:00',      'lastLogin' => '2012-08-30T11:43:17',      'newsletter' => 0,      'validation' => 0,      'languageId' => 0,      'shopId' => 1,      'priceGroupId' => 0,      'internalComment' => '',      'failedLogins' => 0,      'referer' => '',    ),  ),  'shop' =>   array (    0 =>     array (      'id' => 1,      'default' => true,      'localeId' => 0,      'categoryId' => 3,      'name' => 'Deutsch',    ),  ),  'dispatch' =>   array (    0 =>     array (      'id' => 9,      'name' => 'Standard Versand',      'type' => 0,      'comment' => '',      'active' => NULL,      'position' => 1,    ),  ),  'paymentStatus' =>   array (    0 =>     array (      'id' => 17,      'description' => 'Open',    ),  ),  'orderStatus' =>   array (    0 =>     array (      'id' => 0,      'description' => 'Open',    ),  ),  'locale' =>   array (    0 =>     array (      'id' => 1,      'language' => 'Deutsch',      'territory' => 'Deutschland',      'locale' => 'de_DE',      'name' => 'Deutsch (Deutschland)',    ),  ),  'attribute' =>   array (    0 =>     array (      'id' => 1,      'orderId' => 15,      'attribute1' => '',      'attribute2' => '',      'attribute3' => '',      'attribute4' => '',      'attribute5' => '',      'attribute6' => '',    ),  ),  'billing' =>   array (    0 =>     array (      'id' => 1,      'salutation' => 'company',      'company' => 'B2B',      'department' => 'Einkauf',      'firstName' => 'Händler',      'lastName' => 'Kundengruppe-Netto',      'street' => 'Musterweg',      'streetNumber' => '1',      'zipCode' => '00000',      'city' => 'Musterstadt',      'countryId' => 2,      'number' => '',      'phone' => '012345 / 6789',      'fax' => '',      'vatId' => '',      'orderId' => 15,      'shopware.apps.order.model.order' =>       array (      ),    ),  ),  'shipping' =>   array (    0 =>     array (      'id' => 1,      'salutation' => 'company',      'company' => 'B2B',      'department' => 'Einkauf',      'firstName' => 'Händler',      'lastName' => 'Kundengruppe-Netto',      'street' => 'Musterweg',      'streetNumber' => '1',      'zipCode' => '00000',      'city' => 'Musterstadt',      'countryId' => 2,      'orderId' => 15,      'shopware.apps.order.model.order' =>       array (      ),    ),  ),  'debit' =>   array (    0 =>     array (      'id' => 3,      'customerId' => 2,      'account' => '',      'bankCode' => '',      'bankName' => '',      'accountHolder' => '',    ),  ),  'payment' =>   array (    0 =>     array (      'id' => 4,      'name' => 'invoice',      'position' => 3,      'active' => NULL,      'description' => 'Invoice',      'shopware.apps.order.model.order' =>       array (      ),    ),  ),  'paymentInstances' =>   array (  ),  'documents' =>   array (    0 =>     array (      'id' => 1,      'date' => '2014-01-31T00:00:00',      'typeId' => 1,      'customerId' => 2,      'orderId' => 15,      'amount' => 998.56,      'documentId' => 20001,      'hash' => '02e8b8abfca501b3f9df6791750d04bd',      'typeName' => '',      'type' =>       array (        0 =>         array (          'id' => 1,          'template' => 'index.tpl',          'numbers' => 'doc_0',          'left' => 25,          'right' => 10,          'top' => 20,          'bottom' => 20,          'pageBreak' => 10,          'name' => 'Invoice',        ),      ),      'attributes' =>       array (      ),    ),  ),  'details' =>   array (    0 =>     array (      'id' => 42,      'orderId' => 15,      'mode' => 0,      'articleId' => 197,      'articleNumber' => 'SW10196',      'articleName' => 'ESD Download Artikel',      'quantity' => 1,      'statusId' => 0,      'statusDescription' => '',      'price' => 836.134,      'taxId' => 1,      'taxRate' => 19,      'taxDescription' => '',      'inStock' => 1,      'total' => 836.134,      'attribute' =>       array (        0 =>         array (          'id' => 1,          'orderDetailId' => 42,          'attribute1' => '',          'attribute2' => '',          'attribute3' => '',          'attribute4' => '',          'attribute5' => '',          'attribute6' => '',        ),      ),    ),    1 =>     array (      'id' => 43,      'orderId' => 15,      'mode' => 4,      'articleId' => 0,      'articleNumber' => 'SHIPPINGDISCOUNT',      'articleName' => 'Warenkorbrabatt',      'quantity' => 1,      'statusId' => 0,      'statusDescription' => '',      'price' => -2,      'taxId' => 0,      'taxRate' => 19,      'taxDescription' => '',      'inStock' => 0,      'total' => -2,      'attribute' =>       array (        0 =>         array (          'id' => 2,          'orderDetailId' => 43,          'attribute1' => '',          'attribute2' => '',          'attribute3' => '',          'attribute4' => '',          'attribute5' => '',          'attribute6' => '',        ),      ),    ),    2 =>     array (      'id' => 44,      'orderId' => 15,      'mode' => 4,      'articleId' => 0,      'articleNumber' => 'sw-payment-absolute',      'articleName' => 'Zuschlag für Zahlungsart',      'quantity' => 1,      'statusId' => 0,      'statusDescription' => '',      'price' => 5,      'taxId' => 0,      'taxRate' => 19,      'taxDescription' => '',      'inStock' => 0,      'total' => 5,      'attribute' =>       array (        0 =>         array (          'id' => 3,          'orderDetailId' => 44,          'attribute1' => '',          'attribute2' => '',          'attribute3' => '',          'attribute4' => '',          'attribute5' => '',          'attribute6' => '',        ),      ),    ),  ),  'mail' =>   array (  ),  'billingAttribute' =>   array (    0 =>     array (      'id' => 1,      'orderBillingId' => 1,      'text1' => NULL,      'text2' => NULL,      'text3' => NULL,      'text4' => NULL,      'text5' => NULL,      'text6' => NULL,    ),  ),  'shippingAttribute' =>   array (    0 =>     array (      'id' => 1,      'orderShippingId' => 1,      'text1' => NULL,      'text2' => NULL,      'text3' => NULL,      'text4' => NULL,      'text5' => NULL,      'text6' => NULL,    ),  ),);
        $initialShopCount = Shopware()->Db()->fetchOne('SELECT count(distinct id) FROM s_core_shops');
        $documents = Shopware()->Db()->fetchAll(
            'SELECT * FROM `s_order_documents` WHERE `orderID` = :orderID',
            array('orderID' => $this->orderId)
        );

        $this->assertCount(0, $documents);

        $this->Request()
                ->setMethod('POST')
                ->setPost($postData);

        $response = $this->dispatch('backend/Order/batchProcess');

        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);

        $finalShopCount = Shopware()->Db()->fetchOne('SELECT count(distinct id) FROM s_core_shops');
        $this->assertEquals($initialShopCount, $finalShopCount);

        $documents = Shopware()->Db()->fetchAll(
            'SELECT * FROM `s_order_documents` WHERE `orderID` = :orderID',
            array('orderID' => $this->orderId)
        );

        $this->assertCount(1, $documents);
    }
}
