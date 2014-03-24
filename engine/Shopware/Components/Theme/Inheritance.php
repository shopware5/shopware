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
 * @category  Shopware
 * @package   Shopware\Components\Theme
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * Contains all valid less fields which
     * can be selected in the shop configuration query.
     * @var array
     */
    private $validLessFields = array(
        'theme-color-picker',
        'theme-em-field',
        'theme-percent-field',
        'theme-pixel-field',
        'theme-select-field'
    );

    /**
     * @param ModelManager $entityManager
     * @param Util $util
     * @param PathResolver $pathResolver
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        ModelManager $entityManager,
        Util $util,
        PathResolver $pathResolver,
        \Enlight_Event_EventManager $eventManager
    )
    {
        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
        $this->util = $util;
        $this->eventManager = $eventManager;
    }

    /**
     * Returns the inheritance hierarchy for the passed theme.
     * This function is used to iterate a shop template inheritance
     * in the frontend to register or collect different resources.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return \Shopware\Models\Shop\Template[]
     */
    public function buildInheritance(Shop\Template $template)
    {
        $hierarchy = $this->buildInheritanceRecursive($template);

        $hierarchy = $this->eventManager->filter('Theme_Inheritance_Built', $hierarchy, array(
            'template' => $template
        ));

        return $hierarchy;
    }

    /**
     * Returns the shop theme configuration for the passed template.
     * The configuration is built recursive to include the configuration
     * of the template inheritance.
     *
     * This function is used for the less compiler and the template registration.
     * The less compiler can only work with valid configuration types like pixel,
     * em or color fields. For this reason the function contains the $lessCompatible
     * parameter which selects only valid less fields.
     * This fields are defined as class property within this class and can be extended
     * over a shopware event.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param \Shopware\Models\Shop\Shop $shop
     * @param bool $lessCompatible
     * @return array
     */
    public function buildConfig(Shop\Template $template, Shop\Shop $shop, $lessCompatible = true)
    {
        $config = $this->buildConfigRecursive($template, $shop, $lessCompatible);

        $config = $this->eventManager->filter('Theme_Inheritance_Config_Created', $config, array(
            'shop' => $shop,
            'template' => $template,
            'lessCompatible' => $lessCompatible
        ));
        return $config;
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
        $directories = $this->getTemplateDirectoriesRecursive($template);

        $directories = $this->eventManager->filter(
            'Theme_Inheritance_Template_Directories_Collected',
            $directories,
            array('template' => $template)
        );

        return $directories;
    }

    /**
     * Returns all smarty directories for the inheritance
     * structure of the passed shop template.
     * The returned directory array will be registered
     * as smarty plugin directory.
     * This allows the developers to implement own smarty plugins.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    public function getSmartyDirectories(Shop\Template $template)
    {
        $directories = $this->getSmartyDirectoriesRecursive($template);

        $directories = $this->eventManager->filter(
            'Theme_Inheritance_Smarty_Directories_Collected',
            $directories,
            array('template' => $template)
        );

        return $directories;
    }

    /**
     * Returns all css files for the passed shop template inheritance.
     * This function contains no event, because the theme compiler
     * which compress css files and compiles less, contains
     * an event to add own css files.
     * See \Shopware\Components\Theme\Compiler::compressPluginCss() function
     * for more information.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getCssFiles(Shop\Template $template)
    {
        return $this->getCssFilesRecursive($template);
    }

    /**
     * Returns all javascript files for the passed shop template inheritance.
     * This function contains no event, because the theme compiler
     * which compress js files, contains an event to add own js files.
     * See \Shopware\Components\Theme\Compiler::compressPluginJavascript() function
     * for more information.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getJavascriptFiles(Shop\Template $template)
    {
        return $this->getJavascriptFilesRecursive($template);
    }

    /**
     * Helper function which collects the defined theme css
     * files for the passed shop template.
     * This function uses a recursive call to collect
     * all files of the template inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    private function getCssFilesRecursive(Shop\Template $template)
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
                $this->getCssFilesRecursive($template->getParent())
            );
        }

        return $css;
    }

    /**
     * Helper function which collects the defined theme javascript
     * files for the passed shop template.
     * This function uses a recursive call to collect
     * all files of the template inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    private function getJavascriptFilesRecursive(Shop\Template $template)
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
                $this->getJavascriptFilesRecursive($template->getParent())
            );
        }

        return $files;
    }

    /**
     * Helper function which creates an array with all shop templates
     * inside which should be included in the frontend inheritance.
     *
     * @param Shop\Template $template
     * @return array
     */
    private function buildInheritanceRecursive(Shop\Template $template)
    {
        $hierarchy = array($template);

        if ($template->getParent() instanceof Shop\Template) {
            $hierarchy = array_merge(
                $hierarchy,
                $this->buildInheritanceRecursive($template->getParent())
            );
        }
        return $hierarchy;
    }

    /**
     * Helper function which returns all template directories for the
     * passed templates.
     * The function returns an array with all template directories
     * for the inheritance of the passed template.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    private function getTemplateDirectoriesRecursive(Shop\Template $template)
    {
        $directories = array(
            $this->pathResolver->getDirectory($template)
        );

        if ($template->getParent() instanceof Shop\Template) {
            $directories = array_merge(
                $directories,
                $this->getTemplateDirectoriesRecursive($template->getParent())
            );
        }

        return $directories;
    }

    /**
     * Helper function which returns all smarty directories for the
     * passed templates.
     * The function returns an array with all smarty directories
     * for the inheritance of the passed template.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    private function getSmartyDirectoriesRecursive(Shop\Template $template)
    {
        $directories = array(
            $this->pathResolver->getSmartyDirectory($template)
        );

        if ($template->getParent() instanceof Shop\Template) {
            $directories = array_merge(
                $directories,
                $this->getSmartyDirectoriesRecursive($template->getParent())
            );
        }

        return $directories;
    }

    /**
     * Helper function which builds the theme configuration key value array.
     * The configuration is built recursive to include the configuration
     * of the template inheritance.
     *
     * This function is used for the less compiler and the template registration.
     * The less compiler can only work with valid configuration types like pixel,
     * em or color fields. For this reason the function contains the $lessCompatible
     * parameter which selects only valid less fields.
     * This fields are defined as class property within this class and can be extended
     * over a shopware event.
     *
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @param bool $lessCompatible
     * @return array
     */
    private function buildConfigRecursive(Shop\Template $template, Shop\Shop $shop, $lessCompatible = true)
    {
        $config = $this->getShopConfig($template, $shop, $lessCompatible);

        if ($template->getParent() instanceof Shop\Template) {
            $parent = $this->buildConfigRecursive(
                $template->getParent(),
                $shop,
                $lessCompatible
            );
            $config = array_merge($parent, $config);
        }
        return $config;
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
     * @param bool $lessCompatible
     * @return array
     */
    private function getShopConfig(Shop\Template $template, Shop\Shop $shop, $lessCompatible = true)
    {
        $builder = $this->getShopConfigQuery($template, $lessCompatible);

        $builder->setParameter('templateId', $template->getId())
            ->setParameter('shopId', $shop->getId());

        $data = $builder->getQuery()->getArrayResult();

        foreach ($data as &$row) {
            if (empty($row['value'])) {
                $row['value'] = $row['defaultValue'];
            }
        }

        //creates a key value array for the configuration.
        return array_combine(
            array_column($data, 'name'),
            array_column($data, 'value')
        );
    }

    /**
     * Returns the query builder object to select the theme configuration for the
     * current shop.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param $lessCompatible
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    private function getShopConfigQuery(Shop\Template $template, $lessCompatible)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'element.name',
            'values.value',
            'element.defaultValue'
        ));

        $builder->from('Shopware\Models\Shop\TemplateConfig\Element', 'element')
            ->leftJoin('element.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->where('element.templateId = :templateId');

        if ($lessCompatible) {
            $validLessFields = $this->validLessFields;

            $validLessFields = $this->eventManager->filter('Theme_Inheritance_Collect_Valid_Less_Fields', $validLessFields, array(
                'template' => $template
            ));

            $builder->andWhere('element.type IN (:type)')
                ->setParameter('type', $validLessFields);
        }

        $this->eventManager->notify('Theme_Inheritance_Shop_Query_Built', array(
            'builder' => $builder,
            'template' => $template,
            'lessCompatible' => $lessCompatible
        ));

        return $builder;
    }
}