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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Plugin\Manager as PluginManager;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Template;
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
     * @var \Shopware\Components\Plugin\Manager
     */
    protected $pluginManager;

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

    function __construct(
        $rootDir,
        ModelManager $entityManager,
        PluginManager $pluginManager,
        \Enlight_Template_Manager $templateManager,
        DatabaseHandler $snippetWriter)
    {
        $this->entityManager = $entityManager;
        $this->pluginManager = $pluginManager;
        $this->rootDir = $rootDir;
        $this->templateManager = $templateManager;
        $this->snippetWriter = $snippetWriter;
        $this->repository = $entityManager->getRepository('Shopware\Models\Shop\Template');
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

            $bootstrap = $this->pluginManager->getPluginBootstrap(
                $theme->getPlugin()
            );

            //bootstrap not found? skip plugin.
            if (!$bootstrap instanceof \Shopware_Components_Plugin_Bootstrap) {
                return null;
            }

            return $bootstrap->Path() . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . $theme->getTemplate();
        } else {
            return $this->getDefaultThemeDirectory() . DIRECTORY_SEPARATOR . $theme->getTemplate();
        }
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
     * Reads the snippet of all theme ini files and write them
     * into the database
     * @param Template $template
     */
    public function initialThemeSnippets(Template $template)
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

            //get the plugin bootstrap to get access on the plugin path.
            $bootstrap = $this->pluginManager->getPluginBootstrap(
                $plugin
            );

            //bootstrap not found? skip plugin.
            if (!$bootstrap instanceof \Shopware_Components_Plugin_Bootstrap) {
                continue;
            }

            //check if plugin contains themes
            if (!file_exists($bootstrap->Path() . DIRECTORY_SEPARATOR . 'Themes')) {
                continue;
            }

            $directories = new \DirectoryIterator(
                $bootstrap->Path() . DIRECTORY_SEPARATOR . 'Themes'
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

            $template = $this->getThemeWithConfig($theme);

            if (!$template instanceof Template) {
                $template = new Template();
                $this->entityManager->persist($template);
            }

            $template->fromArray($data);

            $this->initialThemeConfiguration($theme, $template);

            $this->entityManager->flush($template);

            $this->initialThemeSnippets($template);

            $themes[] = $theme;
        }

        return $themes;
    }

    /**
     * Helper function to select the shopware template with all config elements
     * with only one query.
     *
     * @param Theme $theme
     * @return mixed
     */
    private function getThemeWithConfig(Theme $theme)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'template',
            'elements'
        ))
            ->from('Shopware\Models\Shop\Template', 'template')
            ->leftJoin('template.elements', 'elements')
            ->where('template.template = :name')
            ->setParameter('name', $theme->getTemplate());

        return $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );
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
     * Helper function which refresh the theme configuration element definition.
     *
     * @param Theme $theme
     * @param Template $template
     */
    private function initialThemeConfiguration(Theme $theme, Template $template)
    {
        $theme->createConfig();

        $definition = $theme->getConfig();
        $existing = $template->getElements();

        /**@var $element Template\ConfigElement */
        foreach ($definition as $element) {
            $exist = $this->getElementByName(
                $existing,
                $element->getName()
            );

            if ($exist instanceof Template\ConfigElement) {
                $exist->fromArray($element->toArray());
            } else {
                $existing->add($element);
            }

            $element->setTemplate($template);
        }

        $toRemove = array();
        foreach ($existing as $element) {
            if (!array_key_exists($element->getName(), $definition)) {
                $toRemove[] = $element;
            }
        }

        foreach ($toRemove as $element) {
            $existing->removeElement($element);
        }
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
     * Helper function which checks if the element name is already exists in the
     * passed collection of config elements.
     *
     * @param $collection
     * @param $name
     * @return null|Template\ConfigElement
     */
    private function getElementByName($collection, $name)
    {
        /**@var $element Template\ConfigElement */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }
        return null;
    }
}