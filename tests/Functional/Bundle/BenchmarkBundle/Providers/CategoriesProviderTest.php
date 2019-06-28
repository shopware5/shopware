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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;

class CategoriesProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.categories';
    const EXPECTED_KEYS_COUNT = 2;
    const EXPECTED_TYPES = [
        'products' => [
            'average' => IsType::TYPE_FLOAT,
            'max' => IsType::TYPE_INT,
        ],
        'tree' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetAverageProductsPerCategoryPerShop()
    {
        $this->installDemoData('category_products');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));
        static::assertSame(2.0, $resultData['products']['average']);

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        static::assertSame(1.0, $resultData['products']['average']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetMaxProductsPerCategoryPerShop()
    {
        $this->installDemoData('category_products');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));
        static::assertSame(6, $resultData['products']['max']);

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        static::assertSame(2, $resultData['products']['max']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetCategoryTreePerShop()
    {
        $this->installDemoData('category_products');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));

        // First child category, "Example Parent 1"
        static::assertCount(2, $resultData['tree'][0]['children']);
        // First child of "Example parent 1", name "Example 3"
        static::assertCount(1, $resultData['tree'][0]['children'][1]['children']);
        // Child of "Example 3", name "Example 5"
        static::assertCount(1, $resultData['tree'][0]['children'][1]['children'][0]['children']);

        static::assertEquals(0, $resultData['tree'][0]['children'][1]['active']);
        static::assertEquals(1, $resultData['tree'][0]['children'][1]['children'][0]['active']);
        static::assertEquals(1, $resultData['tree'][0]['children'][0]['hasProductStream']);

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));

        // First child category, "Example Parent 2"
        static::assertCount(1, $resultData['tree'][0]['children']);
        // First child of "Example Parent 2", name "Example 4"
        static::assertCount(0, $resultData['tree'][0]['children'][0]['children']);

        static::assertEquals(1, $resultData['tree'][0]['active']);
        static::assertEquals(0, $resultData['tree'][0]['children'][0]['active']);
    }
}
