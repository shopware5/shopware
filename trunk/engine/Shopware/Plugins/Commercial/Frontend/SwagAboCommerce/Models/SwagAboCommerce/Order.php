<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\CustomModels\SwagAboCommerce;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware SwagAboCommerce Plugin - Order Model
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBundle\Models
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_orders")
 */
class Order extends ModelEntity
{
    /**
     * Unique identifier for a single bundle
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The id of the selected order.
     *
     * @var integer
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * The id of the selected order.
     *
     * @var integer
     * @ORM\Column(name="article_order_detail_id", type="integer", nullable=true)
     */
    private $articleOrderDetailId;

    /**
     * The id of the selected order.
     *
     * @var integer
     * @ORM\Column(name="discount_order_detail_id", type="integer", nullable=true)
     */
    private $discountOrderDetailId;

    /**
     * The id of the last abo order
     *
     * @var integer
     * @ORM\Column(name="last_order_id", type="integer", nullable=true)
     */
    private $lastOrderId;

    /**
     * Duration in unit $durationUnit
     *
     * @var integer
     * @ORM\Column(name="duration", type="integer", nullable=false)
     */
    private $duration;

    /**
     * Unit of $duration weeks/months etc.
     *
     * @var string
     * @ORM\Column(name="duration_unit", type="string", nullable=true)
     */
    private $durationUnit;

    /**
     * Maximum duration in unit $maxDurationUnit
     *
     * @var integer
     * @ORM\Column(name="delivery_interval", type="integer", nullable=false)
     */
    private $deliveryInterval;

    /**
     * Unit of $deliveryInterval weeks/months etc.
     *
     * @var integer
     * @ORM\Column(name="delivery_interval_unit", type="integer", nullable=false)
     */
    private $deliveryIntervalUnit;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $articleOrderDetailId
     * @return int
     */
    public function setArticleOrderDetailId($articleOrderDetailId)
    {
        $this->articleOrderDetailId = $articleOrderDetailId;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleOrderDetailId()
    {
        return $this->articleOrderDetailId;
    }

    /**
     * @param int $deliveryInterval
     * @return int
     */
    public function setDeliveryInterval($deliveryInterval)
    {
        $this->deliveryInterval = $deliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryInterval()
    {
        return $this->deliveryInterval;
    }

    /**
     * @param int $deliveryIntervalUnit
     * @return int
     */
    public function setDeliveryIntervalUnit($deliveryIntervalUnit)
    {
        $this->deliveryIntervalUnit = $deliveryIntervalUnit;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryIntervalUnit()
    {
        return $this->deliveryIntervalUnit;
    }

    /**
     * @param int $discountOrderDetailId
     * @return int
     */
    public function setDiscountOrderDetailId($discountOrderDetailId)
    {
        $this->discountOrderDetailId = $discountOrderDetailId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountOrderDetailId()
    {
        return $this->discountOrderDetailId;
    }

    /**
     * @param int $lastOrderId
     */
    public function setLastOrderId($lastOrderId)
    {
        $this->lastOrderId = $lastOrderId;
    }

    /**
     * @return int
     */
    public function getLastOrderId()
    {
        return $this->lastOrderId;
    }

    /**
     * @param int $duration
     * @return int
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $durationUnit
     * @return string
     */
    public function setDurationUnit($durationUnit)
    {
        $this->durationUnit = $durationUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * @param int $orderId
     * @return int
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
