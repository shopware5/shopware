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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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

    /**
     * @param AttributeHydrator     $attributeHydrator
     * @param MediaHydrator         $mediaHydrator
     * @param ProductStreamHydrator $productStreamHydrator
     */
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
     * @param array $data
     *
     * @return Struct\Category
     */
    public function hydrate(array $data)
    {
        $category = Struct\Category::create(
            (int) $data['__category_id'],
            (int) $data['__category_parent_id'],
            array_filter(explode('|', $data['__category_path'])),
            (string) $data['__category_description'],
            [
                'position' => (int) $data['__category_position'],
                'name' => (string) $data['__category_description'],
                'metaTitle' => (string) $data['__category_metatitle'],
                'metaKeywords' => (string) $data['__category_metakeywords'],
                'metaDescription' => (string) $data['__category_metadescription'],
                'cmsHeadline' => (string) $data['__category_cmsheadline'],
                'cmsText' => (int) $data['__category_cmstext'],
                'productBoxLayout' => (string) $data['__category_product_box_layout'],
                'template' => (int) $data['__category_template'],
                'blog' => (int) $data['__category_blog'],
                'externalLink' => (string) $data['__category_external'],
                'displayFacets' => (bool) !$data['__category_hidefilter'],
                'displayInNavigation' => (bool) !$data['__category_hidetop'],
                'hideSortings' => (bool) $data['__category_hide_sortings'],
                'blockedCustomerGroupIds' => explode(',', $data['__category_customer_groups']),
            ]
        );

        if ($data['__media_id']) {
            $category->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
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
}
