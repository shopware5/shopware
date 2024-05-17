<?php
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

namespace Shopware\Tests\Functional\Core;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Enlight_Components_Test_TestCase;
use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\DataLoaderInterface;
use Shopware\Bundle\AttributeBundle\Service\DataPersisterInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_Config;
use sRewriteTable;

class RewriteTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;

    private const ATTRIBUTE_NAME = 'sw_foo';

    private const ATTRIBUTE_VALUE = 'loremipsum';

    private const NEW_ROUTER_PRODUCT_TEMPLATE = '{sCategoryPath articleID=$sArticle.id}/{$sArticle.id}/{$sArticle.name}/{if $sArticle.' . self::ATTRIBUTE_NAME . '}{$sArticle.' . self::ATTRIBUTE_NAME . '}{/if}';

    private const DEFAULT_PRODUCT_URL = 'Sommerwelten/Beachwear/178/Strandtuch-Ibiza';

    private const PRODUCT_DETAILS_ID = 407;

    private const PRODUCT_ID = 178;

    /**
     * @var sRewriteTable
     */
    private $rewriteTable;

    private ModelManager $entityManager;

    private Shopware_Components_Config $config;

    private DataPersisterInterface $dataPersister;

    private DataLoaderInterface $dataLoader;

    private RouterInterface $router;

    private CrudServiceInterface $attributesService;

    private DateTimeImmutable $date;

    private Shop $shop;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->rewriteTable = Shopware()->Modules()->RewriteTable();
        $this->attributesService = $this->getContainer()->get(CrudServiceInterface::class);
        $this->entityManager = $this->getContainer()->get(ModelManager::class);
        $this->config = $this->getContainer()->get('config');
        $this->dataPersister = $this->getContainer()->get(DataPersisterInterface::class);
        $this->dataLoader = $this->getContainer()->get(DataLoaderInterface::class);
        $this->router = $this->getContainer()->get(RouterInterface::class);
        $this->date = new DateTimeImmutable();
        $this->connection = $this->getContainer()->get(Connection::class);

        $shop = $this->entityManager->find(Shop::class, 1);
        static::assertInstanceOf(Shop::class, $shop);
        $this->shop = $shop;
    }

    /**
     * * @dataProvider provider
     */
    public function testRewriteString(string $string, string $result): void
    {
        static::assertEquals($result, $this->rewriteTable->sCleanupPath($string));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function provider(): array
    {
        return [
            [' a  b ', 'a-b'],
            ['hello', 'hello'],
            ['Hello', 'Hello'],
            ['Hello World', 'Hello-World'],
            ['Hello-World', 'Hello-World'],
            ['Hello:World', 'Hello-World'],
            ['Hello,World', 'Hello-World'],
            ['Hello;World', 'Hello-World'],
            ['Hello&World', 'Hello-World'],
            ['Hello & World', 'Hello-World'],
            ['Hello.World.html', 'Hello.World.html'],
            ['Hello World.html', 'Hello-World.html'],
            ['Hello World!', 'Hello-World'],
            ['Hello World!.html', 'Hello-World.html'],
            ['Hello / World', 'Hello/World'],
            ['Hello/World', 'Hello/World'],
            ['H+e#l1l--o/W§o r.l:d)', 'H-e-l1l-o/W-o-r.l-d'],
            [': World', 'World'],
            ['Nguyễn Đăng Khoa', 'Nguyen-Dang-Khoa'],
            ['Ä ä Ö ö Ü ü ß', 'AE-ae-OE-oe-UE-ue-ss'],
            ['Á À á à É È é è Ó Ò ó ò Ñ ñ Ú Ù ú ù', 'A-A-a-a-E-E-e-e-O-O-o-o-N-n-U-U-u-u'],
            ['Â â Ê ê Ô ô Û û', 'A-a-E-e-O-o-U-u'],
            ['Â â Ê ê Ô ô Û 1', 'A-a-E-e-O-o-U-1'],
            ['Привет мир', 'Privet-mir'],
            ['Привіт світ', 'Privit-svit'],
            ['°¹²³@', '0123at'],
            ['Mórë thån wørds', 'More-thaan-woerds'],
            ['Блоґ їжачка', 'Blog-jizhachka'],
            ['фильм', 'film'],
            ['драма', 'drama'],
            ['ελληνικά', 'ellinika'],
            ['C’est du français !', 'C-est-du-francais'],
            ['Één jaar', 'Een-jaar'],
            ['tiếng việt rất khó', 'tieng-viet-rat-kho'],
        ];
    }

    public function testCustomAttributesInSeoProductUrl(): void
    {
        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerShop($this->shop);

        $this->createCustomProductAttribute();

        $this->connection->beginTransaction();
        $this->connection->executeStatement(
            'UPDATE s_articles SET changetime = :changeTime WHERE id = :productId;',
            [
                'changeTime' => $this->date->modify('-1 days')->format('Y:m:d H:i:s'),
                'productId' => self::PRODUCT_ID,
            ]
        );

        $this->enterDataToNewAttribute();

        $this->setCustomProductSeoUrl();
        static::assertSame(self::NEW_ROUTER_PRODUCT_TEMPLATE, $this->config->get('routerarticletemplate'));

        $this->rebuildSeoIndex();

        $url = $this->checkProductUrl();
        static::assertStringEndsWith(self::ATTRIBUTE_VALUE, $url);

        $this->connection->rollBack();
        $this->entityManager->clear();

        $this->removeCustomProductAttribute();
        static::assertNull($this->attributesService->get('s_articles_attributes', self::ATTRIBUTE_NAME));

        $url = $this->checkProductUrl();
        static::assertStringContainsString(strtolower(self::DEFAULT_PRODUCT_URL), strtolower($url));
        static::assertStringEndsNotWith(self::ATTRIBUTE_VALUE, $url);
    }

    private function createCustomProductAttribute(): void
    {
        $this->attributesService->update(
            's_articles_attributes',
            self::ATTRIBUTE_NAME,
            'string'
        );

        $this->entityManager->generateAttributeModels(['s_articles_attributes']);

        $column = $this->attributesService->get('s_articles_attributes', self::ATTRIBUTE_NAME);
        static::assertInstanceOf(ConfigurationStruct::class, $column);
    }

    private function enterDataToNewAttribute(): void
    {
        $this->dataPersister->persist([self::ATTRIBUTE_NAME => self::ATTRIBUTE_VALUE], 's_articles_attributes', self::PRODUCT_DETAILS_ID);
        $result = $this->dataLoader->load('s_articles_attributes', self::PRODUCT_DETAILS_ID);
        static::assertSame(self::ATTRIBUTE_VALUE, $result[self::ATTRIBUTE_NAME]);
    }

    private function setCustomProductSeoUrl(): void
    {
        $this->setConfig('routerarticletemplate', self::NEW_ROUTER_PRODUCT_TEMPLATE);
    }

    private function rebuildSeoIndex(): void
    {
        $this->rewriteTable->baseSetup();
        $this->rewriteTable->sCreateRewriteTableArticles($this->date->modify('-2 days')->format('Y:m:d H:i:s'));
    }

    private function checkProductUrl(): string
    {
        $context = Context::createFromShop($this->shop, $this->config);
        static::assertInstanceOf(Context::class, $context);

        return $this->router->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => self::PRODUCT_ID], $context);
    }

    private function removeCustomProductAttribute(): void
    {
        $this->attributesService->delete('s_articles_attributes', self::ATTRIBUTE_NAME);
    }
}
