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

namespace Shopware\Tests\Functional\Bundle\SearchBundleEs\ConditionHandler;

use ONGR\ElasticsearchDSL\Search;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\ConditionHandler\ProductAttributeConditionHandler;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

/**
 * @group elasticSearch
 */
class ProductAttributeConditionHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    private const BOOL_ATTRIBUTE_FIELD = 'foo';
    private const ATTRIBUTE_TABLE = 's_articles_attributes';

    public function testCreateBoolAttributeFilter(): void
    {
        $this->createAttributeField();

        $productAttributeConditionHandler = $this->getProductAttributeConditionHandler();
        $search = new Search();
        $productAttributeCondition = new ProductAttributeCondition(self::BOOL_ATTRIBUTE_FIELD, ConditionInterface::OPERATOR_EQ, 0);
        $criteria = new Criteria();
        $shopContext = $this->createShopContext();

        $productAttributeConditionHandler->handleFilter($productAttributeCondition, $criteria, $search, $shopContext);

        static::assertSame([
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'attributes.core.foo' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ], $search->toArray());

        $this->deleteAttributeField();
    }

    private function getProductAttributeConditionHandler(): ProductAttributeConditionHandler
    {
        return $this->getContainer()->get(ProductAttributeConditionHandler::class);
    }

    private function createAttributeField(): void
    {
        $this->getAttributeCrudService()->update(
            self::ATTRIBUTE_TABLE,
            self::BOOL_ATTRIBUTE_FIELD,
            TypeMappingInterface::TYPE_BOOLEAN
        );
    }

    private function deleteAttributeField(): void
    {
        $this->getAttributeCrudService()->delete(
            self::ATTRIBUTE_TABLE,
            self::BOOL_ATTRIBUTE_FIELD
        );
    }

    private function getAttributeCrudService(): CrudServiceInterface
    {
        return $this->getContainer()->get(CrudService::class);
    }
}
