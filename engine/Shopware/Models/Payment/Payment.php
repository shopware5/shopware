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

namespace Shopware\Models\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Payment as PaymentAttribute;
use Shopware\Models\Plugin\Plugin;

/**
 * Shopware payment model represents a single payment type.
 * <br>
 * The Shopware payment model represents a row of the s_core_paymentmeans.
 * One payment has the follows associations:
 * <code>
 *
 * </code>
 * The core_paymentmeans table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `name` (`name`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_core_paymentmeans")
 * @ORM\HasLifecycleCallbacks()
 */
class Payment extends ModelEntity
{
    /**
     * @var ArrayCollection<\Shopware\Models\Country\Country>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Country\Country", inversedBy="payments")
     * @ORM\JoinTable(name="s_core_paymentmeans_countries",
     *     joinColumns={
     *         @ORM\JoinColumn(name="paymentID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="countryID", referencedColumnName="id")
     *     }
     * )
     */
    protected $countries;

    /**
     * INVERSE SIDE
     *
     * @var PaymentAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Payment", mappedBy="payment", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var Plugin
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="payments")
     * @ORM\JoinColumn(name="pluginID", referencedColumnName="id")
     */
    protected $plugin;

    /**
     * @var ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\PaymentInstance", mappedBy="paymentMean")
     */
    protected $paymentInstances;

    /**
     * @var ArrayCollection<\Shopware\Models\Customer\PaymentData>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\PaymentData", mappedBy="paymentMean")
     */
    protected $paymentData;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    private $class = '';

    /**
     * @var string
     *
     * @ORM\Column(name="`table`", type="string", length=70, nullable=false)
     */
    private $table = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="hide", type="boolean", nullable=false)
     */
    private $hide = false;

    /**
     * @var string
     *
     * @ORM\Column(name="additionaldescription", type="text", nullable=false)
     */
    private $additionalDescription;

    /**
     * @var float
     *
     * @ORM\Column(name="debit_percent", type="float", nullable=false)
     */
    private $debitPercent = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="surcharge", type="float", nullable=false)
     */
    private $surcharge = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="surchargestring", type="string", length=255, nullable=false)
     */
    private $surchargeString = '';

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="esdactive", type="boolean", nullable=false)
     */
    private $esdActive = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="mobile_inactive", type="boolean", nullable=false)
     */
    private $mobileInactive = false;

    /**
     * @var string
     *
     * @ORM\Column(name="embediframe", type="string", length=255, nullable=false)
     */
    private $embedIFrame = '';

    /**
     * @var int
     *
     * @ORM\Column(name="hideprospect", type="integer", nullable=false)
     */
    private $hideProspect = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @var int
     *
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var int
     *
     * @ORM\Column(name="source", type="integer", nullable=true)
     */
    private $source;

    /**
     * @var ArrayCollection<\Shopware\Models\Shop\Shop>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_core_paymentmeans_subshops",
     *     joinColumns={
     *         @ORM\JoinColumn(name="paymentID", referencedColumnName="id"
     *         )},
     *         inverseJoinColumns={
     *             @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     *         }
     *     )
     */
    private $shops;

    /**
     * @var ArrayCollection<\Shopware\Models\Payment\RuleSet>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\RuleSet", mappedBy="payment")
     * @ORM\JoinColumn(name="id", referencedColumnName="paymentID")
     */
    private $ruleSets;

    /**
     * Gets the id of the payment
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of a payment
     *
     * @param string $name
     *
     * @return Payment
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name of a payment
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the description of a payment
     *
     * @param string $description
     *
     * @return Payment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the description of a payment
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the template of a payment
     *
     * @param string $template
     *
     * @return Payment
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Gets the template of a payment
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the class of a payment
     *
     * @param string $class
     *
     * @return Payment
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Gets the class of a payment
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the special table of a payment
     *
     * @param string $table
     *
     * @return Payment
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Gets the table of a payment
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the hide-mode of a payment
     *
     * @param bool $hide
     *
     * @return Payment
     */
    public function setHide($hide)
    {
        $this->hide = (bool) $hide;

        return $this;
    }

    /**
     * Gets the hide-mode of a payment
     *
     * @return bool
     */
    public function getHide()
    {
        return $this->hide;
    }

    /**
     * Sets the additional-description of a payment
     *
     * @param string $additionalDescription
     *
     * @return Payment
     */
    public function setAdditionalDescription($additionalDescription)
    {
        $this->additionalDescription = $additionalDescription;

        return $this;
    }

    /**
     * Gets the additional-description of a payment
     *
     * @return string
     */
    public function getAdditionalDescription()
    {
        return $this->additionalDescription;
    }

    /**
     * Sets the debit in percent of a payment
     *
     * @param float $debitPercent
     *
     * @return Payment
     */
    public function setDebitPercent($debitPercent)
    {
        $this->debitPercent = $debitPercent;

        return $this;
    }

    /**
     * Gets the debit in percent of a payment
     *
     * @return float
     */
    public function getDebitPercent()
    {
        return $this->debitPercent;
    }

    /**
     * Sets the surcharge of a payment
     *
     * @param float $surcharge
     *
     * @return Payment
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    /**
     * Gets the surcharge of a payment
     *
     * @return float
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * Sets the country-surcharge as a string of a payment
     *
     * @param string $surchargeString
     *
     * @return Payment
     */
    public function setSurchargeString($surchargeString)
    {
        $this->surchargeString = $surchargeString;

        return $this;
    }

    /**
     * Gets the country-surcharge-string of a payment
     *
     * @return string
     */
    public function getSurchargeString()
    {
        return $this->surchargeString;
    }

    /**
     * Sets the position of a payment
     *
     * @param int $position
     *
     * @return Payment
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * Gets the position of a payment
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the active-state of a payment
     *
     * @param bool $active
     *
     * @return Payment
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * Gets the active-state of a payment
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Sets the esd-active-state of a payment
     *
     * @param bool $esdActive
     *
     * @return Payment
     */
    public function setEsdActive($esdActive)
    {
        $this->esdActive = (bool) $esdActive;

        return $this;
    }

    /**
     * Gets the esd-active-state of a payment
     *
     * @return bool
     */
    public function getEsdActive()
    {
        return $this->esdActive;
    }

    /**
     * Sets the mobile inactive state of a payment
     *
     * @param bool $mobileInactive
     *
     * @return Payment
     */
    public function setMobileInactive($mobileInactive)
    {
        $this->mobileInactive = (bool) $mobileInactive;

        return $this;
    }

    /**
     * Gets the mobile inactive state of a payment
     *
     * @return bool
     */
    public function getMobileInactive()
    {
        return $this->mobileInactive;
    }

    /**
     * Sets the embed-IFrame of a payment
     *
     * @param string $embedIFrame
     *
     * @return Payment
     */
    public function setEmbedIFrame($embedIFrame)
    {
        $this->embedIFrame = $embedIFrame;

        return $this;
    }

    /**
     * Gets the embed-IFrame of a payment
     *
     * @return string
     */
    public function getEmbedIFrame()
    {
        return $this->embedIFrame;
    }

    /**
     * Sets hide-prospect-state of a payment
     *
     * @param int $hideProspect
     *
     * @return Payment
     */
    public function setHideProspect($hideProspect)
    {
        $this->hideProspect = $hideProspect;

        return $this;
    }

    /**
     * Gets the hide-prospect of a payment
     *
     * @return int
     */
    public function getHideProspect()
    {
        return $this->hideProspect;
    }

    /**
     * Sets the action of a payment
     *
     * @param string $action
     *
     * @return Payment
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Gets the action of a payment
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets the pluginId of a payment
     *
     * @param int|null $pluginId
     *
     * @return Payment
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * Gets the pluginId of a payment
     *
     * @return int|null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * Gets the countries related to the payment
     *
     * @return ArrayCollection<\Shopware\Models\Country\Country>
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Sets the countries related to the payment
     *
     * @param ArrayCollection<\Shopware\Models\Country\Country> $countries
     *
     * @return Payment
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Gets the shops related to the payment
     *
     * @return ArrayCollection<\Shopware\Models\Shop\Shop>
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * Sets the shops related to the payment
     *
     * @param ArrayCollection<\Shopware\Models\Shop\Shop> $shops
     *
     * @return Payment
     */
    public function setShops($shops)
    {
        $this->shops = $shops;

        return $this;
    }

    /**
     * Sets the source of a payment.
     * NULL = default payment, 1 = self-created
     *
     * @param int|null $source
     *
     * @return Payment
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Gets the source of a payment
     * NULL = default payment, 1 = self-created
     *
     * @return int|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Payment\RuleSet>
     */
    public function getRuleSets()
    {
        return $this->ruleSets;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Payment\RuleSet> $ruleSets
     *
     * @return Payment
     */
    public function setRuleSets($ruleSets)
    {
        $this->ruleSets = $ruleSets;

        return $this;
    }

    /**
     * @return PaymentAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param PaymentAttribute|array|null $attribute
     *
     * @return Payment
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, PaymentAttribute::class, 'attribute', 'payment');
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param Plugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Payment\PaymentInstance> $paymentInstances
     */
    public function setPaymentInstances($paymentInstances)
    {
        $this->paymentInstances = $paymentInstances;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
     */
    public function getPaymentInstances()
    {
        return $this->paymentInstances;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Customer\PaymentData> $paymentData
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Customer\PaymentData>
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }
}
