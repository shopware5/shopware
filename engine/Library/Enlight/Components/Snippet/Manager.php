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
 * Manager for the Enlight snippet component.
 *
 * The Enlight_Components_Snippet_Manager manage all snippet namespaces.
 * It is responsible to read and write the corresponding namespaces.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Snippet_Manager extends Enlight_Class
{
    /**
     * @var Enlight_Config_Adapter Can be overwrite in the constructor
     */
    protected $adapter;

    /**
     * @var array Array with all registered namespaces
     */
    protected $namespaces = [];

    /**
     * @var string Default config class
     */
    protected $defaultNamespaceClass = 'Enlight_Components_Snippet_Namespace';

    /**
     * @var string Default config class
     */
    protected $defaultSection = null;

    /**
     * @var string Array of all ignored namespaces. Can be set in the constructor.
     */
    protected $ignoreNamespace;

    /**
     * The Enlight_Components_Snippet_Manager class constructor excepts an configuration for the adapter.
     * The adapter can be set in the options array element "adapter" and have to been an instance
     * of the Enlight_Config_Adapter.
     *
     * @param array|Enlight_Config_Adapter|null $options
     */
    public function __construct($options = null)
    {
        if (!is_array($options)) {
            $options = ['adapter' => $options];
        }

        if (isset($options['adapter']) && $options['adapter'] instanceof Enlight_Config_Adapter) {
            $this->setAdapter($options['adapter']);
        }

        if (isset($options['ignore_namespace'])) {
            $this->ignoreNamespace = (bool) $options['ignore_namespace'];
        }
    }

    /**
     * Returns a snippet model instance
     *
     * @param string $namespace
     *
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getNamespace($namespace = null)
    {
        $key = $namespace === null ? '__ignore' : (string) $namespace;
        if (!isset($this->namespaces[$key])) {
            $this->namespaces[$key] = new $this->defaultNamespaceClass([
                'adapter' => $this->adapter,
                'name' => $namespace,
                'section' => $this->defaultSection,
            ]);
        }

        return $this->namespaces[$key];
    }

    /**
     * Adapter class of the snippet manager
     *
     * @return Enlight_Config_Adapter
     */
    public function Adapter()
    {
        return $this->adapter;
    }

    /**
     * Standard setter method for the adapter
     *
     * @param Enlight_Config_Adapter $adapter
     *
     * @return Enlight_Components_Snippet_Manager
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Writes each registered namespace to the database
     *
     * @return Enlight_Components_Snippet_Manager
     */
    public function write()
    {
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        foreach ($this->namespaces as $namespace) {
            $namespace->write();
        }

        return $this;
    }
}
