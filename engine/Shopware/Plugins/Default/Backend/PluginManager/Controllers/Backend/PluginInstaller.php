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

use Shopware\Bundle\PluginInstallerBundle\Service\PluginExtractor;
use Shopware\Components\Model\ModelRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class Shopware_Controllers_Backend_PluginInstaller
    extends Shopware_Controllers_Backend_ExtJs
{
    protected $model = 'Shopware\Models\Plugin\Plugin';

    /**
     * @return ModelRepository
     */
    private function getRepository()
    {
        return $this->get('models')->getRepository($this->model);
    }

    public function installPluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        if (!$plugin instanceof Shopware\Models\Plugin\Plugin) {
            $this->get('shopware_plugininstaller.plugin_manager')->refreshPluginList();
            $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));
        }

        if (!$plugin instanceof \Shopware\Models\Plugin\Plugin) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Plugin not found %s', $this->Request()->getParam('technicalName'))
            ]);
            return;
        }

        try {
            $result = $this->get('shopware_plugininstaller.plugin_manager')->installPlugin($plugin);

            if ($result === true || $result === false) {
                $result = ['success' => $result];
            }
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');

        $plugin = $this->getPluginModel($technicalName);

        $plugin->setUpdateVersion(true);

        //disable plugin and save state
        $active = $plugin->getActive();
        $plugin->setActive(false);
        $this->get('models')->flush();

        try {
            if ($plugin->getInstalled()) {
                $result = $this->get('shopware_plugininstaller.plugin_manager')->updatePlugin($plugin);
            } else {
                $result = $this->get('shopware_plugininstaller.plugin_manager')->installPlugin($plugin);
            }
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return;
        }

        $plugin = $this->getPluginModel($technicalName);

        $plugin->setActive($active);
        $this->get('models')->flush();

        if ($result === true || $result === false) {
            $result = ['success' => $result];
        }
        $this->View()->assign($result);
    }

    public function uninstallPluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->get('shopware_plugininstaller.plugin_manager')->uninstallPlugin($plugin);

            if ($result === true || $result === false) {
                $result = ['success' => $result];
            }
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function secureUninstallPluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->get('shopware_plugininstaller.plugin_manager')->uninstallPlugin(
                $plugin,
                !$plugin->hasCapabilitySecureUninstall()
            );

            if ($result === true || $result === false) {
                $result = ['success' => $result];
            }
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deletePluginAction()
    {
        $directory = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager')
            ->getPluginPath($this->Request()->getParam('technicalName'));

        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        switch (true) {
            case ($plugin->getSource() == 'Default'):
                return $this->View()->assign(['success' => false, 'message' => 'Default plugins can not be deleted']);
            case ($plugin->getInstalled()):
                return $this->View()->assign(['success' => false, 'message' => 'Installed plugins can not be deleted']);
            default:
                try {
                    $this->removeDirectory($directory);
                } catch (Exception $e) {
                    return $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
                }
        }

        return $this->View()->assign('success', true);
    }

    public function activatePluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->get('shopware_plugininstaller.plugin_manager')->activatePlugin($plugin);
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deactivatePluginAction()
    {
        $plugin = $this->getPluginModel($this->Request()->getParam('technicalName'));

        try {
            $result = $this->get('shopware_plugininstaller.plugin_manager')->deactivatePlugin($plugin);
            $this->View()->assign($result);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function uploadAction()
    {
        $root = Shopware()->Container()->getParameter('kernel.root_dir');
        $root .= '/engine/Shopware/Plugins/Community';

        if (!is_writable($root)) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Plugin Community directory is not writable'
            ]);
            return;
        }

        try {
            $fileBag = new FileBag($_FILES);

            /** @var $file UploadedFile */
            $file = $fileBag->get('plugin');
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            return;
        }

        $information = pathinfo($file->getClientOriginalName());

        if ($information['extension'] !== 'zip') {
            $this->View()->assign([
                'success' => false,
                'message' => 'Wrong archive extension %s. Zip archive expected'
            ]);
            unlink($file->getPathname());
            unlink($file);
            return;
        }

        $name = $information['basename'];

        $path = $root . '/' . $name;
        try {
            $file->move($root, $name);

            $extractor = new PluginExtractor();
            $extractor->extract($path, $root);

            unlink($path);
            unlink($file->getPathname());
            unlink($file);
        } catch (Exception $e) {
            $this->View()->assign(
                [
                'success' => false,
                'message' => $e->getMessage()
                ]
            );
            unlink($path);
            unlink($file->getPathname());
            unlink($file);
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            return;
        }

        $this->View()->assign('success', true);
    }

    /**
     * @param $technicalName
     * @return \Shopware\Models\Plugin\Plugin
     */
    public function getPluginModel($technicalName)
    {
        return $this->getRepository()->findOneBy(['name' => $technicalName]);
    }

    /**
     * @param $path
     * @return array
     */
    private function removeDirectory($path)
    {
        $it = new RecursiveDirectoryIterator($path);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        $returns = [];
        foreach ($files as $file) {
            if ($file->isDir()) {
                $returns[] = rmdir($file->getRealPath());
            } else {
                $returns[] =unlink($file->getRealPath());
            }
        }
        $returns[] = rmdir($path);
        return $returns;
    }
}
