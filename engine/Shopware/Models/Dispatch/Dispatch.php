<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Dispatch;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Dispatch as DispatchAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Country\Country;
use Shopware\Models\Payment\Payment;

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
    public const TYPE_STANDARD = 0;
    public const TYPE_ALTERNATIVE = 1;
    public const TYPE_SURCHARGE = 2;
    public const TYPE_DISCOUNT = 3;
    public const TYPE_DISPATCH_SURCHARGE_ON_POSITION = 4;

    public const CALCULATION_WEIGHT = 0;
    public const CALCULATION_PRICE = 1;
    public const CALCULATION_NUMBER_OF_PRODUCTS = 2;
    public const CALCULATION_CUSTOM = 3;
    public const CALCULATIONS = [
        self::CALCULATION_WEIGHT,
        self::CALCULATION_PRICE,
        self::CALCULATION_NUMBER_OF_PRODUCTS,
        self::CALCULATION_CUSTOM,
    ];

    public const SURCHARGE_CALCULATION_ALWAYS = 0;
    public const SURCHARGE_CALCULATION_EXCLUDE_SHIPPING_FREE_PRODUCTS = 1;
    public const SURCHARGE_CALCULATION_NEVER = 2;
    public const SURCHARGE_CALCULATION_AS_CART_ITEM = 3;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<ShippingCost>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Dispatch\ShippingCost", mappedBy="dispatch", orphanRemoval=true, cascade={"persist"})
     */
    protected $costsMatrix;

    /**
     * INVERSE SIDE
     *
     * @var DispatchAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Dispatch", mappedBy="dispatch", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of the Dispatch
     *
     * @var string
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
     *  - Discount Shipping == 3
     *
     * @var self::TYPE_*
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * Description of this dispatch
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Comments to the Tracking Link
     *
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=false)
     */
    private $comment;

    /**
     * Active flag
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * Defines the position on which this dispatch will be displayed.
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * Way to calculate the shipping costs
     * Known types:
     *  - 0 == Weight
     *  - 1 == Price
     *  - 2 == Number of products
     *  - 3 == Custom calculation
     *
     * @var self::CALCULATION_*
     *
     * @ORM\Column(name="calculation", type="integer", nullable=false)
     */
    private $calculation;

    /**
     * Type of the calculation
     * Known Types:
     *  - 0 == Always
     *  - 1 == Exclude shipping free products
     *  - 2 == Never
     *  - 3 == Display as basket item
     *
     * @var self::SURCHARGE_CALCULATION_*
     *
     * @ORM\Column(name="surcharge_calculation", type="integer", nullable=false)
     */
    private $surchargeCalculation;

    /**
     * Choose which tax key should used
     * If non is given the highest tax rate will apply
     *
     * @var int
     *
     * @ORM\Column(name="tax_calculation", type="integer", nullable=false)
     */
    private $taxCalculation;

    /**
     * Defines the value after the shipping fee will be dropped.
     *
     * @var string|null
     *
     * @ORM\Column(name="shippingfree", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $shippingFree = '0.0';

    /**
     * Id of the sub shop used for this dispatch
     *
     * @var int|null
     *
     * @ORM\Column(name="multishopID", type="integer", nullable=true)
     */
    private $multiShopId;

    /**
     * The dispatch can be restricted to a given user group ID. If non ID is given
     * there will be no restriction to a user group.
     *
     * @var int|null
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
     * @var int
     *
     * @ORM\Column(name="bind_shippingFree", type="integer", nullable=false)
     */
    private $bindShippingFree = 0;

    /**
     * If the dispatch type should only be available during a given time frame, the start time can be selected here.
     * The time is given as an Integer in seconds.
     *
     * @var int|null
     *
     * @ORM\Column(name="bind_time_from", type="integer", nullable=true)
     */
    private $bindTimeFrom;

    /**
     * If the dispatch type should only be available during a given time frame, the end time can be selected here.
     * The time is given as an Integer in seconds.
     *
     * @var int|null
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
     * @var int|null
     *
     * @ORM\Column(name="bind_instock", type="integer", nullable=true)
     */
    private $bindInStock;

    /**
     * Just use this dispatch if there are sales articles in the shopping cart.
     *
     * @var int
     *
     * @ORM\Column(name="bind_laststock", type="integer", nullable=false)
     */
    private $bindLastStock;

    /**
     * This dispatch is just available between specific weekdays.
     * The beginning of the weekdays is defined here
     *
     * @var int|null
     *
     * @ORM\Column(name="bind_weekday_from", type="integer", nullable=true)
     */
    private $bindWeekdayFrom;

    /**
     * This dispatch is just available between specific weekdays.
     * The ending of the weekdays is defined here
     *
     * @var int|null
     *
     * @ORM\Column(name="bind_weekday_to", type="integer", nullable=true)
     */
    private $bindWeekdayTo;

    /**
     * This dispatch is only available if the weight of the shopping cart is between this start point and and the end point.
     * The start point is defined here.
     *
     * @var string|null
     *
     * @ORM\Column(name="bind_weight_from", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $bindWeightFrom;

    /**
     * This dispatch is only available if the weight of the shopping cart is between a start point and and this end point.
     * The end point is defined here.
     *
     * @var string|null
     *
     * @ORM\Column(name="bind_weight_to", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $bindWeightTo;

    /**
     * This dispatch is only available from this price to the end price.
     * The start price is defined here.
     *
     * @var string|null
     *
     * @ORM\Column(name="bind_price_from", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $bindPriceFrom;

    /**
     * This dispatch is only available from a price to this end price.
     * The end price is defined here.
     *
     * @var string|null
     *
     * @ORM\Column(name="bind_price_to", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $bindPriceTo;

    /**
     * Defines a SQL Query used to calculate the dispatch prices
     *
     * @var string|null
     *
     * @ORM\Column(name="bind_sql", type="text", nullable=true)
     */
    private $bindSql;

    /**
     * Link to the delivery tracking system.
     *
     * @var string|null
     *
     * @ORM\Column(name="status_link", type="text", nullable=true)
     */
    private $statusLink;

    /**
     * Defines a SQL Query used to calculate the dispatch prices
     *
     * @var string|null
     *
     * @ORM\Column(name="calculation_sql", type="text", nullable=true)
     */
    private $calculationSql;

    /**
     * Contains all known countries for whom this dispatch is available.
     *
     * @var ArrayCollection<Country>
     *
     * @ORM\ManyToMany(targetEntity="\Shopware\Models\Country\Country")
     * @ORM\JoinTable(name="s_premium_dispatch_countries",
     *     joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="countryID", referencedColumnName="id", nullable=false)}
     * )
     */
    private $countries;

    /**
     * A list of categories in which the dispatch is not allowed.
     *
     * @var ArrayCollection<Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_premium_dispatch_categories",
     *     joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="categoryID", referencedColumnName="id", nullable=false)}
     * )
     */
    private $categories;

    /**
     * A list if payments means that are allowed this this dispatch
     *
     * @var ArrayCollection<Payment>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Payment\Payment")
     * @ORM\JoinTable(name="s_premium_dispatch_paymentmeans",
     *     joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="paymentID", referencedColumnName="id", nullable=false)}
     * )
     */
    private $payments;

    /**
     * A list of dates (holidays) on which this dispatch is not allowed.
     *
     * @var ArrayCollection<Holiday>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Dispatch\Holiday")
     * @ORM\JoinTable(name="s_premium_dispatch_holidays",
     *     joinColumns={@ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="holidayID", referencedColumnName="id", nullable=false)}
     * )
     */
    private $holidays;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->holidays = new ArrayCollection();
        $this->costsMatrix = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Dispatch
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @param self::TYPE_* $type
     *
     * @return Dispatch
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return self::TYPE_*
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $description
     *
     * @return Dispatch
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
     * @param string $comment
     *
     * @return Dispatch
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param bool $active
     *
     * @return Dispatch
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $position
     *
     * @return Dispatch
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param self::CALCULATION_* $calculation
     *
     * @return Dispatch
     */
    public function setCalculation($calculation)
    {
        $this->calculation = $calculation;

        return $this;
    }

    /**
     * @return self::CALCULATION_*
     */
    public function getCalculation()
    {
        return $this->calculation;
    }

    /**
     * @param self::SURCHARGE_CALCULATION_* $surchargeCalculation
     *
     * @return Dispatch
     */
    public function setSurchargeCalculation($surchargeCalculation)
    {
        $this->surchargeCalculation = $surchargeCalculation;

        return $this;
    }

    /**
     * @return self::SURCHARGE_CALCULATION_*
     */
    public function getSurchargeCalculation()
    {
        return $this->surchargeCalculation;
    }

    /**
     * @param int $taxCalculation
     *
     * @return Dispatch
     */
    public function setTaxCalculation($taxCalculation)
    {
        $this->taxCalculation = $taxCalculation;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaxCalculation()
    {
        return $this->taxCalculation;
    }

    /**
     * @param string|null $shippingFree
     *
     * @return Dispatch
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param int $multiShopId
     *
     * @return Dispatch
     */
    public function setMultiShopId($multiShopId)
    {
        $this->multiShopId = $multiShopId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMultiShopId()
    {
        return $this->multiShopId;
    }

    /**
     * @param int $customerGroupId
     *
     * @return Dispatch
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @param int $bindShippingFree
     *
     * @return Dispatch
     */
    public function setBindShippingFree($bindShippingFree)
    {
        $this->bindShippingFree = $bindShippingFree;

        return $this;
    }

    /**
     * @return int
     */
    public function getBindShippingFree()
    {
        return $this->bindShippingFree;
    }

    /**
     * @param int $bindTimeFrom
     *
     * @return Dispatch
     */
    public function setBindTimeFrom($bindTimeFrom)
    {
        $this->bindTimeFrom = $bindTimeFrom;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBindTimeFrom()
    {
        return $this->bindTimeFrom;
    }

    /**
     * @param int $bindTimeTo
     *
     * @return Dispatch
     */
    public function setBindTimeTo($bindTimeTo)
    {
        $this->bindTimeTo = $bindTimeTo;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBindTimeTo()
    {
        return $this->bindTimeTo;
    }

    /**
     * @param int $bindInStock
     *
     * @return Dispatch
     */
    public function setBindInStock($bindInStock)
    {
        $this->bindInStock = $bindInStock;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBindInStock()
    {
        return $this->bindInStock;
    }

    /**
     * @param int $bindLastStock
     *
     * @return Dispatch
     */
    public function setBindLastStock($bindLastStock)
    {
        $this->bindLastStock = $bindLastStock;

        return $this;
    }

    /**
     * @return int
     */
    public function getBindLastStock()
    {
        return $this->bindLastStock;
    }

    /**
     * @param int $bindWeekdayFrom
     *
     * @return Dispatch
     */
    public function setBindWeekdayFrom($bindWeekdayFrom)
    {
        $this->bindWeekdayFrom = $bindWeekdayFrom;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBindWeekdayFrom()
    {
        return $this->bindWeekdayFrom;
    }

    /**
     * @param int $bindWeekdayTo
     *
     * @return Dispatch
     */
    public function setBindWeekdayTo($bindWeekdayTo)
    {
        $this->bindWeekdayTo = $bindWeekdayTo;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBindWeekdayTo()
    {
        return $this->bindWeekdayTo;
    }

    /**
     * @param string|null $bindWeightFrom
     *
     * @return Dispatch
     */
    public function setBindWeightFrom($bindWeightFrom)
    {
        $this->bindWeightFrom = $bindWeightFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBindWeightFrom()
    {
        return $this->bindWeightFrom;
    }

    /**
     * @param string|null $bindWeightTo
     *
     * @return Dispatch
     */
    public function setBindWeightTo($bindWeightTo)
    {
        $this->bindWeightTo = $bindWeightTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBindWeightTo()
    {
        return $this->bindWeightTo;
    }

    /**
     * @param string|null $bindPriceFrom
     *
     * @return Dispatch
     */
    public function setBindPriceFrom($bindPriceFrom)
    {
        $this->bindPriceFrom = $bindPriceFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBindPriceFrom()
    {
        return $this->bindPriceFrom;
    }

    /**
     * @param string|null $bindPriceTo
     *
     * @return Dispatch
     */
    public function setBindPriceTo($bindPriceTo)
    {
        $this->bindPriceTo = $bindPriceTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBindPriceTo()
    {
        return $this->bindPriceTo;
    }

    /**
     * @param string $bindSql
     *
     * @return Dispatch
     */
    public function setBindSql($bindSql)
    {
        $this->bindSql = $bindSql;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBindSql()
    {
        return $this->bindSql;
    }

    /**
     * @param string $statusLink
     *
     * @return Dispatch
     */
    public function setStatusLink($statusLink)
    {
        $this->statusLink = $statusLink;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusLink()
    {
        return $this->statusLink;
    }

    /**
     * @param string $calculationSql
     *
     * @return Dispatch
     */
    public function setCalculationSql($calculationSql)
    {
        $this->calculationSql = $calculationSql;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCalculationSql()
    {
        return $this->calculationSql;
    }

    /**
     * Returns an ArrayCollection of holiday objects
     *
     * @return ArrayCollection<Holiday>
     */
    public function getHolidays()
    {
        return $this->holidays;
    }

    /**
     * Takes an ArrayCollection of holiday objects
     *
     * @param ArrayCollection<Holiday> $holidays
     *
     * @return Dispatch
     */
    public function setHolidays($holidays)
    {
        $this->holidays = $holidays;

        return $this;
    }

    /**
     * Returns an ArrayCollection of payment objects
     *
     * @return ArrayCollection<Payment>
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Takes an ArrayCollection of payments
     *
     * @param ArrayCollection<Payment> $payments
     *
     * @return Dispatch
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * Returns an ArrayCollection of category objects
     *
     * @return ArrayCollection<Category>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Takes an ArrayCollection of category objects
     *
     * @param ArrayCollection<Category> $categories
     *
     * @return Dispatch
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Returns an ArrayCollection of country objects
     *
     * @return ArrayCollection<Country>
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Takes an ArrayCollection of country objects
     *
     * @param ArrayCollection<Country> $countries
     *
     * @return Dispatch
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * @return ArrayCollection<ShippingCost>
     */
    public function getCostsMatrix()
    {
        return $this->costsMatrix;
    }

    /**
     * @param ShippingCost[]|null $costsMatrix
     *
     * @return Dispatch
     */
    public function setCostsMatrix($costsMatrix)
    {
        return $this->setOneToMany($costsMatrix, ShippingCost::class, 'costsMatrix', 'dispatch');
    }

    /**
     * @return DispatchAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param DispatchAttribute|array|null $attribute
     *
     * @return Dispatch
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, DispatchAttribute::class, 'attribute', 'dispatch');
    }
}
