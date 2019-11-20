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

use Shopware\Bundle\CookieBundle\CookieCollection;

class CookieGroupStruct implements \JsonSerializable
{
    public const TECHNICAL = 'technical';
    public const COMFORT = 'comfort';
    public const PERSONALIZATION = 'personalization';
    public const STATISTICS = 'statistics';
    public const OTHERS = 'others';

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
    public $description;

    /**
     * @var CookieCollection
     */
    public $cookies;

    /**
     * Only used by the technical group, do not use otherwise!
     *
     * @var bool
     */
    private $required;

    public function __construct(string $name, string $label, string $description = '', bool $required = false)
    {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->required = $required;
        $this->cookies = new CookieCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getCookies(): CookieCollection
    {
        return $this->cookies;
    }

    public function addCookie(CookieStruct $cookieStruct): void
    {
        $this->cookies->add($cookieStruct);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
