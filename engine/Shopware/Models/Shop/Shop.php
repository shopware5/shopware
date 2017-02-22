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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Theme\Inheritance;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Container;

/**
 *
 * @ORM\Table(name="s_core_shops")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Shop extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var integer $mainId
     * @ORM\Column(name="main_id", type="integer", nullable=true)
     */
    protected $mainId;

    /**
     * @var integer $categoryId
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    protected $categoryId;

    /**
     * @var Shop $main
     * @ORM\ManyToOne(targetEntity="Shop", inversedBy="children")
     */
    protected $main;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var integer $position
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     * @var string $name
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string $basePath
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    protected $basePath;

    /**
     * @var string $baseUrl
     * @ORM\Column(name="base_url", type="string", length=255, nullable=true)
     */
    protected $baseUrl;

    /**
     * @var string $hosts
     * @ORM\Column(name="hosts", type="text", nullable=false)
     */
    protected $hosts = '';

    /**
     * @var boolean $secure
     * @ORM\Column(name="secure", type="boolean", nullable=false)
     */
    protected $secure = false;

    /**
     * @var boolean $secure
     * @ORM\Column(name="always_secure", type="boolean", nullable=false)
     */
    protected $alwaysSecure = false;

    /**
     * @var string $name
     * @ORM\Column(name="secure_host", type="string", length=255, nullable=true)
     */
    protected $secureHost;

    /**
     * @var string $secureBasePath
     * @ORM\Column(name="secure_base_path", type="string", length=255, nullable=true)
     */
    protected $secureBasePath;

    /**
     * @var string $secureBaseUrl
     */
    protected $secureBaseUrl;

    /**
     * @var $template int
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId;

    /**
     * @var Template $template
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="shops")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var Template $documentTemplate
     * @ORM\ManyToOne(targetEntity="Template")
     * @ORM\JoinColumn(name="document_template_id", referencedColumnName="id")
     */
    protected $documentTemplate;

    /**
     * @var \Shopware\Models\Category\Category $category
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     */
    protected $category;

    /**
     * @var Locale $locale
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Locale")
     */
    protected $locale;

    /**
     * @var Currency $currency
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    protected $currency;

    /**
     * @var \Shopware\Models\Customer\Group $customerGroup
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     */
    protected $customerGroup;

    /**
     * @var boolean $default
     * @ORM\Column(name="`default`", type="boolean", nullable=false)
     */
    protected $default = false;

    /**
     * @var boolean $active
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @var Shop $fallback
     * @ORM\ManyToOne(targetEntity="Shop")
     */
    protected $fallback;

    /**
     * @var boolean $default
     * @ORM\Column(name="customer_scope", type="boolean", nullable=false)
     */
    protected $customerScope = false;

    /**
     * @var Currency[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Currency")
     * @ORM\JoinTable(name="s_core_shop_currencies")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $currencies;

    /**
     * @var \Shopware\Models\Site\Group[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Site\Group")
     * @ORM\JoinTable(name="s_core_shop_pages")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $pages;

    /**
     * @var Shop[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Shop", mappedBy="main", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    protected $children;

    /**
     * Class constructor.
     */
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
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param integer $position
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
     * @return Template
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
     * @return \Shopware\Models\Category\Category
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
     * @return int
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
     * @return \Shopware\Models\Shop\Shop
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
     * @return boolean
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param boolean $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return string
     */
    public function getSecureHost()
    {
        return $this->secureHost;
    }

    /**
     * @param string $secureHost
     */
    public function setSecureHost($secureHost)
    {
        $this->secureHost = $secureHost;
    }

    /**
     * @return string
     */
    public function getSecureBasePath()
    {
        return $this->secureBasePath;
    }

    /**
     * @param string $secureBasePath
     */
    public function setSecureBasePath($secureBasePath)
    {
        $this->secureBasePath = $secureBasePath;
    }

    /**
     * @return string
     */
    public function getSecureBaseUrl()
    {
        return $this->secureBaseUrl;
    }

    /**
     * @param string $secureBaseUrl
     */
    public function setSecureBaseUrl($secureBaseUrl)
    {
        $this->secureBaseUrl = $secureBaseUrl;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
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
     * @return boolean
     */
    public function getCustomerScope()
    {
        return $this->customerScope;
    }

    /**
     * @param boolean $customerScope
     */
    public function setCustomerScope($customerScope)
    {
        $this->customerScope = $customerScope;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
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
     * @param  Shop[]|\Doctrine\Common\Collections\ArrayCollection $children
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
     * @param $name
     * @return mixed
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
     * @param null $bootstrap Deprecated since 5.2 will be removed in 5.3
     * @return DetachedShop
     */
    public function registerResources($bootstrap = null)
    {
        /** @var Container $container */
        $container = Shopware()->Container();

        $container->set('Shop', $this);

        /** @var $locale \Zend_Locale */
        $locale = $container->get('Locale');
        $locale->setLocale($this->getLocale()->toString());

        /** @var $currency \Zend_Currency */
        $currency = $container->get('Currency');
        $currency->setLocale($locale);
        $currency->setFormat($this->getCurrency()->toArray());

        /** @var $config \Shopware_Components_Config */
        $config = $container->get('Config');
        $config->setShop($this);

        /** @var $snippets \Shopware_Components_Config */
        $snippets = $container->get('Snippets');
        $snippets->setShop($this);

        /** @var $plugins \Enlight_Plugin_PluginManager */
        $plugins = $container->get('Plugins');

        /** @var $pluginNamespace  \Shopware_Components_Plugin_Namespace */
        foreach ($plugins as $pluginNamespace) {
            if ($pluginNamespace instanceof \Shopware_Components_Plugin_Namespace) {
                $pluginNamespace->setShop($this);
            }
        }

        //Initializes the frontend session to prevent output before session started.
        $container->get('session');

        if ($this->getTemplate() !== null) {
            /** @var $templateManager \Enlight_Template_Manager */
            $templateManager = $container->get('Template');
            $template = $this->getTemplate();
            $localeName = $this->getLocale()->toString();

            if ($template->getVersion() == 3) {
                $this->registerTheme($template);
            } elseif ($template->getVersion() == 2) {
                $templateManager->addTemplateDir(array(
                    'custom' => $template->toString(),
                    'local' => '_emotion_local',
                    'emotion' => '_emotion',
                    'include_dir' => '.'
                ));
            } else {
                throw new \Exception(sprintf(
                    'Tried to load unsupported template version %s for template: %s',
                    $template->getVersion(),
                    $template->getName()
                ));
            }

            $templateManager->setCompileId(
                'frontend' .
                '_' . $template->toString() .
                '_' . $localeName .
                '_' . $this->getId()
            );
        }

        /** @var $templateMail \Shopware_Components_TemplateMail */
        $templateMail = $container->get('TemplateMail');
        $templateMail->setShop($this);

        return $this;
    }

    /**
     * @param Template $template
     * @throws \Exception
     */
    private function registerTheme(Template $template)
    {
        /**@var $templateManager \Enlight_Template_Manager*/
        $templateManager = Shopware()->Container()->get('template');

        /**@var $inheritance Inheritance*/
        $inheritance = Shopware()->Container()->get('theme_inheritance');

        $path = $inheritance->getTemplateDirectories($template);
        $templateManager->setTemplateDir($path);
    }

    /**
     * @return boolean
     */
    public function getAlwaysSecure()
    {
        return $this->alwaysSecure;
    }

    /**
     * @param boolean $alwaysSecure
     */
    public function setAlwaysSecure($alwaysSecure)
    {
        $this->alwaysSecure = $alwaysSecure;
    }
}
