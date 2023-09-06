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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductDownloadServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;

class DownloadTest extends TestCase
{
    public function testSingleProduct(): void
    {
        $context = $this->getContext();
        $number = 'testSingleProduct';
        $data = $this->getProduct($number, $context);
        $this->helper->createProduct($data);

        $product = Shopware()->Container()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($product);
        $downloads = Shopware()->Container()->get(ProductDownloadServiceInterface::class)->get($product, $context);
        static::assertIsArray($downloads);

        static::assertCount(2, $downloads);

        foreach ($downloads as $download) {
            static::assertInstanceOf(Download::class, $download);
            static::assertContains($download->getFile(), [$data['downloads'][0]['file'], $data['downloads'][1]['file']]);
            static::assertCount(1, $download->getAttributes());
            static::assertTrue($download->hasAttribute('core'));
        }
    }

    public function testDownloadList(): void
    {
        $numbers = ['testDownloadList-1', 'testDownloadList-2'];
        $context = $this->getContext();
        foreach ($numbers as $number) {
            $data = $this->getProduct($number, $context);
            $this->helper->createProduct($data);
        }

        $products = Shopware()->Container()->get(ListProductServiceInterface::class)->getList($numbers, $context);
        $downloads = Shopware()->Container()->get(ProductDownloadServiceInterface::class)->getList($products, $context);

        static::assertCount(2, $downloads);

        foreach ($downloads as $number => $productDownloads) {
            static::assertContains($number, $numbers);
            static::assertCount(2, $productDownloads);
        }

        foreach ($numbers as $number) {
            static::assertArrayHasKey($number, $downloads);
        }
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $additionally = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $mediaImgs = Shopware()->Db()->fetchCol('SELECT path FROM s_media LIMIT 2');

        $product['downloads'] = [
            [
                'name' => 'first-download',
                'size' => 100,
                'file' => $mediaImgs[0],
                'attribute' => ['id' => 20000],
            ],
            [
                'name' => 'second-download',
                'size' => 200,
                'file' => $mediaImgs[0],
                'attribute' => ['id' => 20000],
            ],
        ];

        return $product;
    }
}
