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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Generator;
use Shopware\Bundle\SearchBundle\Condition\HasPseudoPriceCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Converter;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class HasPseudoPriceConditionCustomerGroupTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const PRODUCT_NUMBER = 'A';

    private const CURRENT_CUSTOMER_GROUP = 'PHP';
    private const FALLBACK_CUSTOMER_GROUP = 'EK';

    protected Helper $helper;

    protected Converter $converter;

    /**
     * @var \Shopware\Models\Article\Configurator\Group[]
     */
    private array $groups = [];

    protected function setUp(): void
    {
        $this->helper = new Helper($this->getContainer());
        parent::setUp();
    }

    /**
     * @dataProvider getCustomerGroupPseudoPrices
     *
     * @param array<self::PRODUCT_NUMBER> $expectedNumber
     */
    public function testPseudoPriceOnlyAvailableForSpecificCustomerGroup(
        bool $hasCurrentCustomerGroupPseudoPrice,
        bool $hasFallbackCustomerGroupPseudoPrice,
        array $expectedNumber
    ): void {
        $context = $this->getContext();
        $fallbackCustomerGroup = $this->converter->convertCustomerGroup($this->helper->createCustomerGroup(['key' => self::FALLBACK_CUSTOMER_GROUP]));
        $context->setFallbackCustomerGroup($fallbackCustomerGroup);
        $this->search(
            [
                self::PRODUCT_NUMBER => compact('hasCurrentCustomerGroupPseudoPrice', 'hasFallbackCustomerGroupPseudoPrice'),
            ],
            $expectedNumber,
            null,
            [new HasPseudoPriceCondition()],
            [],
            [],
            $context
        );
    }

    public function getCustomerGroupPseudoPrices(): Generator
    {
        yield 'No customer group has pseudo price' => [
            false,
            false,
            [],
        ];
        yield 'Only current customer group has pseudo price' => [
            true,
            false,
            [self::PRODUCT_NUMBER],
        ];
        yield 'Only fallback customer group has pseudo price' => [
            false,
            true,
            [],
        ];
        yield 'Current and fallback customer groups have pseudo price' => [
            true,
            true,
            [self::PRODUCT_NUMBER],
        ];
    }

    /**
     * @dataProvider getCustomerGroupPseudoPricesWithVariants
     *
     * @param array<self::PRODUCT_NUMBER> $expectedNumber
     * @param array<string>               $expectedNumber
     */
    public function testPseudoPriceOnlyAvailableForSpecificCustomerGroupWithVariants(
        bool $hasCurrentCustomerGroupPseudoPrice,
        bool $hasFallbackCustomerGroupPseudoPrice,
        array $expectedNumber
    ): void {
        $this->groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $products = [
            'A' => [
                'groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']]),
                'hasCurrentCustomerGroupPseudoPrice' => $hasCurrentCustomerGroupPseudoPrice,
                'hasFallbackCustomerGroupPseudoPrice' => $hasFallbackCustomerGroupPseudoPrice,
            ],
            'B' => [
                'groups' => $this->buildConfigurator(['color' => ['green'], 'size' => ['xl']]),
                'hasCurrentCustomerGroupPseudoPrice' => $hasCurrentCustomerGroupPseudoPrice,
                'hasFallbackCustomerGroupPseudoPrice' => $hasFallbackCustomerGroupPseudoPrice,
            ],
            'C' => [
                'groups' => $this->buildConfigurator(['color' => ['red', 'green']]),
                'hasCurrentCustomerGroupPseudoPrice' => $hasCurrentCustomerGroupPseudoPrice,
                'hasFallbackCustomerGroupPseudoPrice' => $hasFallbackCustomerGroupPseudoPrice,
            ],
        ];

        $context = $this->getContext();
        $fallbackCustomerGroup = $this->converter->convertCustomerGroup($this->helper->createCustomerGroup(['key' => self::FALLBACK_CUSTOMER_GROUP]));
        $context->setFallbackCustomerGroup($fallbackCustomerGroup);
        $this->search(
            $products,
            $expectedNumber,
            null,
            [new HasPseudoPriceCondition(), $this->createCondition(['xl', 'l'], 'size', true)],
            [],
            [],
            $context
        );
    }

    public function getCustomerGroupPseudoPricesWithVariants(): Generator
    {
        yield 'No customer group has pseudo price' => [
            false,
            false,
            [],
        ];
        yield 'Only current customer group has pseudo price' => [
            true,
            false,
            ['A1', 'A2', 'B1'],
        ];
        yield 'Only fallback customer group has pseudo price' => [
            false,
            true,
            [],
        ];
        yield 'Current and fallback customer groups have pseudo price' => [
            true,
            true,
            ['A1', 'A2', 'B1'],
        ];
    }

    /**
     * Creates and return the VariantCondition of the given options of the given group.
     *
     * @param array<string> $options
     */
    public function createCondition(array $options, string $groupName, bool $expand = false): VariantCondition
    {
        $mapping = $this->mapOptions();

        if (!isset($mapping['groups'])) {
            throw new Exception('Group is not set');
        }

        if (!isset($mapping['options'])) {
            throw new Exception('Options is not set');
        }

        $ids = array_intersect_key(
            $mapping['options'],
            array_flip($options)
        );

        return new VariantCondition(array_values($ids), $expand, $mapping['groups'][$groupName]);
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $additionally = []
    ): array {
        static::assertIsArray($additionally);

        $product = parent::getProduct($number, $context, $category, $additionally);

        $currentCustomerGroupPrice = [
            'from' => 1,
            'to' => 'beliebig',
            'price' => 7,
            'customerGroupKey' => self::CURRENT_CUSTOMER_GROUP,
            'pseudoPrice' => $additionally['hasCurrentCustomerGroupPseudoPrice'] ? 10 : 0,
        ];
        $fallbackCustomerGroupPrice = [
            'from' => 1,
            'to' => 'beliebig',
            'price' => 8,
            'customerGroupKey' => self::FALLBACK_CUSTOMER_GROUP,
            'pseudoPrice' => $additionally['hasFallbackCustomerGroupPseudoPrice'] ? 10 : 0,
        ];

        $product['mainDetail']['prices'] = [
            $currentCustomerGroupPrice,
            $fallbackCustomerGroupPrice,
        ];

        if (!isset($additionally['groups'])) {
            return $product;
        }

        $configurator = $this->helper->createConfiguratorSet($additionally['groups']);

        $variants = [
            'prices' => [
                $currentCustomerGroupPrice,
                $fallbackCustomerGroupPrice,
            ], ];

        $variants = $this->helper->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );

        $product['configuratorSet'] = $configurator;
        $product['variants'] = $variants;

        return $product;
    }

    /**
     * Returns the mapping of group and option names to ids.
     *
     * @return array{options?: array<string, int>, groups?: array<string, int>}
     */
    private function mapOptions(): array
    {
        $mapping = [];
        foreach ($this->groups as $group) {
            $mapping['groups'][$group->getName()] = $group->getId();
            foreach ($group->getOptions() as $option) {
                $mapping['options'][$option->getName()] = $option->getId();
            }
        }

        return $mapping;
    }

    /**
     * Creates the structure of the configurator.
     *
     * @param array<string, array<string>> $expected
     *
     * @return Group[]
     */
    private function buildConfigurator(array $expected): array
    {
        $groups = [];
        foreach ($expected as $group => $optionNames) {
            foreach ($this->groups as $globalGroup) {
                if ($globalGroup->getName() !== $group) {
                    continue;
                }

                $options = [];
                foreach ($globalGroup->getOptions() as $option) {
                    if (\in_array($option->getName(), $optionNames, true)) {
                        $options[] = $option;
                    }
                }

                $clone = clone $globalGroup;
                $clone->setOptions(new ArrayCollection($options));

                $groups[] = $clone;
            }
        }

        return $groups;
    }
}
