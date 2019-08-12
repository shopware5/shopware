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

namespace Shopware\Components\Plugin;

use Doctrine\DBAL\Connection;
use Shopware\Models\Shop\Shop;

class DBALConfigReader implements ConfigReader
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $pluginName
     *
     * @return array
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select([
                'ce.name',
                'COALESCE(currentShop.value, parentShop.value, fallbackShop.value, ce.value) as value',
            ])
            ->from('s_core_plugins', 'p')
            ->innerJoin('p', 's_core_config_forms', 'cf', 'cf.plugin_id = p.id')
            ->innerJoin('cf', 's_core_config_elements', 'ce', 'ce.form_id = cf.id')
            ->leftJoin('ce', 's_core_config_values', 'currentShop', 'currentShop.element_id = ce.id AND currentShop.shop_id = :currentShopId')
            ->leftJoin('ce', 's_core_config_values', 'parentShop', 'parentShop.element_id = ce.id AND parentShop.shop_id = :parentShopId')
            ->leftJoin('ce', 's_core_config_values', 'fallbackShop', 'fallbackShop.element_id = ce.id AND fallbackShop.shop_id = :fallbackShopId')
            ->where('p.name = :pluginName')
            ->setParameters([
                'fallbackShopId' => 1, //Shop parent id
                'parentShopId' => $shop !== null && $shop->getMain() !== null ? $shop->getMain()->getId() : 1,
                'currentShopId' => $shop !== null ? $shop->getId() : null,
                'pluginName' => $pluginName,
            ]);

        $config = $builder->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($config as $key => $value) {
            $config[$key] = !empty($value) ? @unserialize($value) : null;
        }

        return $config;
    }
}
