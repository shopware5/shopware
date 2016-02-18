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
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ManufacturerHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var array
     */
    private $translationMapping = [
        'description' => '__manufacturer_description',
        'metaTitle' => '__manufacturer_meta_title',
        'metaDescription' => '__manufacturer_meta_description',
        'metaKeywords' => '__manufacturer_meta_keywords',
    ];

    /**
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return Struct\Product\Manufacturer
     */
    public function hydrate(array $data)
    {
        $translation = $this->getTranslation($data);
        $data = array_merge($data, $translation);

        $manufacturer = new Struct\Product\Manufacturer();

        $this->assignData($manufacturer, $data);

        if (isset($data['__manufacturerAttribute_id'])) {
            $this->assignAttribute($manufacturer, $data);
        }

        return $manufacturer;
    }

    /**
     * @param Struct\Product\Manufacturer $manufacturer
     * @param array $data
     */
    private function assignData(Struct\Product\Manufacturer $manufacturer, array $data)
    {
        if (isset($data['__manufacturer_id'])) {
            $manufacturer->setId((int) $data['__manufacturer_id']);
        }

        if (isset($data['__manufacturer_name'])) {
            $manufacturer->setName($data['__manufacturer_name']);
        }

        if (isset($data['__manufacturer_description'])) {
            $manufacturer->setDescription($data['__manufacturer_description']);
        }

        if (isset($data['__manufacturer_meta_title'])) {
            $manufacturer->setMetaTitle($data['__manufacturer_meta_title']);
        }

        if (isset($data['__manufacturer_meta_description'])) {
            $manufacturer->setMetaDescription($data['__manufacturer_meta_description']);
        }

        if (isset($data['__manufacturer_meta_keywords'])) {
            $manufacturer->setMetaKeywords($data['__manufacturer_meta_keywords']);
        }

        if (isset($data['__manufacturer_link'])) {
            $manufacturer->setLink($data['__manufacturer_link']);
        }

        if (isset($data['__manufacturer_img'])) {
            $manufacturer->setCoverFile($data['__manufacturer_img']);
        }
    }

    /**
     * @param Struct\Product\Manufacturer $manufacturer
     * @param array $data
     */
    private function assignAttribute(Struct\Product\Manufacturer $manufacturer, array $data)
    {
        $attribute = $this->attributeHydrator->hydrate(
            $this->extractFields('__manufacturerAttribute_', $data)
        );

        $manufacturer->addAttribute('core', $attribute);
    }

    /**
     * @param $data
     * @return array|mixed
     */
    private function getTranslation($data)
    {
        if (!isset($data['__manufacturer_translation'])
            || empty($data['__manufacturer_translation'])
        ) {
            $translation = [];
        } else {
            $translation = unserialize($data['__manufacturer_translation']);
        }

        if (isset($data['__manufacturer_translation_fallback'])
            && !empty($data['__manufacturer_translation_fallback'])
        ) {
            $fallbackTranslation = unserialize($data['__manufacturer_translation_fallback']);
            $translation += $fallbackTranslation;
        }

        if (empty($translation)) {
            return [];
        }

        return $this->convertArrayKeys(
            $translation,
            $this->translationMapping
        );
    }
}
