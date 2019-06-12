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
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\ShopRegistrationServiceInterface;

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
     * @var int
     *
     * @ORM\Column(name="main_id", type="integer", nullable=true)
     */
    protected $mainId;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    protected $categoryId;

    /**
     * @var Shop
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
     * @var string
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
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string
     *
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    protected $basePath;

    /**
     * @var string
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
     * @var int
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="shops")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="Template")
     * @ORM\JoinColumn(name="document_template_id", referencedColumnName="id")
     */
    protected $documentTemplate;

    /**
     * @var \Shopware\Models\Category\Category
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     */
    protected $category;

    /**
     * @var Locale
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Locale")
     */
    protected $locale;

    /**
     * @var Currency
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    protected $currency;

    /**
     * @var \Shopware\Models\Customer\Group
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
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
     * @var Shop
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
     * @var \Doctrine\Common\Collections\ArrayCollection<Currency>
     *
     * @ORM\ManyToMany(targetEntity="Currency")
     * @ORM\JoinTable(name="s_core_shop_currencies")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $currencies;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Site\Group>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Site\Group")
     * @ORM\JoinTable(name="s_core_shop_pages")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $pages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<Shop>
     *
     * @ORM\OneToMany(targetEntity="Shop", mappedBy="main", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $children;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Shop", mappedBy="shop", orphanRemoval=true, cascade={"persist"})
     *
     * @var \Shopware\Models\Attribute\Shop
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
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
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
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
     * @param Template $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return Template
     */
    public function getDocumentTemplate()
    {
        return $this->documentTemplate;
    }

    /**
     * @param Template $documentTemplate
     */
    public function setDocumentTemplate($documentTemplate)
    {
        $this->documentTemplate = $documentTemplate;
    }

    /**
     * @return \Shopware\Models\Category\Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Shopware\Models\Category\Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return \Shopware\Models\Shop\Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param \Shopware\Models\Shop\Locale $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return \Shopware\Models\Shop\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \Shopware\Models\Shop\Currency $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Models\Customer\Group $customerGroup
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
     * @return Currency[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @param Currency[]|\Doctrine\Common\Collections\ArrayCollection $currencies
     */
    public function setCurrencies($currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * @return \Shopware\Models\Shop\Shop|null
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $main
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
     * @return \Shopware\Models\Shop\Shop|null
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $fallback
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
     * @return Shop[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Shop[]|\Doctrine\Common\Collections\ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Site\Group[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Site\Group[] $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return \Shopware\Models\Attribute\Shop
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Shop|array|null $attribute
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Shop', 'attribute', 'shop');
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
        $service = Shopware()->Container()->get('shopware.components.shop_registration_service');

        $service->registerShop($this);

        return $this;
    }
}
