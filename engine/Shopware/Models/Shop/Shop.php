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

namespace Shopware\Models\Shop;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Attribute\Shop as ShopAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Shop\Exception\ShopCurrencyNotSetException;
use Shopware\Models\Shop\Exception\ShopLocaleNotSetException;
use Shopware\Models\Site\Group;

/**
 * @ORM\Table(name="s_core_shops")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Shop extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\OneToMany(targetEntity="Shopware\Models\Mail\Log", mappedBy="shop")
     */
    protected $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="main_id", type="integer", nullable=true)
     */
    protected $mainId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    protected $categoryId;

    /**
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="Shop", inversedBy="children")
     */
    protected $main;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    protected $basePath;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_url", type="string", length=255, nullable=true)
     */
    protected $baseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="hosts", type="text", nullable=false)
     */
    protected $hosts = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="secure", type="boolean", nullable=false)
     */
    protected $secure = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId;

    /**
     * @var Template|null
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="shops")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var Template|null
     *
     * @ORM\ManyToOne(targetEntity="Template")
     * @ORM\JoinColumn(name="document_template_id", referencedColumnName="id")
     */
    protected $documentTemplate;

    /**
     * @var Category|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     */
    protected $category;

    /**
     * @var Locale|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Locale")
     */
    protected $locale;

    /**
     * @var Currency|null
     *
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    protected $currency;

    /**
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", nullable=false)
     */
    protected $customerGroup;

    /**
     * @var bool
     *
     * @ORM\Column(name="`default`", type="boolean", nullable=false)
     */
    protected $default = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="Shop")
     */
    protected $fallback;

    /**
     * @var bool
     *
     * @ORM\Column(name="customer_scope", type="boolean", nullable=false)
     */
    protected $customerScope = false;

    /**
     * @var ArrayCollection<Currency>
     *
     * @ORM\ManyToMany(targetEntity="Currency")
     * @ORM\JoinTable(name="s_core_shop_currencies")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $currencies;

    /**
     * @var ArrayCollection<Group>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Site\Group")
     * @ORM\JoinTable(name="s_core_shop_pages")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $pages;

    /**
     * @var ArrayCollection<Shop>
     *
     * @ORM\OneToMany(targetEntity="Shop", mappedBy="main", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $children;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Attribute\Shop", mappedBy="shop", orphanRemoval=true, cascade={"persist"})
     *
     * @var ShopAttribute|null
     */
    protected $attribute;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string|null
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string|null $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string|null $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @param string $hosts
     */
    public function setHosts($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return Template|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Template|null $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return Template|null
     */
    public function getDocumentTemplate()
    {
        return $this->documentTemplate;
    }

    /**
     * @param Template|null $documentTemplate
     */
    public function setDocumentTemplate($documentTemplate)
    {
        $this->documentTemplate = $documentTemplate;
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        if (!$this->locale instanceof Locale) {
            throw new ShopLocaleNotSetException();
        }

        return $this->locale;
    }

    /**
     * @param Locale|null $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        if (!$this->currency instanceof Currency) {
            throw new ShopCurrencyNotSetException();
        }

        return $this->currency;
    }

    /**
     * @param Currency|null $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param CustomerGroup $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return bool
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return Currency[]|ArrayCollection
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @param Currency[]|ArrayCollection $currencies
     */
    public function setCurrencies($currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * @return Shop|null
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @param Shop|null $main
     */
    public function setMain($main)
    {
        $this->main = $main;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return Shop|null
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param Shop $fallback
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * @return bool
     */
    public function getCustomerScope()
    {
        return $this->customerScope;
    }

    /**
     * @param bool $customerScope
     */
    public function setCustomerScope($customerScope)
    {
        $this->customerScope = $customerScope;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return Shop[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Shop[]|ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param ArrayCollection|Group[] $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return ShopAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ShopAttribute|array|null $attribute
     *
     * @return Shop
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, Shop::class, 'attribute', 'shop');
    }

    /**
     * @param string $name
     */
    public function get($name)
    {
        switch ($name) {
            case 'isocode':
                return $this->getId();
            case 'skipbackend':
                return $this->getDefault() ? 1 : 0;
            case 'parentID':
                if (!$this->getCategory() instanceof Category) {
                    throw new RuntimeException('Shop does not have a parent category set');
                }

                return $this->getCategory()->getId();
            case 'esi':
                return $this->getTemplate() !== null ? $this->getTemplate()->getEsi() : false;
            case 'navigation':
                return $this->getPages();
            case 'defaultcustomergroup':
                return $this->getCustomerGroup()->getKey();
            case 'defaultcurrency':
                return $this->getCurrency()->getId();
            case 'fallback':
                return $this->getFallback() !== null ? $this->getFallback()->getId() : null;
        }

        return null;
    }

    /**
     * @return Shop
     *
     * @deprecated Shop::registerResources is deprecated since 5.6 and will be removed with 5.8. Use service ShopRegistrationService instead
     */
    public function registerResources(): self
    {
        trigger_error('Shop::registerResources is deprecated since 5.6 and will be removed with 5.8. Use service ShopRegistrationService instead', E_USER_DEPRECATED);

        /** @var ShopRegistrationServiceInterface $service */
        $service = Shopware()->Container()->get(ShopRegistrationServiceInterface::class);

        $service->registerShop($this);

        return $this;
    }
}
