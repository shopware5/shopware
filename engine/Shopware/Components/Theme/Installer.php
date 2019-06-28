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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Components\Theme;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop;

/**
 * The Theme\Installer class handles the theme installation.
 * It synchronize the file system themes with the already
 * installed themes which stored in the database.
 * Within the synchronization process the installer class
 * uses the Theme\Configurator class to synchronize the
 * theme configuration with the database.
 */
class Installer
{
    /**
     * @var array The config options provided in the global config.php file
     */
    protected $snippetConfig;

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

    /**
     * @var Service
     */
    private $service;

    public function __construct(
        ModelManager $entityManager,
        Configurator $configurator,
        PathResolver $pathResolver,
        Util $util,
        DatabaseHandler $snippetWriter,
        Service $service,
        array $snippetConfig = []
    ) {
        $this->configurator = $configurator;
        $this->entityManager = $entityManager;
        $this->pathResolver = $pathResolver;
        $this->snippetWriter = $snippetWriter;
        $this->util = $util;
        $this->repository = $entityManager->getRepository(\Shopware\Models\Shop\Template::class);
        $this->service = $service;
        $this->snippetConfig = $snippetConfig;
    }

    /**
     * Synchronize the file system themes
     * with the already installed themes which stored in the database.
     *
     * The synchronization are processed in the synchronizeThemes and
     * synchronizeTemplates function.
     *
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function synchronize()
    {
        $this->synchronizeThemes();
    }

    /**
     * Iterates all Shopware 5 themes which
     * stored in the /engine/Shopware/Themes directory.
     * Each theme are stored as new Shopware\Models\Shop\Template.
     *
     * After the themes are initialed and stored in the database,
     * the function resolves the inheritance of each theme.
     *
     * After the inheritance is build, the installer uses
     * the Theme\Configurator to synchronize the theme configurations.
     *
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function synchronizeThemes()
    {
        // Creates a directory iterator for the default theme directory (engine/Shopware/Themes)
        $directories = new \DirectoryIterator($this->pathResolver->getFrontendThemeDirectory());

        // Synchronize the default themes which are stored in the engine/Shopware/Themes directory.
        $themes = $this->synchronizeThemeDirectories($directories);

        // To prevent inconsistent data, themes that were removed from the file system have to be removed.
        $this->removeDeletedThemes();

        // Before the inheritance can be built, the plugin themes have to be initialized.
        $pluginThemes = $this->synchronizePluginThemes();

        $themes = array_merge($themes, $pluginThemes);

        // Builds the theme inheritance
        $this->setParents($themes);

        /** @var Theme $theme */
        foreach ($themes as $theme) {
            $this->configurator->synchronize($theme);
        }
    }

    /**
     * Helper function which iterates the engine\Shopware\Themes directory
     * and registers all stored themes within the directory as \Shopware\Models\Shop\Template.
     *
     * @param \Shopware\Models\Plugin\Plugin $plugin
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     *
     * @return Theme[]
     */
    private function synchronizeThemeDirectories(\DirectoryIterator $directories, Plugin $plugin = null)
    {
        $themes = [];

        $settings = $this->service->getSystemConfiguration(
            AbstractQuery::HYDRATE_OBJECT
        );

        /** @var \DirectoryIterator $directory */
        foreach ($directories as $directory) {
            // Check valid directory

            if ($directory->isDot() || !$directory->isDir() || $directory->getFilename() === '_cache') {
                continue;
            }

            try {
                $theme = $this->util->getThemeByDirectory($directory);
            } catch (\Exception $e) {
                continue;
            }

            $data = $this->getThemeDefinition($theme);

            $template = $this->repository->findOneBy([
                'template' => $theme->getTemplate(),
            ]);

            if (!$template instanceof Shop\Template) {
                $template = new Shop\Template();

                if ($plugin) {
                    $template->setPlugin($plugin);
                }

                $this->entityManager->persist($template);
            }

            $template->fromArray($data);
            if (!$template->getId() || $settings->getReloadSnippets()) {
                $this->synchronizeSnippets($template);
            }

            $this->entityManager->flush($template);

            $themes[] = $theme;
        }

        return $themes;
    }

    /**
     * Helper function which iterates all plugins
     * and registers their themes.
     *
     * Returns an array with all registered plugin themes.
     * The return value is used to iterate all themes within the
     * synchronizeThemes function to build the theme inheritance
     * and the theme configuration.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     *
     * @return Theme[]
     */
    private function synchronizePluginThemes()
    {
        $plugins = $this->util->getActivePlugins();

        $themes = [];

        /** @var Plugin $plugin */
        foreach ($plugins as $plugin) {
            $path = $this->pathResolver->getPluginPath($plugin);

            // Check if plugin contains themes
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'Themes')) {
                continue;
            }

            // Check if plugin contains themes
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . 'Frontend')) {
                continue;
            }

            $directories = new \DirectoryIterator(
                $path . DIRECTORY_SEPARATOR . 'Themes' . DIRECTORY_SEPARATOR . 'Frontend'
            );

            // The synchronizeThemeDirectories function create for each theme directory a shop template.
            $pluginThemes = $this->synchronizeThemeDirectories($directories, $plugin);

            if (empty($pluginThemes)) {
                continue;
            }

            //merge the plugin themes into the already detected plugin themes.
            $themes = array_merge($themes, $pluginThemes);

            $this->entityManager->flush();
        }

        return $themes;
    }

    /**
     * Reads the snippet of all theme ini files and write them
     * into the database.
     *
     * The theme snippet namespace are prefixed with themes/theme-name
     */
    private function synchronizeSnippets(Shop\Template $template)
    {
        $directory = $this->pathResolver->getSnippetDirectory($template);

        if (!file_exists($directory) || !$this->snippetConfig['writeToDb']) {
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
     * Helper function which returns the theme information of the
     * passed theme.
     * Used to update the Shopware\Models\Shop\Template entity with
     * the theme data.
     *
     * @return array
     */
    private function getThemeDefinition(Theme $theme)
    {
        return [
            'template' => $theme->getTemplate(),
            'name' => $theme->getName(),
            'author' => $theme->getAuthor(),
            'license' => $theme->getLicense(),
            'description' => $theme->getDescription(),
            'version' => 3,
            'esi' => true,
            'style' => true,
            'emotion' => true,
        ];
    }

    /**
     * Removes the database entries for themes which file no more exist.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    private function removeDeletedThemes()
    {
        $themes = $this->repository->createQueryBuilder('templates');
        $themes->where('templates.version = 3')
            ->andWhere('templates.pluginId IS NULL');

        $themes = $themes->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        /** @var Shop\Template $theme */
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
     * @throws \Exception
     */
    private function setParents(array $themes)
    {
        /** @var Theme $theme */
        foreach ($themes as $theme) {
            if ($theme->getExtend() === null) {
                continue;
            }

            $template = $this->repository->findOneBy([
                'template' => $theme->getTemplate(),
            ]);

            $parent = $this->repository->findOneBy([
                'template' => $theme->getExtend(),
            ]);

            if (!$parent instanceof Shop\Template) {
                throw new \Exception(sprintf(
                    'Parent %s of theme %s not found',
                    $theme->getExtend(),
                    $theme->getTemplate()
                ));
            }

            $template->setParent($parent);

            $this->entityManager->flush();
        }
    }
}
