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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Shopware\Models\ProductFeed\ProductFeed;

class ProductFeedTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var ProductFeed
     */
    protected $repository;

    /**
     * @var array
     */
    private $feedData = [
        'name' => 'UnitTest Produktsuche',
        'lastExport' => '2012-06-13 13:45:12',
        'active' => '1',
        'hash' => '0805bbb935327228edb5374083b81416',
        'show' => '1',
        'countArticles' => '89',
        'expiry' => '2000-01-01 00:00:00',
        'interval' => '3456',
        'formatId' => '2',
        'lastChange' => '0000-00-00 00:00:00',
        'fileName' => 'export.txt',
        'encodingId' => '2',
        'categoryId' => null,
        'currencyId' => '1',
        'customerGroupId' => '1',
        'partnerId' => '',
        'languageId' => null,
        'activeFilter' => '0',
        'imageFilter' => '0',
        'stockminFilter' => '0',
        'instockFilter' => '0',
        'priceFilter' => '0',
        'ownFilter' => '',
        'header' => '{#BOM#}{strip}id{#S#}{/strip}{#L#}',
        'body' => '',
        'footer' => '',
        'countFilter' => '0',
        'shopId' => '1',
        'variantExport' => '1',
    ];

    /** @var \Shopware\Components\Model\ModelManager */
    private $manager = null;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(ProductFeed::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * test the feed list
     */
    public function testGetFeeds()
    {
        // Delete old data
        $feeds = $this->repository->findBy(['hash' => '0805bbb935327228edb5374083b81416']);
        foreach ($feeds as $testFeed) {
            $this->manager->remove($testFeed);
        }
        $this->manager->flush();

        $feed = $this->createDummy();
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/ProductFeed/getFeeds?page=1&start=0&limit=30');
        static::assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->totalCount);
        $foundDummyFeed = [];
        foreach ($returnData as $feedData) {
            if ($feedData['name'] == $feed->getName()) {
                $foundDummyFeed = $feedData;
            }
        }

        static::assertEquals($feed->getId(), $foundDummyFeed['id']);
        $this->manager->remove($feed);
        $this->manager->flush();
    }

    /**
     * test adding a feed
     *
     * @return int The id to for the testUpdateFeed Method
     */
    public function testAddFeed()
    {
        $params = $this->feedData;
        $this->Request()->setParams($params);

        $this->dispatch('backend/ProductFeed/saveFeed');
        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertEquals($params['name'], $this->View()->data['name']);

        return $this->View()->data['id'];
    }

    /**
     * test the getDetailFeed Method
     *
     * @depends testAddFeed
     *
     * @param string $id
     *
     * @return string The id to for the testUpdateFeed Method
     */
    public function testGetDetailFeed($id)
    {
        $params['feedId'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/ProductFeed/getDetailFeed');
        static::assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $dummyFeedData = $this->feedData;
        static::assertEquals($dummyFeedData['name'], $returningData['name']);
        static::assertEquals($dummyFeedData['active'], $returningData['active']);
        static::assertEquals($dummyFeedData['hash'], $returningData['hash']);
        static::assertEquals($dummyFeedData['countArticles'], $returningData['countArticles']);
        static::assertEquals($dummyFeedData['formatId'], $returningData['formatId']);
        static::assertEquals($dummyFeedData['fileName'], $returningData['fileName']);
        static::assertEquals($dummyFeedData['customerGroupId'], $returningData['customerGroupId']);
        static::assertEquals($dummyFeedData['header'], $returningData['header']);
        static::assertEquals($dummyFeedData['body'], $returningData['body']);
        static::assertEquals($dummyFeedData['footer'], $returningData['footer']);
        static::assertEquals($dummyFeedData['shopId'], $returningData['shopId']);

        return $id;
    }

    /**
     * test updating a feed
     *
     * @depends testGetDetailFeed
     *
     * @param string $id
     */
    public function testUpdateFeed($id)
    {
        $params = $this->feedData;
        $params['feedId'] = $id;
        $params['name'] = 'phpUnit Test New Name';
        $this->Request()->setParams($params);

        $this->dispatch('backend/ProductFeed/saveFeed');

        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertEquals($params['name'], $this->View()->data['name']);

        return $id;
    }

    /**
     * test delete the feed method
     *
     * @depends testUpdateFeed
     *
     * @param string $id
     */
    public function testDeleteFeed($id)
    {
        $params = [];
        $params['id'] = intval($id);
        $this->Request()->setParams($params);
        $this->dispatch('backend/ProductFeed/deleteFeed');
        static::assertTrue($this->View()->success);
        static::assertNull($this->repository->find($params['id']));
    }

    /**
     * test getSuppliers action
     */
    public function testGetSuppliersAction()
    {
        $this->dispatch('backend/ProductFeed/getSuppliers');
        static::assertTrue($this->View()->success);
        static::assertGreaterThan(0, $this->View()->total);
    }

    /**
     * test getArticles action
     */
    public function testGetArticlesAction()
    {
        $this->dispatch('backend/ProductFeed/getArticles');
        static::assertTrue($this->View()->success);
        static::assertEquals(20, count($this->View()->data));
    }

    /**
     * Creates the dummy feed
     *
     * @return ProductFeed
     */
    private function getDummyFeed()
    {
        $feed = new ProductFeed();
        $feedData = $this->feedData;
        $feed->fromArray($feedData);

        return $feed;
    }

    /**
     * helper method to create the dummy object
     *
     * @return ProductFeed
     */
    private function createDummy()
    {
        $feed = $this->getDummyFeed();
        $this->manager->persist($feed);
        $this->manager->flush();

        return $feed;
    }
}
