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

use Shopware\Bundle\PluginInstallerBundle\Service\DownloadService;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Plugin\Plugin;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class Shopware_Controllers_Backend_PluginInstaller extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var InstallerService
     */
    private $pluginManager;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->pluginManager = $this->get('shopware_plugininstaller.plugin_manager');
    }

    public function installPluginAction()
    {
        @set_time_limit(300);

        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        if (!$plugin instanceof Plugin) {
            $this->pluginManager->refreshPluginList();
            $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));
        }

        if (!$plugin instanceof Plugin) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Plugin not found %s', $this->Request()->getParam('technicalName')),
            ]);

            return;
        }

        try {
            $result = $this->pluginManager->installPlugin($plugin);
            $this->View()->assign(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateAction()
    {
        @set_time_limit(300);

        $technicalName = $this->Request()->getParam('technicalName');

        $plugin = $this->getPluginModel($technicalName);

        //disable plugin and save state
        $active = $plugin->getActive();
        $plugin->setActive(false);
        $this->get('models')->flush();

        try {
            if ($plugin->getInstalled()) {
                $result = $this->pluginManager->updatePlugin($plugin);
            } else {
                $result = $this->pluginManager->installPlugin($plugin);
            }
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $plugin = $this->getPluginModel($technicalName);

        $plugin->setActive($active);
        $this->get('models')->flush();

        $this->View()->assign(['success' => true, 'result' => $result]);
    }

    public function uninstallPluginAction()
    {
        @set_time_limit(300);

        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->pluginManager->uninstallPlugin($plugin);
            $this->View()->assign(['result' => $result]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function secureUninstallPluginAction()
    {
        @set_time_limit(300);

        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->pluginManager->uninstallPlugin(
                $plugin,
                !$plugin->hasCapabilitySecureUninstall()
            );
            $this->View()->assign(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deletePluginAction()
    {
        $pluginName = $this->Request()->getParam('technicalName');

        $this->container->get('dbal_connection')->delete('s_core_licenses', [
            'module' => $pluginName,
        ]);

        $plugin = $this->getPluginModel($pluginName);

        switch (true) {
            case $plugin->getSource() == 'Default':
                return $this->View()->assign(['success' => false, 'message' => 'Default plugins can not be deleted']);
            case $plugin->getInstalled():
                return $this->View()->assign(['success' => false, 'message' => 'Installed plugins can not be deleted']);
            default:
                try {
                    $directory = $this->pluginManager->getPluginPath($pluginName);
                    $this->removeDirectory($directory);
                } catch (InvalidArgumentException $e) {
                    // empty catch intended
                } catch (Exception $e) {
                    return $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
                } finally {
                    $this->get('models')->remove($plugin);
                    $this->get('models')->flush();
                }
        }

        return $this->View()->assign('success', true);
    }

    public function activatePluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->pluginManager->activatePlugin($plugin);
            $this->View()->assign(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deactivatePluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->pluginManager->deactivatePlugin($plugin);
            $this->View()->assign(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function uploadAction()
    {
        /** @var DownloadService $service */
        $pluginDownloadService = Shopware()->Container()->get('shopware_plugininstaller.plugin_download_service');

        try {
            $fileBag = new FileBag($_FILES);

            /** @var UploadedFile $file */
            $file = $fileBag->get('plugin');
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $information = pathinfo($file->getClientOriginalName());

        if ($information['extension'] !== 'zip') {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Wrong archive extension %s. Zip archive expected', $information['extension']),
            ]);
            unlink($file->getPathname());

            return;
        }

        $tempFileName = tempnam(sys_get_temp_dir(), $file->getClientOriginalName());
        $tempDirectory = dirname(realpath($tempFileName));

        try {
            $file = $file->move($tempDirectory, $tempFileName);

            $pluginName = $information['basename'];
            $pluginDownloadService->extractPluginZip($file->getPathname(), $pluginName);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        } finally {
            unlink($file->getPathname());
        }

        $this->View()->assign('success', true);
    }

    /**
     * @param string $technicalName
     *
     * @return Plugin
     */
    public function getPluginModel($technicalName)
    {
        return $this->getRepository()->findOneBy(['name' => $technicalName]);
    }

    /**
     * @return ModelRepository
     */
    private function getRepository()
    {
        return $this->get('models')->getRepository(Plugin::class);
    }

    /**
     * @param string $path
     */
    private function removeDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }
        $it = new RecursiveDirectoryIterator($path);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($path);
    }
}
