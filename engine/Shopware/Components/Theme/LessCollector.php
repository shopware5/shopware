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
class LessCollector
{
    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var Inheritance
     */
    private $inheritance;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param PathResolver                $pathResolver
     * @param Inheritance                 $inheritance
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        PathResolver $pathResolver,
        Inheritance $inheritance,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->pathResolver = $pathResolver;
        $this->inheritance = $inheritance;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     *
     * @return LessDefinition[]
     */
    public function collectLessDefinitions(Shop\Template $template, Shop\Shop $shop)
    {
        $inheritances = $this->inheritance->buildInheritances($template);

        $definitions = $this->collectInheritanceLess($inheritances['bare']);

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceCss($inheritances['bare'])
        );

        $definitions = array_merge(
            $definitions,
            $this->collectPluginLess($template, $shop)
        );

        $definitions = array_merge(
            $definitions,
            $this->collectPluginCss($template, $shop)
        );

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceLess($inheritances['custom'])
        );

        $definitions = array_merge(
            $definitions,
            $this->collectInheritanceCss($inheritances['custom'])
        );

        $definitions = $this->eventManager->filter(
            'Theme_Compiler_Collect_Less_Definitions_FilterResult',
            $definitions,
            [
                'shop' => $shop,
                'template' => $template,
            ]
        );

        return $definitions;
    }

    /**
     * @param $inheritance
     *
     * @return LessDefinition[]
     */
    private function collectInheritanceLess($inheritance)
    {
        $definitions = [];
        //use array_reverse to compile the bare themes first.
        foreach (array_reverse($inheritance) as $shopTemplate) {
            $definition = new LessDefinition();

            $definition->setImportDirectory(
                $this->pathResolver->getPublicDirectory($shopTemplate)
            );

            $definition->setFiles([
                $this->pathResolver->getThemeLessFile($shopTemplate),
            ]);

            $definitions[] = $definition;
        }

        return $definitions;
    }

    /**
     * @param $inheritance
     *
     * @return LessDefinition[]
     */
    private function collectInheritanceCss($inheritance)
    {
        $files = [];
        foreach (array_reverse($inheritance) as $template) {
            $files = array_merge(
                $files,
                $this->inheritance->getTemplateCssFiles($template)
            );
        }
        if (empty($files)) {
            return [];
        }

        $definition = new LessDefinition();
        $definition->setFiles($files);

        return [$definition];
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     *
     * @throws \Enlight_Event_Exception
     *
     * @return LessDefinition[]
     */
    private function collectPluginLess(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Less',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        return $collection->toArray();
    }

    /**
     * @param Shop\Template $template
     * @param Shop\Shop     $shop
     *
     * @throws \Enlight_Event_Exception
     *
     * @return LessDefinition[]
     */
    private function collectPluginCss(Shop\Template $template, Shop\Shop $shop)
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Theme_Compiler_Collect_Plugin_Css',
            $collection,
            ['shop' => $shop, 'template' => $template]
        );

        if (count($collection) <= 0) {
            return [];
        }

        $definition = new LessDefinition();
        $definition->setFiles($collection->toArray());

        return [$definition];
    }
}
