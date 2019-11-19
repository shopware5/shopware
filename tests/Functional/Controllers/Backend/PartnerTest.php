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

namespace Shopware\Tests\Controllers\Backend;

class PartnerTest extends \Enlight_Components_Test_Controller_TestCase
{
    /** @var \Shopware\Models\Partner\Partner $repository */
    protected $repository;

    /**
     * dummy data
     *
     * @var array
     */
    private $dummyData = [
        'idCode' => '31337',
        'date' => '02.07.2013',
        'company' => 'phpUnitTestCompany',
        'contact' => 'contactDummy',
        'street' => 'streetDummy',
        'zipCode' => 'zipCodeDummy',
        'city' => 'cityDummy',
        'phone' => 'phoneDummy',
        'fax' => 'faxDummy',
        'countryName' => 'countryDummy',
        'email' => 'emailDummy',
        'web' => 'webDummy',
        'profile' => 'profileDummy',
        'fix' => '0',
        'percent' => '12',
        'cookieLifeTime' => '12334',
        'active' => '1',
    ];

    private $updateStreet = 'Abbey Road';

    /** @var \Shopware\Components\Model\ModelManager */
    private $manager = null;

    /**
     * Cleaning up testData
     */
    public static function tearDownAfterClass(): void
    {
        $sql = 'DELETE FROM s_emarketing_partner WHERE idcode = ?';
        Shopware()->Db()->query($sql, ['31337']);
    }

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(\Shopware\Models\Partner\Partner::class);

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * test getList controller action
     */
    public function testGetList()
    {
        //delete old data
        $repositoryData = $this->repository->findBy(['company' => $this->dummyData['company']]);
        foreach ($repositoryData as $testDummy) {
            $this->manager->remove($testDummy);
        }
        $this->manager->flush();

        $dummy = $this->createDummy();
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/Partner/getList?page=1&start=0&limit=30');
        static::assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->totalCount);
        $foundDummy = [];
        foreach ($returnData as $dummyData) {
            if ($dummyData['company'] == $dummy->getCompany()) {
                $foundDummy = $dummyData;
            }
        }

        static::assertEquals($dummy->getIdCode(), $foundDummy['idCode']);
        $this->manager->remove($dummy);
        $this->manager->flush();
    }

    /**
     * test savePartner controller action
     *
     * @return string the id of the new dummy partner
     */
    public function testSavePartner()
    {
        $params = $this->dummyData;
        //test new partner
        $this->Request()->setParams($params);
        $this->dispatch('backend/Partner/savePartner');
        static::assertTrue($this->View()->success);
        static::assertCount(19, $this->View()->data);
        static::assertEquals('streetDummy', $this->View()->data['street']);

        //test update partner
        $params['id'] = $this->View()->data['id'];
        $params['street'] = $this->updateStreet;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Partner/savePartner');
        static::assertTrue($this->View()->success);
        static::assertEquals($this->updateStreet, $this->View()->data['street']);

        return $this->View()->data['id'];
    }

    /**
     * test getDetail controller action
     *
     * @depends testSavePartner
     *
     * @param string $id
     *
     * @return the id to for the testGetDetail Method
     */
    public function testGetDetail($id)
    {
        $filter = [['property' => 'id', 'value' => $id]];
        $params['filter'] = json_encode($filter);
        $this->Request()->setParams($params);

        $this->dispatch('backend/Partner/getDetail');
        static::assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $dummyData = $this->dummyData;

        static::assertEquals($dummyData['idCode'], $returningData['idCode']);
        static::assertEquals($dummyData['company'], $returningData['company']);
        static::assertEquals($dummyData['contact'], $returningData['contact']);
        static::assertEquals($this->updateStreet, $returningData['street']);
        static::assertEquals($dummyData['zipCode'], $returningData['zipCode']);
        static::assertEquals($dummyData['city'], $returningData['city']);
        static::assertEquals($dummyData['phone'], $returningData['phone']);
        static::assertEquals($dummyData['fax'], $returningData['fax']);
        static::assertEquals($dummyData['countryName'], $returningData['countryName']);
        static::assertEquals($dummyData['email'], $returningData['email']);
        static::assertEquals($dummyData['web'], $returningData['web']);
        static::assertEquals($dummyData['profile'], $returningData['profile']);
        static::assertEquals($dummyData['fix'], $returningData['fix']);
        static::assertEquals($dummyData['percent'], $returningData['percent']);
        static::assertEquals($dummyData['cookieLifeTime'], $returningData['cookieLifeTime']);
        static::assertEquals($dummyData['active'], $returningData['active']);

        return $id;
    }

    /**
     * test validateTrackingCode controller action
     *
     * @depends testSavePartner
     *
     * @param string $id
     *
     * @return $id | dummy id
     */
    public function testValidateTrackingCode($id)
    {
        $params['value'] = '31337';
        $params['param'] = $id;
        $this->Request()->setParams($params);
        $this->Response()->clearBody();
        $this->dispatch('backend/Partner/validateTrackingCode');
        $body = $this->Response()->getBody();
        static::assertEquals('1', $body);

        $newDummy = $this->createDummy();
        $this->Response()->clearBody();
        $params['value'] = '31337';
        $params['param'] = $newDummy->getId();
        $this->Request()->setParams($params);
        $this->dispatch('backend/Partner/validateTrackingCode');
        $body = $this->Response()->getBody();
        static::assertTrue(empty($body));

        //delete the new dummy
        $this->manager->remove($newDummy);
        $this->manager->flush();

        return $id;
    }

    /**
     * test getCustomer controller action
     */
    public function testMapCustomerAccount()
    {
        $this->Response()->clearBody();
        $this->Request()->request->set('mapCustomerAccountValue', 2);
        $this->dispatch('backend/Partner/mapCustomerAccount');
        $body = $this->Response()->getBody();
        static::assertTrue(!empty($body));

        $this->Response()->clearBody();
        $this->Request()->request->set('mapCustomerAccountValue', 542350);
        $this->dispatch('backend/Partner/mapCustomerAccount');
        $body = $this->Response()->getBody();
        static::assertTrue(empty($body));
    }

    /**
     * test deletePartner controller action
     *
     * @depends testSavePartner
     *
     * @param string $id
     */
    public function testDeletePartner($id)
    {
        $params['id'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Partner/deletePartner');
        static::assertTrue($this->View()->success);
        static::assertCount(4, $this->View()->data);
    }

    /**
     * Creates the dummy data
     *
     * @return \Shopware\Models\Partner\Partner
     */
    private function getDummyData()
    {
        $dummyModel = new \Shopware\Models\Partner\Partner();
        $dummyData = $this->dummyData;
        $dummyModel->fromArray($dummyData);

        return $dummyModel;
    }

    /**
     * helper method to create the dummy object
     *
     * @return \Shopware\Models\Partner\Partner
     */
    private function createDummy()
    {
        $dummyData = $this->getDummyData();
        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }
}
