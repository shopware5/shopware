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
namespace Shopware\Bundle\PluginInstallerBundle\Struct;

/**
 * Class StructHydrator
 *
 * @package Shopware\Bundle\PluginInstallerBundle\Struct
 */
interface StructHydratorInterface
{
    /**
     * @param $data
     *
     * @return BasketStruct
     */
    public function hydrateBasket($data);

    /**
     * @param array  $data
     * @param string $shopwareId
     *
     * @return AccessTokenStruct
     */
    public function hydrateAccessToken($data, $shopwareId);

    /**
     * @param $data
     *
     * @return PluginStruct
     */
    public function hydrateStorePlugin($data);

    /**
     * @param $data
     *
     * @return PluginStruct
     */
    public function hydrateLocalPlugin($data);

    /**
     * @param $data
     *
     * @return PluginStruct[] Indexed by plugin code
     */
    public function hydrateStorePlugins($data);

    /**
     * @param $data
     *
     * @return PluginStruct[] Indexed by plugin code
     */
    public function hydrateLocalPlugins($data);

    public function assignStorePluginStruct(PluginStruct $localPlugin, PluginStruct $storePlugin);

    /**
     * @param PluginStruct $storePlugin
     * @param PluginStruct $localPlugin
     */
    public function assignLocalPluginStruct(PluginStruct $storePlugin, PluginStruct $localPlugin);

    /**
     * @param $data
     *
     * @return CategoryStruct[]
     */
    public function hydrateCategories($data);

    public function hydrateLicences($data);

    /**
     * @param $data
     *
     * @return CategoryStruct
     */
    public function hydrateCategory($data);

    /**
     * @param PluginStruct $plugin
     * @param              $data
     */
    public function assignLocalData(PluginStruct $plugin, $data);

    /**
     * @param $data
     *
     * @return LocaleStruct[]
     */
    public function hydrateLocales($data);
}
