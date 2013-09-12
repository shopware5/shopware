<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     Stefan Hamann
 */

/**
 * Shopware Plugin Manager
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Plugin extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Components\Model\ModelRepository[]
     */
    public static $repository = null;

    /**
     * @var $communityStore CommunityStore
     */
    protected $communityStore = null;

    /**
     * @return Shopware\Components\Model\ModelRepository
     */
    protected function getRepository()
    {
        if (!isset(self::$repository)) {
            self::$repository = Shopware()->Models()->getRepository('Shopware\Models\Plugin\Plugin');
        }
        return self::$repository;
    }

    /**
     * helper method to return the community store
     *
     * @return CommunityStore|null
     */
    private function getCommunityStore()
    {
        if ($this->communityStore === null) {
            $this->communityStore = new CommunityStore();
        }
        return $this->communityStore;
    }

    /**
     * Returns a certain plugin by plugin id.
     *
     * @param \Shopware\Models\Plugin\Plugin $plugin
     * @return Shopware_Components_Plugin_Bootstrap|null
     */
    public function getPluginBootstrap($plugin)
    {
        $namespace = Shopware()->Plugins()->get($plugin->getNamespace());
        if ($namespace === null) {
            return null;
        }
        $plugin = $namespace->get($plugin->getName());
        return $plugin;
    }

    /**
     * Activate / deactivate plugin
     */
    public function savePluginAction()
    {
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? $data[0] : $data;

        try {
            $repository = $this->getRepository();
            /** @var $plugin Shopware\Models\Plugin\Plugin */
            $plugin = $repository->find($data['id']);
            $bootstrap = $this->getPluginBootstrap($plugin);
            /** @var $namespace Shopware_Components_Plugin_Namespace */
            $namespace = $bootstrap->Collection();

            if($plugin->getVersion() != $data['version']) {
                $result = $namespace->updatePlugin($bootstrap);
            } elseif(!$plugin->getInstalled() !== empty($data['installed'])) {
                if (!empty($data['installed'])) {
                    $result = $namespace->installPlugin($bootstrap);
                } else {
                    $result = $namespace->uninstallPlugin($bootstrap);
                }
            } else {
                if (!empty($data['active'])) {
                    $result = $bootstrap->enable();
                } else {
                    $result = $bootstrap->disable();
                }
                $success = is_bool($result) ? $result : !empty($result['success']);
                if($success) {
                    $plugin->setActive(!empty($data['active']));
                }
                Shopware()->Models()->flush();
            }
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }
        if(is_bool($result)) {
            $result = array(
                'success' => $result
            );
        }
        $this->View()->assign($result);
    }

    /**
     * Returns a full list of all plugins
     */
    public function getListAction()
    {
        $errorMessage = null;
        try {
            //if (empty($start) && empty($sort) && empty($filter)) {
                $this->refreshPluginList();
            //}
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $filter = (array) $this->Request()->getParam('filter', array());

        $repository = $this->getRepository();

        /** @var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $repository->createQueryBuilder('p')
            ->leftJoin('p.configForms', 'f')
            ->addSelect('PARTIAL f.{id}')
            ->groupBy('p.id');

        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->where('p.name LIKE :search OR ' .
                            'p.label LIKE :search OR ' .
                            'p.namespace LIKE :search OR ' .
                            'p.source LIKE :search OR ' .
                            'p.author LIKE :search')
                    ->setParameter('search', $filter[0]['value']);
            unset($filter[0]);
        }

        $builder->andWhere('p.capabilityEnable=1');

        $builder->addOrderBy((array)$this->Request()->getParam('sort', array()))
            ->addFilter($filter);

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        try {
            $query = $builder->getQuery();
            $total = Shopware()->Models()->getQueryCount($query);
            $data = $query->getArrayResult();
            $this->View()->assign(array(
                'success' => true,
                'data' => $data,
                'total' => $total,
                'message' => $errorMessage
            ));
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Refresh the plugin list
     */
    public function refreshPluginList()
    {
        $refreshed = Zend_Date::now();

        /** @var $collection Shopware_Components_Plugin_Namespace */
        foreach (Shopware()->Plugins() as $namespace => $collection) {
            if (!$collection instanceof Shopware_Components_Plugin_Namespace) {
                continue;
            }
            foreach (array('Local', 'Community', 'Commercial', 'Default') as $source) {
                $path = Shopware()->AppPath('Plugins_' . $source . '_' . $namespace);
                if (!is_dir($path)) {
                    continue;
                }
                foreach (new DirectoryIterator($path) as $dir) {
                    if (!$dir->isDir() || $dir->isDot()) {
                        continue;
                    }
                    $file = $dir->getPathname() . DIRECTORY_SEPARATOR . 'Bootstrap.php';
                    if (!file_exists($file)) {
                        continue;
                    }
                    $name = $dir->getFilename();
                    $plugin = $collection->get($name);
                    if ($plugin === null) {
                        $plugin = $collection->initPlugin($name, new Enlight_Config(array(
                            'source' => $source,
                            'path' => $dir->getPathname() . DIRECTORY_SEPARATOR
                        )));
                    }
                    $collection->registerPlugin($plugin);
                }
            }
        }

        $sql = 'SELECT id, refresh_date FROM s_core_plugins WHERE refresh_date<?';
        $pluginIds = Shopware()->Db()->fetchCol($sql, array($refreshed));
        foreach($pluginIds as $pluginId) {
            $plugin = $this->getRepository()->find($pluginId);
            Shopware()->Models()->remove($plugin);
        }
        Shopware()->Models()->flush();
    }

    /**
     * Deletes a complete plugin directory structure.
     */
    public function deletePluginAction()
    {
        $id = $this->Request()->getParam('id');
        $repository = $this->getRepository();
        /** @var $plugin Shopware\Models\Plugin\Plugin */
        $plugin = $repository->find($id);

        if ($plugin === null) {
            return;
        }

        $pluginPath = Shopware()->AppPath(implode('_', array(
            'Plugins', $plugin->getSource(), $plugin->getNamespace(), $plugin->getName()
        )));

        if($plugin->getSource() === "Default") {
            $message = "'Default' Plugins may not be deleted.";
        } elseif ($plugin->getInstalled() !== null) {
            $message = 'Please uninstall the plugin first.';
        } elseif (!$this->deletePath($pluginPath)) {
            $message = 'Plugin path "' . $pluginPath . '" could not be deleted.';
        } else {
            Shopware()->Models()->remove($plugin);
            Shopware()->Models()->flush();
        }

        $this->View()->assign(array(
            'success' => empty($message),
            'message' => isset($message) ? $message : ''
        ));
    }

    /**
     * @param $deletePath
     * @return bool
     */
    protected function deletePath($deletePath)
    {
        if (is_dir($deletePath)) {
            $dirIterator = new RecursiveDirectoryIterator($deletePath);
            $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                if ($file->isDir()) {
                    if (!$iterator->isDot()) {
                        @rmdir($path);
                    }
                } else {
                    @unlink($path);
                }
            }
            @rmdir($deletePath);
        } elseif (is_file($deletePath)) {
            @unlink($deletePath);
        }
        return !file_exists($deletePath);
    }

    /**
     * Upload plugin action
     *
     * Saves the uploaded plugin in the plugins directory.
     */
    public function uploadAction()
    {
        $upload = new Zend_File_Transfer_Adapter_Http();

        try {
            $upload->setDestination(Shopware()->DocPath() . 'files/downloads');
            $upload->addValidator('Extension', false, 'zip');
            if (!$upload->receive()) {
                $message = $upload->getMessages();
                $message = implode("\n", $message);
            } else {
                $this->getCommunityStore()->decompressFile($upload->getFileName());
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($upload->getFileName()) {
            @unlink($upload->getFileName());
        }

        die(Zend_Json::encode(array(
            'success' => isset($message) ? false : true,
            'message' => isset($message) ? $message : ''
        )));
    }

    /**
     * Decompress a given plugin zip file.
     *
     * @deprecated unused method use the decompressFile method in the CommunityStore component instead
     * @param  $file
     */
    public function decompressFile($file)
    {
        $target = Shopware()->AppPath('Plugins_Community');
        $filter = new Zend_Filter_Decompress(array(
            'adapter' => 'Zip',
            'options' => array(
                'target' => $target
            )
        ));
        $filter->filter($file);
    }

    /**
     * Direct download of a plugin zip file.
     * @deprecated | unused action
     */
    public function downloadAction()
    {
        return; //As long as this action is not being used, they should be inactive.
        $url = $this->Request()->link;
        $tmp = @tempnam(Shopware()->DocPath() . 'files/downloads', 'plugins');

        try {
            $client = new Zend_Http_Client($url, array(
                'timeout' => 10,
                'useragent' => 'Shopware/' . Shopware()->Config()->Version
            ));
            $client->setStream($tmp);
            $client->request('GET');

            $this->decompressFile($tmp);

        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        @unlink($tmp);

        $this->View()->assign(array(
            'success' => !isset($message),
            'message' => isset($message) ? $message : ''
        ));
    }
}
