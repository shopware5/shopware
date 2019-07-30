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

use Psr\Log\LoggerInterface;
use Shopware\Components\CSRFWhitelistAware;
use ShopwarePlugins\SwagUpdate\Components\Checks\EmotionTemplateCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\IonCubeLoaderCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\LicenseCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\MySQLVersionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\PHPExtensionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\PHPVersionCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\RegexCheck;
use ShopwarePlugins\SwagUpdate\Components\Checks\WritableCheck;
use ShopwarePlugins\SwagUpdate\Components\ExtensionMissingException;
use ShopwarePlugins\SwagUpdate\Components\ExtJsResultMapper;
use ShopwarePlugins\SwagUpdate\Components\FeedbackCollector;
use ShopwarePlugins\SwagUpdate\Components\Steps\DownloadStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\ErrorResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\UnpackStep;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;
use ShopwarePlugins\SwagUpdate\Components\UpdateCheck;
use ShopwarePlugins\SwagUpdate\Components\Validation;
use Symfony\Component\Filesystem\Filesystem;

class Shopware_Controllers_Backend_SwagUpdate extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Cache key for update response
     */
    const CACHE_KEY = 'swag_update_response';

    public function changelogAction()
    {
        try {
            $data = $this->getCachedVersion();
        } catch (\Exception $e) {
            /** @var LoggerInterface $logger */
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

        $user = Shopware()->Container()->get('auth')->getIdentity();
        $userLang = $this->getUserLanguage($user);
        $languagePriorities = [
            $userLang,
            'en',
            'de',
        ];

        $changeLog = $this->getLocalizedChangeLog($data, $languagePriorities);

        $this->View()->assign([
            'success' => true,
            'data' => [
                'version' => $data->version,
                'changelog' => $changeLog['changelog'],
            ],
        ]);
    }

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

        $fileSystem = new \ShopwarePlugins\SwagUpdate\Components\FileSystem();
        $conn = $this->get('dbal_connection');
        $checks = [
            new RegexCheck($namespace, $userLang),
            new MySQLVersionCheck($conn, $namespace),
            new PHPVersionCheck($namespace),
            new EmotionTemplateCheck($conn, $namespace),
            new PHPExtensionCheck($namespace),
            new WritableCheck($fileSystem, $namespace),
            new IonCubeLoaderCheck($namespace),
            new LicenseCheck($conn, $this->container->getParameter('shopware.store.apiEndpoint'), $this->getShopwareVersion(), $namespace),
        ];
        $validation = new Validation($namespace, $checks);

        $this->View()->assign([
            'success' => true,
            'data' => $validation->checkRequirements($data->checks),
        ]);
    }

    public function pluginsAction()
    {
        $data = $this->getCachedVersion();

        $version = $data->version;

        $pluginCheck = new \ShopwarePlugins\SwagUpdate\Components\PluginCheck($this->container);
        $result = $pluginCheck->checkInstalledPluginsAvailableForNewVersion($version);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * $this->View()->assign(array(
     *     'success' => false,
     *     'error' => 'There are some problems. SORRY!!'
     * ));
     *
     * $this->View()->assign(array(
     *    'success' => true,
     *     'ftpRequired' => false
     * ));
     */
    public function isUpdateAllowedAction()
    {
        $fs = new \ShopwarePlugins\SwagUpdate\Components\FileSystem();

        $result = $fs->checkDirectoryPermissions(Shopware()->DocPath(), true);

        if (!empty($result)) {
            $wrongPermissionCount = count($result);

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

    public function saveFtpAction()
    {
        $ftpParams = [
            'user' => $this->Request()->getParam('user'),
            'password' => $this->Request()->getParam('password'),
            'path' => $this->Request()->getParam('path'),
            'server' => $this->Request()->getParam('server'),
        ];

        $basepath = rtrim($ftpParams['path'], '/');
        $testFile = $basepath . '/shopware.php';

        $localFh = fopen($testFile, 'rb');
        $remoteFh = fopen('php://memory', 'w+');

        if (false === $connection = ftp_connect($ftpParams['server'], 21, 5)) {
            $this->View()->assign([
                'success' => false,
                'error' => 'Could not connect to server',
            ]);

            return;
        }

        if (!ftp_login($connection, $ftpParams['user'], $ftpParams['password'])) {
            $this->View()->assign([
                'success' => false,
                'error' => 'Could not login into server',
            ]);
            ftp_close($connection);

            return;
        }

        if (!ftp_fget($connection, $remoteFh, $testFile, FTP_ASCII)) {
            $this->View()->assign([
                'success' => false,
                'error' => 'Could not read files from connection.',
            ]);
            ftp_close($connection);

            return;
        }

        if (!$this->checkIdentical($localFh, $remoteFh)) {
            $this->View()->assign([
                'success' => false,
                'error' => 'Files are not identical.',
            ]);
            ftp_close($connection);

            return;
        }

        ftp_close($connection);

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = Shopware()->BackendSession();
        $session->offsetSet('update_ftp', $ftpParams);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    public function popupAction()
    {
        $config = $this->getPluginConfig();

        if ($config['update-send-feedback']) {
            $apiEndpoint = $config['update-feedback-api-endpoint'];
            $rootDir = Shopware()->Container()->getParameter('kernel.root_dir');
            $publicKey = trim(file_get_contents($rootDir . '/engine/Shopware/Components/HttpClient/public.key'));
            $shopwareRelease = $this->container->get('shopware.release');

            $collector = new FeedbackCollector($apiEndpoint, new \Shopware\Components\OpenSSLEncryption($publicKey), $this->getUnique(), $shopwareRelease);

            try {
                $collector->sendData();
            } catch (\Exception $e) {
                // ignore for now
            }
        }

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

    public function startUpdateAction()
    {
        $clientIp = $this->Request()->getClientIp();
        $base = $this->Request()->getBaseUrl();
        $user = Shopware()->Container()->get('auth')->getIdentity();

        /** @var \Shopware\Models\Shop\Locale $locale */
        $locale = $user->locale;

        $payload = [
            'clientIp' => $clientIp,
            'locale' => $locale->getLocale(),
        ];

        $version = $this->getCachedVersion();
        $payload['version'] = $version->version;

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = Shopware()->BackendSession();
        if ($session->offsetExists('update_ftp')) {
            $payload['ftp_credentials'] = $session->offsetGet('update_ftp');
        }

        $payload = json_encode($payload);
        $projectDir = $this->container->getParameter('shopware.app.rootdir');
        $updateFilePath = $projectDir . 'files/update/update.json';

        if (!file_put_contents($updateFilePath, $payload)) {
            throw new \Exception(sprintf('Could not write file %s', $updateFilePath));
        }

        $this->redirect($base . '/recovery/update/index.php');
    }

    public function downloadAction()
    {
        $offset = (int) $this->Request()->get('offset', 0);

        /** @var Version $version */
        $version = $this->getCachedVersion();

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

    public function unpackAction()
    {
        /** @var Version $version */
        $version = $this->getCachedVersion();
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
     * @return \RecursiveIteratorIterator
     */
    protected function createRecursiveFileIterator($path)
    {
        $directoryIterator = new \RecursiveDirectoryIterator(
            $path,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        return new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
    }

    /**
     * @param string $fileDir
     */
    private function replaceRecoveryFiles($fileDir)
    {
        $recoveryDir = $fileDir . '/recovery';
        if (!is_dir($recoveryDir)) {
            return;
        }

        $iterator = $this->createRecursiveFileIterator($recoveryDir);

        $fs = new Filesystem();

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $sourceFile = $file->getPathname();
            $destinationFile = Shopware()->DocPath() . str_replace($fileDir, '', $file->getPathname());

            $destinationDirectory = dirname($destinationFile);
            $fs->mkdir($destinationDirectory);
            $fs->rename($sourceFile, $destinationFile, true);
        }
    }

    /**
     * Checks if two file handles contain the identical bytes.
     *
     * Warning:
     * The file handles are being rewound and closed afterwards.
     *
     * @param resource $fp1
     * @param resource $fp2
     *
     * @return bool
     */
    private function checkIdentical($fp1, $fp2)
    {
        $blockSize = 4096;
        rewind($fp1);
        rewind($fp2);

        $same = true;
        while (!feof($fp1) && !feof($fp2)) {
            if (fread($fp1, $blockSize) !== fread($fp2, $blockSize)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

    /**
     * Returns unique id of this shop installation.
     * If no unique id exists it will be created.
     *
     * @return string
     */
    private function getUnique()
    {
        /** @var Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGeneratorInterface $uniqueIdGenerator */
        $uniqueIdGenerator = $this->container->get('shopware_plugininstaller.unique_id_generator');

        return $uniqueIdGenerator->getUniqueId();
    }

    /**
     * @return array
     */
    private function getPluginConfig()
    {
        return Shopware()->Plugins()->Backend()->SwagUpdate()->Config()->toArray();
    }

    /**
     * @return string
     */
    private function getShopwareVersion()
    {
        $pluginConfig = $this->getPluginConfig();

        if (!empty($pluginConfig['update-fake-version'])) {
            $shopwareVersion = $pluginConfig['update-fake-version'];
        } else {
            $shopwareVersion = $this->container->getParameter('shopware.release.version');
            $versionText = $this->container->getParameter('shopware.release.version_text');
            if (!empty($versionText)) {
                $shopwareVersion .= '-' . $versionText;
            }
        }

        return $shopwareVersion;
    }

    /**
     * @return Version|null
     */
    private function getCachedVersion()
    {
        /** @var \Zend_Cache_Core $cache */
        $cache = $this->get('cache');
        if (false === $version = $cache->load(self::CACHE_KEY)) {
            $version = $this->fetchUpdateVersion();
        }

        return $version;
    }

    /**
     * @return Version
     */
    private function fetchUpdateVersion()
    {
        $shopwareVersion = $this->getShopwareVersion();

        $pluginConfig = $this->getPluginConfig();
        $params = [
            'code' => $pluginConfig['update-code'],
        ];

        /** @var UpdateCheck $update */
        $update = $this->get('swagupdateupdatecheck');
        $result = $update->checkUpdate($shopwareVersion, $params);

        /** @var \Zend_Cache_Core $cache */
        $cache = $this->get('cache');
        $cache->save($result, self::CACHE_KEY, [], 60);

        return $result;
    }

    /**
     * @return string path to update file
     */
    private function createDestinationFromVersion(Version $version)
    {
        $filename = 'update_' . $version->sha1 . '.zip';

        return $this->container->getParameter('shopware.app.rootdir') . $filename;
    }

    /**
     * Map result object to extjs array format
     *
     * @param ValidResult|FinishResult|ErrorResult $result
     *
     * @return array
     */
    private function mapResult($result)
    {
        $mapper = new ExtJsResultMapper();

        return $mapper->toExtJs($result);
    }

    /**
     * @param array $languages
     *
     * @return string
     */
    private function getLocalizedChangeLog(Version $version, $languages)
    {
        while ($language = array_shift($languages)) {
            if (isset($version->changelog[$language])) {
                return $version->changelog[$language];
            }
        }

        return null;
    }

    /**
     * @return string
     */
    private function getUserLanguage(stdClass $user)
    {
        /** @var \Shopware\Models\Shop\Locale $locale */
        $locale = $user->locale;
        $locale = strtolower($locale->getLocale());

        return substr($locale, 0, 2);
    }

    private function checkSecurityPlugin(): bool
    {
        return array_key_exists('SwagSecurity', $this->container->getParameter('active_plugins'));
    }
}
