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
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Shopware\Recovery\Update\CleanupFilesFinder;
use Shopware\Recovery\Update\DummyPluginFinder;
use Shopware\Recovery\Update\Utils;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Slim;
use SplFileInfo;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Update\Controller
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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
     * @param Request $request
     * @param Response $response
     * @param DummyPluginFinder $pluginFinder
     * @param CleanupFilesFinder $filesFinder
     * @param Slim $app
     * @param string $shopwarePath
     * @param \PDO $conn
     */
    public function __construct(
        Request $request,
        Response $response,
        DummyPluginFinder $pluginFinder,
        CleanupFilesFinder $filesFinder,
        Slim $app,
        $shopwarePath,
        \PDO $conn
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->app = $app;
        $this->pluginFinder = $pluginFinder;
        $this->filesFinder = $filesFinder;
        $this->shopwarePath = $shopwarePath;
        $this->conn = $conn;
    }

    public function cleanupOldFiles()
    {
        $_SESSION['DB_DONE'] = true;

        $cleanupList = array_merge(
            $this->pluginFinder->getDummyPlugins(),
            $this->filesFinder->getCleanupFiles()
        );

        if ($this->request->isPost()) {
            $this->cleanupMedia();
        }

        $cacheDirectoryList = $this->getCacheDirectoryList();
        $cleanupList = array_merge(
            $cacheDirectoryList,
            $cleanupList
        );

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

    private function cleanupMedia()
    {
        $mediaPath = $this->shopwarePath . '/media/image';
        $thumbnailPath = $this->shopwarePath . '/media/image/thumbnail';

        $iterator = new RecursiveIteratorIterator(
            new \RecursiveRegexIterator(
                new RecursiveDirectoryIterator($mediaPath, RecursiveDirectoryIterator::SKIP_DOTS),
                '/ad/'
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        if (!file_exists($thumbnailPath)) {
            mkdir($thumbnailPath);
        }

        /** @var SplFileInfo $a */
        foreach ($iterator as $a) {
            $isThumbnail = preg_match('#_(\d)+x(\d)+\.#', $a->getFilename());

            if (!$isThumbnail) {
                $isThumbnail = preg_match('#_(\d)+x(\d)+@2x\.#', $a->getFilename());
            }

            if ($isThumbnail) {
                rename($a->getPathname(), $thumbnailPath . "/" . $a->getFilename());
            } else {
                rename($a->getPathname(), $mediaPath . "/" . $a->getFilename());
            }
        }
    }

    private function cleanupTemplateRelations()
    {
        $affectedShopsSql = <<<SQL
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

        $sql = "SELECT id FROM `s_core_templates` WHERE version = 3 AND parent_id IS NOT NULL ORDER BY id ASC LIMIT 1";
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
     * @param $field
     * @param $value
     * @param $shopId
     */
    private function updateShopConfig($field, $value, $shopId)
    {
        $this->conn->prepare("UPDATE `s_core_shops` SET " . $field . " = :newValue WHERE id = :shopId")
            ->execute([
                ':newValue' => $value,
                ':shopId' => $shopId
            ]);
    }

    /**
     * @return DirectoryIterator
     */
    private function getCacheDirectoryIterator()
    {
        $cacheDirectory = $this->shopwarePath . '/var/cache';

        return new DirectoryIterator($cacheDirectory);
    }

    /**
     * Deletes outdated cache folders from earlier shopware versions.
     */
    public function deleteOutdatedCacheFolders()
    {
        /** @var DirectoryIterator $cacheDirectoryIterator */
        $cacheDirectoryIterator = $this->getCacheDirectoryIterator();

        $endTime = time() + 5;
        $deletedFileCount = 0;

        foreach ($cacheDirectoryIterator as $directory) {
            if ($directory->isDot() || $directory->isFile()) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory->getPath(), FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            /** @var $path \SplFileInfo */
            foreach ($iterator as $path) {
                if ($path->getFilename() == '.gitkeep') {
                    continue;
                }

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
                $deletedFileCount++;

                if ($endTime < time()) {
                    echo json_encode(['deletedFiles' => $deletedFileCount, 'ready' => false, 'error' => false]);
                    exit();
                }
            }
        }

        echo json_encode(['deletedFiles' => $deletedFileCount, 'ready' => true, 'error' => false]);
        exit();
    }

    /**
     * returns a array of directory names in the cache directory
     *
     * @return array
     */
    private function getCacheDirectoryList()
    {
        $cacheDirectories = $this->getCacheDirectoryIterator();

        $directoryNames = [];
        foreach ($cacheDirectories as $directory) {
            if ($directory->isDot() || $directory->isFile()) {
                continue;
            }

            $directoryNames[] = $directory->getRealPath();
        }

        return $directoryNames;
    }
}
