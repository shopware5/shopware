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

class CategoryHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var MediaHydrator
     */
    private $mediaHydrator;

    /**
     * @var ProductStreamHydrator
     */
    private $productStreamHydrator;

    public function __construct(
        AttributeHydrator $attributeHydrator,
        MediaHydrator $mediaHydrator,
        ProductStreamHydrator $productStreamHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaHydrator = $mediaHydrator;
        $this->productStreamHydrator = $productStreamHydrator;
    }

    /**
     * @return Struct\Category
     */
    public function hydrate(array $data)
    {
        $category = new Struct\Category();
        $translation = $this->getTranslation($data, '__category');
        $data = array_merge($data, $translation);

        $this->assignCategoryData($category, $data);

        if ($data['__media_id']) {
            $category->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
        }

        if (isset($data['mediaTranslation'])) {
            $category->setMedia($data['mediaTranslation']);
        }

        if ($data['__categoryAttribute_id']) {
            $this->attributeHydrator->addAttribute($category, $data, 'categoryAttribute');
        }

        if (isset($data['__stream_id']) && $data['__stream_id']) {
            $category->setProductStream(
                $this->productStreamHydrator->hydrate($data)
            );
        }

        return $category;
    }

    private function assignCategoryData(Struct\Category $category, array $data)
    {
        if (isset($data['__category_id'])) {
            $category->setId((int) $data['__category_id']);
        }

        if (isset($data['__category_path'])) {
            $path = ltrim($data['__category_path'], '|');
            $path = rtrim($path, '|');

            $path = explode('|', $path);

            $category->setPath(array_reverse($path));
        }

        if (isset($data['__category_description'])) {
            $category->setName($data['__category_description']);
        }

        $category->setParentId((int) $data['__category_parent_id']);

        $category->setPosition((int) $data['__category_position']);

        $category->setProductBoxLayout($data['__category_product_box_layout']);

        if (isset($data['__category_metatitle'])) {
            $category->setMetaTitle($data['__category_metatitle']);
        }

        if (isset($data['__category_metakeywords'])) {
            $category->setMetaKeywords($data['__category_metakeywords']);
        }

        if (isset($data['__category_metadescription'])) {
            $category->setMetaDescription($data['__category_metadescription']);
        }

        if (isset($data['__category_cmsheadline'])) {
            $category->setCmsHeadline($data['__category_cmsheadline']);
        }

        if (isset($data['__category_cmstext'])) {
            $category->setCmsText($data['__category_cmstext']);
        }

        if (isset($data['__category_template'])) {
            $category->setTemplate($data['__category_template']);
        }

        if (isset($data['__category_blog'])) {
            $category->setBlog((bool) $data['__category_blog']);
        }

        if (isset($data['__category_external'])) {
            $category->setExternalLink($data['__category_external']);
        }

        if (isset($data['__category_external_target'])) {
            $category->setExternalTarget($data['__category_external_target']);
        }

        if (isset($data['__category_hidefilter'])) {
            $category->setDisplayFacets((bool) !$data['__category_hidefilter']);
        }

        if (isset($data['__category_hidetop'])) {
            $category->setDisplayInNavigation((bool) !$data['__category_hidetop']);
        }

        if (isset($data['__category_customer_groups'])) {
            /** @var int[] $categoryCustomerGroups */
            $categoryCustomerGroups = explode(',', $data['__category_customer_groups']);
            $category->setBlockedCustomerGroupIds($categoryCustomerGroups);
        }

        $category->setHideSortings((bool) $data['__category_hide_sortings']);
    }
}
