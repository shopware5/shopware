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

use Shopware\Bundle\StoreFrontBundle\Struct;

class ShopPageHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Struct\ShopPage
     */
    public function hydrate(array $data)
    {
        $page = new Struct\ShopPage();

        $translation = $this->getTranslation($data, '__page');
        $data = array_merge($data, $translation);

        $this->assignData($page, $data);

        return $page;
    }

    private function assignData(Struct\ShopPage $shopPage, array $data)
    {
        $shopPage->setId((int) $data['__page_id']);
        $shopPage->setTpl1Variable($data['__page_tpl1variable']);
        $shopPage->setTpl1Path($data['__page_tpl1path']);
        $shopPage->setTpl2Variable($data['__page_tpl2variable']);
        $shopPage->setTpl2Path($data['__page_tpl2path']);
        $shopPage->setTpl3Variable($data['__page_tpl3variable']);
        $shopPage->setTpl3Path($data['__page_tpl3path']);
        $shopPage->setDescription($data['__page_description']);
        $shopPage->setHtml($data['__page_html']);
        $shopPage->setGrouping(explode('|', $data['__page_grouping']));
        $shopPage->setPosition((int) $data['__page_position']);
        $shopPage->setLink($data['__page_link']);
        $shopPage->setTarget($data['__page_target']);
        $shopPage->setPageTitle($data['__page_page_title']);
        $shopPage->setMetaKeywords($data['__page_meta_keywords']);
        $shopPage->setMetaDescription($data['__page_meta_description']);
        $shopPage->setChildrenCount((int) $data['__page_children_count']);

        if (isset($data['__page_parent_id']) && $data['__page_parent_id'] > 0) {
            $shopPage->setParentId((int) $data['__page_parent_id']);
        }

        if (!empty($data['__page_changed'])) {
            $shopPage->setChanged(\DateTime::createFromFormat('Y-m-d H:i:s', $data['__page_changed']));
        }

        $shopIds = [];

        if (isset($data['__page_shop_ids'])) {
            $shopIds = explode('|', $data['__page_shop_ids']);
            $shopIds = array_keys(array_flip($shopIds));
            $shopIds = array_filter($shopIds);
            $shopIds = array_map('intval', $shopIds);
        }

        $shopPage->setShopIds($shopIds);

        $this->attributeHydrator->addAttribute($shopPage, $data, 'pageAttribute');
    }
}
