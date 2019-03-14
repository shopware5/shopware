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

namespace Shopware\Models\Benchmark;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_benchmark_config")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks()
 */
class BenchmarkConfig extends ModelEntity
{
    /**
     * Primary Key
     *
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid", nullable=false)
     */
    private $id;

    /**
     * The shop id for this config
     *
     * @var int
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * Defines the date and time when the statistics were sent the last time
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_sent", type="datetime", nullable=false)
     */
    private $lastSent;

    /**
     * Defines the date and time when the last statistics where retrieved from the server
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_received", type="datetime", nullable=false)
     */
    private $lastReceived;

    /**
     * The id of the last order that was sent to the server
     *
     * @var int
     *
     * @ORM\Column(name="last_order_id", type="integer", nullable=false)
     */
    private $lastOrderId;

    /**
     * The id of the last customer that was sent to the server
     *
     * @var int
     *
     * @ORM\Column(name="last_customer_id", type="integer", nullable=false)
     */
    private $lastCustomerId;

    /**
     * The id of the last product that was sent to the server
     *
     * @var int
     *
     * @ORM\Column(name="last_product_id", type="integer", nullable=false)
     */
    private $lastProductId;

    /**
     * The id of the last analytics that was sent to the server
     *
     * @var int
     *
     * @ORM\Column(name="last_analytics_id", type="integer", nullable=false)
     */
    private $lastAnalyticsId;

    /**
     * The most recent date to figure out which orders have been updated since they have last been transmitted
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_updated_orders_date", type="datetime", nullable=true)
     */
    private $lastUpdatedOrdersDate;

    /**
     * The batch size in which entities are to be transmitted
     *
     * @var int
     *
     * @ORM\Column(name="batch_size", type="integer", nullable=false)
     */
    private $batchSize;

    /**
     * The industry the shop is in
     *
     * @var int
     *
     * @ORM\Column(name="industry", type="integer", nullable=false)
     */
    private $industry;

    /**
     * The shop type, e.g. "b2b" or "b2c"
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * The latest token provided by the server
     *
     * @var string
     *
     * @ORM\Column(name="response_token", type="string", length=200, nullable=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="cached_template", type="text", nullable=false)
     */
    private $cachedTemplate;

    /**
     * Flag which defines if the service is active at all
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * Flag which defines if the current shop is locked for transmitting data.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="locked", type="datetime", nullable=true)
     */
    private $locked;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;

        // Default values
        $this->lastReceived = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('UTC'));
        $this->lastSent = new \DateTime('1970-01-01 00:00:00', new \DateTimeZone('UTC'));
        $this->lastOrderId = 0;
        $this->lastCustomerId = 0;
        $this->lastProductId = 0;
        $this->lastAnalyticsId = 0;
        $this->batchSize = 1000;
        $this->active = false;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastSent()
    {
        return $this->lastSent;
    }

    public function setLastSent(\DateTimeInterface $lastSent)
    {
        $this->lastSent = $lastSent;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastReceived()
    {
        return $this->lastReceived;
    }

    public function setLastReceived(\DateTimeInterface $lastReceived)
    {
        $this->lastReceived = $lastReceived;
    }

    /**
     * @return int
     */
    public function getLastOrderId()
    {
        return (int) $this->lastOrderId;
    }

    /**
     * @param int $lastOrderId
     */
    public function setLastOrderId($lastOrderId)
    {
        $this->lastOrderId = (int) $lastOrderId;
    }

    /**
     * @return int
     */
    public function getLastCustomerId()
    {
        return (int) $this->lastCustomerId;
    }

    /**
     * @param int $lastCustomerId
     */
    public function setLastCustomerId($lastCustomerId)
    {
        $this->lastCustomerId = (int) $lastCustomerId;
    }

    /**
     * @return int
     */
    public function getLastProductId()
    {
        return (int) $this->lastProductId;
    }

    /**
     * @param int $lastProductId
     */
    public function setLastProductId($lastProductId)
    {
        $this->lastProductId = (int) $lastProductId;
    }

    /**
     * @return int
     */
    public function getLastAnalyticsId()
    {
        return (int) $this->lastAnalyticsId;
    }

    /**
     * @param int $lastAnalyticsId
     */
    public function setLastAnalyticsId($lastAnalyticsId)
    {
        $this->lastAnalyticsId = (int) $lastAnalyticsId;
    }

    /**#
     * @return \DateTimeInterface
     */
    public function getLastUpdatedOrdersDate()
    {
        return $this->lastUpdatedOrdersDate;
    }

    public function setLastUpdatedOrdersDate(\DateTimeInterface $lastUpdatedOrdersDate)
    {
        $this->lastUpdatedOrdersDate = $lastUpdatedOrdersDate;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * @param int $batchSize
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = (int) $batchSize;
    }

    /**
     * @return int
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * @param int $industry
     */
    public function setIndustry($industry)
    {
        $this->industry = (int) $industry;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getCachedTemplate()
    {
        return $this->cachedTemplate;
    }

    /**
     * @param string $cachedTemplate
     */
    public function setCachedTemplate($cachedTemplate)
    {
        $this->cachedTemplate = $cachedTemplate;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLocked()
    {
        return $this->locked;
    }

    public function setLocked(\DateTimeInterface $locked)
    {
        $this->locked = $locked;
    }

    /**
     * Returns the uuid.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
