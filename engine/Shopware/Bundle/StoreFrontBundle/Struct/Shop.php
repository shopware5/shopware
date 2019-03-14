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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group as CustomerGroup;
use Shopware\Models\Shop\Shop as ShopEntity;

class Shop extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $isDefault;

    /**
     * Id of the parent shop if current shop is a language shop,
     * Id of the current shop otherwise.
     *
     * @var int
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string[]
     */
    protected $hosts;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var int
     */
    protected $fallbackId;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var bool
     */
    protected $customerScope;

    /**
     * @return Shop
     */
    public static function createFromShopEntity(ShopEntity $shop)
    {
        $struct = new self();
        $struct->setId($shop->getId());
        $struct->setParentId($shop->getMain() ? $shop->getMain()->getId() : $shop->getId());

        $struct->setCustomerScope($shop->getMain() ? $shop->getMain()->getCustomerScope() : $shop->getCustomerScope());
        $struct->setIsDefault($shop->getDefault());
        $struct->setName($shop->getName());
        $struct->setHost($shop->getHost());
        $struct->setPath($shop->getBasePath());
        $struct->setUrl($shop->getBaseUrl());
        $struct->setSecure($shop->getSecure());
        if ($shop->getCategory()) {
            $struct->setCategory(
                Category::createFromCategoryEntity($shop->getCategory())
            );
        }

        if ($shop->getFallback()) {
            $struct->setFallbackId(
                $shop->getFallback()->getId()
            );
        }

        return $struct;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = (bool) $isDefault;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool) $this->isDefault;
    }

    /**
     * @param int $id
     */
    public function setParentId($id)
    {
        $this->parentId = $id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->getId() == $this->getParentId();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param int $fallbackId
     */
    public function setFallbackId($fallbackId)
    {
        $this->fallbackId = $fallbackId;
    }

    /**
     * @return int
     */
    public function getFallbackId()
    {
        return $this->fallbackId;
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
     * @return string[]
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @param string[] $hosts
     */
    public function setHosts($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency)
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

    public function setCustomerGroup(CustomerGroup $customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate(Template $template)
    {
        $this->template = $template;
    }

    /**
     * @return bool
     */
    public function hasCustomerScope()
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
}
