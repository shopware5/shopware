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
use Shopware\Components\Theme\LessDefinition;
use Symfony\Component\Finder\Finder;

class ResourceSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    /**
     * @param string $pluginPath
     */
    public function __construct($pluginPath)
    {
        $this->pluginPath = $pluginPath;
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
