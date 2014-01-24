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

/**
 * Shopware Plugin Manager
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_PluginManager extends Shopware_Controllers_Backend_ExtJs
{
    protected function initAcl()
    {
        $this->addAclPermission('index', 'read', 'Insufficient Permissions');
        $this->addAclPermission('load', 'read', 'Insufficient Permissions');
        $this->addAclPermission('pluginList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('detail', 'read', 'Insufficient Permissions');
        $this->addAclPermission('updateablePlugins', 'read', 'Insufficient Permissions');
        $this->addAclPermission('refreshPluginList', 'read', 'Insufficient Permissions');

        $this->addAclPermission('upload', 'upload', 'Insufficient Permissions');

        $this->addAclPermission('downloadUpdate', 'download', 'Insufficient Permissions');

        $this->addAclPermission('install', 'install', 'Insufficient Permissions');
        $this->addAclPermission('installLicensePlugin', 'install', 'Insufficient Permissions');
        $this->addAclPermission('restorePlugin', 'install', 'Insufficient Permissions');

        $this->addAclPermission('updatePlugin', 'update', 'Insufficient Permissions');
        $this->addAclPermission('savePlugin', 'update', 'Insufficient Permissions');
    }
    /**
     * @var $communityStore CommunityStore
     */
    protected $communityStore = null;
    /**
     * @var \Shopware\Components\Model\ModelRepository[]
     */
    public static $repository = null;
    /**
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
     * The init function calls first the parent init function. After the parent classes initialed,
     * the plugin list will be refreshed over the "refreshPluginList" function.
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/");
        parent::init();
    }

    private function isStoreApiAvailable()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('plugin'))
                ->from('Shopware\Models\Plugin\Plugin', 'plugin')
                ->where('plugin.name = :name')
                ->andWhere('plugin.installed IS NOT NULL')
                ->andWhere('plugin.active = :active')
                ->setParameter('name', 'StoreApi')
                ->setParameter('active', 1);

        $api = $builder->getQuery()->getArrayResult();
        if (empty($api)) {
            return false;
        }
        try {
            $product = $this->getCommunityStore()->getProductService()->getProductById(-654);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

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
     * index action is called if no other action is triggered
     * @return void
     */
    public function indexAction()
    {
        $this->View()->assign('storeApiAvailable', $this->isStoreApiAvailable());
    }

    public function loadAction()
    {
        $this->View()->assign('storeApiAvailable', $this->isStoreApiAvailable());
    }

    /**
     * The pluginListAction() function returns an array with all installed plugins in your shop.
     * It is used in the plugin manager backend module, for the the plugin listing.
     * The function accepts the following parameters which can be set in the request object:
     *   offset / limit => int values for a store paging
     *   sort           => array with order by parameters for the listing query object
     *   filter         => array with query conditions, expects a two dimensional array with the array keys "property" and "value" for each element.
     */
    public function pluginListAction()
    {
        try {
            $category = $this->Request()->getParam('category', null);
            $start = $this->Request()->getParam('start', null);
            $limit = $this->Request()->getParam('limit', null);
            $sort = $this->Request()->getParam('sort', null);
            $filter = $this->Request()->getParam('filter', null);

            try {
                $this->refreshPluginList();
            } catch (Exception $e) {

            }

            $plugins = $this->getPlugins($category, $start, $limit, $sort, $filter);

            $this->View()->assign(array(
                'success' => true,
                'data' => $plugins['plugins'],
                'total' => $plugins['total']
            ));
        } catch (Exception $e) {
            if (!empty($plugins)) {
                $this->View()->assign(array(
                   'success' => true,
                   'data' => $plugins['plugins'],
                   'total' => $plugins['total']
                ));
            } else {
                $this->View()->assign(array(
                   'success' => false,
                   'message' => $e->getMessage()
                ));
            }
        }
    }

    /**
     * The detailAction function returns the plugin configuration and the community data for the passed
     * plugin id. Then community data is identified over the plugin name.
     */
    public function detailAction()
    {
        $id = $this->Request()->getParam('pluginId', null);
        if (empty($id)) {
            $this->View()->assign(array(
               'success' => false,
               'noId' => true
            ));
            return;
        }

        $data = $this->getPlugin($id, \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $plugin = $this->getPlugin($id, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!$plugin instanceof \Shopware\Models\Plugin\Plugin) {
            $this->View()->assign(array(
               'success' => false,
               'noId' => true
            ));
            return;
        }

        $this->View()->assign(array(
           'success' => true,
           'data' => $data
        ));
    }

    /**
     * Controller action which can be used to get the store plugin data
     * for a single plugin.
     * Function expects the plugin id as parameter "pluginId".
     */
    public function getPluginStoreDataAction()
    {
        if (!$this->isStoreApiAvailable()) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Store api not available'
            ));
            return;
        }

        $id = $this->Request()->getParam('pluginId', null);
        if (empty($id)) {
            $this->View()->assign(array(
                'success' => false,
                'noId' => true
            ));
            return;
        }
        $plugin = $this->getPlugin($id, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        $data = $this->getCommunityStore()->getPluginCommunityData($plugin);

        $details = array();
        foreach ($data['details'] as $key => $detail) {
            $detail['rent_version'] = (bool) ($key === 'rent');
            $details[] = $detail;
        }
        $data['details'] = $details;

        $this->View()->assign(array(
            'success' => empty($data['code']),
            'data' => array($data)
        ));
    }

    /**
     * Internal helper function to get the current shopware version as a numeric value with four positions.
     * @return string
     */
    private function getNumericShopwareVersion()
    {
        $version = Shopware()->Config()->get('version');
        $paths = explode('.', $version);
        if (count($paths) === 3) {
            $paths[] = 0;
        }
        return (int) implode('', $paths);
    }

    /**
     * The updateablePluginsAction function is a controller action which is used in the plugin manager backend module
     * to check all available plugin updates.
     */
    public function updateablePluginsAction()
    {
        try {
            $version = $this->getCommunityStore()->getNumericShopwareVersion();

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->select(array('plugin.name', 'plugin.version', $version . ' as shopwareVersion', 'plugin.id as pluginId'))
                    ->from('Shopware\Models\Plugin\Plugin', 'plugin', 'plugin.name')
                    ->where('plugin.capabilityUpdate = 1')
                    ->andWhere('plugin.name != :pluginManager')
                    ->andWhere('plugin.name != :storeApi')
                    ->setParameter('pluginManager', 'PluginManager')
                    ->setParameter('storeApi', 'StoreApi');

            $plugins = $builder->getQuery()->getArrayResult();

            //if the plugin has an invalid version number use a fallback to 1.0.0
            foreach ($plugins as &$plugin) {
                if (preg_match('/\d{1,2}\.\d{1,2}\.\d{1,2}/',$plugin["version"]) !== 1) {
                    $plugin['version'] = '1.0.0';
                }
            }

            $plugins = $this->getCommunityStore()->getUpdateablePlugins($plugins);

            $this->View()->assign($plugins);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * The getPlugins function returns an array with the shop plugins.
     */
    private function getPlugins($category = null, $offset = null, $limit = null, $orderBy = null, $filters = null)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('plugins', 'licenses'))
                ->from('Shopware\Models\Plugin\Plugin', 'plugins')
                ->leftJoin('plugins.licenses', 'licenses')
                ->andWhere('plugins.capabilityEnable = 1');

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $order) {
                if (empty($order) || empty($order['property'])) {
                    continue;
                }
                $builder->addOrderBy('plugins.' . $order['property'], $order['direction']);
            }
        }
        $builder->addOrderBy('plugins.added', 'DESC');

        if (!empty($category)) {
            $builder->andWhere('plugins.source = :source')
                    ->setParameter('source', $category);
        }

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if ($filter['property'] === 'free') {
                    $builder->andWhere(
                        $builder->expr()->orX(
                            'plugins.name LIKE :free',
                            'plugins.label LIKE :free',
                            'plugins.namespace LIKE :free',
                            'plugins.description LIKE :free',
                            'plugins.added LIKE :free',
                            'plugins.installed LIKE :free',
                            'plugins.updated LIKE :free',
                            'plugins.author LIKE :free'
                        )
                    );
                    $builder->setParameter('free', '%' . $filter['value'] . '%');
                } else {
                    $builder->addFilter($filter);
                }
            }
        }

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = Shopware()->Models()->createPaginator($query);

        //returns the total count of the query
        $total = $paginator->count();

        //returns the customer data
        $plugins = $paginator->getIterator()->getArrayCopy();
        $plugins = $this->setPluginIcons($plugins);

        return array(
            'plugins' => $plugins,
            'total' => $total
        );
    }

    public function downloadDummyAction()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');
        $name = $this->Request()->getParam('name');

        $plugin = $this->getPluginByName($name);
        if (!$plugin instanceof \Shopware\Models\Plugin\Plugin) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $namespace->get('locale_plugin_not_found', "The locale plugin can't be found!")
            ));
            return;
        }

        /**@var $plugin \Shopware\Models\Plugin\Plugin*/
        $bootstrap = $this->getPluginBootstrap($plugin);
        $tmpPath = '/tmp/' . $plugin->getName() . '_BACKUP';
        if (file_exists($tmpPath)) {
            $this->removeDirectory($tmpPath);
        }

        if (file_exists($bootstrap->Path())) {
            rename($bootstrap->Path(), $tmpPath);
        }

        $url = Shopware()->Plugins()->Backend()->StoreApi()->Config()->DummyPluginUrl;

        $version = $this->getCommunityStore()->getNumericShopwareVersion();

        $url = str_replace('%version%', $version, $url);
        $url = str_replace('%name%', $name, $url);

        $result = $this->getCommunityStore()->downloadPlugin($url, 'Default');
        $result['activated'] = false;
        $result['installed'] = false;

        $this->View()->assign($result);
     }

    /**
     * Controller Action to trigger the download of a plugin by a given name
     */
    public function downloadPluginByNameAction()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        $name = $this->Request()->getParam('name', null);
        if (!$name) {
            $this->View()->assign(array(
                 'success' => false,
                 'message' => $namespace->get('no_valid_parameter', "Not all parameters are valid!")
             ));
             return;
        }

        $downloadResult = $this->downloadPluginByName($name);

        $this->View()->assign($downloadResult);
    }

    /**
     * @param string $name
     * @return Array
     */
    public function downloadPluginByName($name)
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        $pluginModel = $this->getPluginByName($name);
        $oldActive = $pluginModel->getActive();
        $oldInstalled = $pluginModel->getInstalled();
        $oldInstalled = !empty($oldInstalled);

        $response = $this->getCommunityStore()->getPluginInfos(array($name));
        if ($response['success'] && $response['data'] && !empty($response['data'])) {
            $plugin = $response['data'][0];
        } else {
            return array(
                'success' => false,
                'message' => $namespace->get(
                    'store_plugin_not_found',
                    "The store plugin can't be found!"
                ) . '<br>' . $response['message']
            );
        }

        $downloadResult = $this->downloadUpdate($plugin->getId(), $name);
        $downloadResult['articleId'] = $plugin->getId();
        $downloadResult['activated'] = $oldActive;
        $downloadResult['installed'] = $oldInstalled;
        $downloadResult['availableVersion'] = $plugin->getVersion();

        return $downloadResult;
    }

    /**
     * Controller action to trigger the download of an update for a given store id
     */
    public function downloadUpdateAction()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');
        $articleId = $this->Request()->getParam('articleId');
        $name = $this->Request()->getParam('name');

        if (empty($name) || empty($articleId)) {
            $this->View()->assign(array(
                 'success' => false,
                 'message' => $namespace->get('no_valid_parameter', "Not all parameters are valid!")
             ));
             return;
        }

        $result = $this->downloadUpdate($articleId,  $name);

        $this->View()->assign($result);
    }

    /**
     * Downloads a store plugin by its store-articleId
     *
     * @param int $articleId
     * @param string $name
     * @return Array
     */
    public function downloadUpdate($articleId, $name)
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        //after the product founded, we have to get the domain object for the current host and shopware account
        $domain = $this->getCommunityStore()->getAccountService()->getDomain(
            $this->getCommunityStore()->getIdentity(),
            $this->Request()->getHttpHost()
        );

        if ($domain instanceof Shopware_StoreApi_Exception_Response) {
            $message = $domain->getMessage();
            if ($domain->getCode() === 200) {
                $message = $this->getCommunityStore()->getDomainMessage();
            }
            return array(
                'success' => false,
                'code' => $domain->getCode(),
                'message' => $message
            );
        }

        /** @var $product Shopware_StoreApi_Models_Licence */
        $product = $this->getCommunityStore()->getAccountService()->getLicencedProductById(
            $this->getCommunityStore()->getIdentity(),
            $domain,
            $articleId,
            $this->getCommunityStore()->getNumericShopwareVersion()
        );

        if ($product instanceof Shopware_StoreApi_Exception_Response) {
            return array(
                'success' => false,
                'message' => $namespace->get(
                    'store_plugin_not_found',
                    "The store plugin can't be found!"
                ) . '<br>' . $product->getMessage()
            );
        }

        try {
            $downloads = $product->getDownloads();
            $url = $downloads['download']['url'];

            $plugin = $this->getPluginByName($name);
            if (!$plugin instanceof \Shopware\Models\Plugin\Plugin) {
                return array(
                    'success' => false,
                    'message' => $namespace->get('locale_plugin_not_found', "The locale plugin can't be found!")
                );
            }

            /**@var $plugin \Shopware\Models\Plugin\Plugin */
            $bootstrap = $this->getPluginBootstrap($plugin);
            $activated = $plugin->getActive();
            $installed = $plugin->getInstalled();

            $tmpPath = '/tmp/' . $plugin->getName() . '_BACKUP';
            if (file_exists($tmpPath)) {
                $this->removeDirectory($tmpPath);
            }

            if (file_exists($bootstrap->Path())) {
                rename($bootstrap->Path(), $tmpPath);
            }
            $source = 'Community';
            if (strlen($plugin->getSource()) > 0) {
                $source = ucfirst(strtolower($plugin->getSource()));
            }

            $result = $this->getCommunityStore()->downloadPlugin($url, $source);
            $result['activated'] = $activated;
            $result['installed'] = $installed;
            return $result;
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * @param string $name
     * @return \Shopware\Models\Plugin\Plugin
     */
    private function getPluginByName($name)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('plugin'))
                       ->from('Shopware\Models\Plugin\Plugin', 'plugin')
                       ->where('plugin.name = :name')
                       ->setParameter('name', $name)
                       ->setFirstResult(0)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * Internal helper function to set the grid icons into the passed plugin array.
     * @param $plugins
     * @return mixed
     */
    private function setPluginIcons($plugins)
    {
        foreach ($plugins as &$plugin) {
            $fullPath = Shopware()->AppPath('Plugins_' . $plugin['source'] . '_' . $plugin['namespace']);
            $pluginPath = str_replace(Shopware()->OldPath(), '', $fullPath);
            $fullPath = $fullPath . $plugin['name'] . DIRECTORY_SEPARATOR . 'plugin.png';
            $pluginPath = $pluginPath . $plugin['name'] . DIRECTORY_SEPARATOR . 'plugin.png';

            if (file_exists($fullPath)) {
                $plugin['icon'] = $this->Request()->getBasePath() . DIRECTORY_SEPARATOR . ltrim($pluginPath, DIRECTORY_SEPARATOR);
            } else {
                $plugin['icon'] = null;
            }
        }
        return $plugins;
    }

    public function restorePluginAction()
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');
        $name = $this->Request()->getParam('name');
        $activated = $this->Request()->getParam('activated', null);
        $installed = $this->Request()->getParam('installed', null);
        $version = $this->Request()->getParam('version', null);
        try {
            $plugin = $this->getPluginByName($name);
            if (!$plugin instanceof \Shopware\Models\Plugin\Plugin) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => $namespace->get('plugin_not_found', "The plugin can't be found.")
                ));
                return;
            }

            /**@var $plugin \Shopware\Models\Plugin\Plugin*/
            $bootstrap = $this->getPluginBootstrap($plugin);
            if ($plugin->getActive()) {
                $result = $bootstrap->disable();
                $plugin->setActive(false);
                Shopware()->Models()->flush();
            }

            $tmpPath = '/tmp/' . $plugin->getName() . '_BACKUP';

            if (!file_exists($tmpPath)) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => $namespace->get('backup_no_more_exist', "The plugin backup exists no more.")
                ));
                return;
            }

            $path = $bootstrap->Path();
            $this->removeDirectory($path);
            rename($tmpPath, $path);
        } catch (Exception $e) {
            $this->View()->assign(array(
               'success' => false,
               'message' => $e->getMessage()
            ));
            return;
        }

        $plugin->setVersion($version);
        Shopware()->Models()->flush();
        $this->View()->assign(array(
            'success' => true
        ));

    }

    private function removeDirectory($path)
    {
        $it = new RecursiveDirectoryIterator($path);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        $returns = array();
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

    /**
     * The updatePluginAction is a controller action function which called over the plugin manager
     * when the user want to update a plugin.
     */
    public function updatePluginAction()
    {
        $name = $this->Request()->getParam('name');
        $availableVersion = $this->Request()->getParam('availableVersion');
        $activated = $this->Request()->getParam('activated', null);
        $installed = $this->Request()->getParam('installed', null);

        $result = $this->updatePlugin($name, $availableVersion, $installed, $activated);

        $this->View()->assign($result);
    }


    /**
     * Updates a given plugin
     *
     * @param string $name
     * @param string $availableVersion
     * @param bool $installed
     * @param bool $activated
     * @return array|bool
     */
    public function updatePlugin($name, $availableVersion, $installed, $activated)
    {
        $plugin = $this->getPluginByName($name);

        $bootstrap = $this->getPluginBootstrap($plugin);

        if ($plugin->getActive() && $bootstrap) {
            $plugin->setActive(false);
            Shopware()->Models()->flush();
        }

        $result = $this->savePlugin(
            $plugin->getId(),
            array(
                'version' => $availableVersion,
                'installed' => $installed,
                'active' => $activated
            )
        );

        if (empty($result)) {
            $result = array('success' => true);
        }

        if ($activated && ($result['success'] || $result === true)) {
            Shopware()->Models()->clear();
            $plugin = $this->getPluginByName($name);
            /**@var $plugin \Shopware\Models\Plugin\Plugin */
            $plugin->setActive(true);
        }
        $plugin->setUpdateVersion($plugin->getVersion());

        Shopware()->Models()->flush();
        return $result;
    }

    /**
     * The savePluginAction function is a controller action function and can be called for example
     * over the url plugin.
     * This function saves the passed plugin data. If the install flag or active flag changed,
     * the plugin will be installed/uninstalled or activated/deactivated.
     */
    public function savePluginAction()
    {
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? $data[0] : $data;
        $id = (int) $data['id'];
        $result = $this->savePlugin($id, $data);
        $this->View()->assign($result);
    }

    /**
     * @param int $id
     * @param array $data
     * @return array|bool
     */
    private function savePlugin($id, $data)
    {
        try {
            $repository = $this->getRepository();
            /** @var $plugin Shopware\Models\Plugin\Plugin */
            $plugin = $repository->find($id);

            // Special handling for dummy plugins
            if ($data['capabilityDummy']) {
                $plugin->setVersion($data['updateVersion']);
                $plugin->setUpdateVersion(null);
                $plugin->setActive(false);
                $plugin->disableDummy();

                Shopware()->Models()->flush();

                $bootstrap = $this->getPluginBootstrap($plugin);

                /** @var $namespace Shopware_Components_Plugin_Namespace */
                $namespace = $bootstrap->Collection();
                $result    = $namespace->installPlugin($bootstrap);

                Shopware()->Models()->flush();

                return array(
                    'success' => $result
                );
            }

            $bootstrap = $this->getPluginBootstrap($plugin);
            /** @var $namespace Shopware_Components_Plugin_Namespace */
            $namespace = $bootstrap->Collection();

            if ($plugin->getVersion() != $data['version']) {
                $result = $namespace->updatePlugin($bootstrap);
            } elseif (!$plugin->getInstalled() !== empty($data['installed'])) {
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
                if ($success) {
                    $plugin->setActive(!empty($data['active']));
                }
                Shopware()->Models()->flush();
            }

        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
        if (is_bool($result)) {
            $result = array(
                'success' => $result
            );
        }
        return $result;
    }


    /**
     * Internal helper function to get single plugin data
     * @param     $id
     * @param int $hydrateMode
     * @return mixed
     */
    private function getPlugin($id, $hydrateMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('plugins', 'configForms', 'elements'))
                ->from('Shopware\Models\Plugin\Plugin', 'plugins')
                ->leftJoin('plugins.configForms', 'configForms')
                ->leftJoin('configForms.elements', 'elements')
                ->andHaving('plugins.id = :id')
                ->setParameter('id', $id)
                ->setFirstResult(0)
                ->setMaxResults(1);

        return $builder->getQuery()->getOneOrNullResult($hydrateMode);
    }

    /**
     * Returns a certain plugin by plugin id.
     *
     * @param \Shopware\Models\Plugin\Plugin $plugin
     * @return Shopware_Components_Plugin_Bootstrap|null
     */
    private function getPluginBootstrap($plugin)
    {
        $namespace = Shopware()->Plugins()->get($plugin->getNamespace());
        if ($namespace === null) {
            return null;
        }
        $plugin = $namespace->get($plugin->getName());
        return $plugin;
    }

    public function installLicensePluginAction()
    {
        $plugin = $this->getLocaleLicensePlugin();
        $bootstrap = $this->getPluginBootstrap($plugin);

        if (!$plugin->getInstalled()) {
            $result = $bootstrap->Collection()->installPlugin($bootstrap);
        }

        if (!$plugin->getActive()) {
            $result = $bootstrap->enable();
            if ($result) {
                $plugin->setActive(true);
            }
            Shopware()->Models()->flush();
        }
        $this->View()->assign(array('success' => $result));
    }

    /**
     * Internal helper function to check if the license plugin exist on the system.
     * @return mixed
     */
    private function getLocaleLicensePlugin()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('plugin'))
                       ->from('Shopware\Models\Plugin\Plugin', 'plugin')
                       ->where('plugin.name = :name')
                       ->setParameter('name', 'SwagLicense')
                       ->setFirstResult(0)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
    }

    public function refreshPluginListAction()
    {
        $this->refreshPluginList();
        $this->View()->assign(array('success' => true));
    }

    /**
     * The refreshPluginList function reads out all plugins from the file system
     * and creates a entry in the s_core_plugins table.
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
        foreach ($pluginIds as $pluginId) {
            $plugin = $this->getRepository()->find($pluginId);
            Shopware()->Models()->remove($plugin);
        }
        Shopware()->Models()->flush();
    }

    /**
     * The uploadAction function is a controller action function which can be called over the url|action plugin.
     * This function is responsible to upload|install plugins not over the community store rather over a locale
     * file upload.
     */
    public function uploadAction()
    {
        $upload = new Zend_File_Transfer_Adapter_Http();
        try {
            $upload->addValidator('Extension', false, 'zip');
            $installed = false;
            $message = '';
            if (!$upload->receive()) {
                $message = $upload->getMessages();
                $message = implode("\n", $message);
            } else {
                $info = $this->getPluginInfo($upload->getFileName());
                if (empty($info)) {
                    die(json_encode(array('success' => false, 'noNamespace' => true)));
                }
                $this->getCommunityStore()->decompressFile($upload->getFileName());
                $this->refreshPluginList();
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($upload->getFileName()) {
            @unlink($upload->getFileName());
        }

        die(json_encode(array('success' => empty($message), 'message' => $message, 'installed' => $installed)));
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

        if ($plugin->getSource() === "Default") {
            $message = "'Default' Plugins may not be deleted.";
        } elseif ($plugin->getInstalled() !== null) {
            $message = 'Please uninstall the plugin first.';
        } else {
            $this->removeDirectory($pluginPath);
            Shopware()->Models()->remove($plugin);
            Shopware()->Models()->flush();
        }

        $this->View()->assign(array(
            'success' => empty($message),
            'message' => isset($message) ? $message : ''
        ));
    }

    /**
     * Internal helper function to get the plugin namespace of an uploaded zip file.
     * @param $path
     * @return string
     */
    private function getPluginInfo($path)
    {
        $zipArchive = new ZipArchive();
        $zipArchive->open($path);
        $pluginData = '';
        for ($i=0; $i< $zipArchive->numFiles;$i++) {
            $fileInfo = $zipArchive->statIndex($i);
            //Checks if the bootstrap is available and at the right position
            preg_match('#^(Backend|Frontend|Core)\/(\w+)\/Bootstrap.php$#', $fileInfo['name'], $matches);
            if (!empty($matches)) {
                $pluginData = $matches;
                break;
            }
        }
        return $pluginData;
    }

    /**
     * Decompress a given plugin zip file.
     * @deprecated unused method use the decompressFile method in the CommunityStore component instead
     * @param $file
     * @throws Enlight_Exception
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


}
