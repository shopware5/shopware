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

namespace Shopware\Bundle\EsBackendBundle\Struct;

class Backlog
{
    /**
     * @var string
     */
    public $entity;

    /**
     * @var int|null
     */
    public $entity_id;

    /**
     * @var string
     */
    public $time;

    /**
     * @param int|null $entity_id
     */
    public function __construct(string $entity, $entity_id)
    {
        $this->entity = $entity;
        $this->entity_id = $entity_id;
        $this->time = date('Y-m-d H:i:s');
    }

    /**
     * @return array{entity: string, entity_id: int|null, time: string}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
