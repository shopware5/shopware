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

namespace Shopware\Category\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryHydrator
{
    //    /**
    //     * @var AttributeHydrator
    //     */
    //    private $attributeHydrator;
    //
    //    /**
    //     * @var \Shopware\Bundle\StoreFrontBundle\Media\MediaHydrator
    //     */
    //    private $mediaHydrator;
    //
    //    /**
    //     * @var ProductStreamHydrator
    //     */
    //    private $productStreamHydrator;
    //
    //    /**
    //     * @param AttributeHydrator                                     $attributeHydrator
    //     * @param \Shopware\Bundle\StoreFrontBundle\Media\MediaHydrator $mediaHydrator
    //     * @param ProductStreamHydrator                                 $productStreamHydrator
    //     */
    //    public function __construct(
    //        AttributeHydrator $attributeHydrator,
    //        MediaHydrator $mediaHydrator,
    //        ProductStreamHydrator $productStreamHydrator
    //    ) {
    //        $this->attributeHydrator = $attributeHydrator;
    //        $this->mediaHydrator = $mediaHydrator;
    //        $this->productStreamHydrator = $productStreamHydrator;
    //    }

    public function hydrate(array $data): Category
    {
        $category = new Category(
            (int) $data['__category_id'],
            (int) $data['__category_parent_id'],
            array_filter(explode('|', $data['__category_path'])),
            (string) $data['__category_description']
        );

        $category->assign(
            [
                'uuid' => $data['__category_uuid'],
                'level' => $data['__category_level'],
                'createdAt' => new \DateTime($data['__category_added']),
                'updatedAt' => new \DateTime($data['__category_changed_at']),
                'active' => $data['__category_active'],
                'facetIds' => array_filter(explode('|', $data['__category_facet_ids'])),
                'sortingIds' => array_filter(explode('|', $data['__category_sorting_ids'])),

                'position' => (int) $data['__category_position'],
                'name' => (string) $data['__category_description'],
                'metaTitle' => (string) $data['__category_meta_title'],
                'metaKeywords' => (string) $data['__category_meta_keywords'],
                'metaDescription' => (string) $data['__category_meta_description'],
                'cmsHeadline' => (string) $data['__category_cms_headline'],
                'cmsText' => (string) $data['__category_cms_description'],
                'productBoxLayout' => (string) $data['__category_product_box_layout'],
                'template' => (int) $data['__category_template'],
                'blog' => (int) $data['__category_blog'],
                'externalLink' => (string) $data['__category_external'],
                'displayFacets' => (bool) !$data['__category_hide_filter'],
                'displayInNavigation' => (bool) !$data['__category_hide_top'],
                'hideSortings' => (bool) $data['__category_hide_sortings'],
                'blockedCustomerGroupIds' => explode(',', $data['__category_customer_groups']),
                'isShopCategory' => (bool) $data['__category_is_shop_category'],
            ]
        );

        //        if ($data['__category_media_id']) {
        //            $category->setMedia(
        //                $this->mediaHydrator->hydrate($data)
        //            );
        //        }
        //
        //        if ($data['__categoryAttribute_id']) {
        //            $this->attributeHydrator->addAttribute($category, $data, 'categoryAttribute');
        //        }
        //
        //        if ($data['__category_stream_id']) {
        //            $category->setProductStream(
        //                $this->productStreamHydrator->hydrate($data)
        //            );
        //        }

        return $category;
    }
}
