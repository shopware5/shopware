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

namespace Shopware\CustomModels\LiveShopping;
use Shopware\Components\Model\ModelEntity,
 Doctrine\ORM\Mapping AS ORM,
 Symfony\Component\Validator\Constraints as Assert,
 Symfony\Component\Validator\ExecutionContext,
 Doctrine\Common\Collections\ArrayCollection;

/**
 * Shopware LiveShopping model
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Models\LiveShopping
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_lives")
 *
 * @Assert\Callback(methods={"validateLiveShopping"})
 */
class LiveShopping extends ModelEntity
{
    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    protected $id;

    /**
     * @var integer $articleId
     *
     * @ORM\Column(name="article_id", type="integer", nullable=true)
     */
    protected $articleId;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    protected $type;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name = '';

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    protected $active = false;

    /**
     * @var string $orderNumber
     *
     * @ORM\Column(name="order_number", type="string", nullable=true)
     */
    protected $number;

    /**
     * @var integer $maxQuantityEnable
     *
     * @ORM\Column(name="max_quantity_enable", type="integer", nullable=false)
     */
    protected $limited = false;

    /**
     * @var integer $maxQuantity
     *
     * @ORM\Column(name="max_quantity", type="integer", nullable=false)
     */
    protected $quantity = 0;

    /**
     * @var \DateTime $validFrom
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    protected $validFrom = null;

    /**
     * @var \DateTime $validTo
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    protected $validTo = null;

    /**
     * @var \DateTime $datum
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     */
    protected $created = 'now';

    /**
     * @var integer $sells
     *
     * @ORM\Column(name="sells", type="integer", nullable=false)
     */
    protected $sells = 0;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\LiveShopping\Price", mappedBy="liveShopping", cascade={"persist", "update"}, orphanRemoval=true)
     * @var ArrayCollection
     */
    protected $prices;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *
     * @var \Shopware\Models\Article\Article
     */
    protected $article;

    /**
     * The $customerGroups property contains an offset of \Shopware\Models\Customer\Group.
     * All defined customer groups can buy the defined live shopping article.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_live_customer_groups",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     *      }
     * )
     * @var ArrayCollection $customerGroups
     */
    protected $customerGroups;

    /**
     * INVERSE SIDE
     * The $limitedVariants property contains an offset of \Shopware\Models\Article\Detail.
     * If the live shopping created on a configurator article, the bundle only displayed in the store
     * front if the user select one of the variants of this collection.
     * Otherwise the live shopping article will be hidden.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinTable(name="s_articles_live_stint",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     *      }
     * )
     * @var ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    protected $limitedVariants;

    /**
     * INVERSE SIDE
     * The $shops property contains an offset of \Shopware\Models\Shop\Shop.
     * The live shopping article is only displayed in the sub shops which are defined in this array.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_articles_live_shoprelations",
     *      joinColumns={
     *          @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     *      }
     * )
     * @var ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    protected $shops;

    /**
     * Current price of the live shopping article.
     *
     * The current price property is an class property which
     * calculates from the Shopware_Components_LiveShopping class.
     * This property contains the numeric value of the current price
     * of the live shopping article.
     *
     * @var float
     */
    protected $currentPrice;

    /**
     * Class property.
     *
     * This property is only used in the store front.
     * The property contains updated live shopping prices for the current frontend session.
     * The updated prices based on the customer group, selected shipping address and customer login.
     *
     * @var ArrayCollection
     */
    protected $updatedPrices;

    /**
     * Class constructor, initials the array collection of this model.
     */
    public function __construct()
    {
        $this->shops = new ArrayCollection();
        $this->limitedVariants = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->updatedPrices = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return LiveShopping
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return LiveShopping
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return LiveShopping
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool|int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param $active
     *
     * @return LiveShopping
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param $validFrom
     *
     * @return LiveShopping
     */
    public function setValidFrom($validFrom)
    {
        if (!($validFrom instanceof \DateTime) && strlen($validFrom) > 0) {
            $this->validFrom = new \DateTime($validFrom);
        } else {
            $this->validFrom = $validFrom;
        }
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param $validTo
     *
     * @return LiveShopping
     */
    public function setValidTo($validTo)
    {
        if (!($validTo instanceof \DateTime) && strlen($validTo) > 0) {
            $this->validTo = new \DateTime($validTo);
        } else {
            $this->validTo = $validTo;
        }
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param $created
     *
     * @return LiveShopping
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getSells()
    {
        return $this->sells;
    }

    /**
     * @param $sells
     *
     * @return LiveShopping
     */
    public function setSells($sells)
    {
        $this->sells = $sells;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $prices
     *
     * @return \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function setPrices($prices)
    {
        $this->setOneToMany($prices, '\Shopware\CustomModels\LiveShopping\Price', 'prices', 'liveShopping');
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    public function getLimitedVariants()
    {
        return $this->limitedVariants;
    }

    /**
     * @param $limitedVariants
     */
    public function setLimitedVariants($limitedVariants)
    {
        $this->limitedVariants = $limitedVariants;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * Validation callback function.
     * This function is responsible to validate the liveshopping base data in the backend.
     */
    public function validateLiveShopping(ExecutionContext $context)
    {
//        $context->addViolation('Message', array(), null);
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }


    /**
     * @param int $limited
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;
    }

    /**
     * @return int
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
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
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * Time difference calculation.
     *
     * Returns an instance of the \DateInterval class which contains the difference between the valid from
     * and valid to date of this class.
     * In case that the valid from or valid to date isn't an instance of \DateTime the function returns false
     *
     * @return \DateInterval|bool
     */
    public function getTimeDifference()
    {
        try {
            return $this->getDateDifference($this->getValidFrom(), $this->getValidTo());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Minute difference calculation.
     *
     * Returns the numeric value of the minute difference between the valid from and valid to date.
     * In case that the getTimeDifference function returns false, this function returns false, too.
     * @return bool|int
     */
    public function getMinuteDifference()
    {
        return $this->getTotalMinutesOfDateInterval(
            $this->getTimeDifference()
        );
    }

    /**
     * Price calculation for the difference between start and end price.
     *
     * This function returns the difference between the start and end price
     * of the first price record of the prices collection.
     * In case that the prices collection has no record, this function
     * returns false.
     *
     * @return float|bool
     */
    public function getPriceDifference()
    {
        $price = $this->getPrices()->first();

        if (!$price instanceof \Shopware\CustomModels\LiveShopping\Price) {
            return false;
        }

        if ($this->type === \Shopware_Components_LiveShopping::SURCHARGE_TYPE) {
            return $price->getEndPrice() - $price->getPrice();
        } else {
            return $price->getPrice() - $price->getEndPrice();
        }
    }

    /**
     * Per minute calculation.
     *
     * This function returns the discount/surcharge value per minute
     * for live shopping articles which has configured as "discount per minute" or
     * as "surcharge per minute". The per minute value is used for the frontend price calculation
     * for each live shopping article. This calculation is based on the valid from and valid to
     * dates.
     */
    public function getPerMinuteValue()
    {
        $timeDifference = $this->getMinuteDifference();

        $priceDiffence = $this->getPriceDifference();

        if ($timeDifference === false || $priceDiffence === false) {
            return false;
        }

        return $priceDiffence / $timeDifference;
    }

    /**
     * Returns the total minutes of the passed interval.
     *
     * This function is used to calculate the current price of a live shopping
     * article.
     *
     * @param $interval|bool
     * @return bool|int
     */
    public function getTotalMinutesOfDateInterval($interval)
    {
        if (!$interval instanceof \DateInterval) {
            return false;
        }

        $dayMinutes = $interval->days * 24 * 60;

        $hourMinutes = $interval->h * 60;

        return $interval->i + $dayMinutes + $hourMinutes;
    }

    /**
     * Returns the time difference of the passed dates.
     *
     * This function returns an instance of the \DateInterval class which contains the
     * difference between the two passed date objects.
     *
     * <pre>
     * Example:
     *  DateInterval Object
     *  (
     *      [y] => 0
     *      [m] => 0
     *      [d] => 0
     *      [h] => 12
     *      [i] => 40
     *      [s] => 0
     *      [invert] => 0
     *      [days] => 0
     *  )
     * </pre>
     * @param $from \DateTime
     * @param $to \DateTime
     * @return bool|\DateInterval
     */
    public function getDateDifference($from, $to)
    {
        if (!($from instanceof \DateTime || !($to instanceof \DateTime))) {
            return false;
        }

        return $from->diff($to);
    }

    /**
     * Returns the expired minutes.
     *
     * This function returns the total minutes which has expired
     * since the valid from date to the passed date.
     *
     * @param string $date
     * @return bool|\DateInterval
     */
    public function getExpiredDateInterval($date = 'now')
    {
        if (!$date instanceof \DateTime && strlen($date) > 0) {
            $date = new \DateTime($date);
        } else if (!$date instanceof \DateTime) {
            $date = new \DateTime();
        }

        return $this->getDateDifference($this->getValidFrom(), $date);
    }

    /**
     * Returns the remaining time.
     *
     * This function is used to calculate the remaining time of a single live shopping
     * article.
     *
     * @param string $date
     *
     * @return bool|\DateInterval
     */
    public function getRemainingDateInterval($date = 'now')
    {
        if (!$date instanceof \DateTime && strlen($date) > 0) {
            $date = new \DateTime($date);
        } else if (!$date instanceof \DateTime) {
            $date = new \DateTime();
        }

        return $this->getDateDifference($date, $this->getValidTo());
    }

    /**
     * @return float
     */
    public function getCurrentPrice()
    {
        return $this->currentPrice;
    }

    /**
     * @param float $currentPrice
     */
    public function setCurrentPrice($currentPrice)
    {
        $this->currentPrice = $currentPrice;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUpdatedPrices()
    {
        if (!$this->updatedPrices instanceof ArrayCollection) {
            if (is_array($this->updatedPrices)) {
                $this->updatedPrices = new ArrayCollection($this->updatedPrices);
            } else {
                $this->updatedPrices = new ArrayCollection();
            }
        }
        return $this->updatedPrices;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $updatedPrices
     */
    public function setUpdatedPrices($updatedPrices)
    {
        $this->updatedPrices = $updatedPrices;
    }
}