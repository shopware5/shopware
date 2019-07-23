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
 *
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
    public $template_class = 'Enlight_Template_Default';

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * Class constructor, initializes basic smarty properties:
     * Template, compile, plugin, cache and config directory.
     *
     * @param array|Enlight_Config|null $options
     * @param array                     $backendOptions
     */
    public function __construct($options = null, $backendOptions = [])
    {
        // Self pointer needed by some other class methods
        $this->smarty = $this;

        $this->start_time = microtime(true);

        if (!isset($backendOptions['cache_file_perm'])) {
            $backendOptions['cache_file_perm'] = 0666 & ~umask();
        }

        if (is_string($backendOptions['cache_file_perm'])) {
            $backendOptions['cache_file_perm'] = octdec($backendOptions['cache_file_perm']);
        }

        if (!isset($backendOptions['hashed_directory_perm'])) {
            $backendOptions['hashed_directory_perm'] = 0777 & ~umask();
        }

        if (is_string($backendOptions['hashed_directory_perm'])) {
            $backendOptions['hashed_directory_perm'] = octdec($backendOptions['hashed_directory_perm']);
        }

        $this->_file_perms = $backendOptions['cache_file_perm'];
        $this->_dir_perms = $backendOptions['hashed_directory_perm'];

        // Set default dirs
        $this->setTemplateDir('.' . DS . 'templates' . DS)
            ->setCompileDir('.' . DS . 'templates_c' . DS)
            ->setPluginsDir([dirname(__FILE__) . '/Plugins/', SMARTY_PLUGINS_DIR])
            ->setCacheDir('.' . DS . 'cache' . DS)
            ->setConfigDir('.' . DS . 'configs' . DS);

        $this->debug_tpl = 'file:' . SMARTY_DIR . '/debug.tpl';

        $this->setOptions($options);
        $this->setCharset();
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
        parent::__clone();

        if ($this->security_policy !== null) {
            $this->security_policy = clone $this->security_policy;
            $this->security_policy->smarty = $this;
        }
    }

    /**
     * @param string $charset
     *
     * @return Enlight_Template_Manager
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
     * @param array|Enlight_Config $options
     *
     * @return Enlight_Template_Manager
     */
    public function setOptions($options = null)
    {
        if ($options === null) {
            return $this;
        }

        if ($options instanceof Enlight_Config) {
            $options = $options->toArray();
        }

        foreach ($options as $key => $option) {
            $key = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $this->{'set' . $key}($option);
        }

        return $this;
    }

    /**
     * Set template directory
     *
     * @param string|array $template_dir directory(s) of template sources
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir)
    {
        $template_dir = (array) $template_dir;

        foreach ($template_dir as $k => $v) {
            $template_dir[$k] = $this->resolveTemplateDir($v, $k);
            if ($template_dir[$k] === false) {
                unset($template_dir[$k]);
            }
        }

        // Filter all directories which includes the new shopware themes
        $themeDirectories = array_filter($template_dir, static function ($themeDir) {
            return stripos($themeDir, '/Themes/Frontend/');
        });

        // If no shopware theme assigned, we have to use the passed inheritance
        if (empty($themeDirectories)) {
            return parent::setTemplateDir($template_dir);
        }

        /**
         * Select the plugin directories and the bare theme which used
         * as base theme for all extensions
         */
        $pluginDirs = array_diff($template_dir, $themeDirectories);

        $inheritance = $this->buildInheritance($themeDirectories, $pluginDirs);

        $inheritance = $this->unifyDirectories($inheritance);

        return parent::setTemplateDir($inheritance);
    }

    /**
     * Add template directory(s)
     *
     * @param string|string[] $template_dir directory(s) of template sources
     * @param string          $key          of the array element to assign the template dir to
     * @param string|null     $position
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function addTemplateDir($template_dir, $key = null, $position = null)
    {
        if (is_array($template_dir)) {
            foreach ($template_dir as $k => $v) {
                $this->addTemplateDir($v, is_int($k) ? null : $k);
            }

            return $this;
        }
        $_template_dir = $this->getTemplateDir();
        if ($position === self::POSITION_PREPEND) {
            if ($key === null) {
                array_unshift($_template_dir, $template_dir);
            } else {
                $_template_dir = array_merge([$key => $template_dir], $_template_dir);
                $_template_dir[$key] = $template_dir;
            }
        } elseif ($key !== null) {
            $_template_dir[$key] = $template_dir;
        } else {
            $_template_dir[] = $template_dir;
        }
        $this->setTemplateDir($_template_dir);

        return $this;
    }

    /**
     * @param string   $templateDir
     * @param int|null $key
     *
     * @return string
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
     * @param \Enlight_Event_EventManager $eventManager
     *
     * @return Enlight_Template_Manager
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * @param string[] $inheritance
     *
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
     *
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

            $class = '\\Shopware\\Themes\\' . $name . '\\Theme';

            /** @var \Shopware\Components\Theme $theme */
            $theme = new $class();

            if ($theme->injectBeforePlugins()) {
                $before[] = $dir;
            } else {
                $after[] = $dir;
            }
        }

        $folders = array_merge($after, $pluginDirs, $before);

        if ($this->eventManager) {
            $folders = $this->eventManager->filter('Enlight_Template_Manager_FilterBuildInheritance', $folders, [
                'themeDirectories' => $themeDirectories,
                'pluginDirectories' => $pluginDirs,
            ]);
        }

        return $folders;
    }

    /**
     * @param string[] $inheritance
     *
     * @return string[]
     */
    private function enforceEndingSlash($inheritance)
    {
        return array_map(function ($dir) {
            $dir = rtrim($dir, '/') . '/';

            return $dir;
        }, $inheritance);
    }
}
