<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class DetailTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const PRODUCT_ID_WITH_SIMILAR_PRODUCTS = 2;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testProductWithSimilarProducts(): void
    {
        $this->dispatch('/genusswelten/2/muensterlaender-lagerkorn-32');
        $product = $this->View()->getAssign('sArticle');

        $similarProducts = $product['sSimilarArticles'];

        static::assertCount(6, $similarProducts);
    }

    public function testProductWithMaxSimilarProducts(): void
    {
        $maxSimilarConfigBefore = (int) $this->getContainer()->get('config')->get('maxcrosssimilar');
        $maxSimilarConfigNew = 3;
        static::assertNotSame($maxSimilarConfigNew, $maxSimilarConfigBefore);
        $this->setConfig('maxcrosssimilar', $maxSimilarConfigNew);
        $this->dispatch('/genusswelten/2/muensterlaender-lagerkorn-32');
        $product = $this->View()->getAssign('sArticle');

        $similarProducts = $product['sSimilarArticles'];

        static::assertCount($maxSimilarConfigNew, $similarProducts);

        $this->setConfig('maxcrosssimilar', $maxSimilarConfigBefore);
    }

    public function testErrorActionShowsDirectSimilarProductsFirst(): void
    {
        $similarProductIds = $this->connection->fetchFirstColumn(
            'SELECT relatedarticle FROM s_articles_similar WHERE articleID = :productId',
            ['productId' => self::PRODUCT_ID_WITH_SIMILAR_PRODUCTS]
        );
        $similarProductIds = array_map('\intval', $similarProductIds);

        $this->Request()->setParam('sArticle', self::PRODUCT_ID_WITH_SIMILAR_PRODUCTS);
        $this->dispatch('/detail/error');
        $similarProducts = $this->View()->getAssign('sRelatedArticles');

        $shownSimilarProductIds = array_map('\intval', array_column($similarProducts, 'articleID'));

        foreach ($similarProductIds as $similarProductId) {
            static::assertContains($similarProductId, $shownSimilarProductIds);
        }
    }

    public function testErrorActionConsiderMaxSimilarProductsConfig(): void
    {
        $maxSimilarConfigBefore = (int) $this->getContainer()->get('config')->get('maxcrosssimilar');
        $maxSimilarConfigNew = 3;
        static::assertNotSame($maxSimilarConfigNew, $maxSimilarConfigBefore);
        $this->setConfig('maxcrosssimilar', $maxSimilarConfigNew);

        $this->Request()->setParam('sArticle', self::PRODUCT_ID_WITH_SIMILAR_PRODUCTS);
        $this->dispatch('/detail/error');
        $similarProducts = $this->View()->getAssign('sRelatedArticles');

        static::assertCount($maxSimilarConfigNew, $similarProducts);

        $this->setConfig('maxcrosssimilar', $maxSimilarConfigBefore);
    }

    public function testDefaultVariant(): void
    {
        // Request a variant that is not the default one
        $this->Request()->setMethod('POST');

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');

        $product = $this->View()->getAssign('sArticle');

        static::assertSame('SW10201.2', $product['ordernumber']);
        static::assertSame(444, $product['articleDetailsID']);
    }

    public function testNonDefaultVariant(): void
    {
        // Request a variant that is not the default one
        $this->Request()
            ->setMethod('POST')
            ->setPost('group', [
                6 => 15,
                7 => 65,
            ]);

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');

        $product = $this->View()->getAssign('sArticle');
        static::assertSame('SW10201.5', $product['ordernumber']);
        static::assertSame(447, $product['articleDetailsID']);
    }

    /**
     * @dataProvider gtinDataProvider
     */
    public function testGtins(?string $gtin, string $value): void
    {
        $this->connection->executeStatement("UPDATE `s_articles_details` SET `ean`=? WHERE `ordernumber`='SW10006'", [$value]);

        $body = $this->dispatch('/genusswelten/edelbraende/6/cigar-special-40')->getBody();
        static::assertIsString($body);

        if (\is_string($gtin)) {
            static::assertStringContainsString($gtin, $body);
            static::assertStringContainsString(sprintf('"%s"', trim($value)), $body);
        } else {
            static::assertStringNotContainsString(trim($value), $body);
        }
    }

    /**
     * @return list<array{gtin: string|null, value: string}>
     */
    public function gtinDataProvider(): array
    {
        return [
            ['gtin' => 'gtin8', 'value' => '12345678'],
            ['gtin' => 'gtin8', 'value' => '12345678 '],
            ['gtin' => 'gtin8', 'value' => ' 12345678'],
            ['gtin' => 'gtin8', 'value' => '   12345678  '],
            ['gtin' => 'gtin12', 'value' => '012345678912'],
            ['gtin' => 'gtin13', 'value' => '0123456789123'],
            ['gtin' => 'gtin14', 'value' => '01234567891234'],
            ['gtin' => null, 'value' => '01234567891232343324'],
            ['gtin' => null, 'value' => 'foobar'],
        ];
    }

    public function testProductQuickView(): void
    {
        $orderNumberExport = 't3st' . mt_rand(1000, 9999);
        $orderNumber = 'SW10170';
        $this->connection->insert(
            's_addon_premiums',
            [
                'startprice' => 0,
                'ordernumber' => $orderNumber,
                'ordernumber_export' => $orderNumberExport,
                'subshopID' => 0,
            ]
        );
        $this->dispatch('/detail/productQuickView?ordernumber=' . $orderNumberExport);
        $product = $this->View()->getAssign('sArticle');
        static::assertSame($orderNumber, $product['ordernumber']);
    }
}
