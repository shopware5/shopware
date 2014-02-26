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
use Shopware\Components\DependencyInjection\Container;

/**
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Compiler {

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @Event Enlight_Plugins_ViewRenderer_PreRender
     */
    public function beforeRender(\Enlight_Event_EventArgs $args)
    {
        /**@var $request \Enlight_Controller_Request_RequestHttp*/
        $request = $args->getRequest();

        //only compile if it's a frontend request
        if (!$this->isFrontendRequest($request)) {
            return;
        }

        /**@var $shop \Shopware\Models\Shop\Shop*/
        $shop = $this->container->get('shop');

        if ($shop->getTemplate()->getVersion() < 3) {
            return;
        }

        /**@var $manager Manager*/
        $manager = $this->container->get('theme_manager');

        $hierarchy = $manager->getInheritanceHierarchy($shop->getTemplate());

        $config = $manager->getHierarchyConfig($hierarchy, $shop);

        /**@var $less \lessc*/
        $less = $this->container->get('less_compiler');

        $less->setVariables($config);

        $content = '';

        foreach($hierarchy as $template) {
            $instance = $manager->getThemeByTemplate($template);

            $files = $instance->getLess();

            if (empty($files)) continue;

            $directory = $manager->getThemeLessDirectory($template);

            $content = '';
            foreach($files as &$file) {
                $path = $directory . DIRECTORY_SEPARATOR . $file;
                if (!file_exists($path)) {
                    throw new \Exception(sprintf(
                        "Tried to compile %s file which doesn't exist",
                        $path
                    ));
                }
                $content .= file_get_contents($path) . "\n";
            }
        }

        $lessFile = 'cache/output' . $shop->getId()  . '.less';
        $cssFile = 'cache/output' . $shop->getId()  . '.css';

        $before = null;
        if (file_exists($lessFile)) {
            $before = file_get_contents($lessFile);
        }

        if ($before != $content) {
            file_put_contents($lessFile, $content);
        }

        if (file_exists($lessFile)) {
            $less->checkedCompile($lessFile, $cssFile);
        }
    }

    /**
     * @param \Enlight_Controller_Request_RequestHttp $request
     * @return bool
     */
    private function isFrontendRequest(\Enlight_Controller_Request_RequestHttp $request)
    {
        $module = strtolower($request->getModuleName());

        return (bool) in_array($module, array('frontend', 'widgets'));
    }
}