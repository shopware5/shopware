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

class BusinessIntelligenceResponse
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var string
     */
    private $html;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @param string $html
     * @param int    $shopId
     */
    public function __construct(\DateTimeInterface $dateTime, $html, $shopId = null)
    {
        $this->dateTime = $dateTime;
        $this->html = $html;
        $this->shopId = $shopId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
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
