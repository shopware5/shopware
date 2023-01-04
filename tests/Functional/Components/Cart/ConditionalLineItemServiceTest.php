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

namespace Shopware\Tests\Functional\Components\Cart;

use Enlight_Components_Session_Namespace;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\ConditionalLineItemService;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Config;
use sSystem;

class ConditionalLineItemServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const TEST_SESSION_ID = 'ConditionalLineItemServiceSessionId';

    public function testAddConditionalLineItemShouldAddAttributeEntry(): void
    {
        $this->getConditionalLineItemService()->addConditionalLineItem(
            'discountName',
            'SWOrderNumber',
            10.99,
            19.00,
            3
        );

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

    private function getConditionalLineItemService(): ConditionalLineItemService
    {
        return new ConditionalLineItemService(
            $this->createSystemMock(),
            $this->createSessionMock(),
            $this->createConfigMock(),
            $this->getContainer()->get('shopware.cart.basket_helper'),
            $this->getContainer()->get('dbal_connection')
        );
    }

    private function createSystemMock(): sSystem
    {
        $sSystemMock = $this->createMock(sSystem::class);

        $sSystemMock->sUSERGROUPDATA = [];

        static::assertInstanceOf(sSystem::class, $sSystemMock);

        return $sSystemMock;
    }

    private function createSessionMock(): Enlight_Components_Session_Namespace
    {
        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        $sessionMock->expects(static::once())->method('get')->willReturn(self::TEST_SESSION_ID);

        static::assertInstanceOf(Enlight_Components_Session_Namespace::class, $sessionMock);

        return $sessionMock;
    }

    private function createConfigMock(): Shopware_Components_Config
    {
        $configMock = $this->createMock(Shopware_Components_Config::class);
        $configMock->expects(static::once())->method('get')->willReturn(false);

        static::assertInstanceOf(Shopware_Components_Config::class, $configMock);

        return $configMock;
    }
}
