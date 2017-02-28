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

namespace Shopware\Tests\Functional\Components\AdvancedMenu;

use Doctrine\DBAL\Connection;

class AdvancedMenuTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    public function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        Shopware()->Container()->get('front')->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $this->connection->insert('s_categories', [
            'parent' => '1',
            'description' => 'SwagTestCategory',
            'active' => '1',
        ]);
        $mainCatId = $this->getCategoryID('SwagTestCategory');

        $this->connection->insert('s_categories', [
            'parent' => $mainCatId,
            'description' => 'SwagTestSubCategory1',
            'active' => '1',
            'path' => '|' . $mainCatId . '|',
        ]);
        $subCat1Id = $this->getCategoryID('SwagTestSubCategory1');

        $this->connection->insert('s_categories', [
            'parent' => $mainCatId,
            'description' => 'SwagTestSubCategory2',
            'active' => '1',
            'path' => '|' . $mainCatId . '|',
        ]);
        $this->connection->insert('s_categories', [
            'parent' => $mainCatId,
            'description' => 'SwagTestSubSubCategory1',
            'active' => '1',
            'path' => '|' . $mainCatId . '|' . $subCat1Id . '|',
        ]);

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->connection->rollBack();

        parent::tearDown();
    }

    public function testGetAdvancedMenu()
    {
        $subscriber = Shopware()->Container()->get('AdvancedMenuSubscriber');

        $mainCatId = $this->getCategoryID('SwagTestCategory');

        /* First Call creates the menu and saves it in cache */
        $menu = $subscriber->getAdvancedMenu($mainCatId, $mainCatId, 5);
        $this->assertSame($this->getExprectedArray(), $menu);

        /* second call reads the menu from cache */
        $menu = $subscriber->getAdvancedMenu($mainCatId, $mainCatId, 2);
        $this->assertSame($this->getExprectedArray(), $menu);
    }

    private function getCategoryID($name)
    {
        return $this->connection->fetchColumn('SELECT `id` FROM `s_categories` WHERE `description` = ?', [$name]);
    }

    private function getExprectedArray()
    {
        $json = <<<'JSON'
[
  {
    "id": SwagTestSubCategory1_id,
    "parentId": SwagTestCategory_id,
    "name": "SwagTestSubCategory1",
    "position": 0,
    "metaTitle": null,
    "metaKeywords": null,
    "metaDescription": null,
    "cmsHeadline": null,
    "cmsText": null,
    "active": true,
    "template": null,
    "productBoxLayout": "basic",
    "blog": false,
    "path": "|SwagTestCategory_id|",
    "external": null,
    "hideFilter": false,
    "hideTop": false,
    "changed": null,
    "added": null,
    "attribute": [],
    "attributes": [],
    "media": null,
    "mediaId": null,
    "link": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory1_id",
    "streamId": null,
    "productStream": null,
    "childrenCount": 0,
    "description": "SwagTestSubCategory1",
    "cmsheadline": null,
    "cmstext": null,
    "sSelf": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory1_id",
    "sSelfCanonical": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory1_id",
    "canonicalParams": {
      "sViewport": "cat",
      "sCategory": SwagTestSubCategory1_id
    },
    "hide_sortings": false,
    "rssFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory1_id&sRss=1",
    "atomFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory1_id&sAtom=1",
    "flag": false,
    "sub": [],
    "activeCategories": 0
  },
  {
    "id": SwagTestSubCategory2_id,
    "parentId": SwagTestCategory_id,
    "name": "SwagTestSubCategory2",
    "position": 0,
    "metaTitle": null,
    "metaKeywords": null,
    "metaDescription": null,
    "cmsHeadline": null,
    "cmsText": null,
    "active": true,
    "template": null,
    "productBoxLayout": "basic",
    "blog": false,
    "path": "|SwagTestCategory_id|",
    "external": null,
    "hideFilter": false,
    "hideTop": false,
    "changed": null,
    "added": null,
    "attribute": [],
    "attributes": [],
    "media": null,
    "mediaId": null,
    "link": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory2_id",
    "streamId": null,
    "productStream": null,
    "childrenCount": 0,
    "description": "SwagTestSubCategory2",
    "cmsheadline": null,
    "cmstext": null,
    "sSelf": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory2_id",
    "sSelfCanonical": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory2_id",
    "canonicalParams": {
      "sViewport": "cat",
      "sCategory": SwagTestSubCategory2_id
    },
    "hide_sortings": false,
    "rssFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory2_id&sRss=1",
    "atomFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubCategory2_id&sAtom=1",
    "flag": false,
    "sub": [],
    "activeCategories": 0
  },
  {
    "id": SwagTestSubSubCategory1_id,
    "parentId": SwagTestCategory_id,
    "name": "SwagTestSubSubCategory1",
    "position": 0,
    "metaTitle": null,
    "metaKeywords": null,
    "metaDescription": null,
    "cmsHeadline": null,
    "cmsText": null,
    "active": true,
    "template": null,
    "productBoxLayout": "basic",
    "blog": false,
    "path": "|SwagTestSubCategory1_id|SwagTestCategory_id|",
    "external": null,
    "hideFilter": false,
    "hideTop": false,
    "changed": null,
    "added": null,
    "attribute": [],
    "attributes": [],
    "media": null,
    "mediaId": null,
    "link": "shopware.php?sViewport=cat&sCategory=SwagTestSubSubCategory1_id",
    "streamId": null,
    "productStream": null,
    "childrenCount": 0,
    "description": "SwagTestSubSubCategory1",
    "cmsheadline": null,
    "cmstext": null,
    "sSelf": "shopware.php?sViewport=cat&sCategory=SwagTestSubSubCategory1_id",
    "sSelfCanonical": "shopware.php?sViewport=cat&sCategory=SwagTestSubSubCategory1_id",
    "canonicalParams": {
      "sViewport": "cat",
      "sCategory": SwagTestSubSubCategory1_id
    },
    "hide_sortings": false,
    "rssFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubSubCategory1_id&sRss=1",
    "atomFeed": "shopware.php?sViewport=cat&sCategory=SwagTestSubSubCategory1_id&sAtom=1",
    "flag": false,
    "sub": [],
    "activeCategories": 0
  }
]
JSON;

        return json_decode(str_replace([
            'SwagTestCategory_id',
            'SwagTestSubCategory1_id',
            'SwagTestSubCategory2_id',
            'SwagTestSubSubCategory1_id',
        ], [
            $this->getCategoryID('SwagTestCategory'),
            $this->getCategoryID('SwagTestSubCategory1'),
            $this->getCategoryID('SwagTestSubCategory2'),
            $this->getCategoryID('SwagTestSubSubCategory1'),
        ], $json), true);
    }
}
