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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop as Shop;

/**
 * The Theme\Inheritance class is used to
 * to resolve shop template inheritance in the frontend.
 *
 * The class implements different functions to build configurations,
 * template directories or other resources which should include the
 * theme inheritance.
 *
 * @package Shopware\Components\Theme
 */
class Inheritance
{
    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var Util
     */
    private $util;

    /**
     * @param ModelManager $entityManager
     * @param Util $util
     * @param PathResolver $pathResolver
     */
    function __construct(
        ModelManager $entityManager,
        Util $util,
        PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
        $this->util = $util;
    }

    /**
     * Returns the inheritance hierarchy for the passed theme.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return \Shopware\Models\Shop\Template[]
     */
    public function buildInheritance(Shop\Template $template)
    {
        $hierarchy = array();
        $hierarchy[] = $template;

        if ($template->getParent() instanceof Shop\Template) {
            $hierarchy = array_merge(
                $hierarchy,
                $this->buildInheritance($template->getParent())
            );
        }
        return $hierarchy;
    }

    /**
     * Returns the shop theme configuration for the passed
     * hierarchy.
     * Iterates all passed themes and merges the configuration.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param \Shopware\Models\Shop\Shop $shop
     * @param bool $onlyLess
     * @return array
     */
    public function buildConfig(Shop\Template $template, Shop\Shop $shop, $onlyLess = true)
    {
        $config = $this->getShopConfig($template, $shop, $onlyLess);

        if ($template->getParent() instanceof Shop\Template) {
            $parent = $this->buildConfig($template->getParent(), $shop, $onlyLess);
            $config = array_merge($parent, $config);
        }
        return $config;
    }

    /**
     * Returns all public directories for the passed shop template inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getPublicDirectories(Shop\Template $template)
    {
        $directories = array(
            $this->pathResolver->getPublicDirectory($template)
        );

        if ($template->getParent() instanceof Shop\Template) {
            $directories = array_merge(
                $directories,
                $this->getPublicDirectories($template->getParent())
            );
        }

        return $directories;
    }

    /**
     * This function is used to build the inheritance template hierarchy.
     * The returned directory array will be registered as template directories.
     * The function is used from the ViewRenderer plugin of Enlight.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    public function getTemplateDirectories(Shop\Template $template)
    {
        $directories = array(
            $this->pathResolver->getDirectory($template)
        );

        if ($template->getParent() instanceof Shop\Template) {
            $directories = array_merge(
                $directories,
                $this->getTemplateDirectories($template->getParent())
            );
        }

        return $directories;
    }

    /**
     * Registers all smarty functions for each passed
     * shopware theme.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    public function getSmartyDirectories(Shop\Template $template)
    {
        $directories = array(
            $this->pathResolver->getSmartyDirectory($template)
        );

        if ($template->getParent() instanceof Shop\Template) {
            $directories = array_merge(
                $directories,
                $this->getSmartyDirectories($template->getParent())
            );
        }

        return $directories;
    }

    /**
     * Returns all css files for the passed shop template inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getCssFiles(Shop\Template $template)
    {
        $theme = $this->util->getThemeByTemplate($template);

        $css = $theme->getCss();

        $directory = $this->pathResolver->getPublicDirectory($template);
        foreach ($css as &$file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
        }

        if ($template->getParent() instanceof Shop\Template) {
            $css = array_merge(
                $css,
                $this->getCssFiles($template->getParent())
            );
        }

        return $css;
    }

    /**
     * Returns all javascript files for the passed shop template inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getJavascriptFiles(Shop\Template $template)
    {
        $theme = $this->util->getThemeByTemplate($template);

        $files = $theme->getJavascript();

        $directory = $this->pathResolver->getPublicDirectory($template);

        foreach ($files as &$file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
        }

        if ($template->getParent() instanceof Shop\Template) {
            $files = array_merge(
                $files,
                $this->getJavascriptFiles($template->getParent())
            );
        }

        return $files;
    }

    /**
     * Helper function which returns the theme configuration as
     * key - value array.
     *
     * The element name is used as array key, the shop config
     * as value. If no shop config saved, the value will fallback to
     * the default value.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param \Shopware\Models\Shop\Shop $shop
     * @param bool $onlyLess
     * @return array
     */
    private function getShopConfig(Shop\Template $template, Shop\Shop $shop, $onlyLess = true)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'element.name',
            'values.value',
            'element.defaultValue'
        ));
        $builder->from('Shopware\Models\Shop\TemplateConfig\Element', 'element')
            ->leftJoin('element.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->where('element.templateId = :templateId')
            ->setParameter('shopId', $shop->getId())
            ->setParameter('templateId', $template->getId());

        if ($onlyLess) {
            $builder->andWhere('element.type IN (:type)')
                ->setParameter('type', array(
                    'theme-color-picker',
                    'theme-em-field',
                    'theme-percent-field',
                    'theme-pixel-field',
                    'theme-select-field'
                ));
        }

        $data = $builder->getQuery()->getArrayResult();

        foreach ($data as &$row) {
            if (empty($row['value'])) {
                $row['value'] = $row['defaultValue'];
            }
        }

        //creates a key value array for the configuration.
        $config = array_combine(
            array_column($data, 'name'),
            array_column($data, 'value')
        );

        if ($template->getParent() instanceof Shop\Template) {
            $config = array_merge(
                $this->getShopConfig($template->getParent(), $shop, $onlyLess),
                $config
            );
        }

        return $config;
    }
}