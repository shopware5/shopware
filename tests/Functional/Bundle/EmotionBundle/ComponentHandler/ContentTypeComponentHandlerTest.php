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

namespace Shopware\Tests\Functional\Bundle\EmotionBundle\ComponentHandler;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_TestCase;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ContentTypeComponentHandler;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Tests\Functional\KernelStorage;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

class ContentTypeComponentHandlerTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    public function setUp(): void
    {
        parent::setUp();

        static::assertFalse($this->getContainer()->has('shopware.bundle.content_type.testcontent'));
        $fixtures = file_get_contents(__DIR__ . '/fixtures/fixtures.sql');
        static::assertIsString($fixtures);
        $this->getContainer()->get(Connection::class)->exec($fixtures);
        KernelStorage::unset();
        static::assertTrue($this->getContainer()->has('shopware.bundle.content_type.testcontent'));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        static::assertTrue($this->getContainer()->has('shopware.bundle.content_type.testcontent'));
        $fixtures = file_get_contents(__DIR__ . '/fixtures/cleanUp.sql');
        static::assertIsString($fixtures);
        $this->getContainer()->get(Connection::class)->exec($fixtures);
        KernelStorage::unset();
        static::assertFalse($this->getContainer()->has('shopware.bundle.content_type.testcontent'));
    }

    public function testLoadAllSelectedContent(): void
    {
        $contentTypeComponentHandler = $this->getContainer()->get(ContentTypeComponentHandler::class);

        $resolvedDataCollection = new ResolvedDataCollection();
        $resolvedDataCollection->setBatchResult(new BatchProductSearchResult([]));
        $elementConfig = new ElementConfig();
        $elementConfig->set(ContentTypeComponentHandler::CONTENT_TYPE_KEY, 'testcontent');
        $elementConfig->set(ContentTypeComponentHandler::MODE_KEY, ContentTypeComponentHandler::MODE_SELECTED);
        $elementConfig->set(ContentTypeComponentHandler::IDS_KEY, implode(ContentTypeComponentHandler::ID_SEPERATOR, [1, 2, 3, 4, 5, 6]));
        $element = new Element();
        $element->setId(PHP_INT_MAX);
        $element->setConfig($elementConfig);
        $shopContext = $this->createShopContext();

        $contentTypeComponentHandler->handle($resolvedDataCollection, $element, $shopContext);

        static::assertCount(6, $element->getData()->get(ContentTypeComponentHandler::ITEMS_KEY));
    }
}
