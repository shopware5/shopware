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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use sBasket;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set as StoreFrontConfiguratorSet;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail as Variant;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_Article;
use Symfony\Component\HttpFoundation\Request;

class ArticleTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const PRODUCT_WITH_VARIANTS_ID = 180;
    private const PRODUCT_ID_SPACHTELMASSE = 272;

    private Shopware_Controllers_Backend_Article $controller;

    private ModelManager $modelManager;

    private sBasket $basketModule;

    private ConfiguratorServiceInterface $configuratorService;

    public function setUp(): void
    {
        $this->controller = new Shopware_Controllers_Backend_Article();
        $this->controller->setContainer($this->getContainer());
        $this->controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        $this->modelManager = $this->getContainer()->get(ModelManager::class);
        $this->basketModule = $this->getContainer()->get('modules')->Basket();
        $this->configuratorService = $this->getContainer()->get(ConfiguratorServiceInterface::class);
    }

    /**
     * Tests whether a product cannot be overwritten by a save request that bases on outdated data.
     * (The product in the database is newer than that one the request body is based on.)
     */
    public function testSaveArticleOverwriteProtection(): void
    {
        $helper = new Helper($this->getContainer());
        $product = $helper->createProduct([
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'lastStock' => true,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 29.97,
                    ],
                ],
            ],
            'taxId' => 4,
            'supplierId' => 2,
            'categories' => [10],
        ]);

        // Prepare post data for request
        $postData = [
            'id' => $product->getId(),
            'changed' => $product->getChanged()->format('c'),
        ];

        // Try to change the entity with the correct timestamp. This should work
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost($postData);
        $this->controller->setRequest($request);
        $this->controller->saveAction();

        static::assertTrue($this->controller->View()->getAssign('success'));

        // Now use an outdated timestamp. The controller should detect this and fail.
        $postData['changed'] = '2008-08-07 18:11:31';
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost($postData);
        $this->controller->setRequest($request);
        $this->controller->saveAction();
        static::assertFalse($this->controller->View()->getAssign('success'));
    }

    public function testInterpretNumberSyntax(): void
    {
        $product = new Product();

        $detail = new Variant();
        $detail->setNumber('SW500');
        $product->setMainDetail($detail);

        $class = new ReflectionClass($this->controller);
        $prepareNumberSyntaxMethod = $class->getMethod('prepareNumberSyntax');
        $prepareNumberSyntaxMethod->setAccessible(true);
        $commands = $prepareNumberSyntaxMethod->invokeArgs($this->controller, ['{mainDetail.number}.{n}']);

        $interpretNumberSyntaxMethod = $class->getMethod('interpretNumberSyntax');
        $interpretNumberSyntaxMethod->setAccessible(true);
        $result = $interpretNumberSyntaxMethod->invokeArgs($this->controller, [
            $product,
            $detail,
            $commands,
            2,
        ]);

        static::assertSame('SW500.2', $result);
    }

    public function testSaveNetRegulationPrice(): void
    {
        $product = [
            'supplierId' => 5,
            'name' => 'test',
            'active' => true,
            'taxId' => 1,
            'autoNumber' => '10002',
            'mainPrices' => [
                [
                    'from' => 1,
                    'to' => 'Beliebig',
                    'price' => 10,
                    'pseudoPrice' => 0,
                    'regulationPrice' => 119,
                    'percent' => 0,
                    'customerGroupKey' => 'EK',
                ],
            ],
        ];

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setPost($product);
        $this->controller->setRequest($request);
        $this->controller->saveAction();

        $data = $this->controller->View()->getAssign('data');
        $firstProduct = array_pop($data);

        $regulationPrice = $this->modelManager->getConnection()->fetchOne('SELECT regulation_price FROM s_articles_prices WHERE articleID = ' . $firstProduct['id']);

        // (119 / 119) * 100
        static::assertEquals(100, (float) $regulationPrice);
    }

    public function testProductNameAfterTurningVariantItemBackToDefaultProduct(): void
    {
        $product = $this->modelManager->getRepository(Product::class)->find(self::PRODUCT_WITH_VARIANTS_ID);
        static::assertInstanceOf(Product::class, $product);
        $productName = $product->getName();

        $variant = $product->getMainDetail();
        static::assertInstanceOf(Variant::class, $variant);
        $ordernumber = (string) $variant->getNumber();
        static::assertInstanceOf(Set::class, $product->getConfiguratorSet());

        $configOptions = $variant->getConfiguratorOptions();
        static::assertGreaterThan(0, $configOptions->count());
        $options = [];
        foreach ($configOptions as $option) {
            $options[] = $option->getName();
        }

        $productNameInBasket = $this->addToBasket($ordernumber);
        static::assertSame($productName . ' ' . implode(' /', $options), $productNameInBasket);

        $this->deleteVariants();
        $this->turnToDefaultProduct();

        $this->modelManager->clear();
        $product = $this->modelManager->getRepository(Product::class)->find(self::PRODUCT_WITH_VARIANTS_ID);
        static::assertInstanceOf(Product::class, $product);
        $variant = $product->getMainDetail();
        static::assertInstanceOf(Variant::class, $variant);

        static::assertNull($product->getConfiguratorSet());
        static::assertCount(0, $variant->getConfiguratorOptions());
        static::assertSame('', $variant->getAdditionalText());

        $productNameInBasket = $this->addToBasket($ordernumber);
        static::assertSame($productName, $productNameInBasket);
    }

    public function testVariantOptionsNotShownOnProductDetailPageAfterDeletingThese(): void
    {
        $product = $this->modelManager->getRepository(Product::class)->find(self::PRODUCT_WITH_VARIANTS_ID);
        static::assertInstanceOf(Product::class, $product);
        $variant = $product->getMainDetail();
        static::assertInstanceOf(Variant::class, $variant);
        $ordernumber = $variant->getNumber();
        static::assertIsString($ordernumber);

        $this->deleteVariants();

        $context = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext();
        $product = Shopware()->Container()->get(ListProductServiceInterface::class)->get($ordernumber, $context);
        static::assertInstanceOf(ListProduct::class, $product);
        $configurator = $this->configuratorService->getProductConfigurator(
            $product,
            $context,
            []
        );
        static::assertInstanceOf(StoreFrontConfiguratorSet::class, $configurator);
        foreach ($configurator->getGroups() as $group) {
            static::assertCount(1, $group->getOptions());
        }
    }

    public function testGetArticleImagesIfImageWasDeleted(): void
    {
        $manipulatedImagePosition = 2;
        $connection = $this->getContainer()->get(Connection::class);
        $connection->update('s_articles_img', ['media_id' => null], ['position' => $manipulatedImagePosition]);
        $images = $this->controller->getArticleImages(self::PRODUCT_ID_SPACHTELMASSE);

        foreach ($images as $image) {
            if ((int) $image['position'] === $manipulatedImagePosition) {
                static::assertNull($image['media']);
            } else {
                static::assertIsArray($image['media']);
            }
        }
    }

    private function addToBasket(string $ordernumber): string
    {
        $this->basketModule->sAddArticle($ordernumber);
        $sql = 'SELECT articlename FROM s_order_basket WHERE sessionID = :sessionId;';
        $productName = $this->modelManager->getConnection()->executeQuery($sql, ['sessionId' => $this->getContainer()->get('session')->getId()])->fetchOne();
        $this->basketModule->sDeleteBasket();
        static::assertSame(0, $this->basketModule->sCountBasket());

        return $productName;
    }

    private function deleteVariants(): void
    {
        $sql = '
        SELECT
            ad.id,
            acor.option_id
        FROM
            s_articles_details AS ad,
            s_article_configurator_option_relations AS acor
        WHERE
            ad.articleID = :id AND acor.article_id = ad.id';

        $variantDatas = $this->modelManager->getConnection()->executeQuery($sql, ['id' => self::PRODUCT_WITH_VARIANTS_ID])->fetchAllAssociative();
        static::assertGreaterThan(1, \count($variantDatas), 'This product has no variants.');

        $params = [
            'details' => [],
        ];

        for ($i = 0, $size = \count($variantDatas); $i < $size; ++$i) {
            $params['details'][$i] = ['id' => (int) $variantDatas[$i]['id']];
            $params['details'][$i]['configuratorOptions'][] = ['id' => (int) $variantDatas[$i]['option_id']];
        }

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);
        $this->controller->setRequest($request);
        $this->controller->deleteDetailAction();

        static::assertTrue($this->controller->View()->getAssign('success'));

        $variants = $this->modelManager->getConnection()->executeQuery($sql, ['id' => self::PRODUCT_WITH_VARIANTS_ID])->fetchAllAssociative();
        static::assertCount(1, $variants);
    }

    private function turnToDefaultProduct(): void
    {
        $productDataParams = require __DIR__ . '/_fixtures/article/productData.php';

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($productDataParams);
        $this->controller->setRequest($request);
        $this->controller->saveAction();

        $response = $this->controller->View()->getAssign();
        static::assertTrue($response['success']);
        static::assertSame('', $response['data'][0]['mainDetail']['additionalText']);
    }
}
