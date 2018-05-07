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

use PHPUnit_Framework_Constraint_IsType as IsType;

class ProductsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.products';
    const EXPECTED_KEYS_COUNT = 4;
    const EXPECTED_TYPES = [
        'total' => IsType::TYPE_INT,
        'variants' => [
            'average' => IsType::TYPE_FLOAT,
            'max' => IsType::TYPE_INT,
        ],
        'images' => [
            'sizes' => IsType::TYPE_ARRAY,
            'average' => IsType::TYPE_FLOAT,
            'missing' => IsType::TYPE_INT,
        ],
        'shippingReadyProducts' => IsType::TYPE_INT,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductsTotal()
    {
        $this->installDemoData('products_basic');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertSame(5, $resultData['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetVariantsInformation()
    {
        $this->installDemoData('products_details');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertSame(5, $resultData['variants']['max']);
        $this->assertSame(3.0, $resultData['variants']['average']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetImageSizes()
    {
        $this->installDemoData('media_album_settings');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertCount(8, $resultData['images']['sizes']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductImageInformation()
    {
        $this->installDemoData('products_with_images');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertSame(3.0, $resultData['images']['average']);
        $this->assertSame(2, $resultData['images']['missing']);
    }
}
