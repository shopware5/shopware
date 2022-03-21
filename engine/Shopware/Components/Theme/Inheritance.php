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

use Doctrine\DBAL\Connection;
use Enlight_Event_EventManager;
use Enlight_Event_Exception;
use Exception;
use PDO;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

/**
 * The Theme\Inheritance class is used to
 * to resolve shop template inheritance in the frontend.
 *
 * The class implements different functions to build configurations,
 * template directories or other resources which should include the
 * theme inheritance.
 */
class Inheritance
{
    private PathResolver $pathResolver;

    private ModelManager $entityManager;

    private Util $util;

    private Enlight_Event_EventManager $eventManager;

    private MediaServiceInterface $mediaService;

    public function __construct(
        ModelManager $entityManager,
        Util $util,
        PathResolver $pathResolver,
        Enlight_Event_EventManager $eventManager,
        MediaServiceInterface $mediaService
    ) {
        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
        $this->util = $util;
        $this->eventManager = $eventManager;
        $this->mediaService = $mediaService;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function buildInheritances(Template $template)
    {
        $hierarchy = $this->buildInheritanceRecursive($template);

        $util = $this->util;
        $bare = array_filter($hierarchy, function (Template $template) use ($util) {
            $theme = $util->getThemeByTemplate($template);

            return $theme->injectBeforePlugins();
        });

        $custom = array_filter($hierarchy, function (Template $template) use ($util) {
            $theme = $util->getThemeByTemplate($template);

            return !$theme->injectBeforePlugins();
        });

        return [
            'full' => $hierarchy,
            'bare' => array_values($bare),
            'custom' => array_values($custom),
        ];
    }

    /**
     * Returns the inheritance-path of a given shop theme as an array of
     * template-names. The names are sorted descending in priority.
     *
     * @return string[]
     */
    public function getInheritancePath(Template $template)
    {
        $hierarchy = $this->buildInheritanceRecursive($template);
        $path = [];

        foreach ($hierarchy as $hierarchicalTemplate) {
            $path[] = $hierarchicalTemplate->getTemplate();
        }

        return $path;
    }

    /**
     * Returns the shop theme configuration for the passed template.
     * The configuration is built recursive to include the configuration
     * of the template inheritance.
     *
     * This function is used for the less compiler and the template registration.
     * The less compiler can only work with valid configuration types like pixel,
     * em or color fields. For this reason the function contains the $lessCompatible
     * parameter which selects only valid "less" fields.
     * These fields are defined as class property within this class and can be extended
     * over a shopware event.
     *
     * @param bool $lessCompatible
     *
     * @throws Enlight_Event_Exception
     *
     * @return array
     */
    public function buildConfig(Template $template, Shop $shop, $lessCompatible = true)
    {
        $templates = $this->fetchTemplates();

        $templates = $this->filterAffectedTemplates($template->getId(), $templates);

        $shopId = $shop->getMain() ? $shop->getMain()->getId() : $shop->getId();

        $configs = $this->getConfigs(
            array_keys($templates),
            $shopId,
            $lessCompatible
        );

        $config = $this->buildConfigRecursive(
            $template->getId(),
            $templates,
            $configs,
            $lessCompatible
        );

        return $this->eventManager->filter('Theme_Inheritance_Config_Created', $config, [
            'shop' => $shop,
            'template' => $template,
            'lessCompatible' => $lessCompatible,
        ]);
    }

    /**
     * This function is used to build the inheritance template hierarchy.
     * The returned directory array will be registered as template directories.
     * The function is used from the ViewRenderer plugin of Enlight.
     *
     * @throws Enlight_Event_Exception
     *
     * @return array
     */
    public function getTemplateDirectories(Template $template)
    {
        $directories = $this->getTemplateDirectoriesRecursive(
            $template->getId(),
            $this->fetchTemplates()
        );

        return $this->eventManager->filter(
            'Theme_Inheritance_Template_Directories_Collected',
            $directories,
            ['template' => $template]
        );
    }

    /**
     * Returns all smarty directories for the inheritance
     * structure of the passed shop template.
     * The returned directory array will be registered
     * as smarty plugin directory.
     * This allows the developers to implement own smarty plugins.
     *
     * @throws Enlight_Event_Exception
     *
     * @return array
     */
    public function getSmartyDirectories(Template $template)
    {
        $directories = $this->getTemplateDirectoriesRecursive(
            $template->getId(),
            $this->fetchTemplates()
        );

        $directories = array_map(function ($dir) {
            return implode(DIRECTORY_SEPARATOR, [$dir, '_private', 'smarty']) . DIRECTORY_SEPARATOR;
        }, $directories);

        return $this->eventManager->filter(
            'Theme_Inheritance_Smarty_Directories_Collected',
            $directories,
            ['template' => $template]
        );
    }

    /**
     * @throws Exception
     *
     * @return string[]
     */
    public function getTemplateCssFiles(Template $template)
    {
        $css = $this->util->getThemeByTemplate($template)->getCss();

        $directory = $this->pathResolver->getPublicDirectory($template);
        foreach ($css as &$file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
        }

        return $css;
    }

    /**
     * @throws Exception
     *
     * @return string[]
     */
    public function getTemplateJavascriptFiles(Template $template)
    {
        $files = $this->util->getThemeByTemplate($template)->getJavascript();

        $directory = $this->pathResolver->getPublicDirectory($template);

        foreach ($files as &$file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
        }

        return $files;
    }

    /**
     * @throws Exception
     *
     * @return Theme
     */
    public function getTheme(Template $template)
    {
        return $this->util->getThemeByTemplate($template);
    }

    /**
     * Helper function which creates an array with all shop templates
     * inside which should be included in the frontend inheritance.
     */
    private function buildInheritanceRecursive(Template $template): array
    {
        $hierarchy = [$template];

        if ($template->getParent() instanceof Template) {
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
     * @param array[] $templates
     */
    private function getTemplateDirectoriesRecursive(int $templateId, array $templates): array
    {
        $template = $templates[$templateId];

        $directories = [
            $this->pathResolver->getDirectoryByArray($template),
        ];

        if ($template['parent_id'] !== null) {
            $directories = array_merge(
                $directories,
                $this->getTemplateDirectoriesRecursive((int) $template['parent_id'], $templates)
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
     * parameter which selects only valid "less" fields.
     * These fields are defined as class property within this class and can be extended
     * over a shopware event.
     *
     * @param array[] $configs
     */
    private function buildConfigRecursive(int $templateId, array $templates, array $configs, bool $lessCompatible = true): array
    {
        $template = $templates[$templateId];

        $config = [];
        if (\array_key_exists($templateId, $configs)) {
            $config = $configs[$templateId];
        }

        $config = $this->parseConfig($config, $lessCompatible);

        if ($template['parent_id']) {
            $parent = $this->buildConfigRecursive(
                (int) $template['parent_id'],
                $templates,
                $configs,
                $lessCompatible
            );

            return array_merge($parent, $config);
        }

        return $config;
    }

    /**
     * Helper function which returns the theme configuration as key - value array.
     *
     * The element name is used as array key, the shop config as value.
     * If no shop config saved, the value will fall back to the default value.
     */
    private function parseConfig(array $config, bool $lessCompatible = true): array
    {
        foreach ($config as &$row) {
            if (!isset($row['value'])) {
                $row['value'] = unserialize($row['defaultValue'], ['allowed_classes' => false]);
            } else {
                $row['value'] = unserialize($row['value'], ['allowed_classes' => false]);
            }

            if ($row['type'] === 'theme-media-selection' && $row['value'] !== $row['defaultValue'] && str_contains($row['value'], 'media/')) {
                $row['value'] = $this->mediaService->getUrl($row['value']);
            }

            if ($lessCompatible && $row['type'] === 'theme-media-selection') {
                $row['value'] = '"' . $row['value'] . '"';
            }
        }

        if (empty($config)) {
            return [];
        }

        //creates a key value array for the configuration.
        $combinedArray = array_combine(
            array_column($config, 'name'),
            array_column($config, 'value')
        );
        if (!\is_array($combinedArray)) {
            return [];
        }

        return $combinedArray;
    }

    private function fetchTemplates(): array
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select([
            'template.id',
            'template.id',
            'template.template',
            'template.plugin_id',
            'template.parent_id',
            'plugin.name as plugin_name',
            'plugin.namespace as plugin_namespace',
            'plugin.source as plugin_source',
        ]);
        $query->leftJoin('template', 's_core_plugins', 'plugin', 'plugin.id = template.plugin_id');
        $query->from('s_core_templates', 'template');

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }

    /**
     * @param array[] $templates
     */
    private function filterAffectedTemplates(int $id, array $templates): array
    {
        $active = $templates[$id];

        if ($active['parent_id']) {
            $children = $this->filterAffectedTemplates((int) $active['parent_id'], $templates);
            $directories = [$id => $active];
            $directories += $children;

            return $directories;
        }

        return [$id => $active];
    }

    /**
     * @param int[] $templateIds
     *
     * @return array[] indexed by template id
     */
    private function getConfigs(array $templateIds, int $shopId, bool $lessCompatible): array
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();

        $query->select([
            'element.template_id',
            'element.name',
            'element_value.value',
            'element.default_value as defaultValue',
            'element.type',
        ]);

        $query->from('s_core_templates_config_elements', 'element');
        $query->leftJoin('element', 's_core_templates_config_values', 'element_value', 'element_value.element_id = element.id AND element_value.shop_id = :shopId');
        $query->where('element.template_id IN (:ids)');

        if ($lessCompatible) {
            $query->andWhere('element.less_compatible = 1');
        }

        $query->setParameter(':shopId', $shopId);
        $query->setParameter(':ids', $templateIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_GROUP);
    }
}
