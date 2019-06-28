<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
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
