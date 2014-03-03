<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

/**
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Compiler
{
    /**
     * Compiles all required resources for the frontend template
     *
     * @param Template $template
     * @param Shop $shop
     */
    public function compile(Template $template, Shop $shop)
    {
        /**@var $manager Manager */
        $manager = $this->container->get('theme_manager');

        $hierarchy = $manager->getInheritanceHierarchy($template);
        $config = $manager->getHierarchyConfig($hierarchy, $shop);

        $compiler = $this->container->get('less_compiler');

        $compiler->setFormatter("compressed");
        $compiler->setImportDir(null);

        $eventArguments = new \Enlight_Event_EventArgs(array(
            'subject' => $this,
            'container' => $this->container,
            'themeManager' => $manager,
            'themeConfig' => $config,
            'template' => $template,
            'shop' => $shop
        ));

        $this->addPluginLessConfig($compiler, $eventArguments);

        $this->compileThemeLess($template, $shop, $hierarchy, $config);

        $this->compilePluginLess($template, $shop, $config);
    }


    /**
     * Collects all theme .less files and compiles them into one compressed .css file.
     * If the .css file already exists the .less file won't be compiled
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param Shop $shop
     * @param Template[] $hierarchy
     * @param array $config
     */
    protected function compileThemeLess(Template $template, Shop $shop, array $hierarchy, array $config)
    {
        $cssFile = $this->getCompilerDirectory() . 'theme' . $shop->getId() . '.css';
        $lessFile = $this->getCompilerDirectory() . 'theme' . $shop->getId() . '.less';

//        // only compile if the css file no more exists
//        if (file_exists($cssFile)) {
//            return;
//        }

        /**@var $manager Manager */
        $manager = $this->container->get('theme_manager');

        /**@var $compiler \lessc */
        $compiler = $this->container->get('less_compiler');

        $compiler->setVariables($config);

        $content = '';

        foreach ($hierarchy as $template) {
            $instance = $manager->getThemeByTemplate($template);
            $files = $instance->getLess();

            // no less files in theme configured? skip this theme
            if (empty($files)) continue;

            // returns the frontend/_public/src/less directory of the theme
            $directory = $manager->getLessDirectory($template);

            // adds the frontend/_public directory as import directory.
            $public = $manager->getPublicDirectory($template);

            $compiler->addImportDir($public);

            $content .= $this->concatenateFileContents($directory, $files);
        }

        file_put_contents($lessFile, $content);

        $compiler->compileFile($lessFile, $cssFile);
    }

    /**
     * @param Template $template
     * @param Shop $shop
     * @param array $config
     * @throws \Exception
     */
    protected function compilePluginLess(Template $template, Shop $shop, array $config)
    {
        $cssFile = $this->getCompilerDirectory() . 'theme' . $shop->getId() . '.css';

//        // only compile if the css file no more exists
//        if (file_exists($cssFile)) {
//            return;
//        }

        /**@var $compiler \lessc */
        $compiler = $this->container->get('less_compiler');
        $compiler->setVariables($config);

        /**@var $manager Manager */
        $manager = $this->container->get('theme_manager');

        $eventArguments = new \Enlight_Event_EventArgs(array(
            'subject' => $this,
            'container' => $this->container,
            'themeManager' => $manager,
            'themeConfig' => $config,
            'template' => $template,
            'shop' => $shop
        ));

        $this->addPluginImportDirectories($compiler, $eventArguments);

        $this->addPluginLessFiles($compiler, $eventArguments, $shop);
    }

    /**
     * Helper function which collects the less configuration of all plugins
     * which registered on the `Theme_Compiler_Collect_Plugin_Less_Config` event.
     *
     * @param \lessc $compiler
     * @param \Enlight_Event_EventArgs $eventArguments
     * @throws \Exception
     */
    private function addPluginLessConfig(\lessc $compiler, \Enlight_Event_EventArgs $eventArguments)
    {
        $collection = new ArrayCollection();

        $this->container->get('events')->collect(
            'Theme_Compiler_Collect_Plugin_Less_Config',
            $collection,
            $eventArguments
        );

        foreach ($collection as $config) {
            if (!is_array($config)) {
                throw new \Exception("The passed plugin less config isn't an array!");
            }
            $compiler->setVariables($config);
        }
    }

    /**
     * Helper function which collects the less import directories of all plugins
     * which registered on the `Theme_Compiler_Collect_Plugin_Import_Directories` event.
     *
     * @param \lessc $compiler
     * @param \Enlight_Event_EventArgs $eventArguments
     * @throws \Exception
     */
    private function addPluginImportDirectories(\lessc $compiler, \Enlight_Event_EventArgs $eventArguments)
    {
        $importDirectories = new ArrayCollection();
        $this->container->get('events')->collect(
            'Theme_Compiler_Collect_Plugin_Import_Directories',
            $importDirectories,
            $eventArguments
        );

        foreach ($importDirectories as $directory) {
            if (!file_exists($directory)) {
                throw new \Exception(sprintf(
                    "Tried to add %s as less import directory, but the directory doesn't exist",
                    $directory
                ));
            }
            $compiler->addImportDir($directory);
        }
    }

    /**
     * Helper function which collects all plugin less files which
     * registered on the `Theme_Compiler_Collect_Plugin_Less` event.
     * The function concatenate the files and compiles them over the
     * \lessc compiler into the "plugin_shopId.css" file.
     *
     * @param \lessc $compiler
     * @param \Enlight_Event_EventArgs $eventArguments
     * @param Shop $shop
     */
    private function addPluginLessFiles(\lessc $compiler, \Enlight_Event_EventArgs $eventArguments, Shop $shop)
    {
        $cssFile = $this->getCompilerDirectory() . 'plugin' . $shop->getId() . '.css';
        $lessFile = $this->getCompilerDirectory() . 'plugin' . $shop->getId() . '.less';

        $files = new ArrayCollection();
        $this->container->get('events')->collect(
            'Theme_Compiler_Collect_Plugin_Less',
            $files,
            $eventArguments
        );

        $content = $this->concatenateFileContents('', $files->getValues());

        file_put_contents($lessFile, $content);

        $compiler->compileFile($lessFile, $cssFile);
    }

    /**
     * @param $directory
     * @param array $files
     * @return string
     * @throws \Exception
     */
    private function concatenateFileContents($directory, array $files)
    {
        $content = '';
        foreach ($files as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($path)) {
                throw new \Exception(sprintf(
                    "Tried to compile %s file which doesn't exist",
                    $path
                ));
            }

            $content .= file_get_contents($path) . "\n";
        }
        return $content;
    }


    /**
     * @param \Enlight_Controller_Request_RequestHttp $request
     * @return bool
     */
    private function isFrontendRequest(\Enlight_Controller_Request_RequestHttp $request)
    {
        $module = strtolower($request->getModuleName());

        return (bool)in_array($module, array(
            'frontend',
            'widgets'
        ));
    }

    /**
     * Returns the compiler directory which used to
     * create the compiled less files.
     * @return string
     */
    private function getCompilerDirectory()
    {
        return 'cache' . DIRECTORY_SEPARATOR;
    }

}
