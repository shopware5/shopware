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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_Article;

class ArticleTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * @var ReflectionMethod
     */
    private $prepareNumberSyntaxMethod;

    /**
     * @var ReflectionMethod
     */
    private $interpretNumberSyntaxMethod;

    /**
     * @var MockObject|Shopware_Controllers_Backend_Article
     */
    private $controller;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->controller = $this->createPartialMock(Shopware_Controllers_Backend_Article::class, []);

        $class = new ReflectionClass($this->controller);

        $this->prepareNumberSyntaxMethod = $class->getMethod('prepareNumberSyntax');
        $this->prepareNumberSyntaxMethod->setAccessible(true);

        $this->interpretNumberSyntaxMethod = $class->getMethod('interpretNumberSyntax');
        $this->interpretNumberSyntaxMethod->setAccessible(true);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth(false);
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl(false);
    }

    /**
     * Tests whether an article cannot be overwritten by a save request that bases on outdated data. (The article in the
     * database is newer than that one the request body is based on.)
     */
    public function testSaveArticleOverwriteProtection(): void
    {
        $helper = new Helper($this->getContainer());
        $article = $helper->createProduct([
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
            'id' => $article->getId(),
            'changed' => $article->getChanged()->format('c'),
        ];

        // Try to change the entity with the correct timestamp. This should work
        $this->Request()
            ->setMethod('POST')
            ->setPost($postData);

        $this->dispatch('backend/Article/save');
        static::assertTrue($this->View()->getAssign('success'));

        // Now use an outdated timestamp. The controller should detect this and fail.
        $postData['changed'] = '2008-08-07 18:11:31';
        $this->Request()
            ->setMethod('POST')
            ->setPost($postData);

        $this->dispatch('backend/Article/save');
        static::assertFalse($this->View()->getAssign('success'));
    }

    public function testInterpretNumberSyntax(): void
    {
        $article = new Article();

        $detail = new Detail();
        $detail->setNumber('SW500');
        $article->setMainDetail($detail);

        $commands = $this->prepareNumberSyntaxMethod->invokeArgs($this->controller, ['{mainDetail.number}.{n}']);

        $result = $this->interpretNumberSyntaxMethod->invokeArgs($this->controller, [
            $article,
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
                    0 => [
                            'id' => 0,
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

        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $request = new Enlight_Controller_Request_RequestTestCase();

        $request->setPost($product);
        $this->controller->setView($view);
        $this->controller->setRequest($request);
        $this->controller->setContainer($this->getContainer());
        $this->controller->saveAction();

        $data = $view->getAssign('data');
        $firstArticle = array_pop($data);

        $regulationPrice = $this->getContainer()->get(Connection::class)->fetchOne('SELECT regulation_price FROM s_articles_prices WHERE articleID = ' . $firstArticle['id']);

        //(119 / 119) * 100
        static::assertEquals(100, (float) $regulationPrice);
    }
}
