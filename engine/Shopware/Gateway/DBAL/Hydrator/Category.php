<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct;

class Category extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @var Media
     */
    private $mediaHydrator;

    /**
     * @param Attribute $attributeHydrator
     * @param Media $mediaHydrator
     */
    function __construct(
        Attribute $attributeHydrator,
        Media $mediaHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaHydrator = $mediaHydrator;
    }

    /**
     * @param array $data
     * @return Struct\Category
     */
    public function hydrate(array $data)
    {
        $category = new Struct\Category();

        $this->assignCategoryData($category, $data);

        if ($data['__media_id']) {
            $category->setMedia(
                $this->mediaHydrator->hydrate($data)
            );
        }

        if ($data['__categoryAttribute_id']) {
            $attribute = $this->extractFields('__categoryAttribute_', $data);
            $category->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $category;
    }

    /**
     * @param Struct\Category $category
     * @param array $data
     */
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

        if (isset($data['__category_noviewselect'])) {
            $category->setAllowViewSelect((bool) !$data['__category_noviewselect']);
        }

        if (isset($data['__category_blog'])) {
            $category->setBlog($data['__category_blog']);
        }

        if (isset($data['__category_showfiltergroups'])) {
            $category->setDisplayPropertySets((bool) $data['__category_showfiltergroups']);
        }

        if (isset($data['__category_external'])) {
            $category->setExternalLink($data['__category_external']);
        }

        if (isset($data['__category_hidefilter'])) {
            $category->setDisplayFacets((bool) !$data['__category_hidefilter']);
        }

        if (isset($data['__category_hidetop'])) {
            $category->setDisplayInNavigation((bool) !$data['__category_hidetop']);
        }
    }

}
