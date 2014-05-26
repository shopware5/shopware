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

namespace ShopwarePlugins\SwagUpdate\Components;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CommunityStore
{
    /**
     * @var \Shopware_StoreApi_Core_Service_Product
     */
    private $productService;

    /**
     * @param \Shopware_StoreApi_Core_Service_Product $productService
     */
    public function __construct(\Shopware_StoreApi_Core_Service_Product $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Return:
     *
     * array(
     *     'SwagJobQueue' => false,
     *     'SwagTestHidden' => false,
     *     'SwagBepado' => true,
     *     'SwagUpdateCheck' => true,
     *     'SwagBundle' => true,
     * )
     *
     * @param  array   $plugins
     * @param  integer $version numeric shopware version
     * @return array
     */
    public function getPluginsAvailableFor($plugins, $version)
    {
        $resultSet = $this->getProductsByNameAndVersion($plugins, $version);

        return $this->formatResult($plugins, $resultSet);
    }

    /**
     * Get plugin infos for a list of plugin names
     *
     * @param  array                               $plugins
     * @throws \Exception
     * @return \Shopware_StoreApi_Models_Product[]
     */
    public function getPluginInfos($plugins)
    {
        $resultSet = $this->getProductsByName($plugins);

        if ($resultSet instanceof \Shopware_StoreApi_Exception_Response) {
            // If code is not 200 we have a real error here
            if ($resultSet->getCode() != 200) {
                throw new \Exception($resultSet->getMessage(), $resultSet->getCode());
            }

            return array();
        }

        return $resultSet->getIterator();
    }

    /**
     * @param  array                                         $plugins
     * @return \Shopware_StoreApi_Core_Response_SearchResult | \Shopware_StoreApi_Exception_Response
     */
    private function getProductsByName($plugins)
    {
        // Construct API query
        $productQuery = new \Shopware_StoreApi_Models_Query_Product();

        $productQuery->addCriterion(
            new \Shopware_StoreApi_Models_Query_Criterion_PluginName($plugins)
        );

        return $this->productService->getProducts($productQuery);
    }

    /**
     * @param  array                                         $plugins
     * @param  integer                                       $version
     * @return \Shopware_StoreApi_Core_Response_SearchResult | \Shopware_StoreApi_Exception_Response
     */
    private function getProductsByNameAndVersion($plugins, $version)
    {
        // Construct API query
        $productQuery = new \Shopware_StoreApi_Models_Query_Product();

        $productQuery->addCriterion(
            new \Shopware_StoreApi_Models_Query_Criterion_PluginName($plugins)
        );
        $productQuery->addCriterion(
            new \Shopware_StoreApi_Models_Query_Criterion_Version($version)
        );

        return $this->productService->getProducts($productQuery);
    }

    /**
     * @param  array                                                                               $plugins
     * @param  \Shopware_StoreApi_Core_Response_SearchResult|\Shopware_StoreApi_Exception_Response $resultSet
     * @throws \Exception
     * @return array
     */
    private function formatResult($plugins, $resultSet)
    {
        // First mark all plugins as incompatible
        $results = array();
        foreach ($plugins as $name) {
            $results[$name] = false;
        }

        if ($resultSet instanceof \Shopware_StoreApi_Exception_Response) {
            // If code is not 200 we have a real error here
            if ($resultSet->getCode() != 200) {
                throw new \Exception($resultSet->getMessage(), $resultSet->getCode());
            }
        }

        // mark returned plugins as compatible
        foreach ($resultSet as $productModel) {
            $names = $productModel->getPluginNames();
            foreach ($names as $name) {
                $results[$name] = true;
            }
        }

        return $results;
    }
}
