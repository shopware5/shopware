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
 * The Enlight_Template_Manager is an extension of smarty to manually set the config in the class constructor.
 *
 * With the Enlight_Template_Manager it is not only possible to overwrite template files,
 * it is also possible to overwrite all the individual blocks within the template.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Template_Manager extends Smarty
{
    /**
     * Constant for the append parameter.
     */
    const POSITION_APPEND = 'append';

    /**
     * Constant for the prepend parameter.
     */
    const POSITION_PREPEND = 'prepend';

    /**
     * The name of class used for templates
     *
     * @var string
     */
    public $template_class = \Enlight_Template_Default::class;

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * Class constructor, initializes basic smarty properties:
     * Template, compile, plugin, cache and config directory.
     * @param null|array $options
     * @param Enlight_Event_EventManager $eventManager
     * @param array $backendOptions
     */
    public function __construct($options = null, Enlight_Event_EventManager $eventManager = null, $backendOptions = [])
    {
        parent::__construct();

        // rest global vars
        Smarty::$global_tpl_vars = [];

        if (!isset($backendOptions['cache_file_perm'])) {
            $backendOptions['cache_file_perm'] = 0666 & ~umask();
        }

        if (!isset($backendOptions['hashed_directory_perm'])) {
            $backendOptions['hashed_directory_perm'] = 0777 & ~umask();
        }

        $this->_file_perms = $backendOptions['cache_file_perm'];
        $this->_dir_perms = $backendOptions['hashed_directory_perm'];

        // set default dirs
        $this->setPluginsDir([__DIR__ . '/Plugins/', SMARTY_PLUGINS_DIR]);

        $this->debug_tpl = 'file:' . SMARTY_DIR . '/debug.tpl';

        if ($options !== null) {
            $this->setOptions($options);
        }

        if ($eventManager !== null) {
            $this->eventManager = $eventManager;
        }
    }

    /**
     * @param   $charset
     * @return  Enlight_Template_Manager
     */
    public function setCharset($charset = null)
    {
        if ($charset !== null) {
            self::$_CHARSET = $charset;
        }
        mb_internal_encoding(self::$_CHARSET);

        return $this;
    }

    /**
     * @param   array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            // Use public properties
            if (property_exists($this, $key)) {
                $this->$key = $option;
                continue;
            }
            // Add camel case support
            $name = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $key)), '_');
            if (property_exists($this, $name)) {
                $this->$name = $option;
                continue;
            }

            $key = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $this->{'set' . $key}($option);
        }
    }

    /**
     * Set template directories
     *
     * @param string|array $templateDir directory(s) of template sources
     * @param bool $isConfig unused
     * @return Smarty current Smarty instance for chaining
     */
    public function setTemplateDir($templateDir, $isConfig = false)
    {
        $templateDir = (array) $templateDir;
        foreach ($templateDir as $k => $v) {
            $templateDir[$k] = $this->resolveTemplateDir($v, $k);
            if ($templateDir[$k] === false) {
                unset($templateDir[$k]);
            }
        }
        unset($k, $v);

        /**
         * Filter all directories which includes the new shopware themes.
         */
        $themeDirectories = array_filter($templateDir, function ($themeDir) {
            return stripos($themeDir, '/Themes/Frontend/');
        });

        /**
         * If no shopware theme assigned, we have to use the passed inheritance
         */
        if (empty($themeDirectories)) {
            // eg backend
            return parent::setTemplateDir($templateDir);
        }

        /**
         * Select the plugin directories and the bare theme which used
         * as base theme for all extensions
         */
        $pluginDirs = array_diff($templateDir, $themeDirectories);

        $inheritance = $this->buildInheritance($themeDirectories, $pluginDirs);
        $inheritance = $this->unifyDirectories($inheritance);

        return parent::setTemplateDir($inheritance);
    }

    /**
     * Add template directory(s)
     *
     * @param string|array $templateDir directory(s) of template sources
     * @param string       $key          of the array element to assign the template dir to
     * @param null $position
     * @return Smarty current Smarty instance for chaining
     */
    public function addTemplateDir($templateDir, $key = null, $position = null)
    {
        if (is_array($templateDir)) {
            $templateDir = array_reverse($templateDir);
            foreach ($templateDir as $k => $v) {
                $this->addTemplateDir($v, is_int($k) ? null : $k);
            }
            return $this;
        }

        $existingTemplateDir = $this->getTemplateDir();
        if ($position !== self::POSITION_PREPEND) {
            if ($key === null) {
                array_unshift($existingTemplateDir, $templateDir);
            } else {
                $existingTemplateDir = array_merge([$key => $templateDir], $existingTemplateDir);
                $existingTemplateDir[$key] = $templateDir;
            }
        } elseif ($key !== null) {
            $existingTemplateDir[$key] = $templateDir;
        } else {
            $existingTemplateDir[] = $templateDir;
        }

        $this->template_dir = [];
        $this->_processedTemplateDir = [];

        parent::addTemplateDir($existingTemplateDir);

        return $this;
    }

    /**
     * @param   string $templateDir
     * @param   int|null $key
     * @return  string
     */
    public function resolveTemplateDir($templateDir, $key = null)
    {
        if ($this->eventManager !== null) {
            $templateDir = $this->eventManager->filter(
                __CLASS__ . '_ResolveTemplateDir',
                $templateDir,
                ['subject' => $this, 'key' => $key]
            );
        }
        $templateDir = Enlight_Loader::isReadable($templateDir);
        return $templateDir;
    }

    /**
     * @param string[] $inheritance
     * @return string[]
     */
    private function enforceEndingSlash($inheritance)
    {
        return array_map(function ($dir) {
            return rtrim($dir, '/') . '/';
        }, $inheritance);
    }

    /**
     * @param string[] $inheritance
     * @return string[]
     */
    public function unifyDirectories($inheritance)
    {
        $inheritance = $this->enforceEndingSlash($inheritance);
        $inheritance = array_map('Enlight_Loader::realpath', $inheritance);
        $inheritance = array_filter($inheritance);
        $inheritance = array_unique($inheritance);
        return $inheritance;
    }

    /**
     * @param string[] $themeDirectories
     * @param string[] $pluginDirs
     * @return string[]
     */
    public function buildInheritance($themeDirectories, $pluginDirs)
    {
        $themeDirectories = $this->unifyDirectories($themeDirectories);

        $before = [];
        $after = [];
        foreach ($themeDirectories as $dir) {
            $file = $dir . '/Theme.php';
            if (!file_exists($file)) {
                continue;
            }
            require_once $file;

            $parts = explode('/', $dir);
            $name = array_pop($parts);

            $class = "\\Shopware\\Themes\\" . $name . '\\Theme';

            /** @var \Shopware\Components\Theme $theme */
            $theme = new $class();

            if ($theme->injectBeforePlugins()) {
                $before[] = $dir;
            } else {
                $after[] = $dir;
            }
        }
        return array_merge($after, $pluginDirs, $before);
    }

    /**
     * Technically smarty security is enabled, if a security policy is set for the template manager instance. The
     * security policy holds a reference to the template manager instance. When cloning the template manager, the
     * reference of the security_policy to the Smarty instance has be updated to the new cloned Smarty instance.
     *
     * Without doing this, every self::fetch() after a directory was added with self::addTemplateDir(), would lead to a
     * SmartyException with message 'directory [...] not allowed by security setting'. This is because
     * the security_policy still holds a reference to the old Smarty instance that does not know this new directories
     * as template sources.
     *
     * The security_policy is also cloned so other instances of the Enlight_Template_Manager do not get affected.
     */
    public function __clone()
    {
        if ($this->security_policy !== null) {
            $this->security_policy = clone $this->security_policy;
            $this->security_policy->smarty = $this;
        }
    }
}
