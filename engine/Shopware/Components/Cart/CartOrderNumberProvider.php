<?php
declare(strict_types=1);
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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Connection;
use Shopware_Components_Config as Config;

class CartOrderNumberProvider implements CartOrderNumberProviderInterface
{
    private const CONFIG_KEYS = [
        self::DISCOUNT,
        self::SURCHARGE,
        self::PAYMENT_PERCENT,
        self::PAYMENT_ABSOLUTE,
        self::SHIPPING_SURCHARGE,
        self::SHIPPING_DISCOUNT,
    ];

    /**
     * @var array
     */
    private $data;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Connection $connection, Config $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    public function get(string $name): string
    {
        return $this->config->get($name);
    }

    public function getAll(string $name): array
    {
        $this->load();

        return $this->data[$name] ?? [];
    }

    private function load(): void
    {
        if ($this->data !== null) {
            return;
        }

        $qb = $this->connection->createQueryBuilder();
        $values = $qb->from('s_core_config_elements', 'elements')
            ->leftJoin('elements', 's_core_config_values', 'elementValues', 'elementValues.element_id = elements.id')
            ->select(['elements.name', 'CONCAT(elements.value, \'|\', IFNULL(GROUP_CONCAT(elementValues.value SEPARATOR \'|\'), \'\'))'])
            ->where('elements.name IN (:names)')
            ->setParameter('names', self::CONFIG_KEYS, Connection::PARAM_STR_ARRAY)
            ->groupBy('elements.name')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($values as $key => $value) {
            $value = array_filter(explode('|', $value));

            $this->data[$key] = array_map([$this, 'secureUnSerialize'], $value);
        }
    }

    private function secureUnSerialize(string $value): string
    {
        return unserialize($value, ['allowed_classes' => false]);
    }
}
