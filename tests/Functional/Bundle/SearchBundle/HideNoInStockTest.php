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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

/**
 * @group elasticSearch
 */
class HideNoInStockTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    protected const LASTSTOCK_ENABLED = 'productLastStockEnabled';
    protected const LASTSTOCK_DISABLED = 'productLastStockDisabled';

    /**
     * @var array
     */
    private $testProducts = [];

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->testProducts = [
            self::LASTSTOCK_ENABLED => [
                'mainDetail' => $this->helper->getVariantData(
                    [
                        'number' => self::LASTSTOCK_ENABLED,
                        'inStock' => 0,
                        'minPurchase' => 1,
                        'lastStock' => true,
                    ]
                ),
            ],
            self::LASTSTOCK_DISABLED => [
                'mainDetail' => $this->helper->getVariantData(
                    [
                        'number' => self::LASTSTOCK_DISABLED,
                        'inStock' => 0,
                        'minPurchase' => 1,
                        'lastStock' => false,
                    ]
                ),
            ],
        ];

        $this->storeFrontCriteriaFactory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');
    }

    /**
     * When hideNoInStock is disabled, all products - regardless of their inStock and lastStock property - should be
     * available in the search result/listing.
     */
    public function testSearchWithoutHideNoInStock(): void
    {
        $before = Shopware()->Config()->get('hideNoInStock');
        $this->setConfig('hideNoInStock', false);

        $this->search(
            $this->testProducts,
            [self::LASTSTOCK_ENABLED, self::LASTSTOCK_DISABLED],
            null,
            $this->getStoreFrontConditions()
        );

        $this->setConfig('hideNoInStock', $before);
    }

    /**
     * When hideNoInStock is enabled, only out-of-stock products with lastStock disabled should be available in the
     * search result/listing. This is essentially a regression test for SW-23898.
     */
    public function testSearchWithHideNoInStock(): void
    {
        $before = Shopware()->Config()->get('hideNoInStock');
        $this->setConfig('hideNoInStock', true);

        $this->search(
            $this->testProducts,
            [self::LASTSTOCK_DISABLED],
            null,
            $this->getStoreFrontConditions()
        );

        $this->setConfig('hideNoInStock', $before);
    }

    private function getStoreFrontConditions(): array
    {
        $criteria = $this->storeFrontCriteriaFactory->createBaseCriteria([], $this->getContext());

        return array_filter($criteria->getBaseConditions(), [$this, 'categoryConditionFilter']);
    }

    private static function categoryConditionFilter($el): bool
    {
        return get_class($el) !== CategoryCondition::class;
    }
}
