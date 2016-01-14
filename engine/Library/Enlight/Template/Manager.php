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
 * @package    Enlight_Template
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

require_once 'Smarty/Smarty.class.php';

/**
 * The Enlight_Template_Manager is an extension of smarty to manually set the config in the class constructor.
 *
 * With the Enlight_Template_Manager it is not only possible to overwrite template files,
 * it is also possible to overwrite all the individual blocks within the template.
 *
 * @category   Enlight
 * @package    Enlight_Template
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
    public $template_class = 'Enlight_Template_Default';

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * Class constructor, initializes basic smarty properties:
     * Template, compile, plugin, cache and config directory.
     *
     * @param   null|array|Enlight_Config $options
     */
    public function __construct($options = null)
    {
        // self pointer needed by some other class methods
        $this->smarty = $this;

        $this->start_time = microtime(true);

        $this->_file_perms = 0666 & ~umask();
        $this->_dir_perms = 0777 & ~umask();

        // set default dirs
        $this->setTemplateDir('.' . DS . 'templates' . DS)
            ->setCompileDir('.' . DS . 'templates_c' . DS)
            ->setPluginsDir(array(dirname(__FILE__) . '/Plugins/', SMARTY_PLUGINS_DIR))
            ->setCacheDir('.' . DS . 'cache' . DS)
            ->setConfigDir('.' . DS . 'configs' . DS);

        $this->debug_tpl = 'file:' . SMARTY_DIR . '/debug.tpl';

        $this->setOptions($options);
        $this->setCharset();
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
     * @param   array|Enlight_Config $options
     * @return  Enlight_Template_Manager
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

        /**
         * Filter all directories which includes the new shopware themes.
         */
        $themeDirectories = array_filter($template_dir, function ($themeDir) {
            return (stripos($themeDir, '/Themes/Frontend/'));
        });

        /**
         * If no shopware theme assigned, we have to use the passed inheritance
         */
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
     * @param string|array $template_dir directory(s) of template sources
     * @param string       $key          of the array element to assign the template dir to
     * @param null $position
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
                $_template_dir = array_merge(array($key => $template_dir), $_template_dir);
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
                array('subject' => $this, 'key' => $key)
            );
        }
        $templateDir = Enlight_Loader::isReadable($templateDir);
        return $templateDir;
    }

    /**
     * @param   $eventManager
     * @return  Enlight_Template_Manager
     */
    public function setEventManager($eventManager)
    {
        //Enlight_Template_Manager_AddTemplateDir
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * @param string[] $inheritance
     * @return string[]
     */
    private function enforceEndingSlash($inheritance)
    {
        return array_map(function ($dir) {
            $dir = rtrim($dir, '/') . '/';
            return $dir;
        }, $inheritance);
    }

    /**
     * @param string[] $inheritance
     * @return string[]
     */
    public function unifyDirectories($inheritance)
    {
        $inheritance = $this->enforceEndingSlash($inheritance);
        $inheritance = array_map('realpath', $inheritance);
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
}
