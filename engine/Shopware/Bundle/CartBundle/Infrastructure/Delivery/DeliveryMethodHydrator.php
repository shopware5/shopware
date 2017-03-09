<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\CartBundle\Infrastructure\Delivery;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class DeliveryMethodHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var array
     */
    private $mapping = [
        'dispatch_status_link' => 'status_link',
        'dispatch_description' => 'description',
        'dispatch_name' => 'name',
    ];

    /**
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    public function hydrate(array $data): DeliveryMethod
    {
        $id = (int) $data['__deliveryMethod_id'];
        $translation = $this->getTranslation($data, '__deliveryMethod', $this->mapping, $id);
        $data = array_merge($data, $translation);

        $service = new DeliveryMethod(
            (int) $data['__deliveryMethod_id'],
            (string) $data['__deliveryMethod_name'],
            (string) $data['__deliveryMethod_description'],
            (int) $data['__deliveryMethod_type'],
            (bool) $data['__deliveryMethod_active'],
            (int) $data['__deliveryMethod_position']
        );

        if (!empty($data['__deliveryMethodAttribute_id'])) {
            $this->attributeHydrator->addAttribute($service, $data, 'deliveryMethodAttribute');
        }

        return $service;
    }
}
