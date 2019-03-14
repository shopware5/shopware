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

namespace Shopware\Bundle\SitemapBundle\Service;

use Shopware\Bundle\SitemapBundle\Struct\FilterContainer;

class FilterContainerFactory
{
    /**
     * @var ConfigHandler
     */
    private $configHandler;

    public function __construct(ConfigHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    /**
     * @param string $resourceName
     * @param int    $shopId
     *
     * @return FilterContainer
     */
    public function buildFilterContainer($resourceName, $shopId)
    {
        return new FilterContainer($resourceName, $this->selectFiltersForResource($resourceName, $shopId));
    }

    /**
     * @param string $resourceName
     * @param int    $shopId
     *
     * @return array
     */
    private function selectFiltersForResource($resourceName, $shopId)
    {
        $filters = $this->configHandler->get(ConfigHandler::EXCLUDED_URLS_KEY);

        $filters = $this->findFiltersForResource($filters, $resourceName, (int) $shopId);

        if (!$filters) {
            return [];
        }

        $explodedIdentifiers = [];

        // Create an entry for comma separated identifiers
        foreach (array_column($filters, 'identifier') as $identifier) {
            foreach (explode(',', $identifier) as $identifierPart) {
                $explodedIdentifiers[] = trim($identifierPart);
            }
        }

        $explodedIdentifiers = array_keys(array_flip(array_map('intval', $explodedIdentifiers)));

        return $explodedIdentifiers;
    }

    /**
     * @param string $resourceName
     * @param int    $shopId
     *
     * @return array
     */
    private function findFiltersForResource(array $filters, $resourceName, $shopId)
    {
        $filtersForResource = [];

        foreach ($filters as $filter) {
            if ($filter['resource'] === $resourceName && in_array((int) $filter['shopId'], [$shopId, 0], true)) {
                $filtersForResource[] = $filter;
            }
        }

        return $filtersForResource;
    }
}
