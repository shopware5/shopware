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

namespace Shopware\Bundle\PluginInstallerBundle\Context;

class ListingRequest extends BaseRequest
{
    private int $offset;

    private int $limit;

    /**
     * @var array[]
     */
    private array $sortings;

    /**
     * @var array[]
     */
    private array $conditions;

    /**
     * @param array[] $conditions
     * @param array[] $sortings
     */
    public function __construct(
        string $locale,
        string $shopwareVersion,
        int $offset,
        int $limit,
        array $conditions,
        array $sortings
    ) {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->conditions = $conditions;
        $this->sortings = $sortings;

        parent::__construct(
            $locale,
            $shopwareVersion
        );
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return array[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return array[]
     */
    public function getSortings()
    {
        return $this->sortings;
    }
}
