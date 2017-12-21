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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Configurator;

use Shopware\Bundle\StoreFrontBundle\Struct\Struct;

class GroupsByGroup extends Struct implements \JsonSerializable
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $shouldDisplay;

    /**
     * GroupsByGroup constructor.
     *
     * @param string $key
     * @param bool   $shouldDisplay
     */
    public function __construct($key, $shouldDisplay)
    {
        $this->key = $key;
        $this->shouldDisplay = $shouldDisplay;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return bool
     */
    public function isShouldDisplay()
    {
        return $this->shouldDisplay;
    }

    /**
     * @param bool $shouldDisplay
     */
    public function setShouldDisplay($shouldDisplay)
    {
        $this->shouldDisplay = $shouldDisplay;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
