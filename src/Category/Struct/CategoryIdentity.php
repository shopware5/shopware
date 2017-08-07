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

use Shopware\Framework\Struct\Struct;

class CategoryIdentity extends Struct
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $parentId;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int[]
     */
    protected $path = [];

    /**
     * @var bool
     */
    protected $active;

    public function __construct(int $id, ?int $parentId, int $position, array $path, bool $active)
    {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->position = $position;
        $this->path = $path;
        $this->active = $active;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
