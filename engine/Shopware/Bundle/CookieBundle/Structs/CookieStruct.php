<?php declare(strict_types=1);
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

namespace Shopware\Bundle\CookieBundle\Structs;

class CookieStruct implements \JsonSerializable
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $groupName;

    /**
     * @var CookieGroupStruct
     */
    public $group;

    /**
     * @var string
     */
    private $matchingPattern;

    public function __construct(string $name, string $matchingPattern, string $label, string $groupName = CookieGroupStruct::OTHERS)
    {
        $this->name = $name;
        $this->matchingPattern = $matchingPattern;
        $this->label = $label;
        $this->groupName = $groupName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getMatchingPattern(): string
    {
        return $this->matchingPattern;
    }

    public function setMatchingPattern(string $matchingPattern): void
    {
        $this->matchingPattern = $matchingPattern;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getGroup(): ?CookieGroupStruct
    {
        return $this->group;
    }

    public function setGroup(CookieGroupStruct $group): void
    {
        $this->group = $group;
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        unset($data['group']);

        return $data;
    }
}
