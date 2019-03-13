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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ShopHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var TemplateHydrator
     */
    private $templateHydrator;

    /**
     * @var CategoryHydrator
     */
    private $categoryHydrator;

    /**
     * @var LocaleHydrator
     */
    private $localeHydrator;

    /**
     * @var CurrencyHydrator
     */
    private $currencyHydrator;

    /**
     * @var CustomerGroupHydrator
     */
    private $customerGroupHydrator;

    public function __construct(
        AttributeHydrator $attributeHydrator,
        TemplateHydrator $templateHydrator,
        CategoryHydrator $categoryHydrator,
        LocaleHydrator $localeHydrator,
        CurrencyHydrator $currencyHydrator,
        CustomerGroupHydrator $customerGroupHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->templateHydrator = $templateHydrator;
        $this->categoryHydrator = $categoryHydrator;
        $this->localeHydrator = $localeHydrator;
        $this->currencyHydrator = $currencyHydrator;
        $this->customerGroupHydrator = $customerGroupHydrator;
    }

    /**
     * @param array $data
     *
     * @return Shop
     */
    public function hydrate($data)
    {
        $shop = new Shop();
        $shop->setId((int) $data['__shop_id']);
        $shop->setIsDefault((bool) $data['__shop_default']);
        $shop->setName($data['__shop_name']);
        $shop->setTitle($data['__shop_title']);
        $shop->setFallbackId((int) $data['__shop_fallback_id']);
        $shop->setCurrency($this->currencyHydrator->hydrate($data));
        $shop->setCustomerGroup($this->customerGroupHydrator->hydrate($data));
        $shop->setCategory($this->categoryHydrator->hydrate($data));
        $shop->setLocale($this->localeHydrator->hydrate($data));

        $parent = $data;
        if ($data['parent']) {
            $parent = $data['parent'];
        }

        $shop->setTemplate($this->templateHydrator->hydrate($parent));
        $shop->setParentId((int) $parent['__shop_id']);
        $shop->setHost($parent['__shop_host']);
        $shop->setPath($parent['__shop_base_path']);
        $shop->setCustomerScope((bool) $parent['__shop_customer_scope']);
        $shop->setUrl($data['__shop_base_url'] ?: $parent['__shop_base_url']);
        $shop->setSecure((bool) $parent['__shop_secure']);

        $hosts = [];
        if ($parent['__shop_hosts']) {
            $hosts = explode('\n', $parent['__shop_hosts']);
            $hosts = array_unique(array_values(array_filter($hosts)));
        }
        $shop->setHosts($hosts);

        if ($data['__shopAttribute_id']) {
            $this->attributeHydrator->addAttribute($shop, $data, 'shopAttribute');
        }

        return $shop;
    }
}
