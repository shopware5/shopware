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
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop as Shop;
use Shopware\Components\Theme;

class Installer
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var Configurator
     */
    private $configurator;

    /**
     * @var Util
     */
    private $util;

    /**
     * @var DatabaseHandler
     */
    private $snippetWriter;

    /**
     * @var ModelRepository
     */
    private $repository;

    function __construct(
        ModelManager $entityManager,
        Configurator $configurator,
        PathResolver $pathResolver,
        Util $util,
        DatabaseHandler $snippetWriter)
    {
        $this->configurator = $configurator;
        $this->entityManager = $entityManager;
        $this->pathResolver = $pathResolver;
        $this->snippetWriter = $snippetWriter;
        $this->util = $util;
        $this->repository = $entityManager->getRepository('Shopware\Models\Shop\Template');
    }

    public function synchronize()
    {
        $this->synchronizeThemes();
        $this->synchronizeTemplates();
    }

    /**
     * Iterates all Shopware 5 themes which
     * stored in the /engine/Shopware/Themes directory.
     * Each theme are stored as new Shopware\Models\Shop\Template.
     */
    private function synchronizeThemes()
    {
        $directories = new \DirectoryIterator(
            $this->pathResolver->getDefaultThemeDirectory()
        );

        $themes = $this->synchronizeThemeDirectories($directories);

        $this->removeDeletedThemes();

        $pluginThemes = $this->synchronizePluginThemes();

        $themes = array_merge($themes, $pluginThemes);

        $this->setParents($themes);

        /**@var $theme Theme */
        foreach ($themes as $theme) {
            $this->configurator->synchronize($theme);
        }
    }

    /**
     * Iterates all Shopware 4 templates which
     * stored in the /templates/ directory.
     * Each template are stored as new Shopware\Models\Shop\Template.
     */
    private function synchronizeTemplates()
    {
        $directories = new \DirectoryIterator(
            $this->pathResolver->getDefaultTemplateDirectory()
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

            if (!$template instanceof Shop\Template) {
                $template = new Shop\Template();
                $this->entityManager->persist($template);
            }

            $template->fromArray($data);
            $this->entityManager->flush();
        }
    }

    /**
     * Helper function which iterates the engine\Shopware\Themes directory
     * and registers all stored themes within the directory as \Shopware\Models\Shop\Template.
     *
     * @param \DirectoryIterator $directories
     * @return array
     */
    private function synchronizeThemeDirectories(\DirectoryIterator $directories)
    {
        $themes = array();

        /**@var $directory \DirectoryIterator */
        foreach ($directories as $directory) {
            //check valid directory

            if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() == '_cache') {
                continue;
            }

            $theme = $this->util->getThemeByDirectory($directory);

            $data = $this->getThemeDefinition($theme);

            $template = $this->repository->findOneBy(array(
                'template' => $theme->getTemplate()
            ));

            if (!$template instanceof Shop\Template) {
                $template = new Shop\Template();
                $this->entityManager->persist($template);
            }

            $template->fromArray($data);

            $this->entityManager->flush($template);

            $this->synchronizeSnippets($template);

            $themes[] = $theme;
        }

        return $themes;
    }

    /**
     * Helper function which iterates all plugins
     * and registers their themes.
     * Returns an array with all registered plugin themes.
     *
     * @return array
     */
    private function synchronizePluginThemes()
    {
        $plugins = $this->util->getActivePlugins();

        $themes = array();

        /**@var $plugin Plugin */
        foreach ($plugins as $plugin) {
            $path = $this->pathResolver->getPluginPath($plugin);

            //check if plugin contains themes
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'Themes')) {
                continue;
            }

            $directories = new \DirectoryIterator(
                $path . DIRECTORY_SEPARATOR . 'Themes'
            );

            //the synchronizeThemeDirectories function create for each theme directory a shop template.
            $pluginThemes = $this->synchronizeThemeDirectories($directories);

            if (empty($pluginThemes)) {
                continue;
            }

            //merge the plugin themes into the already detected plugin themes.
            $themes = array_merge($themes, $pluginThemes);

            //iterate themes to set the plugin id.
            /**@var $theme Theme */
            foreach ($themes as $theme) {

                /**@var $template Shop\Template */
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
     * Reads the snippet of all theme ini files and write them
     * into the database
     */
    private function synchronizeSnippets(Shop\Template $template)
    {
        $directory = $this->pathResolver->getSnippetDirectory($template);

        if (!file_exists($directory)) {
            return;
        }

        $namespace = $this->util->getSnippetNamespace($template);

        $this->snippetWriter->loadToDatabase(
            $directory,
            false,
            $namespace
        );
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
     * Removes the database entries for themes which file no more exist.
     */
    private function removeDeletedThemes()
    {
        $themes = $this->repository->createQueryBuilder('templates');
        $themes->where('templates.version = 3')
            ->andWhere('templates.pluginId IS NULL');

        $themes = $themes->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        /**@var $theme Shop\Template */
        foreach ($themes as $theme) {
            $directory = $this->pathResolver->getDirectory($theme);
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
    private function setParents(array $themes)
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

            if (!$parent instanceof Shop\Template) {
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
}