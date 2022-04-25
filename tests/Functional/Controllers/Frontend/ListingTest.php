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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Exception;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ListingTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * Test that requesting an existing category-id is successful
     */
    public function testDispatchExistingCategory(): void
    {
        $this->dispatch('/cat/?sCategory=14');
        static::assertEquals(200, $this->Response()->getHttpResponseCode());
    }

    public function testDispatchExistingCategoryWithPageNotAvailable(): void
    {
        $this->expectException(Enlight_Controller_Exception::class);
        $this->dispatch('/cat/?sCategory=14&sPage=2');
        static::assertEquals(200, $this->Response()->getHttpResponseCode());
    }

    /**
     * Test that requesting a non-existing category-id throws an error
     */
    public function testDispatchNonExistingCategory(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch('/cat/?sCategory=4711');
        static::assertEquals(404, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting an empty category-id throws an error
     */
    public function testDispatchEmptyCategoryId(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch('/cat/?sCategory=');
        static::assertEquals(404, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting a category-id of a subshop throws an error
     */
    public function testDispatchSubshopCategoryId(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch('/cat/?sCategory=43');
        static::assertEquals(404, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting a blog category-id creates a redirect
     */
    public function testDispatchBlogCategory(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch('/cat/?sCategory=17');
        static::assertEquals(404, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test the home redirect if the base category called directly
     * The request should return a 301 redirection to the base homepage.
     *
     * @ticket SW-11418
     */
    public function testHomeRedirect(): void
    {
        $mainCategory = $this->getContainer()->get('shop')->getCategory();
        static::assertInstanceOf(Category::class, $mainCategory);
        $mainCategoryId = $mainCategory->getId();

        $this->dispatch(sprintf('/cat/index/sCategory/%s', $mainCategoryId));

        static::assertEquals(301, $this->Response()->getHttpResponseCode());
    }

    public function testManufacturerPage(): void
    {
        $this->dispatch('/das-blaue-haus/');

        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);

        static::assertStringContainsString('blaueshaus_200x200.png', $responseBody);
    }

    public function testWithoutImageManufacturerPage(): void
    {
        $sql = <<<'SQL'
        UPDATE s_articles_supplier
        SET img = ''
        WHERE img = 'media/image/blaueshaus.png';
SQL;

        $this->getContainer()->get(Connection::class)->executeStatement($sql);

        $this->dispatch('/das-blaue-haus/');

        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);

        static::assertStringNotContainsString('blaueshaus_200x200.png', $responseBody);
    }

    public function testCategoryWithProductStream(): void
    {
        $connection = $this->getContainer()->get(Connection::class);

        $createStreamSQL = file_get_contents(__DIR__ . '/fixtures/product_stream.sql');
        static::assertIsString($createStreamSQL);
        $connection->executeStatement($createStreamSQL);
        $streamId = (int) $connection->lastInsertId();

        $createCategoryWithStreamSQL = file_get_contents(__DIR__ . '/fixtures/category_with_product_stream.sql');
        static::assertIsString($createCategoryWithStreamSQL);
        $connection->executeStatement($createCategoryWithStreamSQL, ['streamId' => $streamId]);
        $categoryId = (int) $connection->lastInsertId();

        $this->dispatch(sprintf('/cat/?sCategory=%d', $categoryId));

        static::assertEquals(200, $this->Response()->getHttpResponseCode());
        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);
        static::assertStringContainsString('filter-panel--content', $responseBody, 'No filters available in the HTML');
    }
}
