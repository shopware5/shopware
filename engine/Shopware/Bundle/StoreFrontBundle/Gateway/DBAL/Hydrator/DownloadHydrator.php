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

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;

class DownloadHydrator extends Hydrator
{
    private AttributeHydrator $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * Creates a new Struct\Product\Download struct with the passed data.
     *
     * @return Download
     */
    public function hydrate(array $data)
    {
        $download = new Download();
        $download->setId((int) $data['__download_id']);
        $download->setDescription($data['__download_description']);
        $download->setFile($data['__download_filename']);
        $download->setSize((float) $data['__download_size']);

        if (!empty($data['__downloadAttribute_id'])) {
            $this->attributeHydrator->addAttribute($download, $data, 'downloadAttribute');
        }

        return $download;
    }
}
