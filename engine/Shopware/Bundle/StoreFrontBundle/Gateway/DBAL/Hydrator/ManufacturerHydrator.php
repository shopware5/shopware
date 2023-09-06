<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;

class ManufacturerHydrator extends Hydrator
{
    private AttributeHydrator $attributeHydrator;

    private MediaServiceInterface $mediaService;

    /**
     * @var array<string, string>
     */
    private array $mapping = [
        'metaTitle' => 'meta_title',
        'metaDescription' => 'meta_description',
        'metaKeywords' => 'meta_keywords',
    ];

    public function __construct(AttributeHydrator $attributeHydrator, MediaServiceInterface $mediaService)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->mediaService = $mediaService;
    }

    /**
     * @return Manufacturer
     */
    public function hydrate(array $data)
    {
        $manufacturer = new Manufacturer();
        $this->assignData($manufacturer, $data);

        return $manufacturer;
    }

    private function assignData(Manufacturer $manufacturer, array $data)
    {
        $translation = $this->getTranslation($data, '__manufacturer', $this->mapping);
        $data = array_merge($data, $translation);

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
            $manufacturer->setCoverFile(
                $this->mediaService->getUrl(
                    $data['__manufacturer_img']
                )
            );
        }

        if (isset($data['__manufacturer_img_id'])) {
            $manufacturer->setCoverId($data['__manufacturer_img_id']);
        }

        if (isset($data['__manufacturerAttribute_id'])) {
            $this->attributeHydrator->addAttribute($manufacturer, $data, 'manufacturerAttribute', null, 'manufacturer');
        }
    }
}
