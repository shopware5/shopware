<?php
/**
 * Shopware 4
 * Copyright Â© shopware AG
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
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Form\Container\TabContainer;
use Shopware\Components\Form\Container;
use Shopware\Components\Form\Field;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig;
use Shopware\Theme;

/**
 * Managing class for shopware themes.
 * Used to initial themes and old template over the
 * source directory.
 * Responsible to create the inheritance hierarchy.
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * @var \Enlight_Template_Manager
     */
    protected $templateManager;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var DatabaseHandler
     */
    protected $snippetWriter;

    /**
     * @var ModelRepository
     */
    protected $repository;

    /**
     * @var \Shopware\Components\Form\Persister\Theme
     */
    protected $persister;

    /**
     * @param $rootDir
     * @param ModelManager $entityManager
     * @param \Enlight_Template_Manager $templateManager
     * @param DatabaseHandler $snippetWriter
     * @param \Shopware\Components\Form\Persister\Theme $persister
     */
    function __construct(
        $rootDir,
        ModelManager $entityManager,
        \Enlight_Template_Manager $templateManager,
        DatabaseHandler $snippetWriter,
        \Shopware\Components\Form\Persister\Theme $persister)
    {
        $this->entityManager = $entityManager;
        $this->rootDir = $rootDir;
        $this->templateManager = $templateManager;
        $this->snippetWriter = $snippetWriter;
        $this->repository = $entityManager->getRepository('Shopware\Models\Shop\Template');
        $this->persister = $persister;
    }

    /**
     * Iterates all Shopware 5 themes which
     * stored in the /engine/Shopware/Themes directory.
     * Each theme are stored as new Shopware\Models\Shop\Template.
     */
    public function registerThemes()
    {
        $directories = new \DirectoryIterator(
            $this->getDefaultThemeDirectory()
        );

        $themes = $this->initialThemes($directories);
        $this->removeDeletedThemes();

        $pluginThemes = $this->initialPluginThemes();

        $themes = array_merge($themes, $pluginThemes);

        $this->resolveThemeParents($themes);

        /**@var $theme Theme*/
        foreach($themes as $theme) {
            $this->initialThemeConfiguration($theme);
        }
    }

    /**
     * Returns the theme directory hierarchy.
     *
     * @param array $hierarchy
     * @return array
     */
    public function getHierarchyPaths(array $hierarchy)
    {
        $paths = array();

        /**@var $theme Template */
        foreach ($hierarchy as $theme) {
            $paths[] = $this->getThemeDirectory($theme);
        }

        return $paths;
    }

    /**
     * Returns the shop theme configuration for the passed
     * hierarchy.
     * Iterates all passed themes and merges the configuration.
     *
     * @param array $hierarchy
     * @param Shop $shop
     * @return array
     */
    public function getHierarchyConfig(array $hierarchy, Shop $shop)
    {
        $config = array();

        /**@var $theme Template */
        foreach ($hierarchy as $theme) {
            $config = array_merge(
                $themeConfig = $this->getThemeConfiguration($theme, $shop),
                $config
            );
        }
        return $config;
    }

    /**
     * Registers all smarty functions for each passed
     * shopware theme.
     *
     * @param array $hierarchy
     */
    public function registerHierarchySmartyFunctions(array $hierarchy)
    {
        /**@var $theme Template */
        foreach ($hierarchy as $theme) {
            $dir = $this->getSmartyDirectory($theme);

            if (!file_exists($dir)) {
                return;
            }

            $this->templateManager->addPluginsDir($dir);
        }
    }

    /**
     * Returns the inheritance hierarchy for the passed theme.
     *
     * @param Template $template
     * @return array
     */
    public function getInheritanceHierarchy(Template $template)
    {
        $hierarchy = array();
        $hierarchy[] = $template;

        if ($template->getParent() instanceof Template) {
            $hierarchy = array_merge(
                $hierarchy,
                $this->getInheritanceHierarchy($template->getParent())
            );
        }
        return $hierarchy;
    }

    /**
     * Iterates all Shopware 4 templates which
     * stored in the /templates/ directory.
     * Each template are stored as new Shopware\Models\Shop\Template.
     */
    public function registerTemplates()
    {
        $directories = new \DirectoryIterator(
            $this->rootDir . '/templates'
        );

        /**@var $directory \DirectoryIterator */
        foreach ($directories as $directory) {
            //check valid directory
            if ($directory->isDot()
                || !$directory->isDir()
                || strpos($directory->getFilename(), '_') === 0
            ) {
                continue;
            }

            //draw template information over the directory
            $data = $this->getTemplateDefinition($directory);

            $template = $this->repository->findOneBy(array(
                'template' => $data['template']
            ));

            if (!$template instanceof Template) {
                $template = new Template();
                $this->entityManager->persist($template);
            }

            $template->fromArray($data);
            $this->entityManager->flush();
        }
    }

    /**
     * Returns the preview image for the passed template.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @return null|string
     */
    public function getTemplateImage(Template $template)
    {
        $templateDir = $this->templateManager->resolveTemplateDir(
            $template->getTemplate()
        );

        $thumbnail = $templateDir . '/preview_thb.png';

        if (!file_exists($thumbnail)) {
            return null;
        }

        $thumbnail = file_get_contents($thumbnail);
        return 'data:image/png;base64,' . base64_encode($thumbnail);
    }

    /**
     * Returns the theme preview thumbnail.
     *
     * @param \Shopware\Models\Shop\Template $theme
     * @return null|string
     */
    public function getThemeImage(Template $theme)
    {
        $directory = $this->getThemeDirectory($theme);

        $thumbnail = $directory . '/preview.png';

        if (!file_exists($thumbnail)) {
            return null;
        }

        $thumbnail = file_get_contents($thumbnail);
        return 'data:image/png;base64,' . base64_encode($thumbnail);
    }

    /**
     * Returns the snippet namespace for the passed theme.
     *
     * @param Template $template
     * @return string
     */
    public function getSnippetNamespace(Template $template)
    {
        return 'themes/' . strtolower($template->getTemplate()) . '/';
    }

    /**
     * Returns the fix defined snippet directory of the passed theme.
     *
     * @param Template $template
     * @return string
     */
    public function getSnippetDirectory(Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        '_private' .
        DIRECTORY_SEPARATOR .
        'snippets' .
        DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the fix defined snippet directory of the passed theme.
     *
     * @param Template $template
     * @return string
     */
    public function getSmartyDirectory(Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        '_private' .
        DIRECTORY_SEPARATOR .
        'smarty' .
        DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the less directory for the passed theme.
     * @param Template $template
     * @return string
     */
    public function getThemeLessDirectory(Template $template)
    {
        return $this->getThemeDirectory($template) .
        DIRECTORY_SEPARATOR .
        '_public' .
        DIRECTORY_SEPARATOR .
        'source' .
        DIRECTORY_SEPARATOR .
        'less';
    }

    /**
     * Returns the template directory of the passed shop template.
     * @param Template $template
     * @return string
     */
    public function getTemplateDirectory(Template $template)
    {
        return $this->templateManager->resolveTemplateDir(
            $template->getTemplate()
        );
    }

    /**
     * Helper function which returns the default shopware theme directory.
     * @return string
     */
    public function getDefaultThemeDirectory()
    {
        return $this->rootDir .
        DIRECTORY_SEPARATOR . 'engine' .
        DIRECTORY_SEPARATOR . 'Shopware' .
        DIRECTORY_SEPARATOR . 'Themes';
    }

    /**
     * Helper function which returns the theme directory for the passed
     * shop template.
     *
     * @param Template $theme
     * @return null|string
     */
    public function getThemeDirectory(Template $theme)
    {
        if ($theme->getPlugin()) {

            return $this->getPluginPath($theme->getPlugin()) .
            DIRECTORY_SEPARATOR .
            'Themes' .
            DIRECTORY_SEPARATOR .
            $theme->getTemplate();

        } else {
            return $this->getDefaultThemeDirectory() . DIRECTORY_SEPARATOR . $theme->getTemplate();
        }
    }

    /**
     * @param Template $template
     * @return Theme
     * @throws \Exception
     */
    public function getThemeByTemplate(Template $template)
    {
        $namespace = "Shopware\\Themes\\" . $template->getTemplate();
        $class = $namespace . "\\Theme";

        $directory = $this->getThemeDirectory($template);

        $file = $directory . DIRECTORY_SEPARATOR . 'Theme.php';

        if (!file_exists($file)) {
            throw new \Exception(sprintf(
                "Theme directory %s contains no Theme.php",
                $template->getTemplate()
            ));
        }

        require_once $file;

        return new $class();
    }

    /**
     * Helper function which refresh the theme configuration element definition.
     *
     * @param Theme $theme
     */
    private function initialThemeConfiguration(Theme $theme)
    {
        $container = $this->createConfigContainer($theme);

        $theme->createConfig($container);

        $template = $this->getTemplateWithConfig($theme);

        $this->persister->save($container, $template);

        $this->removeUnusedConfig($template, $container);
    }

    /**
     * Helper function which iterates the engine\Shopware\Themes directory
     * and registers all stored themes within the directory as \Shopware\Models\Shop\Template.
     *
     * @param \DirectoryIterator $directories
     * @return array
     */
    private function initialThemes(\DirectoryIterator $directories)
    {
        $themes = array();

        /**@var $directory \DirectoryIterator */
        foreach ($directories as $directory) {
            //check valid directory
            if ($directory->isDot() || !$directory->isDir()) {
                continue;
            }

            $theme = $this->getThemeClass($directory);

            $data = $this->getThemeDefinition($theme);

            $template = $this->repository->findOneBy(array(
                'template' => $theme->getTemplate()
            ));

            if (!$template instanceof Template) {
                $template = new Template();
                $this->entityManager->persist($template);
            }

            $template->fromArray($data);

            $this->entityManager->flush($template);

            $this->initialThemeSnippets($template);

            $this->initialThemeConfigurationSets($theme, $template);

            $themes[] = $theme;
        }

        return $themes;
    }


    /**
     * @param Theme $theme
     * @param Template $template
     */
    private function initialThemeConfigurationSets(Theme $theme, Template $template)
    {
        $collection = new ArrayCollection();
        $theme->createConfigSets($collection);

        foreach($collection as $item) {
            $existing = $this->getExistingConfigSet(
                $template->getConfigSets(),
                $item['name']
            );
            $existing->setTemplate($template);
            $existing->fromArray($item);
        }
    }

    /**
     * @param ArrayCollection $collection
     * @param $name
     * @return TemplateConfig\Set
     */
    private function getExistingConfigSet(ArrayCollection $collection, $name)
    {
        /**@var $item TemplateConfig\Set*/
        foreach($collection as $item) {
            if ($item->getName() == $name) {
                return $item;
            }
        }
        $item = new TemplateConfig\Set();
        $collection->add($item);
        return $item;
    }

    /**
     * Helper function which iterates all plugins
     * and registers their themes.
     * Returns an array with all registered plugin themes.
     *
     * @return array
     */
    private function initialPluginThemes()
    {
        $plugins = $this->getActivePlugins();

        $themes = array();

        /**@var $plugin Plugin */
        foreach ($plugins as $plugin) {
            $path = $this->getPluginPath($plugin);

            //check if plugin contains themes
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'Themes')) {
                continue;
            }

            $directories = new \DirectoryIterator(
                $path . DIRECTORY_SEPARATOR . 'Themes'
            );

            //the initialThemes function create for each theme directory a shop template.
            $pluginThemes = $this->initialThemes($directories);

            if (empty($pluginThemes)) {
                continue;
            }

            //merge the plugin themes into the already detected plugin themes.
            $themes = array_merge($themes, $pluginThemes);

            //iterate themes to set the plugin id.
            foreach ($themes as $theme) {
                /**@var $theme Theme */
                /**@var $template Template */
                $template = $this->repository->findOneBy(array(
                    'template' => $theme->getTemplate()
                ));

                $template->setPlugin($plugin);
            }

            $this->entityManager->flush();
        }

        return $themes;
    }

    /**
     * Helper function which removes all unused configuration containers and elements
     * which are stored in the database but not in the passed container.
     *
     * @param Template $template
     * @param Container $container
     */
    private function removeUnusedConfig(Template $template, Container $container)
    {
        $existing = $this->getLayout($template);
        $structure = $this->getContainerNames($container);

        /**@var $layout TemplateConfig\Layout*/
        foreach($existing as $layout) {
            if (!in_array($layout->getName(), $structure['containers'])) {
                $this->entityManager->remove($layout);
            }
        }

        $existing = $this->getElements($template);

        /**@var $layout TemplateConfig\Element*/
        foreach($existing as $layout) {
            if (!in_array($layout->getName(), $structure['fields'])) {
                $this->entityManager->remove($layout);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Returns all config containers of the passed template.
     *
     * @param Template $template
     * @return array
     */
    private function getLayout(Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('layout')
            ->from('Shopware\Models\Shop\TemplateConfig\Layout', 'layout')
            ->where('layout.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        return $builder->getQuery()->getResult();
    }

    /**
     * Returns all config elements of the passed template.
     * @param Template $template
     * @return array
     */
    private function getElements(Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('elements')
            ->from('Shopware\Models\Shop\TemplateConfig\Element', 'elements')
            ->where('elements.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        return $builder->getQuery()->getResult();
    }

    /**
     * Helper function to select the shopware template with all config elements
     * with only one query.
     *
     * @param Theme $theme
     * @return mixed
     */
    private function getTemplateWithConfig(Theme $theme)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'template',
            'elements',
            'layouts'
        ))
            ->from('Shopware\Models\Shop\Template', 'template')
            ->leftJoin('template.elements', 'elements')
            ->leftJoin('template.layouts', 'layouts')
            ->where('template.template = :name')
            ->setParameter('name', $theme->getTemplate());

        return $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Helper function which
     * @param Theme $theme
     * @return TabContainer
     */
    private function createConfigContainer(Theme $theme)
    {
        $container = new TabContainer();
        $container->setName('main_container');

        if ($theme->useInheritanceConfig() && $theme->getExtend() !== null) {
            /**@var $template Template*/
            $template = $this->repository->findOneBy(array(
                'template' => $theme->getTemplate()
            ));

            $hierarchy = $this->getInheritanceHierarchy($template);
            unset($hierarchy[0]);

            foreach(array_reverse($hierarchy) as $template) {
                $parent = $this->getThemeByTemplate($template);

                $parent->createConfig($container);

                if (!$parent->useInheritanceConfig()) {
                    break;
                }
            }

            return $container;
        } else {
            return $container;
        }
    }

    /**
     * Reads the snippet of all theme ini files and write them
     * into the database
     * @param Template $template
     */
    private function initialThemeSnippets(Template $template)
    {
        $directory = $this->getSnippetDirectory($template);

        if (!file_exists($directory)) {
            return;
        }

        $namespace = $this->getSnippetNamespace($template);

        $this->snippetWriter->loadToDatabase(
            $directory,
            false,
            $namespace
        );
    }

    /**
     * Helper function to build the path to the passed plugin.
     * @param Plugin $plugin
     * @return string
     */
    private function getPluginPath(Plugin $plugin)
    {
        $namespace = strtolower($plugin->getNamespace());
        $source = strtolower($plugin->getSource());
        $name = $plugin->getName();

        return $this->rootDir .
        DIRECTORY_SEPARATOR . 'engine' .
        DIRECTORY_SEPARATOR . 'Shopware' .
        DIRECTORY_SEPARATOR . 'Plugins' .
        DIRECTORY_SEPARATOR . ucfirst($source) .
        DIRECTORY_SEPARATOR . ucfirst($namespace) .
        DIRECTORY_SEPARATOR . ucfirst($name);
    }

    /**
     * Helper function which returns the template information for
     * the passed shopware 4 template directory.
     *
     * @param \DirectoryIterator $directory
     * @return array Contains the template data
     */
    private function getTemplateDefinition(\DirectoryIterator $directory)
    {
        $info = $directory->getPathname() . '/info.json';

        $data = array();
        if (file_exists($info)) {
            $data = (array)\Zend_Json::decode(file_get_contents($info));
        }

        $data['template'] = $directory->getFilename();

        if (!isset($data['version'])) {
            $data['version'] = strpos($directory->getFilename(), 'emotion_') !== 0 ? 1 : 2;
        }

        if (!isset($data['name'])) {
            $data['name'] = ucwords(str_replace('_', ' ', $directory->getFilename()));
        }

        return $data;
    }

    /**
     * Helper function which returns the theme information of the
     * passed theme.
     * Used to update the Shopware\Models\Shop\Template entity with
     * the theme data.
     *
     * @param Theme $theme
     * @return array
     */
    private function getThemeDefinition(Theme $theme)
    {
        return array(
            'template' => $theme->getTemplate(),
            'name' => $theme->getName(),
            'author' => $theme->getAuthor(),
            'license' => $theme->getLicense(),
            'description' => $theme->getDescription(),
            'version' => 3,
            'esi' => true,
            'style' => true,
            'emotion' => true
        );
    }

    /**
     * Returns an object list with all installed and activated plugins.
     *
     * @return array
     */
    private function getActivePlugins()
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select(array('plugins'))
            ->from('Shopware\Models\Plugin\Plugin', 'plugins')
            ->where('plugins.active = true')
            ->andWhere('plugins.installed IS NOT NULL');

        return $builder->getQuery()->getResult(
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Resolves the passed directory to a theme class.
     * Returns a new instance of the \Shopware\Theme
     *
     * @param \DirectoryIterator $directory
     * @return Theme
     * @throws \Exception
     */
    private function getThemeClass(\DirectoryIterator $directory)
    {
        $namespace = "Shopware\\Themes\\" . $directory->getFilename();
        $class = $namespace . "\\Theme";

        $file = $directory->getPathname() . DIRECTORY_SEPARATOR . 'Theme.php';

        if (!file_exists($file)) {
            throw new \Exception(sprintf(
                "Theme directory %s contains no Theme.php",
                $directory->getFilename()
            ));
        }

        require_once $file;

        return new $class();
    }

    /**
     * Removes the database entries for themes which file no more exist.
     */
    private function removeDeletedThemes()
    {
        $themes = $this->repository->createQueryBuilder('templates');
        $themes->where('templates.version = 3')
            ->andWhere('templates.pluginId IS NULL');

        $themes = $themes->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        /**@var $theme Template */
        foreach ($themes as $theme) {
            $directory = $this->getThemeDirectory($theme);
            if (!file_exists($directory)) {
                $this->entityManager->remove($theme);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * Helper function which resolves the theme parent for each
     * passed theme
     *
     * @param array $themes
     * @throws \Exception
     */
    private function resolveThemeParents(array $themes)
    {
        /**@var $theme Theme */
        foreach ($themes as $theme) {
            if ($theme->getExtend() === null) {
                continue;
            }

            $template = $this->repository->findOneBy(array(
                'template' => $theme->getTemplate()
            ));

            $parent = $this->repository->findOneBy(array(
                'template' => $theme->getExtend()
            ));

            if (!$parent instanceof Template) {
                throw new \Exception(sprintf(
                    "Parent %s of theme %s not found",
                    array(
                        $theme->getExtend(),
                        $theme->getTemplate()
                    )
                ));
            }

            $template->setParent($parent);

            $this->entityManager->flush();
        }
    }

    /**
     * Helper function which returns the theme configuration as
     * key - value array.
     * The element name is used as array key, the shop config
     * as value. If no shop config saved, the value will fallback to
     * the default value.
     *
     * @param Template $template
     * @param Shop $shop
     * @return array
     */
    private function getThemeConfiguration(Template $template, Shop $shop)
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

        $data = $builder->getQuery()->getArrayResult();

        foreach($data as &$row) {
            if (empty($row['value'])) {
                $row['value'] = $row['defaultValue'];
            }
        }

        return array_combine(
            array_column($data, 'name'),
            array_column($data, 'value')
        );
    }

    /**
     * Helper function to create an array with all container and element names.
     *
     * @param Container $container
     * @return array
     */
    private function getContainerNames(Container $container)
    {
        $layout = array(
            'containers' => array(),
            'fields' => array()
        );

        $layout['containers'][] = $container->getName();

        foreach($container->getElements() as $element) {
            if ($element instanceof Container) {
                $child = $this->getContainerNames($element);
                $layout['containers'] = array_merge($layout['containers'], $child['containers']);
                $layout['fields'] = array_merge($layout['fields'], $child['fields']);
            } else if ($element instanceof Field) {
                $layout['fields'][] = $element->getName();
            }
        }

        return $layout;
    }
}