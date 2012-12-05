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

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware SwagAboCommerce Plugin - Article Model
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBundle\Models
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_articles")
 */
class Article extends ModelEntity
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
     * The article id of the selected article.
     *
     * @var integer
     *
     * @ORM\Column(name="article_id", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var \Shopware\Models\Article\Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\CustomModels\SwagAboCommerce\Price",
     *     mappedBy="aboArticle",
     *     cascade={"persist", "update"},
     *     orphanRemoval=true
     * )
     */
    private $prices;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exclusive", type="boolean", nullable=false)
     */
    private $exclusive = false;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", nullable=false)
     */
    private $ordernumber;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * Minimal duration in unit $durationUnit
     *
     * @var integer
     *
     * @ORM\Column(name="min_duration", type="integer", nullable=false)
     */
    private $minDuration;

    /**
     * Maximum duration in unit $durationUnit
     *
     * @var integer
     *
     * @ORM\Column(name="max_duration", type="integer", nullable=false)
     */
    private $maxDuration;

    /**
     * Unit of $durationUnit weeks/months etc.
     *
     * @var string
     *
     * @ORM\Column(name="duration_unit", type="string", nullable=false)
     */
    private $durationUnit;

    /**
     * Minimal duration in unit $durationUnit
     *
     * @var integer
     *
     * @ORM\Column(name="min_delivery_interval", type="integer", nullable=false)
     */
    private $minDeliveryInterval;

    /**
     * Maximum duration in unit $deliveryIntervalUnit
     *
     * @var integer
     *
     * @ORM\Column(name="max_delivery_interval", type="integer", nullable=false)
     */
    private $maxDeliveryInterval;

    /**
     * Unit of delivery in weeks/months etc.
     *
     * @var string
     *
     * @ORM\Column(name="delivery_interval_unit", type="string", nullable=false)
     */
    private $deliveryIntervalUnit;

    /**
     * @var boolean
     *
     * @ORM\Column(name="limited", type="boolean", nullable=false)
     */
    private $limited = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_Units_per_week", type="integer", nullable=false)
     */
    private $maxUnitsPerWeek;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    /**
     * @return \Shopware\Models\Article\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param \Shopware\Models\Article\Article $article
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $prices
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, '\Shopware\CustomModels\SwagAboCommerce\Price', 'prices', 'aboArticle');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param boolean $active
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $description
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param boolean $exclusive
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = (bool) $exclusive;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $limited
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setLimited($limited)
    {
        $this->limited = (bool) $limited;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * @param int $maxDeliveryInterval
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setMaxDeliveryInterval($maxDeliveryInterval)
    {
        $this->maxDeliveryInterval = $maxDeliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDeliveryInterval()
    {
        return $this->maxDeliveryInterval;
    }

    /**
     * @param int $maxDuration
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * @param int $minDeliveryInterval
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setMinDeliveryInterval($minDeliveryInterval)
    {
        $this->minDeliveryInterval = $minDeliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDeliveryInterval()
    {
        return $this->minDeliveryInterval;
    }

    /**
     * @param string $deliveryIntervalUnit
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setDeliveryIntervalUnit($deliveryIntervalUnit)
    {
        $this->deliveryIntervalUnit = $deliveryIntervalUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryIntervalUnit()
    {
        return $this->deliveryIntervalUnit;
    }

    /**
     * @param int $minDuration
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setMinDuration($minDuration)
    {
        $this->minDuration = $minDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDuration()
    {
        return $this->minDuration;
    }

    /**
     * @param string $durationUnit
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
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
     * @param int $maxUnitsPerWeek
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setMaxUnitsPerWeek($maxUnitsPerWeek)
    {
        $this->maxUnitsPerWeek = $maxUnitsPerWeek;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxUnitsPerWeek()
    {
        return $this->maxUnitsPerWeek;
    }

    /**
     * @param string $ordernumber
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function setOrdernumber($ordernumber)
    {
        $this->ordernumber = $ordernumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrdernumber()
    {
        return $this->ordernumber;
    }
}
