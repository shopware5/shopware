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

namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Shop;

class JavascriptCollector
{
    /**
     * @var Inheritance
     */
    private $inheritance;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    public function __construct(
        Inheritance $inheritance,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
    }

    /**
     * @throws \Exception
     *
     * @return string[] returns array with absolute javascript files paths
     */
    public function collectJavascriptFiles(Shop\Template $template, Shop\Shop $shop)
    {
        $inheritances = $this->inheritance->buildInheritances($template);

        $definitions = $this->collectInheritanceJavascript($inheritances['bare']);

        $definitions = array_merge(
            $definitions,
            $this->collectPluginJavascript($shop, $template)
        );

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceJavascript($inheritances['custom'])
        );

        $discardJs = [];

        for ($i = count($definitions) - 1; $i >= 0; --$i) {
            $definition = $definitions[$i];

            $theme = $definition->getTheme();

            // Not all definitions are associated with a specific theme (e.g. plugins)
            if ($theme) {
                $themeClassName = get_class($theme);
                $discardJs = array_merge($discardJs, $theme->getDiscardedJavascriptThemes());

                if (in_array($themeClassName, $discardJs)) {
                    $definition->setFiles([]);
                }
            }
        }

        $files = $this->eventManager->filter(
            'Theme_Compiler_Collect_Javascript_Files_FilterResult',
            $this->getUniqueFiles($definitions),
            [
                'shop' => $shop,
                'template' => $template,
            ]
        );

        return $files;
    }

    /**
     * @param array $inheritance
     *
     * @throws \Exception
     *
     * @return JavascriptDefinition[]
     */
    private function collectInheritanceJavascript($inheritance)
    {
        $definitions = [];
        foreach (array_reverse($inheritance) as $template) {
            $definition = new JavascriptDefinition();

            $definition->setTheme($this->inheritance->getTheme($template));
            $definition->setFiles($this->inheritance->getTemplateJavascriptFiles($template));

            $definitions[] = $definition;
        }

        return $definitions;
    }

    /**
     * @throws \Enlight_Event_Exception
     * @throws \Exception
     *
     * @return JavascriptDefinition[]
     */
    private function collectPluginJavascript(Shop\Shop $shop, Shop\Template $template)
    {
        $collection = new ArrayCollection();
        $definition = new JavascriptDefinition();

        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Javascript',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(
                    sprintf('Some plugin tries to compress a javascript file, but the file %s doesn\'t exist', $file)
                );
            }
        }

        $definition->setFiles($collection->toArray());

        return [$definition];
    }

    /**
     * @param JavascriptDefinition[] $definitions
     *
     * @return array
     */
    private function getUniqueFiles(array $definitions)
    {
        $files = [];
        /** @var JavascriptDefinition $definition */
        foreach ($definitions as $definition) {
            $files = array_merge($files, $definition->getFiles());
        }

        return $files;
    }
}
