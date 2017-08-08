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

namespace Shopware\Shop\Struct;

use Shopware\Category\Struct\Category;
use Shopware\Country\Struct\Country;
use Shopware\Currency\Struct\Currency;
use Shopware\CustomerGroup\Struct\CustomerGroup;
use Shopware\Framework\Struct\Struct;
use Shopware\Locale\Struct\Locale;
use Shopware\PaymentMethod\Struct\PaymentMethod;
use Shopware\ShippingMethod\Struct\ShippingMethod;
use Shopware\ShopTemplate\Struct\ShopTemplate;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shop extends Struct
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
     * @var ShopTemplate
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
     * @var ShippingMethod
     */
    protected $shippingMethod;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var string
     */
    protected $taxCalculation;

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
    public function isDefault(): bool
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
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return int
     */
    public function getId(): int
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
    public function getName(): string
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
    public function getPath(): string
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
    public function getSecure(): bool
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
    public function getUrl(): string
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
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return Category
     */
    public function getCategory(): \Shopware\Category\Struct\Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->getId() == $this->getParentId();
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
    public function getFallbackId(): int
    {
        return $this->fallbackId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
     * @return \string[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * @param \string[] $hosts
     */
    public function setHosts($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): \Shopware\Currency\Struct\Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup(): CustomerGroup
    {
        return $this->customerGroup;
    }

    /**
     * @param CustomerGroup $customerGroup
     */
    public function setCustomerGroup(CustomerGroup $customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return Locale
     */
    public function getLocale(): \Shopware\Locale\Struct\Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return ShopTemplate
     */
    public function getTemplate(): \Shopware\ShopTemplate\Struct\ShopTemplate
    {
        return $this->template;
    }

    /**
     * @param ShopTemplate $template
     */
    public function setTemplate(ShopTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * @return bool
     */
    public function hasCustomerScope(): bool
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

    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    public function getTaxCalculation(): string
    {
        return $this->taxCalculation;
    }

    public function setTaxCalculation(string $taxCalculation): void
    {
        $this->taxCalculation = $taxCalculation;
    }
}
