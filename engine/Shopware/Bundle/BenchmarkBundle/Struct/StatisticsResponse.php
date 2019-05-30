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

namespace Shopware\Bundle\BenchmarkBundle\Struct;

class StatisticsResponse
{
    /**
     * @var \DateTimeInterface
     */
    private $dateUpdated;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $isFinished;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @param string $token
     * @param bool   $isFinished
     * @param int    $shopId
     */
    public function __construct(\DateTimeInterface $dateUpdated, $token, $isFinished, $shopId = null)
    {
        $this->dateUpdated = $dateUpdated;
        $this->token = $token;
        $this->isFinished = $isFinished;
        $this->shopId = $shopId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->isFinished;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return int|null
     */
    public function getShopId()
    {
        return $this->shopId;
    }
}
