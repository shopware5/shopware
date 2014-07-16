<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
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

        $proxy = Enlight_Application::Instance()->Hooks()->getProxy('Shopware_Controllers_Backend_ImportExport');
        $this->controller = new $proxy(new Enlight_Controller_Request_RequestTestCase(), new Enlight_Controller_Response_ResponseTestCase());

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Clean the tables
     */
    public function testClearTables() {
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
    public function testNewsletterImportTest1() {
        $this->controller->importNewsletter(Shopware()->TestPath('DataSets_Newsletter').'test1.csv');

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
        $this->assertSame('streetnumber1', $contactDataOne->getStreetNumber());
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
    public function testNewsletterImportTest2() {
        $this->controller->importNewsletter(Shopware()->TestPath('DataSets_Newsletter').'test2.csv');

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
        $this->assertSame((int)Shopware()->Config()->get("sNEWSLETTERDEFAULTGROUP"), $contactDataFour->getGroupID());
        $this->assertSame('Maria', $contactDataFour->getFirstName());
        $this->assertSame('Four', $contactDataFour->getLastName());
        $this->assertSame('street4', $contactDataFour->getStreet());

        $this->assertSame('email1@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email2@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email3@shopware.de', array_shift($addressData)->getEmail());
        $this->assertSame('email4@shopware.de', array_shift($addressData)->getEmail());
    }
}
