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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\CustomerAttributeCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\CacheManager;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class CustomerAttributeConditionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $service = Shopware()->Container()->get(\Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface::class);
        $service->update('s_user_attributes', 'test', 'integer');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        /** @var CrudService $service */
        $service = Shopware()->Container()->get(\Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface::class);
        $service->delete('s_user_attributes', 'test');

        /** @var CacheManager $cache */
        $cache = Shopware()->Container()->get(\Shopware\Components\CacheManager::class);

        $cache->clearProxyCache();
        $cache->clearOpCache();
    }

    public function testAttribute()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new CustomerAttributeCondition('test', ConditionInterface::OPERATOR_EQ, 30)
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'attribute' => [
                        'test' => 30,
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'attribute' => [
                        'test' => 31,
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'attribute' => [
                        'test' => 29,
                    ],
                ],
            ]
        );
    }
}
