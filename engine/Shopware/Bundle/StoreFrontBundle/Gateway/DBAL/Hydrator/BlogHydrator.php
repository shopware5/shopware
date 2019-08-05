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

class BlogHydrator extends Hydrator
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
     * @return Struct\Blog\Blog
     */
    public function hydrate(array $data)
    {
        $translation = $this->getBlogTranslation($data);
        $data = array_merge($data, $translation);

        $blog = new Struct\Blog\Blog();

        $blog->setId((int) $data['__blog_id']);
        $blog->setTitle($data['__blog_title']);
        $blog->setAuthorId($data['__blog_author_id'] !== null ? (int) $data['__blog_author_id'] : null);
        $blog->setActive((bool) $data['__blog_active']);
        $blog->setShortDescription($data['__blog_short_description']);
        $blog->setDescription($data['__blog_description']);
        $blog->setViews((int) $data['__blog_views']);
        $blog->setDisplayDate($data['__blog_display_date'] ? date_create($data['__blog_display_date']) : null);
        $blog->setCategoryId((int) $data['__blog_category_id']);
        $blog->setTemplate($data['__blog_template']);
        $blog->setMetaKeywords($data['__blog_meta_keywords']);
        $blog->setMetaDescription($data['__blog_meta_description']);
        $blog->setMetaTitle($data['__blog_meta_title']);

        if (isset($data['__blogAttribute_id'])) {
            $attributeData = $this->extractFields('__blogAttribute_', $data);
            $attribute = $this->attributeHydrator->hydrate($attributeData);
            $blog->addAttribute('core', $attribute);
        }

        if (isset($data['__blog_tags'])) {
            $blog->setTags(explode(',', $data['__blog_tags']));
        }

        return $blog;
    }

    public function getBlogTranslation(array $data): array
    {
        $translation = $this->getTranslation($data, '__blog', [], null, false);

        if (empty($translation)) {
            return $translation;
        }

        return $this->convertArrayKeys($translation, [
            'title' => '__blog_title',
            'shortDescription' => '__blog_short_description',
            'description' => '__blog_description',
            'metaTitle' => '__blog_meta_title',
            'metaKeyWords' => '__blog_meta_keywords',
            'metaDescription' => '__blog_meta_description',
        ]);
    }
}
