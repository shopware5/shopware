<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Core;

use Enlight_Components_Session_Namespace;
use Enlight_Components_Snippet_Namespace;
use Enlight_Controller_Front;
use Enlight_Event_EventManager;
use PHPUnit\Framework\TestCase;
use sBasket;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Config;
use Shopware_Components_Modules;
use Shopware_Components_Snippet_Manager;
use sSystem;

class BasketAddBasketAttributesTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const TEST_SESSION_ID = 'BasketAddBasketAttributesTestSessionId';

    /**
     * @before
     */
    public function prepareDatabase(): void
    {
        $sql = file_get_contents(__DIR__ . '/fixtures/basket_add_basket_attribute_test.sql');
        static::assertIsString($sql);

        $this->getContainer()->get('dbal_connection')->executeQuery($sql);
    }

    public function testInsertDiscount(): void
    {
        $this->createSBasket()->sInsertDiscount();

        static::assertNotNull($this->getDatabaseResult());
    }

    public function testInsertSurcharge(): void
    {
        $this->createSBasket()->sInsertSurcharge();

        static::assertNotNull($this->getDatabaseResult());
    }

    public function testInsertSurchargePercent(): void
    {
        $this->createSBasket()->sInsertSurchargePercent();

        static::assertNotNull($this->getDatabaseResult());
    }

    private function getDatabaseResult(): ?int
    {
        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['basketAttributes.id'])
            ->from('s_order_basket', 'basket')
            ->join('basket', 's_order_basket_attributes', 'basketAttributes', 'basket.id = basketAttributes.basketID')
            ->where('basket.sessionID = :sessionId')
            ->setParameter('sessionId', self::TEST_SESSION_ID)
            ->execute()
            ->fetchOne();

        if (!$result) {
            return null;
        }

        return (int) $result;
    }

    private function createSBasket(): sBasket
    {
        return new sBasket(
            $this->getContainer()->get('db'),
            $this->createEventManagerMock(),
            $this->createSnippetManagerMock(),
            $this->createConfigMock(),
            $this->createSessionMock(),
            $this->createFrontControllerMock(),
            $this->createModulesMock(),
            $this->createSystemMock(),
            $this->createContextServiceMock(),
            $this->createAdditionalTextServiceMock()
        );
    }

    private function createAdditionalTextServiceMock(): AdditionalTextServiceInterface
    {
        $additionalTextServiceMock = $this->createMock(AdditionalTextServiceInterface::class);

        static::assertInstanceOf(AdditionalTextServiceInterface::class, $additionalTextServiceMock);

        return $additionalTextServiceMock;
    }

    private function createContextServiceMock(): ContextServiceInterface
    {
        $contextServiceMock = $this->createMock(ContextServiceInterface::class);

        static::assertInstanceOf(ContextServiceInterface::class, $contextServiceMock);

        return $contextServiceMock;
    }

    private function createSystemMock(): sSystem
    {
        $sSystemMock = $this->createMock(sSystem::class);

        $sSystemMock->sUSERGROUPDATA = ['id' => 1, 'minimumorder' => 100, 'minimumordersurcharge' => 1];
        $sSystemMock->sCurrency['factor'] = 1;

        static::assertInstanceOf(sSystem::class, $sSystemMock);

        return $sSystemMock;
    }

    private function createModulesMock(): Shopware_Components_Modules
    {
        $modulesMock = $this->createMock(Shopware_Components_Modules::class);

        static::assertInstanceOf(Shopware_Components_Modules::class, $modulesMock);

        return $modulesMock;
    }

    private function createFrontControllerMock(): Enlight_Controller_Front
    {
        $frontControllerMock = $this->createMock(Enlight_Controller_Front::class);

        static::assertInstanceOf(Enlight_Controller_Front::class, $frontControllerMock);

        return $frontControllerMock;
    }

    private function createSessionMock(): Enlight_Components_Session_Namespace
    {
        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);

        $sessionMock->method('get')->willReturnMap([
            ['sessionId', null, self::TEST_SESSION_ID],
            ['taxFree', null, false],
            ['sPaymentID', null, 2],
        ]);

        static::assertInstanceOf(Enlight_Components_Session_Namespace::class, $sessionMock);

        return $sessionMock;
    }

    private function createConfigMock(): Shopware_Components_Config
    {
        $configMock = $this->createMock(Shopware_Components_Config::class);

        static::assertInstanceOf(Shopware_Components_Config::class, $configMock);

        return $configMock;
    }

    private function createSnippetManagerMock(): Shopware_Components_Snippet_Manager
    {
        $snippetNameSpaceMock = $this->createMock(Enlight_Components_Snippet_Namespace::class);
        $snippetNameSpaceMock->method('get')->willReturn('AnyDiscountName');

        $snippetManagerMock = $this->createMock(Shopware_Components_Snippet_Manager::class);
        $snippetManagerMock->method('getNamespace')->willReturn($snippetNameSpaceMock);

        static::assertInstanceOf(Shopware_Components_Snippet_Manager::class, $snippetManagerMock);

        return $snippetManagerMock;
    }

    private function createEventManagerMock(): Enlight_Event_EventManager
    {
        $params = [
            'sessionID' => 'BasketAddBasketAttributesTestSessionId',
            'articlename' => '-5 % AnyDiscountName',
            'articleID' => 0,
            'ordernumber' => 'sw-discount',
            'quantity' => 1,
            'price' => -0.55,
            'netprice' => -0.55,
            'tax_rate' => 19,
            'datum' => '2023-01-04 13:16:50',
            'modus' => 3,
            'currencyFactor' => 1,
        ];

        $eventManagerMock = $this->createMock(Enlight_Event_EventManager::class);
        $eventManagerMock->method('filter')->willReturn($params);

        static::assertInstanceOf(Enlight_Event_EventManager::class, $eventManagerMock);

        return $eventManagerMock;
    }
}
