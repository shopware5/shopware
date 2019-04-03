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

use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginLicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\CategoryStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;
use Shopware\Bundle\PluginInstallerBundle\Struct\UpdateResultStruct;

class PluginStoreService
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    public function __construct(
        StoreClient $storeClient,
        StructHydrator $hydrator
    ) {
        $this->storeClient = $storeClient;
        $this->hydrator = $hydrator;
    }

    /**
     * @throws \Exception
     *
     * @return ListingResultStruct
     */
    public function getListing(ListingRequest $context)
    {
        $params = [
            'locale' => $context->getLocale(),
            'shopwareVersion' => $context->getShopwareVersion(),
            'offset' => $context->getOffset(),
            'limit' => $context->getLimit(),
            'sort' => json_encode($context->getSortings()),
            'filter' => json_encode($context->getConditions()),
        ];

        $data = $this->storeClient->doGetRequest(
            '/pluginStore/plugins',
            $params
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data['data']);

        return new ListingResultStruct(
            $plugins,
            $data['total']
        );
    }

    /**
     * @return PluginStruct|null
     */
    public function getPlugin(PluginsByTechnicalNameRequest $context)
    {
        $plugins = $this->getPlugins($context);

        return array_shift($plugins);
    }

    /**
     * @return PluginStruct[]
     */
    public function getPlugins(PluginsByTechnicalNameRequest $context)
    {
        $params = [
            'locale' => $context->getLocale(),
            'shopwareVersion' => $context->getShopwareVersion(),
            'technicalNames' => $context->getTechnicalNames(),
        ];

        $data = $this->storeClient->doGetRequest(
            '/pluginStore/pluginsByName',
            $params
        );

        return $this->hydrator->hydrateStorePlugins($data);
    }

    /**
     * @throws \Exception
     *
     * @return UpdateResultStruct
     */
    public function getUpdates(UpdateListingRequest $context)
    {
        $result = $this->storeClient->doGetRequest(
            '/pluginStore/updateablePlugins',
            [
                'shopwareVersion' => $context->getShopwareVersion(),
                'domain' => $context->getDomain(),
                'locale' => $context->getLocale(),
                'plugins' => $context->getPlugins(),
            ]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($result['data']);
        $gtcAcceptanceRequired = isset($result['gtcAcceptanceRequired']) ? $result['gtcAcceptanceRequired'] : false;
        $result = new UpdateResultStruct($plugins, $gtcAcceptanceRequired);

        return $result;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * @return LicenceStruct
     */
    public function getPluginLicence(PluginLicenceRequest $context)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $content = $this->storeClient->doAuthGetRequest(
            $context->getToken(),
            '/licenses',
            [
                'shopwareVersion' => $context->getShopwareVersion(),
                'domain' => $context->getDomain(),
                'pluginName' => $context->getTechnicalName(),
            ]
        );

        $licence = $this->hydrator->hydrateLicences($content);

        return array_shift($licence);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getLicences(
        LicenceRequest $context
    ) {
        $result = $this->storeClient->doAuthGetRequest(
            $context->getToken(),
            '/licenses',
            [
                'shopwareVersion' => $context->getShopwareVersion(),
                'domain' => $context->getDomain(),
            ]
        );

        return $this->hydrator->hydrateLicences($result);
    }

    /**
     * @throws \Exception
     *
     * @return CategoryStruct[]
     */
    public function getCategories()
    {
        $data = $this->storeClient->doGetRequest(
            '/pluginStore/categories'
        );

        return $this->hydrator->hydrateCategories($data);
    }
}
