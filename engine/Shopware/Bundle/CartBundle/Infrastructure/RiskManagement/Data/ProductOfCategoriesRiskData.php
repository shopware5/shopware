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

namespace Shopware\Bundle\CartBundle\Infrastructure\RiskManagement\Data;

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\RiskManagement\Data\RiskDataInterface;

class ProductOfCategoriesRiskData implements RiskDataInterface
{
    use JsonSerializableTrait;

    /**
     * @var int[] indexed by category id
     */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function hasCategory(int $categoryId): bool
    {
        return array_key_exists($categoryId, $this->mapping);
    }

    public function getProductIdsOfCategory(int $categoryId): array
    {
        if (array_key_exists($categoryId, $this->mapping)) {
            return $this->mapping[$categoryId];
        }

        return [];
    }

    public function getCategoryIds(): array
    {
        return array_keys($this->mapping);
    }
}
