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

namespace Shopware\Components\Plugin;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Enlight_Event_EventArgs;
use Enlight_Exception;
use Shopware\Components\Theme\LessDefinition;
use Symfony\Component\Finder\Finder;

class ResourceSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    private $loadViewsDirectory = false;

    /**
     * @param string $pluginPath
     */
    public function __construct($pluginPath, bool $loadViewsDirectory)
    {
        $this->pluginPath = $pluginPath;
        $this->loadViewsDirectory = $loadViewsDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Less' => 'onCollectLess',
            'Theme_Compiler_Collect_Plugin_Css' => 'onCollectCss',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascript',
            'Theme_Inheritance_Template_Directories_Collected' => 'onRegisterTemplate',
            'Enlight_Controller_Action_PreDispatch_Backend' => 'onRegisterControllerTemplate',
        ];
    }

    /**
     * @return ArrayCollection|null
     */
    public function onCollectJavascript()
    {
        $files = $this->collectResourceFiles($this->pluginPath, 'js');
        if ($files) {
            return new ArrayCollection($files);
        }

        return null;
    }

    /**
     * @return ArrayCollection|null
     */
    public function onCollectCss()
    {
        $files = $this->collectResourceFiles($this->pluginPath, 'css');
        if ($files) {
            return new ArrayCollection($files);
        }

        return null;
    }

    /**
     * @return LessDefinition|null
     */
    public function onCollectLess()
    {
        $file = $this->pluginPath . '/Resources/frontend/less/all.less';
        if (!is_file($file)) {
            return null;
        }

        return new LessDefinition(
            [],
            [$file]
        );
    }

    public function onRegisterTemplate(Enlight_Event_EventArgs $args): void
    {
        if (!$this->loadViewsDirectory) {
            return;
        }

        $viewsDirectory = $this->pluginPath . '/Resources/views';

        if (!(@is_dir($viewsDirectory))) {
            return;
        }

        $templates = (array) $args->getReturn();

        if (!in_array($viewsDirectory, $templates, true)) {
            $templates[] = $viewsDirectory;
            $args->setReturn($templates);
        }
    }

    public function onRegisterControllerTemplate(Enlight_Controller_ActionEventArgs $args): void
    {
        if (!$this->loadViewsDirectory) {
            return;
        }

        $viewsDirectory = $this->pluginPath . '/Resources/views';

        if (!(@is_dir($viewsDirectory))) {
            return;
        }

        $controller = $args->getSubject();

        try {
            if (($view = $controller->View()) !== null) {
                $view->Template()->Engine()->addTemplateDir($viewsDirectory);
            }
        } catch (Enlight_Exception $ignored) {
        }
    }

    /**
     * @param string $baseDir resource base directory
     * @param string $type    `css` or `js`
     *
     * @return string[]
     */
    private function collectResourceFiles($baseDir, $type)
    {
        $directory = $baseDir . '/Resources/frontend/' . $type;
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $finder = new Finder();
        $finder->files()->name('*.' . $type)->in($directory);
        $finder->sortByName();

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
