<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\Console\Command;

use Shopware\Components\DependencyInjection\ResourceLoader;
use Shopware\Components\DependencyInjection\ResourceLoaderAwareInterface;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class PluginRefreshCommand extends Command implements ResourceLoaderAwareInterface
{
    /**
     * @var ResourceLoader
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setResourceLoader(ResourceLoader $resourceLoader = null)
    {
        $this->container = $resourceLoader;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw-plugin:refresh')
            ->setDescription('Refreshes plugin list.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Installer $installer */
        $installer  = $this->container->get('shopware.plugin_installer');
        $installer->refreshPluginList();

        //$output->writeln(sprintf("Successfully refreshed. Removed: %s, New: %s.", $removedCount, $addedCount));
        $output->writeln(sprintf("Successfully refreshed"));
    }
}
