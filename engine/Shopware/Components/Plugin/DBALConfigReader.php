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
        $sql = <<<'SQL'
SELECT
  ce.name,
  COALESCE(currentShop.value, parentShop.value, fallbackShop.value, ce.value) as value

FROM s_core_plugins p

INNER JOIN s_core_config_forms cf
  ON cf.plugin_id = p.id

INNER JOIN s_core_config_elements ce
  ON ce.form_id = cf.id

LEFT JOIN s_core_config_values currentShop
  ON currentShop.element_id = ce.id
  AND currentShop.shop_id = :currentShopId

LEFT JOIN s_core_config_values parentShop
  ON parentShop.element_id = ce.id
  AND parentShop.shop_id = :parentShopId

LEFT JOIN s_core_config_values fallbackShop
  ON fallbackShop.element_id = ce.id
  AND fallbackShop.shop_id = :fallbackShopId

WHERE p.name=:pluginName
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'fallbackShopId' => 1, //Shop parent id
            'parentShopId' => $shop !== null && $shop->getMain() !== null ? $shop->getMain()->getId() : 1,
            'currentShopId' => $shop !== null ? $shop->getId() : null,
            'pluginName' => $pluginName,
        ]);

        $config = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($config as $key => $value) {
            $config[$key] = !empty($value) ? @unserialize($value, ['allowed_classes' => false]) : null;
        }

        return $config;
    }
}
