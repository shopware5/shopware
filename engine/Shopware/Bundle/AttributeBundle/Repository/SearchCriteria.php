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

namespace Shopware\Bundle\AttributeBundle\Repository;

use Shopware\Components\Model\ModelEntity;

class SearchCriteria
{
    /**
     * @var class-string<ModelEntity>
     */
    public $entity;

    /**
     * @var int[]|string[]
     */
    public $ids = [];

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var int
     */
    public $limit = 50;

    /**
     * @var string|null
     */
    public $term;

    /**
     * @var array<array{property: string, direction: string}>
     */
    public $sortings = [];

    /**
     * @var array<array{property: string, value: mixed, expression?: string}>
     */
    public $conditions = [];

    /**
     * @var array
     */
    public $params = [];

    /**
     * @param class-string<ModelEntity> $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
