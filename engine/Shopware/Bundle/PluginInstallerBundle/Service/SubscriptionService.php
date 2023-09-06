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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Exception;
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
    private Connection $connection;

    private StoreClient $storeClient;

    private ModelManager $modelManager;

    private PluginLicenceService $pluginLicenceService;

    private ShopwareReleaseStruct $release;

    /**
     * @var Exception|null
     */
    private $exception;

    public function __construct(
        Connection $connection,
        StoreClient $storeClient,
        ModelManager $modelManager,
        PluginLicenceService $pluginLicenceService,
        ShopwareReleaseStruct $release
    ) {
        $this->connection = $connection;
        $this->storeClient = $storeClient;
        $this->modelManager = $modelManager;
        $this->pluginLicenceService = $pluginLicenceService;
        $this->release = $release;
    }

    /**
     * Reset the Secret in the database
     *
     * @return void
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

        return unserialize($statement->fetchColumn(), ['allowed_classes' => false]);
    }

    /**
     * Set new secret to the database
     *
     * @return void
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
     * @return PluginInformationResultStruct|false
     */
    public function getPluginInformation(Response $response, Request $request)
    {
        if (!$this->isPluginsSubscriptionCookieValid($request)) {
            return false;
        }

        try {
            $cookie = new Cookie('lastCheckSubscriptionDate', date('dmY'), time() + 60 * 60 * 24, '/', null, $request->isSecure());
            $response->headers->setCookie($cookie);

            return $this->getPluginInformationFromApi();
        } catch (ShopSecretException $e) {
            $this->exception = $e;
            $this->resetShopSecret();

            return false;
        } catch (Exception $e) {
            $this->exception = $e;

            return false;
        }
    }

    /**
     * Requests the plugin information from the store API and returns the parsed result.
     *
     * @return PluginInformationResultStruct
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
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Generate new secret by API call
     */
    private function generateApiShopSecret(): string
    {
        $allowedClassList = [
            AccessTokenStruct::class,
        ];

        $token = @unserialize(Shopware()->BackendSession()->offsetGet('store_token'), ['allowed_classes' => $allowedClassList]);

        if ($token === null || $token === false) {
            $token = Shopware()->BackendSession()->get('accessToken');
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
     */
    private function getDomain(): string
    {
        $default = $this->modelManager->getRepository(Shop::class)->getActiveDefault();

        return (string) $default->getHost();
    }

    /**
     * Check the date of the last subscription-check var
     */
    private function isPluginsSubscriptionCookieValid(Request $request): bool
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
     * @return array<array<string, string>>
     */
    private function getPluginsNameAndVersion(): array
    {
        return $this->connection->createQueryBuilder()
            ->select(['plugin.name', 'plugin.version', 'plugin.active'])
            ->from('s_core_plugins', 'plugin')
            ->execute()
            ->fetchAllAssociative();
    }
}
