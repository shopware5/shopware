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
    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array[]
     */
    private $sortings;

    /**
     * @var array[]
     */
    private $conditions;

    /**
     * @param string  $locale
     * @param string  $shopwareVersion
     * @param int     $offset
     * @param int     $limit
     * @param array[] $conditions
     * @param array[] $sortings
     */
    public function __construct(
        $locale,
        $shopwareVersion,
        $offset,
        $limit,
        $conditions,
        $sortings
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
