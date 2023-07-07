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
use Enlight_Components_Test_Plugin_TestCase;
use Exception;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class BlogTest extends Enlight_Components_Test_Plugin_TestCase
{
    use DatabaseTransactionBehaviour;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = Shopware()->Container()->get(Connection::class);
    }

    /**
     * Tests the behavior if the blog article is not activated
     */
    public function testDispatchNoActiveBlogItem(): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionCode('404');
        $this->connection->exec('UPDATE `s_blog` SET `active` = 0 WHERE `id` = 3;');

        $this->dispatch('/blog/detail/?blogArticle=3');
        static::assertTrue($this->Response()->isRedirect());
    }

    /**
     * Tests the behavior if the BlogItem does not exist anymore
     */
    public function testDispatchNotExistingBlogItem(): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionCode('404');
        $this->dispatch('/blog/detail/?blogArticle=2222');
        static::assertTrue($this->Response()->isRedirect());
    }

    public function testDispatchInactiveCategory(): void
    {
        // Deactivate blog category
        $this->connection->executeStatement('UPDATE `s_categories` SET `active` = 0 WHERE `id` = 17');

        // Should be redirected because blog category is inactive
        $ex = null;
        try {
            $this->dispatch('/blog/?sCategory=17');
        } catch (Exception $e) {
            $ex = $e;
        }
        static::assertInstanceOf(Exception::class, $ex);
        static::assertEquals(404, $ex->getCode());

        // Should be redirected because blog category is inactive
        try {
            $this->dispatch('/blog/detail/?blogArticle=3');
        } catch (Exception $e) {
            $ex = $e;
        }
        static::assertEquals(404, $ex->getCode());
    }

    /**
     * Test that requesting a non-blog category-id creates a redirect
     */
    public function testDispatchNonBlogCategory(): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionCode('404');
        $this->dispatch('/blog/?sCategory=14');
    }

    /**
     * Test redirect when the blog category does not exist
     *
     * @dataProvider invalidCategoryUrlDataProvider
     */
    public function testDispatchNotExistingBlogCategory(string $url): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionCode('404');
        $this->dispatch($url);
    }

    /**
     * @return list<list<string>>
     */
    public function invalidCategoryUrlDataProvider(): array
    {
        return [
            ['/blog/?sCategory=14'], // Not a blog category
            ['/blog/?sCategory=156165'], // Unknown blog category
        ];
    }
}
