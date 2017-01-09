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

namespace   Shopware\Models\Dispatch;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Dispatch Model
 * <br>
 * One dispatch has the follows associations:
 * <code>
 * - Tax => Shopware\Models\Tax\Tax [1:1]  [s_core_tax]
 * - Countries => Shopware\Models\Country\Country [1:n] [s_core_countries]
 * - Categories => Shopware\Models\Category\Category [1:n] [s_categories]
 * - Payments => Shopware\Models\Payment\Payment [1:n] [s_core_paymentmeans]
 * - Holidays => Shopware\Models\Shipping\Holiday [1:n] [s_premium_holidays]
 * - Shop => Shopware\Models\Shop\Shop [1:1] [s_core_shops]
 * - CustomerGroup => Shopware\Models\Customer\Group [1:1] [s_core_customergroups]
 * - ShippingCosts => Shopware\Models\Dispatch\ShippingCosts [1:n] [s_premium_shippingcosts]
 * </code>
 * Indices for s_premium_dispatch:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_premium_dispatch")
 */
class Dispatch extends ModelEntity
{
    /**
     * Autoincrement ID
     *
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of the Dispatch
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Type of the dispatch.
     * Known types are:
     *  - Standard Shipping == 0
     *  - Alternate Shipping == 1
     *  - Surcharge Shipping == 2
     *  - Reduction Shipping == 3
     *
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * Description of this dispatch
     *
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Comments to the Tracking Link
     *
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=false)
     */
    private $comment;

    /**
     * Active flag
     *
     * @var integer $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * Defines the position on which this dispatch will be displayed.
     *
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * Way to calculate the shipping costs
     * Known types:
     *  - 0 == Weight
     *  - 1 == Price
     *  - 2 == Number of articles
     *  - 3 == Own Calculation
     *
     * @var integer $calculation
     *
     * @ORM\Column(name="calculation", type="integer", nullable=false)
     */
    private $calculation;

    /**
     * Type of the calculation
     * Known Types:
     *  - 0 == Always
     *  - 1 == Exclude dispatch fee free products
     *  - 2 == Never
     *  - 3 == Display as basket item
     * @var integer $surchargeCalculation
     *
     * @ORM\Column(name="surcharge_calculation", type="integer", nullable=false)
     */
    private $surchargeCalculation;

    /**
     * Choose which tax key should used
     * If non is given the highest tax rate will apply
     *
     * @var integer $taxCalculation
     *
     * @ORM\Column(name="tax_calculation", type="integer", nullable=false)
     */
    private $taxCalculation;

    /**
     * Defines the value after the shipping fee will be dropped.
     *
     * @var float $shippingFree
     *
     * @ORM\Column(name="shippingfree", type="decimal", nullable=true)
     */
    private $shippingFree = 0;

    /**
     * Id of the sub shop used for this dispatch
     *
     * @var integer $multiShopId
     *
     * @ORM\Column(name="multishopID", type="integer", nullable=true)
     */
    private $multiShopId;

    /**
     * The dipatch can be restricted to a given user group ID. If non ID is given
     * there will be no restriction to a user group.
     *
     * @var integer $customerGroupId
     *
     * @ORM\Column(name="customergroupID", type="integer", nullable=true)
     */
    private $customerGroupId;

    /**
     * Defines how the dispatch handels with shipping free articles.
     * Known Types:
     *  - 0 == support
     *  - 1 == do not support and lock shipping type
     *  - 2 == support but add shipping costs nevertheless.
     *
     * @var integer $bindShippingFree
     *
     * @ORM\Column(name="bind_shippingFree", type="integer", nullable=false)
     */
    private $bindShippingFree = 0;

    /**
     * If the dispatch type should only be available during a given time frame, the start time can be selected here.
     * The time is given as an Integer in seconds.
     *
     * @var integer $bindTimeFrom
     *
     * @ORM\Column(name="bind_time_from", type="integer", nullable=true)
     */
    private $bindTimeFrom;

    /**
     * If the dispatch type should only be available during a given time frame, the end time can be selected here.
     * The time is given as an Integer in seconds.
     * @var integer $bindTimeTo
     *
     * @ORM\Column(name="bind_time_to", type="integer", nullable=true)
     */
    private $bindTimeTo;

    /**
     * This dispatch is just available at this given stocks
     * Known Types:
     *  - 0 == No selection
     *  - 1 == Order quantity
     *  - 2 == Order quantity + minimum stock
     *
     * @var integer $bindInStock
     *
     * @ORM\Column(name="bind_instock", type="integer", nullable=true)
     */
    private $bindInStock;

    /**
     * Just use this dispatch if there are sales articles in the shopping cart.
     *
     * @var integer $bindLastStock
     *
     * @ORM\Column(name="bind_laststock", type="integer", nullable=false)
     */
    private $bindLastStock;

    /**
     * This dispatch is just available between specific weekdays.
     * The beginning of the weekdays is defined here
     * @var integer $bindWeekdayFrom
     *
     * @ORM\Column(name="bind_weekday_from", type="integer", nullable=true)
     */
    private $bindWeekdayFrom;

    /**
     * This dispatch is just available between specific weekdays.
     * The ending of the weekdays is defined here
     * @var integer $bindWeekdayTo
     *
     * @ORM\Column(name="bind_weekday_to", type="integer", nullable=true)
     */
    private $bindWeekdayTo;

    /**
     * This dispatch is only available if the weight of the shopping cart is between this start point and and the end point.
     * The start poinit is defined here.
     * @var float $bindWeightFrom
     *
     * @ORM\Column(name="bind_weight_from", type="decimal", nullable=true)
     */
    private $bindWeightFrom;

    /**
     * This dispatch is only available if the weight of the shopping cart is between a start point and and this end point.
     * The end point is defined here.
     * @var float $bindWeightTo
     *
     * @ORM\Column(name="bind_weight_to", type="decimal", nullable=true)
     */
    private $bindWeightTo;

    /**
     * This dipatch is only available from this price to the end price.
     * The start price is defined here.
     *
     * @var float $bindPriceFrom
     *
     * @ORM\Column(name="bind_price_from", type="decimal", nullable=true)
     */
    private $bindPriceFrom;

    /**
     * This dipatch is only available from a price to this end price.
     * The end price is defined here.
     *
     * @var float $bindPriceTo
     *
     * @ORM\Column(name="bind_price_to", type="decimal", nullable=true)
     */
    private $bindPriceTo;

    /**
     * Defines a SQL Query used to calculate the dispatch prices
     *
     * @var string $bindSql
     *
     * @ORM\Column(name="bind_sql", type="text", nullable=true)
     */
    private $bindSql;

    /**
     * Link to the delivery tracking system.
     *
     * @var string $statusLink
     *
     * @ORM\Column(name="status_link", type="text", nullable=true)
     */
    private $statusLink;

    /**
     * Defines a SQL Query used to calculate the dispatch prices
     * @var string $calculationSql
     *
     * @ORM\Column(name="calculation_sql", type="text", nullable=true)
     */
    private $calculationSql;

    /**
     * Contains all known countries for whom this dispatch is available.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\Shopware\Models\Country\Country")
     * @ORM\JoinTable(name="s_premium_dispatch_countries",
     *      joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="countryID", referencedColumnName="id")}
     *      )
     */
    private $countries;

    /**
     * A list of categories in which the dispatch is not allowed.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_premium_dispatch_categories",
     *      joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="categoryID", referencedColumnName="id")}
     * )
     */
    private $categories;

    /**
     * A list if payments means that are allowed this this dispatch
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Payment\Payment")
     * @ORM\JoinTable(name="s_premium_dispatch_paymentmeans",
     *      joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="paymentID", referencedColumnName="id")}
     * )
     */
    private $payments;

    /**
     * A list of dates (holidays) on which this dispatch is not allowed.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Dispatch\Holiday")
     * @ORM\JoinTable(name="s_premium_dispatch_holidays",
     *      joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="holidayID", referencedColumnName="id")}
     * )
     */
    private $holidays;

    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Dispatch\ShippingCost", mappedBy="dispatch", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $costsMatrix;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Dispatch", mappedBy="dispatch", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Dispatch
     */
    protected $attribute;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->countries    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payments     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->holidays     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->costsMatrix  = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Dispatch
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Dispatch
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Dispatch
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Dispatch
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Dispatch
     */
    public function setActive($active)
    {
        $this->active = (boolean) $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Dispatch
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set calculation
     *
     * @param integer $calculation
     * @return Dispatch
     */
    public function setCalculation($calculation)
    {
        $this->calculation = $calculation;
        return $this;
    }

    /**
     * Get calculation
     *
     * @return integer
     */
    public function getCalculation()
    {
        return $this->calculation;
    }

    /**
     * Set surchargeCalculation
     *
     * @param integer $surchargeCalculation
     * @return Dispatch
     */
    public function setSurchargeCalculation($surchargeCalculation)
    {
        $this->surchargeCalculation = $surchargeCalculation;
        return $this;
    }

    /**
     * Get surchargeCalculation
     *
     * @return integer
     */
    public function getSurchargeCalculation()
    {
        return $this->surchargeCalculation;
    }

    /**
     * Set taxCalculation
     *
     * @param integer $taxCalculation
     * @return Dispatch
     */
    public function setTaxCalculation($taxCalculation)
    {
        $this->taxCalculation = $taxCalculation;
        return $this;
    }

    /**
     * Get taxCalculation
     *
     * @return integer
     */
    public function getTaxCalculation()
    {
        return $this->taxCalculation;
    }

    /**
     * Set shippingFree
     *
     * @param float $shippingFree
     * @return Dispatch
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
        return $this;
    }

    /**
     * Get shippingFree
     *
     * @return float
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * Set multiShopId
     *
     * @param integer $multiShopId
     * @return Dispatch
     */
    public function setMultiShopId($multiShopId)
    {
        $this->multiShopId = $multiShopId;
        return $this;
    }

    /**
     * Get multiShopId
     *
     * @return integer
     */
    public function getMultiShopId()
    {
        return $this->multiShopId;
    }

    /**
     * Set customerGroupId
     *
     * @param integer $customerGroupId
     * @return Dispatch
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    /**
     * Get customerGroupId
     *
     * @return integer
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * Set bindShippingFree
     *
     * @param integer $bindShippingFree
     * @return Dispatch
     */
    public function setBindShippingFree($bindShippingFree)
    {
        $this->bindShippingFree = $bindShippingFree;
        return $this;
    }

    /**
     * Get bindShippingFree
     *
     * @return integer
     */
    public function getBindShippingFree()
    {
        return $this->bindShippingFree;
    }

    /**
     * Set bindTimeFrom
     *
     * @param integer $bindTimeFrom
     * @return Dispatch
     */
    public function setBindTimeFrom($bindTimeFrom)
    {
        $this->bindTimeFrom = $bindTimeFrom;
        return $this;
    }

    /**
     * Get bindTimeFrom
     *
     * @return integer
     */
    public function getBindTimeFrom()
    {
        return $this->bindTimeFrom;
    }

    /**
     * Set bindTimeTo
     *
     * @param integer $bindTimeTo
     * @return Dispatch
     */
    public function setBindTimeTo($bindTimeTo)
    {
        $this->bindTimeTo = $bindTimeTo;
        return $this;
    }

    /**
     * Get bindTimeTo
     *
     * @return integer
     */
    public function getBindTimeTo()
    {
        return $this->bindTimeTo;
    }

    /**
     * Set bindInStock
     *
     * @param integer $bindInStock
     * @return Dispatch
     */
    public function setBindInStock($bindInStock)
    {
        $this->bindInStock = $bindInStock;
        return $this;
    }

    /**
     * Get bindInStock
     *
     * @return integer
     */
    public function getBindInStock()
    {
        return $this->bindInStock;
    }

    /**
     * Set bindLastStock
     *
     * @param integer $bindLastStock
     * @return Dispatch
     */
    public function setBindLastStock($bindLastStock)
    {
        $this->bindLastStock = $bindLastStock;
        return $this;
    }

    /**
     * Get bindLastStock
     *
     * @return integer
     */
    public function getBindLastStock()
    {
        return $this->bindLastStock;
    }

    /**
     * Set bindWeekdayFrom
     *
     * @param integer $bindWeekdayFrom
     * @return Dispatch
     */
    public function setBindWeekdayFrom($bindWeekdayFrom)
    {
        $this->bindWeekdayFrom = $bindWeekdayFrom;
        return $this;
    }

    /**
     * Get bindWeekdayFrom
     *
     * @return integer
     */
    public function getBindWeekdayFrom()
    {
        return $this->bindWeekdayFrom;
    }

    /**
     * Set bindWeekdayTo
     *
     * @param integer $bindWeekdayTo
     * @return Dispatch
     */
    public function setBindWeekdayTo($bindWeekdayTo)
    {
        $this->bindWeekdayTo = $bindWeekdayTo;
        return $this;
    }

    /**
     * Get bindWeekdayTo
     *
     * @return integer
     */
    public function getBindWeekdayTo()
    {
        return $this->bindWeekdayTo;
    }

    /**
     * Set bindWeightFrom
     *
     * @param float $bindWeightFrom
     * @return Dispatch
     */
    public function setBindWeightFrom($bindWeightFrom)
    {
        $this->bindWeightFrom = $bindWeightFrom;
        return $this;
    }

    /**
     * Get bindWeightFrom
     *
     * @return float
     */
    public function getBindWeightFrom()
    {
        return $this->bindWeightFrom;
    }

    /**
     * Set bindWeightTo
     *
     * @param float $bindWeightTo
     * @return Dispatch
     */
    public function setBindWeightTo($bindWeightTo)
    {
        $this->bindWeightTo = $bindWeightTo;
        return $this;
    }

    /**
     * Get bindWeightTo
     *
     * @return float
     */
    public function getBindWeightTo()
    {
        return $this->bindWeightTo;
    }

    /**
     * Set bindPriceFrom
     *
     * @param float $bindPriceFrom
     * @return Dispatch
     */
    public function setBindPriceFrom($bindPriceFrom)
    {
        $this->bindPriceFrom = $bindPriceFrom;
        return $this;
    }

    /**
     * Get bindPriceFrom
     *
     * @return float
     */
    public function getBindPriceFrom()
    {
        return $this->bindPriceFrom;
    }

    /**
     * Set bindPriceTo
     *
     * @param float $bindPriceTo
     * @return Dispatch
     */
    public function setBindPriceTo($bindPriceTo)
    {
        $this->bindPriceTo = $bindPriceTo;
        return $this;
    }

    /**
     * Get bindPriceTo
     *
     * @return float
     */
    public function getBindPriceTo()
    {
        return $this->bindPriceTo;
    }

    /**
     * Set bindSql
     *
     * @param string $bindSql
     * @return Dispatch
     */
    public function setBindSql($bindSql)
    {
        $this->bindSql = $bindSql;
        return $this;
    }

    /**
     * Get bindSql
     *
     * @return string
     */
    public function getBindSql()
    {
        return $this->bindSql;
    }

    /**
     * Set statusLink
     *
     * @param string $statusLink
     * @return Dispatch
     */
    public function setStatusLink($statusLink)
    {
        $this->statusLink = $statusLink;
        return $this;
    }

    /**
     * Get statusLink
     *
     * @return string
     */
    public function getStatusLink()
    {
        return $this->statusLink;
    }

    /**
     * Set calculationSql
     *
     * @param string $calculationSql
     * @return Dispatch
     */
    public function setCalculationSql($calculationSql)
    {
        $this->calculationSql = $calculationSql;
        return $this;
    }

    /**
     * Get calculationSql
     *
     * @return string
     */
    public function getCalculationSql()
    {
        return $this->calculationSql;
    }

    /**
     * Returns an ArrayCollection of holiday objects
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getHolidays()
    {
        return $this->holidays;
    }

    /**
     * Takes an ArrayCollection of holiday objects
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $holidays
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function setHolidays($holidays)
    {
        $this->holidays = $holidays;
        return $this;
    }

    /**
     * Returns an ArrayCollection of payment objects
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Takes an ArrayCollection of payments
     * @param \Doctrine\Common\Collections\ArrayCollection $payments
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
        return $this;
    }

    /**
     * Returns an ArrayCollection of category objects
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Takes an ArrayCollection of category objects
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $categories
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * Returns an ArrayCollection of country objects
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Takes an ArrayCollection of country objects
     * @param \Doctrine\Common\Collections\ArrayCollection $countries
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCostsMatrix()
    {
        return $this->costsMatrix;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $costsMatrix
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setCostsMatrix($costsMatrix)
    {
        return $this->setOneToMany($costsMatrix, '\Shopware\Models\Dispatch\ShippingCost', 'costsMatrix', 'dispatch');
    }

    /**
     * @return \Shopware\Models\Attribute\Dispatch
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Dispatch|array|null $attribute
     * @return \Shopware\Models\Attribute\Dispatch
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Dispatch', 'attribute', 'dispatch');
    }
}
