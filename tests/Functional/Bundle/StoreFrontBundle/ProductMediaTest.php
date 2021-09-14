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

use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Category\Category;

class ProductMediaTest extends TestCase
{
    public function testProductMediaList(): void
    {
        $this->resetContext();
        $context = $this->getContext();
        $numbers = ['testProductMediaList-1', 'testProductMediaList-2'];
        foreach ($numbers as $number) {
            $this->helper->createArticle(
                $this->getProduct($number, $context, null, 4)
            );
        }

        $listProducts = Shopware()->Container()->get(ListProductServiceInterface::class)->getList($numbers, $context);
        $mediaList = Shopware()->Container()->get(ProductMediaGatewayInterface::class)->getList($listProducts, $context);

        static::assertCount(2, $mediaList);

        foreach ($numbers as $number) {
            static::assertArrayHasKey($number, $mediaList);

            $productMediaList = $mediaList[$number];

            static::assertCount(3, $productMediaList);

            /** @var Media $media */
            foreach ($productMediaList as $media) {
                if ($media->isPreview()) {
                    $this->assertMediaFile('sasse-korn', $media);
                } else {
                    $this->assertMediaFile('test-spachtelmasse', $media);
                }
            }
        }
    }

    public function testVariantMediaList(): void
    {
        $this->resetContext();
        $numbers = ['testVariantMediaList1-', 'testVariantMediaList2-'];
        $context = $this->getContext();
        $productsArray = [];

        foreach ($numbers as $number) {
            $data = $this->getVariantImageProduct($number, $context);
            $article = $this->helper->createArticle($data);
            $productsArray[] = $article;
        }

        $variantNumbers = ['testVariantMediaList1-1', 'testVariantMediaList1-2', 'testVariantMediaList2-1'];

        $products = Shopware()->Container()->get(ListProductServiceInterface::class)->getList($variantNumbers, $context);

        $mediaList = Shopware()->Container()->get(VariantMediaGatewayInterface::class)->getList($products, $context);

        static::assertCount(3, $mediaList);
        foreach ($variantNumbers as $number) {
            static::assertArrayHasKey($number, $mediaList);

            $variantMedia = $mediaList[$number];

            foreach ($variantMedia as $media) {
                $this->assertMediaFile('sasse-korn', $media);
            }
        }

        $products = Shopware()->Container()->get(ListProductServiceInterface::class)->getList($numbers, $context);
        $mediaList = Shopware()->Container()->get(ProductMediaGatewayInterface::class)->getList($products, $context);

        static::assertCount(2, $mediaList);

        foreach ($numbers as $number) {
            static::assertArrayHasKey($number, $mediaList);
            $media = $mediaList[$number];

            static::assertCount(1, $media);
            $media = array_shift($media);
            static::assertTrue($media->isPreview());
        }
    }

    public function testProductImagesWithVariant(): void
    {
        $this->resetContext();
        $number = 'testProductImagesWithVariant';
        $context = $this->getContext();

        $data = $this->getVariantImageProduct($number, $context, 3);

        $data['variants'][0]['number'] = 'testProductImagesWithVariant-1';
        $data['variants'][0]['images'] = [];

        $this->helper->createArticle($data);

        $variantNumber = 'testProductImagesWithVariant-1';
        $product = Shopware()->Container()->get(ProductServiceInterface::class)
            ->get($variantNumber, $context);

        static::assertCount(2, $product->getMedia());
    }

    /**
     * @param string   $number
     * @param int|null $imageCount
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $imageCount = null
    ) {
        $data = parent::getProduct($number, $context, $category);

        $data['images'][] = $this->helper->getImageData(
            'sasse-korn.jpg',
            ['main' => 1]
        );

        for ($i = 0; $i < $imageCount - 2; ++$i) {
            $data['images'][] = $this->helper->getImageData();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getVariantImageProduct(string $number, Struct\ShopContext $context, int $imageCount = 2): array
    {
        $data = $this->getProduct(
            $number,
            $context,
            null,
            $imageCount
        );

        $data = array_merge(
            $data,
            $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                $number,
                ['Farbe' => ['rot', 'gelb']]
            )
        );

        $data['variants'][0]['images'] = [$this->helper->getImageData('sasse-korn.jpg')];
        $data['variants'][1]['images'] = [$this->helper->getImageData('sasse-korn.jpg')];

        return $data;
    }

    private function assertMediaFile(string $expected, Media $media): void
    {
        static::assertInstanceOf(Media::class, $media);
        static::assertNotEmpty($media->getThumbnails());
        static::assertStringContainsString($expected, $media->getFile());

        foreach ($media->getThumbnails() as $thumbnail) {
            static::assertStringContainsString($expected, $thumbnail->getSource());
        }
    }

    private function resetContext(): void
    {
        // correct router context for url building
        Shopware()->Container()->get(RouterInterface::class)->setContext(
            new Context(
                'localhost',
                Shopware()->Shop()->getBasePath(),
                Shopware()->Shop()->getSecure()
            )
        );
    }
}
