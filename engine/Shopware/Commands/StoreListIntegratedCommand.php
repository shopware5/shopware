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

namespace Shopware\Commands;

use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StoreListIntegratedCommand extends StoreCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:store:list:integrated')
            ->setDescription('List all integrated plugins.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopwareVersion = $this->container->getParameter('shopware.release.version');
        $context = new ListingRequest('', $shopwareVersion, 0, 1000, [['property' => 'dummy', 'value' => 1]], []);
        $listing = $this->container->get('shopware_plugininstaller.plugin_service_view')->getStoreListing($context);

        $result = [];
        foreach ($listing->getPlugins() as $plugin) {
            $result[] = [
                'id' => $plugin->getId(),
                'technicalName' => $plugin->getTechnicalName(),
                'label' => $plugin->getLabel(),
                'installed' => ($plugin->getInstallationDate() !== null),
                'version' => $plugin->getVersion(),
                'updateAvailable' => $plugin->isUpdateAvailable(),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Id', 'Technical name', 'Label', 'Installed', 'Version', 'Update available'])
            ->setRows($result);

        $table->render();
    }
}
