<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Doctrine\DBAL\Connection;
use Shopware\Components\CSRFWhitelistAware;
use ShopwarePlugins\SwagUpdate\Components\Checks\EmotionTemplateCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\LicenseCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\MySQLVersionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\PHPExtensionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\PHPVersionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\RegexCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\WritableCheck;
use ShopwarePlugins\SwagUpdate\Components\Exception\ExtensionMissingException;
use ShopwarePlugins\SwagUpdate\Components\ExtJsResultMapper;
use ShopwarePlugins\SwagUpdate\Components\FileSystem as SwagUpdateFileSystem;
use ShopwarePlugins\SwagUpdate\Components\PluginCheck;
use ShopwarePlugins\SwagUpdate\Components\Steps\DownloadStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\ErrorResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\UnpackStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;
use ShopwarePlugins\SwagUpdate\Components\Validation;
use Symfony\Component\Filesystem\Filesystem;

class Shopware_Controllers_Backend_SwagUpdate extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Cache key for update response
     */
    public const CACHE_KEY = 'swag_update_response';

    /**
     * @return void
     */
    public function changelogAction()
    {
        try {
            $data = $this->getCachedVersion();
        } catch (Exception $e) {
            $logger = $this->get('corelogger');
            $logger->error($e);

            $this->View()->assign([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ]);

            return;
        }

        if (!$data instanceof Version || !$data->isNewer) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $assignedData = [
            'version' => $data->version,
            'changelog' => $data->changelog,
        ];

        $this->View()->assign([
            'success' => true,
            'data' => $assignedData,
        ]);
    }

    /**
     * @return void
     */
    public function requirementsAction()
    {
        $data = $this->getCachedVersion();

        if (!isset($data->checks)) {
            $this->View()->assign([
                'success' => true,
            ]);

            return;
        }

        $user = Shopware()->Container()->get('auth')->getIdentity();
        $userLang = $this->getUserLanguage($user);

        $namespace = $this->get('snippets')->getNamespace('backend/swag_update/main');

        $endpoint = $this->container->getParameter('shopware.store.apiEndpoint');

        $fileSystem = new SwagUpdateFileSystem();
        $conn = $this->get(Connection::class);
        $checks = [
            new RegexCheck($userLang),
            new MySQLVersionCheck($namespace),
            new PHPVersionCheck($namespace),
            new EmotionTemplateCheck($conn, $namespace),
            new PHPExtensionCheck($namespace),
            new WritableCheck($fileSystem, $namespace),
            new LicenseCheck($conn, $endpoint, $this->getShopwareVersion(), $namespace),
        ];
        $validation = new Validation($checks);

        $this->View()->assign([
            'success' => true,
            'data' => $validation->checkRequirements($data->checks),
        ]);
    }

    /**
     * @return void
     */
    public function pluginsAction()
    {
        $data = $this->getCachedVersion();
        if (!$data instanceof Version) {
            $this->View()->assign([
                'success' => false,
                'data' => [],
            ]);

            return;
        }

        $version = $data->version;

        $pluginCheck = new PluginCheck($this->container);
        $result = $pluginCheck->checkInstalledPluginsAvailableForNewVersion($version);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * @return void
     */
    public function isUpdateAllowedAction()
    {
        $fs = new SwagUpdateFileSystem();

        $result = $fs->checkDirectoryPermissions(Shopware()->DocPath(), true);

        if (!empty($result)) {
            $wrongPermissionCount = \count($result);

            $this->container->get('corelogger')->error(
                sprintf('SwagUpdate: There are %d files without write permission. FTP credentials are needed.', $wrongPermissionCount),
                $result
            );

            $this->View()->assign([
                'success' => true,
                'ftpRequired' => true,
                'wrongPermissionCount' => $wrongPermissionCount,
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'ftpRequired' => false,
        ]);
    }

    /**
     * @return void
     */
    public function popupAction()
    {
        try {
            $data = $this->fetchUpdateVersion();
        } catch (Exception $e) {
            $opensslMissing = false;

            if ($e instanceof ExtensionMissingException) {
                $opensslMissing = $e->getMessage() === 'openssl';
            }

            $this->View()->assign([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'opensslMissing' => $opensslMissing,
            ]);

            return;
        }

        if ($data instanceof Version && $data->isNewer) {
            $this->View()->assign([
                'success' => true,
                'data' => [
                    'success' => true,
                    'name' => $data->version,
                    'security_update' => (bool) $data->security_update,
                    'security_plugin_active' => $this->checkSecurityPlugin(),
                ],
            ]);
        }
    }

    /**
     * @return void
     */
    public function startUpdateAction()
    {
        $clientIp = $this->Request()->getClientIp();
        $base = $this->Request()->getBaseUrl();
        $user = Shopware()->Container()->get('auth')->getIdentity();

        $locale = $user->locale;

        $payload = [
            'clientIp' => $clientIp,
            'locale' => $locale->getLocale(),
        ];

        $version = $this->getCachedVersion();
        if (!$version instanceof Version) {
            throw new Exception('Could not get version');
        }

        $payload['version'] = $version->version;

        $session = Shopware()->BackendSession();
        if ($session->offsetExists('update_ftp')) {
            $payload['ftp_credentials'] = $session->offsetGet('update_ftp');
        }

        $payload = json_encode($payload);

        $projectDir = $this->container->getParameter('shopware.app.rootDir');
        $updateFilePath = $projectDir . 'files/update/update.json';

        if (!file_put_contents($updateFilePath, $payload)) {
            throw new Exception(sprintf('Could not write file %s', $updateFilePath));
        }

        $this->redirect($base . '/recovery/update/index.php');
    }

    /**
     * @return void
     */
    public function downloadAction()
    {
        $offset = (int) $this->Request()->get('offset', 0);

        $version = $this->getCachedVersion();
        if (!$version instanceof Version) {
            $this->View()->assign('message', 'Could not get version information');
            $this->View()->assign('success', false);

            return;
        }

        try {
            $destination = $this->createDestinationFromVersion($version);
            $downloadStep = new DownloadStep($version, $destination);
            $result = $downloadStep->run($offset);
            $this->view->assign($this->mapResult($result));
        } catch (Exception $e) {
            $this->Response()->setStatusCode(500);
            $this->View()->assign('message', $e->getMessage());
            $this->View()->assign('success', false);
        }
    }

    /**
     * @return void
     */
    public function unpackAction()
    {
        try {
            $version = $this->getCachedVersion();
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ]);

            return;
        }

        if (!$version instanceof Version) {
            $this->View()->assign('message', 'Could not get version information');
            $this->View()->assign('success', false);

            return;
        }
        $source = $this->createDestinationFromVersion($version);

        $fs = new Filesystem();

        $updateDir = Shopware()->DocPath() . 'files/update/';
        $fileDir = Shopware()->DocPath() . 'files/update/files';

        $unpackStep = new UnpackStep($source, $fileDir);

        $offset = (int) $this->Request()->get('offset', 0);
        if ($offset === 0) {
            $fs->remove($updateDir);
        }

        $result = $unpackStep->run($offset);

        $this->view->assign($this->mapResult($result));
        if ($result instanceof FinishResult) {
            $fs->rename($fileDir . '/update-assets/', $updateDir . '/update-assets/');
            $this->replaceRecoveryFiles($fileDir);
        }
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'startUpdate',
        ];
    }

    /**
     * @param string $path the path of the directory to be iterated over
     *
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator>
     */
    protected function createRecursiveFileIterator($path)
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        return new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }

    private function replaceRecoveryFiles(string $fileDir): void
    {
        $recoveryDir = $fileDir . '/recovery';
        if (!is_dir($recoveryDir)) {
            return;
        }

        $iterator = $this->createRecursiveFileIterator($recoveryDir);

        $fs = new Filesystem();

        foreach ($iterator as $file) {
            $sourceFile = $file->getPathname();
            $destinationFile = Shopware()->DocPath() . str_replace($fileDir, '', $file->getPathname());

            $destinationDirectory = \dirname($destinationFile);
            $fs->mkdir($destinationDirectory);
            $fs->rename($sourceFile, $destinationFile, true);
        }
    }

    private function getShopwareVersion(): string
    {
        $shopwareVersion = $this->container->getParameter('shopware.release.version');
        $versionText = $this->container->getParameter('shopware.release.version_text');
        if (!empty($versionText) && $versionText !== '___VERSION_TEXT___') {
            $shopwareVersion .= '-' . $versionText;
        }

        return $shopwareVersion;
    }

    private function getCachedVersion(): ?Version
    {
        $cache = $this->get(Zend_Cache_Core::class);
        $version = $cache->load(self::CACHE_KEY);
        if ($version === false) {
            $version = $this->fetchUpdateVersion();
        }

        return $version;
    }

    private function fetchUpdateVersion(): ?Version
    {
        $shopwareVersion = $this->getShopwareVersion();

        $update = $this->get('swagupdateupdatecheck');
        $result = $update->checkUpdate($shopwareVersion);

        $cache = $this->get(Zend_Cache_Core::class);
        $cache->save($result, self::CACHE_KEY, [], 60);

        return $result;
    }

    /**
     * @return string path to update file
     */
    private function createDestinationFromVersion(Version $version): string
    {
        $filename = 'update_' . $version->sha1 . '.zip';

        $rootDir = $this->container->getParameter('shopware.app.rootDir');

        return $rootDir . $filename;
    }

    /**
     * Map result object to extjs array format
     *
     * @param ValidResult|FinishResult|ErrorResult $result
     *
     * @return array<string, bool|int|string>
     */
    private function mapResult($result): array
    {
        $mapper = new ExtJsResultMapper();

        return $mapper->toExtJs($result);
    }

    private function getUserLanguage(stdClass $user): string
    {
        $locale = $user->locale;
        $locale = strtolower($locale->getLocale());

        return substr($locale, 0, 2);
    }

    private function checkSecurityPlugin(): bool
    {
        $activePlugins = $this->container->getParameter('active_plugins');

        return \array_key_exists('SwagSecurity', $activePlugins);
    }
}
