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

namespace Shopware\Components\HttpCache;

use DateTime;
use Enlight_Controller_Request_Request as Request;
use Shopware\Components\HttpCache\InvalidationDate\InvalidationDateInterface;

class DynamicCacheTimeService implements CacheTimeServiceInterface
{
    /**
     * @var InvalidationDateInterface[]
     */
    private $invalidationDateProvider;

    /**
     * @var CacheTimeServiceInterface
     */
    private $cacheTimeService;

    /**
     * @var CacheRouteGenerationService
     */
    private $cacheRouteGeneration;

    public function __construct(
        CacheRouteGenerationService $cacheRouteGeneration,
        CacheTimeServiceInterface $cacheTimeService,
        \IteratorAggregate $invalidationDateProvider
    ) {
        $this->cacheTimeService = $cacheTimeService;
        $this->cacheRouteGeneration = $cacheRouteGeneration;
        $this->invalidationDateProvider = iterator_to_array($invalidationDateProvider, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTime(Request $request)
    {
        $action = $this->cacheRouteGeneration->getActionRoute($request);
        $invalidationDate = null;
        $defaultInvalidationTime = $this->cacheTimeService->getCacheTime($request);

        /** @var InvalidationDateInterface $dateProvider */
        foreach ($this->invalidationDateProvider as $dateProvider) {
            if ($dateProvider->supportsRoute($action) && $invalidationDate = $dateProvider->getInvalidationDate($request)) {
                $difference = (int) $invalidationDate->format('U') - (int) (new DateTime())->format('U');

                return $difference > 0 && $difference < $defaultInvalidationTime ? $difference : $defaultInvalidationTime;
            }
        }

        return $defaultInvalidationTime;
    }
}
