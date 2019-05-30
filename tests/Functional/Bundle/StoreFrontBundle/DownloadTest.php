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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;

class DownloadTest extends TestCase
{
    public function testSingleProduct()
    {
        $context = $this->getContext();
        $number = 'testSingleProduct';
        $data = $this->getProduct($number, $context);
        $this->helper->createArticle($data);

        $product = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($number, $context);

        $downloads = Shopware()->Container()->get('shopware_storefront.product_download_service')->get($product, $context);

        static::assertCount(2, $downloads);

        /** @var Download $download */
        foreach ($downloads as $download) {
            static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Download', $download);
            static::assertContains($download->getFile(), [$data['downloads'][0]['file'], $data['downloads'][1]['file']]);
            static::assertCount(1, $download->getAttributes());
            static::assertTrue($download->hasAttribute('core'));
        }
    }

    public function testDownloadList()
    {
        $numbers = ['testDownloadList-1', 'testDownloadList-2'];
        $context = $this->getContext();
        foreach ($numbers as $number) {
            $data = $this->getProduct($number, $context);
            $this->helper->createArticle($data);
        }

        $products = Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->getList($numbers, $context);

        $downloads = Shopware()->Container()->get('shopware_storefront.product_download_service')
            ->getList($products, $context);

        static::assertCount(2, $downloads);

        foreach ($downloads as $number => $productDownloads) {
            static::assertContains($number, $numbers);
            static::assertCount(2, $productDownloads);
        }

        foreach ($numbers as $number) {
            static::assertArrayHasKey($number, $downloads);
        }
    }

    /**
     * @param string                             $number
     * @param \Shopware\Models\Category\Category $category
     *
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
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
