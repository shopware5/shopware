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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Shopware\Bundle\PluginInstallerBundle\Exception\ShopSecretException;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\SubscriptionStateStruct;
use Shopware\Components\Model\ModelManager;

/**
 * Class StoreOrderService
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
class SubscriptionService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @param Connection $connection
     * @param StoreClient $storeClient
     * @param ModelManager $models
     */
    public function __construct(Connection $connection, StoreClient $storeClient, ModelManager $models)
    {
        $this->connection = $connection;
        $this->storeClient = $storeClient;
        $this->models = $models;
    }

    /**
     * reset the Secret in the database
     */
    public function resetShopSecret()
    {
        $this->connection->update(
            's_core_config_elements',
            ['value' => serialize('')],
            ['name' => 'tokenSecret', 'form_id' => 0]
        );
    }

    /**
     * get current secret from the database
     * @return string
     */
    public function getShopSecret()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('value')
            ->from('s_core_config_elements', 'element')
            ->where('element.name = \'tokenSecret\'')
            ->andWhere('element.form_id = 0');

        $statement = $queryBuilder->execute();

        $secret = $statement->fetchColumn();
        $secret = unserialize($secret);

        return $secret;
    }

    /**
     * set new secret to the database
     */
    public function setShopSecret()
    {
        $secret = $this->generateApiShopSecret();

        $this->connection->update(
            's_core_config_elements',
            ['value' => serialize($secret)],
            ['name' => 'tokenSecret']
        );
    }

    /**
     * Returns not upgraded plugins, "hacked" plugins, plugins, after do some check secret and cookie
     * @param Response $response
     * @param Request $request
     * @return SubscriptionStateStruct|bool
     */
    public function getPluginsSubscription(Response $response, Request $request)
    {
        if ($this->isPluginsSubscriptionCookieValid($request) == false) {
            return false;
        }

        try {
            $secret = $this->getShopSecret();
            if (empty($secret)) {
                return false;
            }

            $pluginStates = $this->getPluginsSubscriptionState($secret);
            $response->setCookie('lastCheckSubscriptionDate', date('dmY'), time() + 60 * 60 * 24);

            return $pluginStates;
        } catch (ShopSecretException $e) {
            $this->resetShopSecret();
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * function that return not upgraded plugins, plugins with wrong version, plugins which loose subscription, information if license upgrade for shop was executed
     * @param String $secret
     * @return SubscriptionStateStruct
     */
    public function getPluginsSubscriptionState($secret)
    {
        $params = [
            'domain'            => $this->getDomain(),
            'shopwareVersion'   => \Shopware::VERSION,
            'plugins'           => $this->getPluginsNameAndVersion()
        ];

        $header = [
            'X-Shopware-Shop-Secret' => $secret
        ];

        $data = $this->storeClient->doGetRequest(
            '/pluginStore/pluginSubscription',
            $params,
            $header
        );

        $technicalNames = array_column($data['subscription'], 'name');
        $technicalNames = array_merge($technicalNames, array_column($data['notUpgraded'], 'name'));
        $technicalNames = array_merge($technicalNames, array_column($data['wrongVersion'], 'name'));
        $technicalNames = array_values(array_unique($technicalNames));

        $labels = $this->getPluginLabelsByNames($technicalNames);

        $data['subscription'] = $this->assignLabels($data['subscription'], $labels);
        $data['notUpgraded'] = $this->assignLabels($data['notUpgraded'], $labels);
        $data['wrongVersion'] = $this->assignLabels($data['wrongVersion'], $labels);

        $subscriptionStateStruct = new SubscriptionStateStruct($data['shopUpgraded'], $data['notUpgraded'], $data['wrongVersion'], $data['subscription']);

        return $subscriptionStateStruct;
    }

    /**
     * generate new Secret by API Call
     * @return string
     */
    private function generateApiShopSecret()
    {
        $token = Shopware()->BackendSession()->offsetGet('store_token');
        $token = unserialize($token);

        if ($token == null) {
            $token = Shopware()->BackendSession()->accessToken;
        }

        $params = [
            'domain'    => $this->getDomain()
        ];

        $data = $this->storeClient->doAuthGetRequest(
            $token,
            '/shopsecret',
            $params
        );
        return $data['secret'];
    }

    /**
     * returns the domain of the shop
     * @return string
     */
    private function getDomain()
    {
        $repo = $this->models->getRepository('Shopware\Models\Shop\Shop');

        $default = $repo->getActiveDefault();

        return $default->getHost();
    }

    /**
     * Check the date of the last subscription-check var
     * @param Request $request
     * @return bool
     */
    private function isPluginsSubscriptionCookieValid(Request $request)
    {
        $lastCheck = $request->getCookie('lastCheckSubscriptionDate');

        return $lastCheck != date('dmY');
    }

    /**
     * @param array[] $plugins
     * @param PluginStruct[] $labels
     * @return array[]
     */
    private function assignLabels($plugins, $labels)
    {
        foreach ($plugins as &$plugin) {
            $name = $plugin['name'];
            if (isset($labels[$name])) {
                $plugin['label'] = $labels[$name];
            } else {
                $plugin['label'] = $plugin['name'];
            }
        }

        return $plugins;
    }

    /**
     * @param string[] $names
     * @return string[]
     */
    private function getPluginLabelsByNames($names)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['plugins.name', 'plugins.label'])
            ->from('s_core_plugins', 'plugins')
            ->where('plugins.name IN (:names)')
            ->setParameter('names', $names, Connection::PARAM_STR_ARRAY);

        /**@var $statement \PDOStatement*/
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Get all plugins with name and version
     * @return array
     */
    private function getPluginsNameAndVersion()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select(['plugin.name', 'plugin.version'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.active = 1');

        $builderExecute = $queryBuilder->execute();
        $plugins = $builderExecute->fetchAll();

        return $plugins;
    }

    /**
     * function to get expired plugins
     * @return array
     */
    public function getExpiredPluginLicenses()
    {
        if ($this->checkLicensePluginIsInstalled() == false) {
            return [];
        }

        //get all licenses
        $expiredPlugins = [];
        $expireDays = 14; //Days to warn before plugin gets expired
        $licenses = $this->getLicences();

        if (empty($licenses)) {
            return [];
        }

        //decode all license and get info, check for expiring
        foreach ($licenses as $license) {
            $info = \Shopware_Components_License::readLicenseInfo($license['license']);

            $expirationDate = $this->getLicenceExpirationDate($info);

            if ($expirationDate === null) {
                continue;
            }

            $diff = $expirationDate->diff(new \DateTime('now'));

            if ($diff->invert == 1 && $diff->days <= $expireDays) {
                $expiredPlugins[] = [
                    'expireDate' => $expirationDate,
                    'plugin' => $info['label']
                ];
            }
        }

        return $expiredPlugins;
    }

    /**
     * check if license plugin is installed
     * @return boolean
     */
    private function checkLicensePluginIsInstalled()
    {
        $connection = $this->connection;
        $builder = $connection->createQueryBuilder();

        $builder->select(['plugin.id'])
            ->from('s_core_plugins', 'plugin')
            ->where("plugin.name = 'License'")
            ->andWhere('plugin.active = 1')
            ->andWhere('plugin.installation_date IS NOT NULL');

        $builderExecute = $builder->execute();
        $exist = $builderExecute->fetchColumn();

        return (empty($exist)) ? false : true;
    }

    /**
     * function to get all plugin licenses
     * @return array
     */
    private function getLicences()
    {
        /**@var $connection Connection */
        $connection = $this->connection;
        $builder = $connection->createQueryBuilder();

        $builder->select(['license.label', 'license.license'])
            ->from('s_core_licenses', 'license');

        $builderExecute = $builder->execute();
        return $builderExecute->fetchAll();
    }

    /**
     * get expiration date of license-info-array
     * @param $info
     * @return null|\DateTime
     */
    private function getLicenceExpirationDate($info)
    {
        if (empty($info['expiration'])) {
            return null;
        }

        return new \DateTime($info['expiration']);
    }
}
