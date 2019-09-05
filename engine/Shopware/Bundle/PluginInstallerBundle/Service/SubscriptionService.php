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
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationStruct;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Models\Shop\Shop;
use Symfony\Component\HttpFoundation\Cookie;

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
     * @var PluginLicenceService
     */
    private $pluginLicenceService;

    /**
     * @var ShopwareReleaseStruct
     */
    private $release;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(Connection $connection, StoreClient $storeClient, ModelManager $models, PluginLicenceService $pluginLicenceService, ShopwareReleaseStruct $release)
    {
        $this->connection = $connection;
        $this->storeClient = $storeClient;
        $this->models = $models;
        $this->pluginLicenceService = $pluginLicenceService;
        $this->release = $release;
    }

    /**
     * Reset the Secret in the database
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
     * Get current secret from the database
     *
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

        $secret = unserialize($statement->fetchColumn(), ['allowed_classes' => false]);

        return $secret;
    }

    /**
     * Set new secret to the database
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
     * Returns information about shop upgrade state and installed plugins.
     *
     * @return PluginInformationResultStruct|bool
     */
    public function getPluginInformation(Response $response, Request $request)
    {
        if (!$this->isPluginsSubscriptionCookieValid($request)) {
            return false;
        }

        try {
            $response->headers->setCookie(new Cookie('lastCheckSubscriptionDate', date('dmY'), time() + 60 * 60 * 24));

            return $this->getPluginInformationFromApi();
        } catch (ShopSecretException $e) {
            $this->exception = $e;
            $this->resetShopSecret();

            return false;
        } catch (\Exception $e) {
            $this->exception = $e;

            return false;
        }
    }

    /**
     * Requests the plugin information from the store API and returns the parsed result.
     *
     * @return PluginInformationResultStruct|false
     */
    public function getPluginInformationFromApi()
    {
        $secret = $this->getShopSecret();

        $domain = $this->getDomain();
        $params = [
            'domain' => $domain,
            'shopwareVersion' => $this->release->getVersion(),
            'plugins' => $this->getPluginsNameAndVersion(),
        ];

        $header = $secret ? ['X-Shopware-Shop-Secret' => $secret] : [];

        $data = $this->storeClient->doPostRequest(
            '/pluginStore/environmentInformation',
            $params,
            $header
        );

        $isShopUpgraded = $data['general']['isUpgraded'];
        $pluginInformationStructs = array_map(
            function ($plugin) {
                return new PluginInformationStruct($plugin);
            },
            $data['plugins']
        );

        if (isset($data['general']['missingLicenseWarningThreshold'])) {
            $this->connection->update(
                's_core_config_elements',
                ['value' => serialize($data['general']['missingLicenseWarningThreshold'])],
                ['name' => 'missingLicenseWarningThreshold', 'form_id' => 0]
            );
        }

        if (isset($data['general']['missingLicenseStopThreshold'])) {
            $this->connection->update(
                's_core_config_elements',
                ['value' => serialize($data['general']['missingLicenseStopThreshold'])],
                ['name' => 'missingLicenseStopThreshold', 'form_id' => 0]
            );
        }

        $this->pluginLicenceService->updateLocalLicenseInformation($pluginInformationStructs, $domain);

        return new PluginInformationResultStruct($pluginInformationStructs, $isShopUpgraded);
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Generate new secret by API call
     *
     * @return string
     */
    private function generateApiShopSecret()
    {
        $allowedClassList = [
            AccessTokenStruct::class,
        ];

        $token = unserialize(Shopware()->BackendSession()->offsetGet('store_token'), ['allowed_classes' => $allowedClassList]);

        if ($token === null) {
            $token = Shopware()->BackendSession()->accessToken;
        }

        $params = [
            'domain' => $this->getDomain(),
        ];

        $data = $this->storeClient->doAuthGetRequest(
            $token,
            '/shopsecret',
            $params
        );

        return $data['secret'];
    }

    /**
     * Returns the domain of the shop
     *
     * @return string
     */
    private function getDomain()
    {
        $repo = $this->models->getRepository(Shop::class);

        $default = $repo->getActiveDefault();

        return $default->getHost();
    }

    /**
     * Check the date of the last subscription-check var
     *
     * @return bool
     */
    private function isPluginsSubscriptionCookieValid(Request $request)
    {
        if ($request->getParam('force')) {
            return true;
        }

        $lastCheck = $request->getCookie('lastCheckSubscriptionDate');

        return $lastCheck !== date('dmY');
    }

    /**
     * Get all plugins with name and version
     *
     * @return array
     */
    private function getPluginsNameAndVersion()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select(['plugin.name', 'plugin.version'])
            ->from('s_core_plugins', 'plugin');

        $builderExecute = $queryBuilder->execute();

        return $builderExecute->fetchAll();
    }
}
