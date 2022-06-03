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
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Enlight_Controller_Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\HttpFoundation\Response;

class ListingTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const CATEGORY_LINK = '/cat/?sCategory=%s';

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelManager = $this->getContainer()->get(ModelManager::class);
    }

    /**
     * Test that requesting an existing category-id is successful
     */
    public function testDispatchExistingCategory(): void
    {
        $this->dispatch(sprintf(self::CATEGORY_LINK, 14));
        static::assertSame(Response::HTTP_OK, $this->Response()->getHttpResponseCode());
    }

    public function testDispatchExistingCategoryWithPageNotAvailable(): void
    {
        $this->expectException(Enlight_Controller_Exception::class);
        $this->dispatch(sprintf(self::CATEGORY_LINK . '&sPage=2', 14));
        static::assertSame(Response::HTTP_OK, $this->Response()->getHttpResponseCode());
    }

    /**
     * Test that requesting a non-existing category-id throws an error
     */
    public function testDispatchNonExistingCategory(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch(sprintf(self::CATEGORY_LINK, 4711));
        static::assertSame(Response::HTTP_NOT_FOUND, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting an empty category-id throws an error
     */
    public function testDispatchEmptyCategoryId(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch(sprintf(self::CATEGORY_LINK, ''));
        static::assertSame(Response::HTTP_NOT_FOUND, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting a category-id of a subshop throws an error
     */
    public function testDispatchSubshopCategoryId(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch(sprintf(self::CATEGORY_LINK, 43));
        static::assertSame(Response::HTTP_NOT_FOUND, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Test that requesting a blog category-id creates a redirect
     */
    public function testDispatchBlogCategory(): void
    {
        $this->expectException('Enlight_Exception');
        $this->dispatch(sprintf(self::CATEGORY_LINK, 17));
        static::assertSame(Response::HTTP_NOT_FOUND, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());
    }

    public function testExternalLink(): void
    {
        $externalLink = 'https://www.google.com';

        $category = $this->createNewCategory();
        $category->setExternal($externalLink);
        $this->modelManager->persist($category);
        $this->modelManager->flush($category);

        $this->dispatch(sprintf(self::CATEGORY_LINK, $category->getId()));

        static::assertSame(Response::HTTP_MOVED_PERMANENTLY, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());

        static::assertStringContainsString($externalLink, $this->Response()->getHeader('location'));
    }

    public function testCategoryRedirectToProductDetailPageDirectly(): void
    {
        $this->setConfig('categoryDetailLink', true);

        $category = $this->createNewCategory();
        $this->modelManager->persist($category);
        $this->modelManager->flush($category);

        $product = $this->modelManager->getRepository(Product::class)->findOneBy(['active' => true]);
        static::assertInstanceOf(Product::class, $product);

        $productCategories = $product->getCategories();
        $productCategories->add($category);
        $product->setCategories($productCategories);

        $this->modelManager->persist($product);
        $this->modelManager->flush($product);
        $this->modelManager->clear();

        $this->dispatch(sprintf(self::CATEGORY_LINK, $category->getId()));

        static::assertSame(Response::HTTP_MOVED_PERMANENTLY, $this->Response()->getHttpResponseCode());
        static::assertTrue($this->Response()->isRedirect());

        $firstSpace = strpos($product->getName(), ' ');
        static::assertIsInt($firstSpace);
        $firstPartOfProductName = strtolower(substr($product->getName(), 0, $firstSpace));
        static::assertStringContainsString($firstPartOfProductName, $this->Response()->getHeader('location'));
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

        $this->dispatch(sprintf(self::CATEGORY_LINK, $mainCategoryId));

        static::assertSame(Response::HTTP_MOVED_PERMANENTLY, $this->Response()->getHttpResponseCode());
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

        $this->dispatch(sprintf(self::CATEGORY_LINK, $categoryId));

        static::assertSame(Response::HTTP_OK, $this->Response()->getHttpResponseCode());
        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);
        static::assertStringContainsString('filter-panel--content', $responseBody, 'No filters available in the HTML');
    }

    private function createNewCategory(): Category
    {
        $mainCategory = $this->modelManager->find(Category::class, 3);
        static::assertInstanceOf(Category::class, $mainCategory);

        $category = new Category();
        $category->setName('Test');
        $category->setParent($mainCategory);
        $category->setActive(true);

        return $category;
    }
}
