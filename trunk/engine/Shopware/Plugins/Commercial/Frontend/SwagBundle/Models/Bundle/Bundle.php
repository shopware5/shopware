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

namespace Shopware\CustomModels\Bundle;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Symfony\Component\Validator\ExecutionContext,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Shopware Bundle Model
 * Contains the definition of a single shopware article bundle resource.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Models\Bundle
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_bundles")
 *
 * @Assert\Callback(methods={"validateBundle", "validateBundleArticles", "validateBundleCustomerGroups", "validateBundlePrices"})
 */
class Bundle extends ModelEntity
{
    /**
     * Unique identifier for a single bundle
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Custom name for the bundle which displayed in the backend module as bundle definition
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @var string $name
     */
    private $name;

    /**
     * The type property defines which typ of bundle are defined.
     * <code>
     * Valid types:
     *   1 => standard type (discount)
     *   2 => cross selling (checkboxes)
     * <code>
     *
     * @ORM\Column(name="bundle_type", type="integer", nullable=false)
     * @var int $type
     */
    private $type;

    /**
     * The id of the assigned article on which the bundle created.
     * Used as foreign key for the article association.
     * Has no getter and setter. Only defined to have access on the article id in queries without joining the s_articles.
     *
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     * @var int $articleId
     */
    private $articleId;

    /**
     * Active flag for the bundle. The bundle only displayed in the shop front if the active flag is set to true.
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     * @var boolean $active
     */
    private $active;

    /**
     * The discount type used for the price calculation of the bundle.
     *
     * @ORM\Column(name="rab_type", type="string", length=255, nullable=false)
     * @var string $discountType
     */
    private $discountType;

    /**
     * The id of the assigned tax which used for the discount.
     * Can be defined over the backend module.
     * Used as foreign key for the tax association.
     * Has no getter and setter. Only defined to have access on the tax id in queries without joining the s_core_taxes.
     *
     * @ORM\Column(name="taxID", type="integer", nullable=true)
     * @var int $taxId
     */
    private $taxId = null;

    /**
     * The order number of the dynamic discount. Used for the frontend basket.
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=false)
     * @var string $number
     */
    protected $number;

    /**
     * Flag if the quantity of the bundle is limited.
     *
     * @ORM\Column(name="max_quantity_enable", type="boolean", nullable=false)
     * @var boolean $limited
     */
    private $limited;

    /**
     * If the $limited flag is set to true, the $quantity property contains the value for the limitation.
     *
     * @ORM\Column(name="max_quantity", type="integer", nullable=false)
     * @var int $quantity
     */
    private $quantity;

    /**
     * The valid from and valid to property allows a time control for the bundle.
     * If the valid from property is set, the bundle will be displayed in the shop front, after crossing the valid from date.
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     * @var \DateTime $validFrom
     */
    private $validFrom = null;

    /**
     * The valid from and valid to property allows a time control for the bundle.
     * If the valid to property is set, the bundle will be hidden in the shop front, after crossing the valid to date.
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     * @var \DateTime $validTo
     */
    private $validTo = null;

    /**
     * Contains the creation date of the bundle
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     * @var \DateTime $created
     */
    private $created = null;

    /**
     * Counter how many times the bundle sold.
     *
     * @ORM\Column(name="sells", type="integer", nullable=false)
     * @var int $sells
     */
    private $sells;

    /**
     * The $article property is an association property. This
     * property contains the Shopware\Models\Article\Article model
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     * @var \Shopware\Models\Article\Article
     */
    protected $article;

    /**
     * The $customerGroups property contains an offset of \Shopware\Models\Customer\Group.
     * All defined customer groups can buy the defined bundle.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_bundles_customergroups",
     *      joinColumns={
     *          @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
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
     * The $articles property contains all assigned articles of the defined bundle.
     * The array collection contains an offset of \Shopware\CustomModels\Bundle\Article objects.
     * This bundle article models contains a reference to the assigned \Shopware\Models\Article\Detail
     * instance in the $articleDetail property.
     *
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\Bundle\Article", mappedBy="bundle", orphanRemoval=true, cascade={"persist", "update"})
     * @var ArrayCollection
     */
    protected $articles;

    /**
     * INVERSE SIDE
     * The $limitedDetails property contains an offset of \Shopware\Models\Article\Detail.
     * If the bundle created on a configurator article, the bundle only displayed in the store
     * front if the user select one of the variants of this collection.
     * Otherwise the bundle will be hidden.
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinTable(name="s_articles_bundles_stint",
     *      joinColumns={
     *          @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     *      }
     * )
     * @var ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    protected $limitedDetails;

    /**
     * INVERSE SIDE
     * The $prices property contains the defined prices of the bundle.
     * The bundle has one price per customer group of the shop.
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\Bundle\Price", mappedBy="bundle", orphanRemoval=true, cascade={"persist", "update"})
     * @var ArrayCollection|\Shopware\CustomModels\Bundle\Price[]
     */
    protected $prices;

    /**
     * OWNING SIDE
     * Contains the selected tax which can be defined over the backend module.
     * Used for the bundle prices if the discount type is set to "absolute".
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id")
     * @var \Shopware\Models\Tax\Tax
     */
    protected $tax;

    /**
     *
     * Class property which contains the discount data for the bundle
     * @var array
     */
    private $discount;

    /**
     * Class property which contains the price for the current customer group
     * @var Price
     */
    private $currentPrice;

    /**
     * Class property which contains the net and gross total prices for the bundle.
     * @var array
     */
    private $totalPrice;

    /**
     * Class property which contains a flag if all configurator article in the bundle are configured.
     * @var boolean $allConfigured
     */
    private $allConfigured = true;

    /**
     * Class property which contains the configuration for the articles.
     * @var array
     */
    private $articleData = array();

    /**
     * @var ArrayCollection
     */
    private $updatedPrices;

    /**
     * Class constructor. Initials all objects of this class, like ArrayCollections and DateTimes
     */
    public function __construct()
    {
        $this->limitedDetails = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->updatedPrices = new ArrayCollection();
    }

    /**
     * Unique identifier.
     *
     * Returns the unique identifier of this model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Active flag.
     * Used to activate and deactive a single bundle, to hide
     * the bundle in the frontend without deleting the bundle.
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Active flag.
     * Used to activate and deactive a single bundle, to hide
     * the bundle in the frontend without deleting the bundle.
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Date Range validation field.
     * Used to chrink the bundle display in the store front
     * to a specified time.
     *
     * @param \DateTime|string $validFrom
     *
     * @return void
     */
    public function setValidFrom($validFrom = 'now')
    {
        if (!($validFrom instanceof \DateTime) && strlen($validFrom) > 0) {
            $this->validFrom = new \DateTime($validFrom);
        } else {
            $this->validFrom = $validFrom;
        }
    }

    /**
     * Date Range validation field.
     * Used to chrink the bundle display in the store front
     * to a specified time.
     *
     * @return \DateTime|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Date Range validation field.
     * Used to chrink the bundle display in the store front
     * to a specified time.
     *
     * @param \DateTime|string $validTo
     *
     * @return void
     */
    public function setValidTo($validTo = 'now')
    {
        if (!($validTo instanceof \DateTime) && strlen($validTo) > 0) {
            $this->validTo = new \DateTime($validTo);
        } else {
            $this->validTo = $validTo;
        }
    }

    /**
     * Date Range validation field.
     * Used to chrink the bundle display in the store front
     * to a specified time.
     *
     * @return \DateTime|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Creation date.
     * The create date of the bundle.
     *
     * @param \DateTime|string $created
     *
     * @return void
     */
    public function setCreated($created = 'now')
    {
        if (!($created instanceof \DateTime) && strlen($created) > 0) {
            $this->created = new \DateTime($created);
        } else {
            $this->created = $created;
        }
    }

    /**
     * Creation date.
     * The create date of the bundle.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Discount type flag.
     * The discount type property is used to identify which
     * kind of the bundle discount are defined. This property
     * can contains two values:
     * <pre>
     *  'pro' => percentage definition
     *  'abs' => absolute definition
     * </pre>
     * @param string $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * Discount type flag.
     * The discount type property is used to identify which
     * kind of the bundle discount are defined. This property
     * can contains two values:
     * <pre>
     *  'pro' => percentage definition
     *  'abs' => absolute definition
     * </pre>
     *
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * Stock validation flag.
     * The limited flag allows the shop administrator to define
     * that only a limited quantity of this bundle is available.
     *
     * @param boolean $limited
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;
    }

    /**
     * Stock validation flag.
     * The limited flag allows the shop administrator to define
     * that only a limited quantity of this bundle is available.
     * @return boolean
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * Name of the bundle.
     * Used to identify the bundle over a custom name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Name of the bundle.
     * Used to identify the bundle over a custom name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Order number of the bundle.
     * The order number is used to identify the bundle over a custom order
     * number without nowing all bundle ids.
     *
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Order number of the bundle.
     * The order number is used to identify the bundle over a custom order
     * number without nowing all bundle ids.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Stock validation.
     * The quantity property contains the number of the bundle stock.
     * If the limited flag is set to true and the quantity falls down to zero,
     * the bundle is no more available.
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Stock validation.
     * The quantity property contains the number of the bundle stock.
     * If the limited flag is set to true and the quantity falls down to zero,
     * the bundle is no more available.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sells of the bundle.
     * Contains the count of the bundle sells.
     * @param int $sells
     */
    public function setSells($sells)
    {
        $this->sells = $sells;
    }

    /**
     * Sells of the bundle.
     * Contains the count of the bundle sells.
     *
     * @return int
     */
    public function getSells()
    {
        return $this->sells;
    }

    /**
     * Type flag of the bundle.
     * The type property contains the definition of the bundle type.
     * This property can contains two different values:
     * <pre>
     *      1 => Normal bundle
     *      2 => Selectedable bundle
     * </pre>
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Type flag of the bundle.
     * The type property contains the definition of the bundle type.
     * This property can contains two different values:
     * <pre>
     *      1 => Normal bundle
     *      2 => Selectedable bundle
     * </pre>
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Article positons of the bundle.
     * The articles property contains an ArrayCollection with the defined bundle
     * articles. This property don't contains as default the main article on which
     * the bundle are defined.
     * To get the main article as additional position, u can use the
     * Shopware_Components_Bundle::getBundleMainArticle function.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Article positons of the bundle.
     * The articles property contains an ArrayCollection with the defined bundle
     * articles. This property don't contains as default the main article on which
     * the bundle are defined.
     * To get the main article as additional position, u can use the
     * Shopware_Components_Bundle::getBundleMainArticle function.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $articles
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setArticles($articles)
    {
        return $this->setOneToMany($articles, 'Shopware\CustomModels\Bundle\Article', 'articles', 'bundle');
    }

    /**
     * Getter function for the bundle prices.
     * The prices property contains all defined bundle prices.
     * The bundle prices are defined for each customer group. If no price
     * defined for a single customer group, all customers of this group can't see the bundle
     * in the store front.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\CustomModels\Bundle\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Getter function for the bundle prices.
     * The prices property contains all defined bundle prices.
     * The bundle prices are defined for each customer group. If no price
     * defined for a single customer group, all customers of this group can't see the bundle
     * in the store front.
     *
     * This function contains an auto loading which allows to pass the property data
     * as array which will be converted into \Shopware\CustomModels\Bundle\Price.
     *
     * @param $prices
     *
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, 'Shopware\CustomModels\Bundle\Price', 'prices', 'bundle');
    }

    /**
     * Main article of the bundle.
     * The article property contains the article on which the bundle created.
     * This article can be converted to an normal bundle article position by using the
     * Shopware_Components_Bundle::getBundleMainArticle function.
     *
     * @return \Shopware\Models\Article\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Main article of the bundle.
     * The article property contains the article on which the bundle created.
     * This article can be converted to an normal bundle article position by using the
     * Shopware_Components_Bundle::getBundleMainArticle function.
     *
     * @param \Shopware\Models\Article\Article $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * Allowed customer groups.
     * The customer groups property contains an ArrayCollection with all customer groups,
     * which are allowed to see/buy the bundle in the store front.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * Allowed customer groups.
     * The customer groups property contains an ArrayCollection with all customer groups,
     * which are allowed to see/buy the bundle in the store front.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $customerGroups
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * Limited variants of the bundle.
     * The bundle can be limited on specify variants of the article.
     * For example:<br>
     * - You want to bundle only the yellow t-shirt (SW-2000.1) because the customers
     * won't buy this. Now you can add the variant SW-2000.1 to this collection to display
     * the bundle only if the yellow t-shirt is selected.
     *
     * @param $limitedDetails ArrayCollection
     */
    public function setLimitedDetails($limitedDetails)
    {
        $this->limitedDetails = $limitedDetails;
    }

    /**
     * Limited variants of the bundle.
     * The bundle can be limited on specify variants of the article.
     * For example:<br>
     * - You want to bundle only the yellow t-shirt (SW-2000.1) because the customers
     * won't buy this. Now you can add the variant SW-2000.1 to this collection to display
     * the bundle only if the yellow t-shirt is selected.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Article\Detail[]
     */
    public function getLimitedDetails()
    {
        return $this->limitedDetails;
    }

    /**
     * Contains the current price.
     * This property is used by the Shopware_Components_Bundle component to
     * set the current price for the current frontend customer group.
     * This property isn't loaded from the database!
     * @param Price $currentPrice
     */
    public function setCurrentPrice($currentPrice)
    {
        $this->currentPrice = $currentPrice;
    }

    /**
     * Contains the current price.
     * This property is used by the Shopware_Components_Bundle component to
     * set the current price for the current frontend customer group.
     * This property isn't loaded from the database!
     *
     * @return Price
     */
    public function getCurrentPrice()
    {
        return $this->currentPrice;
    }

    /**
     * Discount data of the bundle.
     * This property contains all data for the bundle discount.
     * The property is set by the Shopware_Components_Bundle component.
     * This property isn't loaded from the database!
     *
     * @param array $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * Discount data of the bundle.
     * This property contains all data for the bundle discount.
     * The property is set by the Shopware_Components_Bundle component.
     * This property isn't loaded from the database!
     *
     * @return array
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Total price of the bundle article positions.
     * The totalPrice property contains the summarized article prices
     * of the bundle.
     * This property isn't loaded from the database, the Shopware_Components_Bundle
     * used it in the getCaclulatedBundle function.
     * @param array $totalPrice
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * Total price of the bundle article positions.
     * The totalPrice property contains the summarized article prices
     * of the bundle.
     * This property isn't loaded from the database, the Shopware_Components_Bundle
     * used it in the getCaclulatedBundle function.
     *
     * @return array
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Returns the price for the passed customer group.
     *
     * Model helper function which returns the \Shopware\CustomModels\Bundle\Price object for the passed customer group
     * key.
     *
     * @param $customerGroupKey
     *
     * @return Price
     */
    public function getPriceForCustomerGroup($customerGroupKey)
    {
        $customerGroupPrice = null;
        foreach($this->getUpdatedPrices() as $price) {
            if ($price->getCustomerGroup()->getKey() === $customerGroupKey) {
                $customerGroupPrice = $price;
            }
        }
        return $customerGroupPrice;
    }

    /**
     * Returns the allConfigured flag.
     * This flag is set to true, if all bundle article position, which has the
     * configurable flag, are configured through the customer.
     *
     * @return boolean
     */
    public function getAllConfigured()
    {
        return $this->allConfigured;
    }

    /**
     * Returns the allConfigured flag.
     * This flag is set to true, if all bundle article position, which has the
     * configurable flag, are configured through the customer.
     *
     * @param boolean $allConfigured
     */
    public function setAllConfigured($allConfigured)
    {
        $this->allConfigured = $allConfigured;
    }

    /**
     * Returns the article data as array.
     * This property contains the calculated article data of the bundle.
     * This property isn't loaded from the database, the Shopware_Components_Bundle::getCalulcatedBundle
     * function will set it.
     *
     * @return array
     */
    public function getArticleData()
    {
        return $this->articleData;
    }

    /**
     * Returns the article data as array.
     * This property contains the calculated article data of the bundle.
     * This property isn't loaded from the database, the Shopware_Components_Bundle::getCalulcatedBundle
     * function will set it.
     *
     * @param array $articleData
     */
    public function setArticleData($articleData)
    {
        $this->articleData = $articleData;
    }

    /**
     * Validation callback function.
     * This function is responsible to validate the bundle base data in the backend.
     */
    public function validateBundle(ExecutionContext $context)
    {
//        $context->addViolation('Message', array(), null);
    }

    /**
     * Validation callback function.
     * This function validates the defined bundle article positions.
     *
     */
    public function validateBundleArticles(ExecutionContext $context)
    {
//        $context->addViolation('Message', array(), null);
    }

    /**
     * Validation callback function.
     * This function validates the assigned bundle customer groups.
     */
    public function validateBundleCustomerGroups(ExecutionContext $context)
    {
//        $context->addViolation('Message', array(), null);
    }

    /**
     * Validation callback function.
     * This function validates the defined bundle prices.
     */
    public function validateBundlePrices(ExecutionContext $context)
    {
//        $context->addViolation('Message', array(), null);
    }

    /**
     * Contains updated prices for the shopware store front.
     *
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
     * Contains updated prices for the shopware store front.
     *
     * @param  $updatedPrices
     */
    public function setUpdatedPrices($updatedPrices)
    {
        $this->updatedPrices = $updatedPrices;
    }
}
