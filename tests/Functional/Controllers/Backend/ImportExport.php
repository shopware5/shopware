<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Backend_ImportExportTest extends Enlight_Components_Test_Controller_TestCase
{
    private $controller;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        $proxy = Shopware()->Hooks()->getProxy('Shopware_Controllers_Backend_ImportExport');
        $this->controller = new $proxy(new Enlight_Controller_Request_RequestTestCase(), new Enlight_Controller_Response_ResponseTestCase());

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Clean the tables
     */
    public function testClearTables()
    {
        $contactData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\ContactData')->findAll();
        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Address')->findAll();

        foreach ($contactData as $entity) {
            Shopware()->Models()->remove($entity);
        }
        foreach ($addressData as $entity) {
            Shopware()->Models()->remove($entity);
        }
        Shopware()->Models()->flush();

        $contactData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\ContactData')->findAll();
        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Address')->findAll();
        $this->assertCount(0, $contactData);
        $this->assertCount(0, $addressData);
    }

    /**
     * Test an initial import against an empty DB
     */
    public function testNewsletterImportTest1()
    {
        $this->controller->importNewsletter(__DIR__ . '/testdata/test1.csv');

        $contactData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\ContactData')->findAll();
        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Address')->findAll();
        $this->assertCount(2, $contactData);
        $this->assertCount(2, $addressData);

        $contactDataOne = array_shift($contactData);
        $this->assertSame('email1@shopware.de', $contactDataOne->getEmail());
        $this->assertSame('Newsletter-Empfänger', Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group')->find($contactDataOne->getGroupID())->getName());
        $this->assertSame('John', $contactDataOne->getFirstName());
        $this->assertSame('One', $contactDataOne->getLastName());
        $this->assertSame('street1', $contactDataOne->getStreet());
        $this->assertSame('11111', $contactDataOne->getZipCode());
        $this->assertSame('city1', $contactDataOne->getCity());

        $contactDataTwo = array_shift($contactData);
        $this->assertSame('email2@shopware.de', $contactDataTwo->getEmail());
        $this->assertSame('Newsletter-Empfänger', Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group')->find($contactDataTwo->getGroupID())->getName());
        $this->assertSame('Michael', $contactDataTwo->getFirstName());
        $this->assertSame('Two', $contactDataTwo->getLastName());
        $this->assertSame('street2', $contactDataTwo->getStreet());

        $this->assertSame('email1@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email2@shopware.de', array_shift($addressData)->getEmail());
    }

    /**
     * Test an second import against the previous DB
     * Tests updates as well as new insertions
     */
    public function testNewsletterImportTest2()
    {
        $this->controller->importNewsletter(__DIR__ . '/testdata/test2.csv');

        $contactData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\ContactData')->findAll();
        $addressData = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Address')->findAll();
        $this->assertCount(4, $contactData);
        $this->assertCount(4, $addressData);

        //Ensure data can be updated while keeping the email address
        $contactDataOne = array_shift($contactData);
        $this->assertSame('email1@shopware.de', $contactDataOne->getEmail());
        $this->assertSame('Group2', Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group')->find($contactDataOne->getGroupID())->getName());
        $this->assertSame('John', $contactDataOne->getFirstName());
        $this->assertSame('One', $contactDataOne->getLastName());
        $this->assertSame('street12', $contactDataOne->getStreet());

        //Ensure data can be kept and that a grouped user doesn't change group if none is provided
        $contactDataTwo = array_shift($contactData);
        $this->assertSame('email2@shopware.de', $contactDataTwo->getEmail());
        $this->assertSame('Newsletter-Empfänger', Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group')->find($contactDataTwo->getGroupID())->getName());
        $this->assertSame('Michael', $contactDataTwo->getFirstName());
        $this->assertSame('Two', $contactDataTwo->getLastName());
        $this->assertSame('street2', $contactDataTwo->getStreet());

        //Ensure new data can be added
        $contactDataThree = array_shift($contactData);
        $this->assertSame('email3@shopware.de', $contactDataThree->getEmail());
        $this->assertSame('Group3', Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group')->find($contactDataThree->getGroupID())->getName());
        $this->assertSame('Jake', $contactDataThree->getFirstName());
        $this->assertSame('Three', $contactDataThree->getLastName());
        $this->assertSame('street3', $contactDataThree->getStreet());

        //New data with no (fallback to default) group can be added
        $contactDataFour = array_shift($contactData);
        $this->assertSame('email4@shopware.de', $contactDataFour->getEmail());
        $this->assertSame((int) Shopware()->Config()->get('sNEWSLETTERDEFAULTGROUP'), $contactDataFour->getGroupID());
        $this->assertSame('Maria', $contactDataFour->getFirstName());
        $this->assertSame('Four', $contactDataFour->getLastName());
        $this->assertSame('street4', $contactDataFour->getStreet());

        $this->assertSame('email1@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email2@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email3@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email4@shopware.de', array_shift($addressData)->getEmail());
    }

    /**
     * Checks if the exports contains duplicated rows
     *
     * @ticket SW-5543
     */
    public function testArticleXMLExportDuplicateRows()
    {
        // Set up test data
        $sql = "INSERT IGNORE INTO `s_order_documents` (`date`, `type`, `userID`, `orderID`, `amount`, `docID`, `hash`) VALUES
            ('2013-04-26', 1, 2, 15, 998.56, 20001, 'bb4eef5a6d79acb7fab2b9da19b59ce7'),
            ('2013-04-26', 1, 1, 57, 201.86, 20002, '110068dc105c9651c2cd1f202f0c9be1'),
            ('2013-04-26', 2, 2, 15, 998.56, 20001, '15d2f8a284a648576608f1f26a54948c'),
            ('2013-04-26', 2, 1, 57, 201.86, 20002, '9209b7e17b00e02a4be3f4ae17f943c5')";
        Shopware()->Db()->query($sql);

        $this->Front()->setParam('noViewRenderer', false);
        $this->dispatch('/backend/ImportExport/exportOrders?format=csv');
        $header = $this->Response()->getHeaders();

        $this->assertEquals('Content-Disposition', $header[1]['name']);
        $this->assertEquals('Content-Transfer-Encoding', $header[2]['name']);
        $this->assertEquals('binary', $header[2]['value']);
        $this->assertEquals('text/x-comma-separated-values;charset=utf-8', $header[0]['value']);
        $csvOutput = $this->Response()->getBody();

        $csvData = explode("\n", $csvOutput);

        foreach ($csvData as $key => $row) {
            if (!empty($csvData[$key - 1]) && !empty($row) && $key - 1 != 0) {
                $result = array_diff([$csvData[$key - 1]], [$row]);
                $this->assertNotEmpty($result);
            }
        }

        // Cleanup test data
        $sql = 'DELETE FROM`s_order_documents` WHERE `docID` IN (20001,20002);';
        Shopware()->Db()->query($sql, []);
    }

    /**
     * Checks password confirmation field
     *
     * @ticket SW-5566
     */
    public function testArticleXMLExportPasswordConfirmation()
    {
        @ini_set('memory_limit', '768M');
        $this->Front()->setParam('noViewRenderer', false);

        $this->dispatch('/backend/ImportExport/exportArticles?format=xml&exportVariants=1');
        $header = $this->Response()->getHeaders();

        $this->assertEquals('Content-Disposition', $header[1]['name']);
        $this->assertEquals('Content-Transfer-Encoding', $header[2]['name']);
        $this->assertEquals('binary', $header[2]['value']);
        $xmlOutput = $this->Response()->getBody();

        $xml = simplexml_load_string($xmlOutput, 'SimpleXMLElement', LIBXML_NOCDATA);
        $results = $xml->articles;
        $articleData = $this->simpleXml2array($results);

        //check if the output data is correct
        foreach ($articleData['article'] as $article) {
            //check the main variant attribute data
            $mainDetailData = $article['mainDetail'];
            $attributeData = $mainDetailData['attribute'];
            if (!empty($attributeData)) {
                $this->assertNotEmpty($attributeData['id']);
                $this->assertNotEmpty($attributeData['articleId']);
                $this->assertNotEmpty($attributeData['articleDetailId']);
                $this->assertEquals($article['mainDetailId'], $attributeData['articleDetailId']);
            }

            //check the variant attribute data
            if (!empty($article['variants'])) {
                foreach ($article['variants']['variant'] as $key => $variant) {
                    if (!is_int($key)) {
                        $variant = $article['variants']['variant'];
                    }
                    if (!empty($variant)) {
                        $this->assertNotEmpty($variant['articleId']);
                        $variantAttributeData = $variant['attribute'];
                        if (!empty($variantAttributeData)) {
                            $variantArticleId = $variant['articleId'];
                            $this->assertNotEmpty($variantAttributeData['id']);
                            $this->assertNotEmpty($variantAttributeData['articleId']);
                            $this->assertNotEmpty($variantAttributeData['articleDetailId']);
                            $this->assertEquals($variantArticleId, $variantAttributeData['articleId']);
                            $this->assertEquals($variant['id'], $variantAttributeData['articleDetailId']);
                        }
                        //check if the variant prices are set
                        $this->assertNotEmpty($variant['prices']);
                        $this->assertNotEmpty($variant['prices']['price']);
                    }
                }
            }
            //check if the main prices are set
            $this->assertNotEmpty($mainDetailData['prices']);
            $this->assertNotEmpty($mainDetailData['prices']['price']);
        }
    }

    /**
     * helper method to convert the object to an array
     *
     * @param SimpleXMLElement
     *
     * @return array|string
     */
    private function simpleXml2array($xml)
    {
        if (get_class($xml) == 'SimpleXMLElement') {
            $attributes = $xml->attributes();
            foreach ($attributes as $k => $v) {
                if ($v) {
                    $a[$k] = (string) $v;
                }
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }
        if (is_array($xml)) {
            if (count($xml) == 0) {
                return (string) $x;
            } // for CDATA
            foreach ($xml as $key => $value) {
                $r[$key] = $this->simpleXml2array($value);
            }
            if (isset($a)) {
                $r['@attributes'] = $a;
            }    // Attributes
            return $r;
        }

        return (string) $xml;
    }
}
