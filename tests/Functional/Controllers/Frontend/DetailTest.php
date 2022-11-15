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
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class DetailTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testDefaultVariant(): void
    {
        // Request a variant that is not the default one
        $this->Request()->setMethod('POST');

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');

        $article = $this->View()->getAssign('sArticle');

        static::assertSame('SW10201.2', $article['ordernumber']);
        static::assertSame(444, $article['articleDetailsID']);
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

        $article = $this->View()->getAssign('sArticle');
        static::assertSame('SW10201.5', $article['ordernumber']);
        static::assertSame(447, $article['articleDetailsID']);
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
        $sArticle = $this->View()->getAssign('sArticle');
        static::assertSame($orderNumber, $sArticle['ordernumber']);
    }
}
