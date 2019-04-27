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
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginListCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'filter') {
            return [
                'activate',
                'inactive',
            ];
        }

        if ($optionName === 'namespace') {
            $namespaces = [
                'core',
                'frontend',
                'backend',
                'ShopwarePlugins',
                'ProjectPlugins',
            ];

            return array_diff($namespaces, array_intersect($namespaces, $context->getWords()));
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:list')
            ->setDescription('Lists plugins, all or by status/namespace.')
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_REQUIRED,
                'Filter Plugins (inactive, active, installed, uninstalled)'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Filter Plugins by namespace (core, frontend, backend)',
                []
            )
            ->addOption(
                'plain',
                'p',
                InputOption::VALUE_NONE,
                'Returns only the technical plugin names without rendering a table'
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

        $repository = $em->getRepository(\Shopware\Models\Plugin\Plugin::class);
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

        if ($filter === 'installed') {
            $builder->andWhere('plugin.installed is not NULL');
        }
        if ($filter === 'uninstalled') {
            $builder->andWhere('plugin.installed is NULL');
        }

        $namespace = $input->getOption('namespace');
        if (count($namespace)) {
            $builder->andWhere('plugin.namespace IN (:namespace)');
            $builder->setParameter('namespace', $namespace);
        }

        $plugins = $builder->getQuery()->execute();

        if ($input->getOption('plain')) {
            return $output->writeln(implode(PHP_EOL, array_map(function (Plugin $plugin) {
                return $plugin->getName();
            }, $plugins)));
        }

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
              ->setRows($rows);

        $table->render();
    }
}
