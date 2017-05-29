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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
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

    /**
     * @param Inheritance                 $inheritance
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        Inheritance $inheritance,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->eventManager = $eventManager;
        $this->inheritance = $inheritance;
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     *
     * @throws \Exception
     *
     * @return string[] returns array with absolute javascript files paths
     */
    public function collectJavascriptFiles(Shop\Template $template, Shop\Shop $shop)
    {
        $inheritances = $this->inheritance->buildInheritances($template);

        $files = $this->collectInheritanceJavascript($inheritances['bare']);

        $files = array_merge(
            $files,
            $this->collectPluginJavascript($shop, $template)
        );

        $files = array_merge(
            $files,
            $this->collectInheritanceJavascript($inheritances['custom'])
        );

        $files = $this->eventManager->filter(
            'Theme_Compiler_Collect_Javascript_Files_FilterResult',
            $files,
            [
                'shop' => $shop,
                'template' => $template,
            ]
        );

        return $files;
    }

    /**
     * @param $inheritance
     *
     * @return string[]
     */
    private function collectInheritanceJavascript($inheritance)
    {
        $files = [];
        foreach (array_reverse($inheritance) as $template) {
            $files = array_merge(
                $files,
                $this->inheritance->getTemplateJavascriptFiles($template)
            );
        }

        return $files;
    }

    /**
     * @param Shop\Shop     $shop
     * @param Shop\Template $template
     *
     * @throws \Enlight_Event_Exception
     * @throws \Exception
     *
     * @return string[]
     */
    private function collectPluginJavascript(Shop\Shop $shop, Shop\Template $template)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Javascript',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        foreach ($collection as $file) {
            if (!file_exists($file)) {
                throw new \Exception(
                    sprintf("Some plugin tries to compress a javascript file, but the file %s doesn't exist", $file)
                );
            }
        }

        return $collection->toArray();
    }
}
