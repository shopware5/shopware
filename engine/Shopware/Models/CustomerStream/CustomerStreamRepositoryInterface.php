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

namespace Shopware\Models\CustomerStream;

interface CustomerStreamRepositoryInterface
{
    /**
     * Checks if the provided category id has an configured emotion for some customer streams
     *
     * @param int $categoryId
     *
     * @return int|bool
     */
    public function hasCustomerStreamEmotions($categoryId);

    /**
     * @param int[] $ids
     *
     * @return array[]
     */
    public function fetchBackendListing(array $ids);

    /**
     * @param int[] $streamIds
     *
     * @return array
     */
    public function fetchStreamsCustomerCount(array $streamIds);

    /**
     * @return int
     */
    public function getNotIndexedCount();

    /**
     * @return int
     */
    public function getCustomerCount();

    /**
     * Fetches the next ids for search table indexing
     *
     * @param int  $offset
     * @param bool $full
     *
     * @return array
     */
    public function fetchSearchIndexIds($offset, $full = false);

    /**
     * @param int|null $streamId
     * @param int      $month
     *
     * @return array[]
     */
    public function fetchCustomerAmount($streamId = null, $month = 12);

    /**
     * @return array[] indexed by year-month
     */
    public function fetchAmountPerStreamChart();

    /**
     * @return string|false
     */
    public function getLastFillIndexDate();

    /**
     * @param int[] $customerIds
     *
     * @return array
     */
    public function fetchStreamsForCustomers(array $customerIds);
}
