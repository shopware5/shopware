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

namespace Shopware\Components\Theme;

/**
 * @category  Shopware
 * @package   Shopware\Components\Theme
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Configuration implements \JsonSerializable
{
    /**
     * @var string[]
     */
    private $less = [];

    /**
     * @var string[]
     */
    private $js = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $lessTarget;

    /**
     * @var string
     */
    private $jsTarget;

    /**
     * @param string[] $less
     * @param string[] $js
     * @param array $config
     * @param string $lessTarget
     * @param string $jsTarget
     */
    public function __construct($less, $js, $config, $lessTarget, $jsTarget)
    {
        $this->less = $less;
        $this->js = $js;
        $this->config = $config;
        $this->lessTarget = $lessTarget;
        $this->jsTarget = $jsTarget;
    }

    /**
     * @return string[]
     */
    public function getLess()
    {
        return $this->less;
    }

    /**
     * @return string[]
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getLessTarget()
    {
        return $this->lessTarget;
    }

    /**
     * @return string
     */
    public function getJsTarget()
    {
        return $this->jsTarget;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
