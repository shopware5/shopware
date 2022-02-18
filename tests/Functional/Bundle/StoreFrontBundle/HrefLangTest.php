<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\Core\HrefLangService;
use Shopware\Bundle\StoreFrontBundle\Service\HrefLangServiceInterface;
use Shopware\Components\Api\Resource\Category;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_Translation;

class HrefLangTest extends TestCase
{
    use ContainerTrait;

    /**
     * @var HrefLangServiceInterface
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get(Connection::class)->beginTransaction();

        // Easier to see english link
        Shopware()->Db()->executeQuery('UPDATE s_core_shops SET base_url = "/en", category_id = 3 WHERE id != 1');

        $this->getContainer()->reset('shopware_storefront.href_lang_service');

        $this->service = $this->getContainer()->get(HrefLangService::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->getContainer()->get(Connection::class)->rollBack();
    }

    public function testHrefLinksNotGenerated(): void
    {
        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => 912345], $this->getContext());
        static::assertEmpty($urls);
    }

    public function testHrefLinksHome(): void
    {
        $urls = $this->service->getUrls(['controller' => 'index', 'action' => 'index'], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertStringEndsWith('/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertStringEndsWith('/en/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksListing(): void
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertStringEndsWith('/my-fancy-german-category/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertStringEndsWith('/en/my-fancy-english-category/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksListingWithParameters(): void
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);

        foreach ($urls as $href) {
            if ($href->getLocale() === 'de-DE') {
                static::assertStringEndsWith('/my-fancy-german-category/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            } else {
                static::assertStringEndsWith('/en/my-fancy-english-category/', (string) parse_url($href->getLink(), PHP_URL_PATH));
            }
        }
    }

    public function testHrefLinksWithParameters(): void
    {
        $category = $this->createCategory();

        $urls = $this->service->getUrls(['controller' => 'cat', 'action' => 'index', 'sCategory' => $category, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);
        static::assertStringContainsString('foo=bar', $urls[0]->getLink());

        $urls = $this->service->getUrls(['controller' => 'forms', 'action' => 'index', 'sFid' => 5, 'foo' => 'bar'], $this->getContext());

        static::assertNotEmpty($urls);
        static::assertStringNotContainsString('foo=bar', $urls[0]->getLink());
    }

    private function createCategory(): int
    {
        $category = $this->getContainer()->get(Category::class);
        $category = $category->create([
            'parent' => 3,
            'name' => 'My fancy german category',
        ]);

        $this->getContainer()->get(Shopware_Components_Translation::class)->write(2, 'category', $category->getId(), [
            'description' => 'My fancy english category',
        ]);

        $rewriteTable = Shopware()->Modules()->RewriteTable();
        $rewriteTable->baseSetup();
        $rewriteTable->sCreateRewriteTableCategories();

        $englishShop = Shopware()->Models()->getRepository(Shop::class)->getById(2);
        static::assertInstanceOf(Shop::class, $englishShop);
        $shopRegistrationService = $this->getContainer()->get(ShopRegistrationServiceInterface::class);
        $shopRegistrationService->registerShop($englishShop);
        $rewriteTable->baseSetup();
        $rewriteTable->sCreateRewriteTableCategories();

        $defaultShop = Shopware()->Models()->getRepository(Shop::class)->getActiveDefault();
        $shopRegistrationService->registerShop($defaultShop);

        return $category->getId();
    }
}
