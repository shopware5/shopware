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

namespace Shopware\Components\Plugin\Configuration\Layers;

use Doctrine\DBAL\Connection;
use LogicException;
use PDO;
use Shopware\Components\Plugin\Configuration\WriterException;

class DefaultLayer implements ConfigurationLayerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function readValues(string $pluginName, ?int $shopId): array
    {
        $pluginNameKey = 'pluginName' . abs(crc32($pluginName));
        $builder = $this->connection->createQueryBuilder();

        $values = $builder->from('s_core_config_elements', 'coreConfigElements')
            ->innerJoin(
                'coreConfigElements',
                's_core_config_forms',
                'coreConfigForms',
                'coreConfigElements.form_id = coreConfigForms.id'
            )
            ->innerJoin(
                'coreConfigForms',
                's_core_plugins',
                'corePlugins',
                'coreConfigForms.plugin_id = corePlugins.id'
            )
            ->andWhere($builder->expr()->eq('corePlugins.name', ':' . $pluginNameKey))
            ->setParameter($pluginNameKey, $pluginName)
            ->select([
                'coreConfigElements.name',
                'coreConfigElements.value',
            ])
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR)
        ;

        return AbstractShopConfigurationLayer::unserializeArray($values);
    }

    /**
     * @throws WriterException
     */
    public function writeValues(string $pluginName, ?int $shopId, array $data): void
    {
        $baseException = new LogicException('Cannot change values on default layer');
        throw new WriterException($baseException);
    }
}
