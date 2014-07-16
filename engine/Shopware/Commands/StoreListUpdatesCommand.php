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

namespace Shopware\Commands;

use CommunityStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StoreListUpdatesCommand extends StoreCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();

        $this
            ->setName('sw:store:list:updates')
            ->setDescription('Lists updates for installed plugins.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupShopwareVersion($input);

        /** @var \CommunityStore $store */
        $store = $this->container->get('CommunityStore');

        $em = $this->container->get('models');

        $version = $store->getNumericShopwareVersion();

        $builder = $em->createQueryBuilder();
        $builder->select(array('plugin.name', 'plugin.version', $version . ' as shopwareVersion', 'plugin.id as pluginId'))
                ->from('Shopware\Models\Plugin\Plugin', 'plugin', 'plugin.name')
                ->where('plugin.capabilityUpdate = 1')
                ->andWhere('plugin.name != :pluginManager')
                ->andWhere('plugin.name != :storeApi')
                ->setParameter('pluginManager', 'PluginManager')
                ->setParameter('storeApi', 'StoreApi');

        $plugins = $builder->getQuery()->getArrayResult();

        // if the plugin has an invalid version number use a fallback to 1.0.0
        foreach ($plugins as &$plugin) {
            if (preg_match('/\d{1,2}\.\d{1,2}\.\d{1,2}/',$plugin["version"]) !== 1) {
                $plugin['version'] = '1.0.0';
            }
        }

        $resultSet = $store->getProductService()->getProductUpdates($plugins);

        $updates = array();
        foreach ($resultSet as $data) {
            $updates[] = array(
                $data['name'],
                $data['currentVersion'],
                $data['availableVersion'],
            );
        }

        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array('Name', 'CurrentVersion', 'AvailableVersion'))
              ->setRows($updates);

        $table->render($output);
    }
}
