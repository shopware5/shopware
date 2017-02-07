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
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

/**
 * Class PluginLocalService
 */
class PluginLocalService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    /**
     * @param Connection     $connection
     * @param StructHydrator $hydrator
     */
    public function __construct(Connection $connection, StructHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
    }

    /**
     * @param ListingRequest $context
     *
     * @return ListingResultStruct
     */
    public function getListing(ListingRequest $context)
    {
        $query = $this->getQuery();

        $query
            ->andWhere("plugin.name != 'PluginManager'")
            ->andWhere('plugin.capability_enable = 1')
        ;

        $this->addSortings($context, $query);

        $query->setFirstResult($context->getOffset())
            ->setMaxResults($context->getLimit());

        /** @var $statement \PDOStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $plugins = $this->iteratePlugins($data);

        return new ListingResultStruct(
            $plugins,
            count($plugins)
        );
    }

    /**
     * @param PluginsByTechnicalNameRequest $context
     *
     * @return PluginStruct
     */
    public function getPlugin(PluginsByTechnicalNameRequest $context)
    {
        $plugin = $this->getPlugins($context);

        return array_shift($plugin);
    }

    /**
     * @param PluginsByTechnicalNameRequest $context
     *
     * @return PluginStruct[]
     */
    public function getPlugins(PluginsByTechnicalNameRequest $context)
    {
        $query = $this->getQuery();
        $query->andWhere('plugin.name IN (:names)')
            ->setParameter(
                ':names',
                $context->getTechnicalNames(),
                Connection::PARAM_STR_ARRAY
            );

        /** @var $statement \PDOStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->iteratePlugins($data);
    }

    /**
     * @return array indexed by technical name, value contains the version
     */
    public function getPluginsForUpdateCheck()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['plugin.name', 'plugin.version'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.capability_update = 1');

        /** @var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param ListingRequest $context
     * @param QueryBuilder   $builder
     */
    private function addSortings(ListingRequest $context, QueryBuilder $builder)
    {
        foreach ($context->getSortings() as $sort) {
            if (!isset($sort['property'])) {
                continue;
            }
            $dir = 'ASC';
            if (isset($sort['direction'])) {
                $dir = $sort['direction'];
            }

            $builder->addOrderBy($sort['property'], $dir);
        }
    }

    /**
     * @param $plugins
     *
     * @return \Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct[]
     */
    private function iteratePlugins($plugins)
    {
        foreach ($plugins as &$row) {
            $row['iconPath'] = $this->getIconOfPlugin(
                $row['name']
            );
        }

        return $this->hydrator->hydrateLocalPlugins($plugins);
    }

    /**
     * @param string $name
     *
     * @return bool|string
     */
    private function getIconOfPlugin($name)
    {
        $rootDir = Shopware()->Container()->getParameter('kernel.root_dir');

        $path = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager')->getPluginPath($name);
        $path .= '/plugin.png';

        $relativePath = str_replace($rootDir, '', $path);
        $front = Shopware()->Container()->get('front');

        if (file_exists($path) && $front && $front->Request()) {
            return $front->Request()->getBasePath() . $relativePath;
        }

        return false;
    }

    /**
     * @return QueryBuilder
     */
    private function getQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'plugin.id',
            'plugin.name',
            'plugin.label',
            'plugin.active',
            'plugin.namespace',
            'plugin.description',
            'plugin.source',
            'plugin.version as version',
            'plugin.capability_update',
            'plugin.capability_install',
            'plugin.capability_enable',
            'plugin.capability_secure_uninstall',
            'plugin.update_version',

            'plugin.installation_date',
            'forms.id as form_id',
            'plugin.update_date',
            'plugin.author',
            'plugin.link',
            'plugin.support',

            'licence.id as __licence_id',
            'licence.host as __licence_host',
            'licence.type as __licence_type',
            'licence.creation as __licence_creation',
            'licence.expiration as __licence_expiration',
            'licence.license as __licence_license',
        ]);

        $query->from('s_core_plugins', 'plugin')
            ->leftJoin('plugin', 's_core_config_forms', 'forms', 'forms.plugin_id = plugin.id')
            ->leftJoin('plugin', 's_core_licenses', 'licence', 'licence.module = plugin.name')
            ->groupBy('plugin.id');

        return $query;
    }
}
