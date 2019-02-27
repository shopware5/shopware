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

/**
 * Namespace for an Enlight snippet.
 *
 * The Enlight_Components_Snippet_Namespace represents a single snippet namespace with all according snippets.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Snippet_Namespace extends Enlight_Config
{
    /**
     * Whether in-memory modifications to configuration data are allowed
     *
     * @var bool
     */
    protected $_allowModifications = true;

    /**
     * @var Enlight_Components_Snippet_Namespace
     */
    protected $fallback;

    /**
     * Constructor method
     *
     * @param array|bool $options
     */
    public function __construct($options = null)
    {
        $name = $options['name'] !== null ? $options['name'] : '';
        unset($options['name']);
        parent::__construct($name, $options);
        $this->read();
    }

    /**
     * @return Enlight_Components_Snippet_Namespace|null
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param Enlight_Components_Snippet_Namespace $fallback
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Retrieves a value and returns $default if there is no element set.
     *
     * @param string $name
     * @param mixed  $default
     * @param bool   $save
     *
     * @return mixed
     */
    public function get($name, $default = null, $save = false)
    {
        if ($this->_data === null) {
            $this->read();
        }
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        if ($default == null && $this->fallback) {
            $default = $this->fallback->get($name);
        }
        if ($save) {
            $this->set($name, $default);
            $this->write();
        }

        return $default;
    }
}
