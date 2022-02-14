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
use Shopware\Bundle\SearchBundle\Condition\HasPseudoPriceCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class HasPseudoPriceConditionTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function testPseudoPriceOfVariants(): void
    {
        $groups = $this->helper->insertConfiguratorData(
            [
                'color' => ['red', 'green'],
                'size' => ['xl', 'l'],
            ]
        );

        $condition = new HasPseudoPriceCondition();

        $this->search(
            [
                'A' => ['groups' => $this->buildConfigurator(['color' => ['red', 'green'], 'size' => ['xl', 'l']], $groups)],
                'B' => ['groups' => $this->buildConfigurator(['color' => ['red'], 'size' => ['l']], $groups)],
            ],
            ['A'],
            null,
            [$condition]
        );
    }

    /**
     * Get products and set the graduated prices of the variants.
     *
     * @param array{groups: array<Group>} $data
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        Category $category = null,
        $data = ['groups' => []]
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $configurator = $this->helper->createConfiguratorSet($data['groups']);

        $variants = array_merge([
            'prices' => $this->helper->getGraduatedPrices($context->getCurrentCustomerGroup()->getKey()),
        ], $this->helper->getUnitData());

        $variants = $this->helper->generateVariants(
            $configurator['groups'],
            $number,
            $variants
        );
        $variants = $this->onlySetPseudoPriceToFourthVariant($variants);

        if (isset($variants[0]['prices'])) {
            $product['mainDetail']['prices'] = $variants[0]['prices'];
        }

        $product['configuratorSet'] = $configurator;
        $product['variants'] = $variants;

        return $product;
    }

    /**
     * Creates the structure of the configurator.
     *
     * @param array<string, array<string>> $expected
     * @param array<Group>                 $createdGroups
     *
     * @return array<Group>
     */
    private function buildConfigurator(array $expected, array $createdGroups): array
    {
        $groups = [];
        foreach ($expected as $group => $optionNames) {
            foreach ($createdGroups as $globalGroup) {
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

    /**
     * @param array<array<string, mixed>> $variants
     *
     * @return array<array<string, mixed>>
     */
    private function onlySetPseudoPriceToFourthVariant(array $variants): array
    {
        $count = 0;
        foreach ($variants as &$variant) {
            foreach ($variant['prices'] as &$price) {
                $price['pseudoPrice'] = 0.0;
            }
            unset($price);
            ++$count;
            if ($count === 3) {
                break;
            }
        }
        unset($variant);

        return $variants;
    }
}
