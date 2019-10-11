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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\HrefLangServiceInterface;
use Shopware\Models\Shop\Shop;

class HrefLangTest extends TestCase
{
    /**
     * @var HrefLangServiceInterface
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Container()->get('dbal_connection')->beginTransaction();

        // Easier to see english link
        Shopware()->Db()->executeQuery('UPDATE s_core_shops SET base_url = "/en", category_id = 3 WHERE id != 1');

        Shopware()->Container()->reset('shopware_storefront.href_lang_service');

        $this->service = Shopware()->Container()->get('shopware_storefront.href_lang_service');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testHrefLinksNotGenerated()
    {
        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => 912345], $this->getContext());
        static::assertEmpty($urls);
    }

    public function testHrefLinksHome()
    {
        $urls = $this->service->getUrls(['controller' => 'index', 'action' => 'index'], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertEquals('/', parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertEquals('/en/', parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksListing()
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertEquals('/my-fancy-german-category/', parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertEquals('/en/my-fancy-english-category/', parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksListingWithParameters()
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertEquals('/my-fancy-german-category/', parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertEquals('/en/my-fancy-english-category/', parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksWithParameters()
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);
        static::assertContains('foo=bar', $urls[0]->getLink());

        $urls = $this->service->getUrls(['controller' => 'forms', 'action' => 'index', 'sFid' => 5, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);
        static::assertNotContains('foo=bar', $urls[0]->getLink());
    }

    private function createCategory()
    {
        $category = Shopware()->Container()->get('shopware.api.category');
        $category = $category->create([
            'parent' => 3,
            'name' => 'My fancy german category',
        ]);

        Shopware()->Container()->get('translation')->write(2, 'category', $category->getId(), [
            'description' => 'My fancy english category',
        ]);

        $rewriteTable = Shopware()->Modules()->RewriteTable();
        $rewriteTable->baseSetup();
        $rewriteTable->sCreateRewriteTableCategories();

        $englishShop = Shopware()->Models()->getRepository(Shop::class)->getById(2);
        $englishShop->registerResources();
        $rewriteTable->baseSetup();
        $rewriteTable->sCreateRewriteTableCategories();

        $defaultShop = Shopware()->Models()->getRepository(Shop::class)->getActiveDefault();
        $defaultShop->registerResources();

        return $category->getId();
    }
}
