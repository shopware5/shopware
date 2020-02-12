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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class DetailTest extends \Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = Shopware()->Container()->get('dbal_connection');
    }

    public function testDefaultVariant()
    {
        // Request a variant that is not the default one
        $this->Request()
            ->setMethod('POST');

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');

        $article = $this->View()->getAssign('sArticle');

        static::assertEquals('SW10201.2', $article['ordernumber']);
        static::assertEquals(444, $article['articleDetailsID']);
    }

    public function testNonDefaultVariant()
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
        static::assertEquals('SW10201.5', $article['ordernumber']);
        static::assertEquals('447', $article['articleDetailsID']);
    }

    /**
     * @param string|null $gtin
     * @param string      $value
     *
     * @dataProvider gtinDataprovider
     */
    public function testGtins($gtin, $value)
    {
        $this->connection->executeUpdate('UPDATE `s_articles_details` SET `ean`=? WHERE `ordernumber`="SW10006"', [$value]);

        $response = $this->dispatch('/genusswelten/edelbraende/6/cigar-special-40');

        if ($gtin) {
            static::assertContains($gtin, $response->getBody());
            static::assertContains('"' . trim($value) . '"', $response->getBody());
        } else {
            static::assertNotContains(trim($value), $response->getBody());
        }
    }

    public function gtinDataprovider()
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

    public function testProductQuickView()
    {
        $dbal = Shopware()->Container()->get('dbal_connection');
        $ordernumber_export = 't3st' . mt_rand(1000, 9999);
        $ordernumber = 'SW10170';
        $num = $dbal->insert('s_addon_premiums', ['startprice' => 0, 'ordernumber' => $ordernumber, 'ordernumber_export' => $ordernumber_export, 'subshopID' => 0]);
        $this->dispatch('/detail/productQuickView?ordernumber=' . $ordernumber_export);
        $sArticle = $this->View()->getAssign('sArticle');
        static::assertEquals($ordernumber, $sArticle['ordernumber']);
    }
}
