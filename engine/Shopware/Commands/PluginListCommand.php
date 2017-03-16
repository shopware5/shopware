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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PluginListCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:list')
            ->setDescription('Lists plugins.')
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter Plugins (inactive, active)'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Filter Plugins by namespace (core, frontend, backend)',
                []
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        $repository = $em->getRepository('Shopware\Models\Plugin\Plugin');
        $builder = $repository->createQueryBuilder('plugin');
        $builder->andWhere('plugin.capabilityEnable = true');
        $builder->addOrderBy('plugin.active', 'desc');
        $builder->addOrderBy('plugin.name');

        $filter = strtolower($input->getOption('filter'));
        if ($filter === 'active') {
            $builder->andWhere('plugin.active = true');
        }

        if ($filter === 'inactive') {
            $builder->andWhere('plugin.active = false');
        }

        $namespace = $input->getOption('namespace');
        if (count($namespace)) {
            $builder->andWhere('plugin.namespace IN (:namespace)');
            $builder->setParameter('namespace', $namespace);
        }

        $plugins = $builder->getQuery()->execute();

        $rows = [];

        /** @var Plugin $plugin */
        foreach ($plugins as $plugin) {
            $rows[] = [
                $plugin->getName(),
                $plugin->getLabel(),
                $plugin->getVersion(),
                $plugin->getAuthor(),
                $plugin->getActive() ? 'Yes' : 'No',
                $plugin->getInstalled() ? 'Yes' : 'No',
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Plugin', 'Label', 'Version', 'Author', 'Active', 'Installed'])
            ->setRows($rows)
            ->render();
    }
}
