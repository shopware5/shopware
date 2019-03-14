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

namespace Shopware\Recovery\Update\Controller;

use DirectoryIterator;
use Shopware\Recovery\Update\Cleanup;
use Shopware\Recovery\Update\CleanupFilesFinder;
use Shopware\Recovery\Update\DummyPluginFinder;
use Shopware\Recovery\Update\Utils;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Slim;
use Symfony\Component\Finder\Finder;

class CleanupController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Slim
     */
    private $app;

    /**
     * @var DummyPluginFinder
     */
    private $pluginFinder;

    /**
     * @var CleanupFilesFinder
     */
    private $filesFinder;

    /**
     * @var string
     */
    private $shopwarePath;

    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @var string
     */
    private $backupDirectory;

    /**
     * @var Cleanup
     */
    private $cleanupService;

    /**
     * @param string $shopwarePath
     * @param string $backupDir
     */
    public function __construct(
        Request $request,
        Response $response,
        DummyPluginFinder $pluginFinder,
        CleanupFilesFinder $filesFinder,
        Cleanup $cleanupService,
        Slim $app,
        $shopwarePath,
        \PDO $conn,
        $backupDir
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->app = $app;
        $this->pluginFinder = $pluginFinder;
        $this->filesFinder = $filesFinder;
        $this->cleanupService = $cleanupService;
        $this->shopwarePath = $shopwarePath;
        $this->conn = $conn;
        $this->backupDirectory = $backupDir;
    }

    public function cleanupOldFiles()
    {
        $_SESSION['DB_DONE'] = true;

        $cleanupList = $this->getCleanupList();

        if ($this->request->isPost()) {
            $this->cleanupMedia();
        }

        if (count($cleanupList) == 0) {
            $_SESSION['CLEANUP_DONE'] = true;
            $this->response->redirect($this->app->urlFor('done'));
        }

        if ($this->request->isPost()) {
            $this->cleanupTemplateRelations();

            $result = [];
            foreach ($cleanupList as $path) {
                $result = array_merge($result, Utils::cleanPath($path));
            }

            if (count($result) == 0) {
                $_SESSION['CLEANUP_DONE'] = true;
                $this->response->redirect($this->app->urlFor('done'));
            } else {
                $result = array_map(
                    function ($path) {
                        return substr($path, strlen(SW_PATH) + 1);
                    },
                    $result
                );

                $this->app->render('cleanup.php', ['cleanupList' => $result, 'error' => true]);
            }
        } else {
            $cleanupList = array_map(
                function ($path) {
                    return substr($path, strlen(SW_PATH) + 1);
                },
                $cleanupList
            );

            $this->app->render('cleanup.php', ['cleanupList' => $cleanupList, 'error' => false]);
        }
    }

    /**
     * Deletes outdated folders from earlier shopware versions.
     */
    public function deleteOutdatedFolders()
    {
        echo $this->cleanupService->cleanup();
        exit();
    }

    private function cleanupMedia()
    {
        $mediaPath = $this->shopwarePath . '/media';
        $blacklistMapping = ['ad' => 'g0'];

        $finder = new Finder();
        $files = $finder
            ->in($mediaPath)
            ->files()
            ->path('#/(' . implode('|', array_keys($blacklistMapping)) . ')/#')
            ->getIterator();

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $sanitizedPath = str_replace($mediaPath, '', $file->getPathname());

            foreach ($blacklistMapping as $search => $replace) {
                // must be called 2 times, because the second level won't be matched in the first call
                $sanitizedPath = str_replace('/' . $search . '/', '/' . $replace . '/', $sanitizedPath);
                $sanitizedPath = str_replace('/' . $search . '/', '/' . $replace . '/', $sanitizedPath);
            }

            $sanitizedPath = $mediaPath . $sanitizedPath;

            // create target directory for the case that the new structure does not exist yet
            $saveDirectoryPath = str_replace($file->getFilename(), '', $sanitizedPath);
            if (!is_dir($saveDirectoryPath)) {
                @mkdir($saveDirectoryPath, 0777, true);
            }

            rename($file->getPathname(), $sanitizedPath);
        }
    }

    private function cleanupTemplateRelations()
    {
        $affectedShopsSql = <<<'SQL'
SELECT shops.id, template.id as tplId, doctemplate.id as docTplId, template.version as tplVersion, doctemplate.version as docTplVersion
FROM `s_core_shops` as shops
LEFT JOIN `s_core_templates` as template ON shops.template_id = template.id
LEFT JOIN `s_core_templates` as doctemplate ON shops.document_template_id = doctemplate.id
WHERE
  shops.template_id IS NOT NULL
  OR shops.document_template_id IS NOT NULL
HAVING
  tplId IS NULL
  OR docTplId IS NULL
  OR template.version < 3
  OR doctemplate.version < 3
SQL;
        $affectedShops = $this->conn->query($affectedShopsSql)->fetchAll();

        if (empty($affectedShops)) {
            return;
        }

        $sql = 'SELECT id FROM `s_core_templates` WHERE version = 3 AND parent_id IS NOT NULL ORDER BY id ASC LIMIT 1';
        $templateId = $this->conn->query($sql)->fetchColumn();

        foreach ($affectedShops as $shop) {
            if ($shop['tplId'] === null || $shop['tplVersion'] < 3) {
                $this->updateShopConfig('template_id', $templateId, $shop['id']);
            }

            if ($shop['docTplId'] === null || $shop['docTplVersion'] < 3) {
                $this->updateShopConfig('document_template_id', $templateId, $shop['id']);
            }
        }

        $_SESSION['changedTheme'] = true;
    }

    /**
     * @param string $field
     * @param string $value
     * @param int    $shopId
     */
    private function updateShopConfig($field, $value, $shopId)
    {
        $this->conn->prepare('UPDATE `s_core_shops` SET ' . $field . ' = :newValue WHERE id = :shopId')
            ->execute([
                ':newValue' => $value,
                ':shopId' => $shopId,
            ]);
    }

    /**
     * @param string $path
     *
     * @return array|DirectoryIterator
     */
    private function getDirectoryIterator($path)
    {
        if (is_dir($path)) {
            return new DirectoryIterator($path);
        }

        return [];
    }

    private function getCleanupList()
    {
        $cleanupList = array_merge(
            $this->pluginFinder->getDummyPlugins(),
            $this->filesFinder->getCleanupFiles()
        );

        $cacheDirectoryList = $this->getCacheDirectoryList();
        $cleanupList = array_merge(
            $cacheDirectoryList,
            $cleanupList
        );

        $temporaryBackupDirectories = $this->getTemporaryBackupDirectoryList();
        $cleanupList = array_merge(
            $temporaryBackupDirectories,
            $cleanupList
        );

        return $cleanupList;
    }

    /**
     * returns a array of directory names in the cache directory
     *
     * @return array
     */
    private function getCacheDirectoryList()
    {
        $cacheDirectories = $this->getDirectoryIterator($this->shopwarePath . '/var/cache');

        $directoryNames = [];
        foreach ($cacheDirectories as $directory) {
            if ($directory->isDot() || $directory->isFile()) {
                continue;
            }

            $directoryNames[] = $directory->getRealPath();
        }

        return $directoryNames;
    }

    private function getTemporaryBackupDirectoryList()
    {
        $directories = $this->getDirectoryIterator($this->backupDirectory);

        $directoryNames = [];
        foreach ($directories as $directory) {
            if ($directory->isDot() || $directory->isFile()) {
                continue;
            }

            $directoryNames[] = $directory->getRealPath();
        }

        return $directoryNames;
    }
}
