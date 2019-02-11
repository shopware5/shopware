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
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlFilter\FilterInterface;
use Shopware\Bundle\SitemapBundle\UrlFilter\UrlFilterException;
use Shopware\Bundle\SitemapBundle\UrlFilterInterface;

class UrlFilter implements UrlFilterInterface
{
    /**
     * @var array[FilterContainer[]]
     */
    private $filterContainers = [];

    /**
     * @var FilterContainerFactory
     */
    private $filterContainerFactory;

    /**
     * @var FilterInterface[]
     */
    private $filterHandler;

    public function __construct(FilterContainerFactory $filterContainerFactory, \IteratorAggregate $filterHandler)
    {
        $this->filterContainerFactory = $filterContainerFactory;
        $this->filterHandler = iterator_to_array($filterHandler, false);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $urls, $shopId)
    {
        $filteredUrls = [];

        /** @var Url $url */
        foreach ($urls as $url) {
            $filters = $this->getFilterContainer($url->getResource(), $shopId)->getFilters();

            // Check if the whole resource should be skipped (value: 0)
            if (in_array(0, $filters, true)) {
                continue;
            }

            // Check if no filters exist at all
            if (!$filters) {
                return $urls;
            }

            $filterHandler = $this->getFilterHandler($url->getResource());

            if (!$filterHandler) {
                throw new UrlFilterException(sprintf('No handler known for resource \'%s\'.', $url->getResource()));
            }

            if ($filterHandler->isFiltered($url->getIdentifier(), $filters)) {
                continue;
            }

            $filteredUrls[] = $url;
        }

        return $filteredUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterContainer(FilterContainer $filterContainer, $shopId)
    {
        $this->filterContainers[$shopId][$filterContainer->getResourceName()] = $filterContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterContainer($resourceName, $shopId)
    {
        if (!$this->hasFilterContainer($resourceName, $shopId)) {
            $this->addFilterContainer(
                $this->filterContainerFactory->buildFilterContainer($resourceName, $shopId),
                $shopId
            );
        }

        return $this->filterContainers[$shopId][$resourceName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilterContainer($resourceName, $shopId)
    {
        return isset($this->filterContainers[$shopId][$resourceName]);
    }

    /**
     * @param string $resourceName
     *
     * @return FilterInterface|null
     */
    private function getFilterHandler($resourceName)
    {
        foreach ($this->filterHandler as $filterHandler) {
            if ($filterHandler->supports($resourceName)) {
                return $filterHandler;
            }
        }

        return null;
    }
}
