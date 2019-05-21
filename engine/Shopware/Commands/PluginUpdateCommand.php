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

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Components\Model\ModelManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateCommand extends PluginCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'plugin') {
            return $this->queryPluginNames($context->getCurrentWord());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('sw:plugin:update')
            ->setDescription('Updates specified plugins.')
            ->addArgument(
                'plugin',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Space separated list of plugins to be updated. Ignored if --batch option is used'
            )
            ->addOption(
                'batch',
                null,
                InputOption::VALUE_REQUIRED,
                'Batch update several plugins. Possible values are all, inactive, active, installed, uninstalled'
            )
            ->addOption(
                'no-refresh',
                null,
                InputOption::VALUE_NONE,
                'Do not refresh plugin list.'
            )
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> updates a plugin.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var InstallerService $pluginManager */
        $pluginManager = $this->container->get('shopware_plugininstaller.plugin_manager');
        if (!$input->getOption('no-refresh')) {
            $pluginManager->refreshPluginList();
            $output->writeln('Successfully refreshed');
        }

        $pluginNames = $input->getArgument('plugin');

        $batchUpdate = $input->getOption('batch');
        if (!empty($batchUpdate)) {
            return $this->batchUpdate($pluginManager, $batchUpdate, $output);
        }

        if (!empty($pluginNames)) {
            foreach ($pluginNames as $pluginName) {
                $this->updatePlugin($pluginManager, $pluginName, $input, $output);
            }

            return 0;
        }

        $output->writeln(sprintf('Specify either a plugin name or use the --batch option to update several plugins at once'));

        return 1;
    }

    /**
     * @param string $batchUpdate
     *
     * @return int 0 if everything went fine, or an error code
     */
    private function batchUpdate(InstallerService $pluginManager, $batchUpdate, OutputInterface $output)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        $repository = $em->getRepository(\Shopware\Models\Plugin\Plugin::class);
        $builder = $repository->createQueryBuilder('plugin');
        $builder->andWhere('plugin.capabilityEnable = true');
        $builder->addOrderBy('plugin.active', 'desc');
        $builder->addOrderBy('plugin.name');

        if ($batchUpdate === 'active') {
            $builder->andWhere('plugin.active = true');
        }
        if ($batchUpdate === 'inactive') {
            $builder->andWhere('plugin.active = false');
        }
        if ($batchUpdate === 'installed') {
            $builder->andWhere('plugin.installed is not NULL');
        }
        if ($batchUpdate === 'uninstalled') {
            $builder->andWhere('plugin.installed is NULL');
        }

        $plugins = $builder->getQuery()->execute();

        if (empty($plugins)) {
            $output->writeln(sprintf('No plugin(s) found'));

            return 1;
        }

        $allPluginsUpToDate = true;
        /** @var \Shopware\Models\Plugin\Plugin[] $plugins */
        foreach ($plugins as $plugin) {
            if (!$plugin->getUpdateVersion()) {
                continue;
            }
            $pluginManager->updatePlugin($plugin);
            $output->writeln(sprintf('Plugin %s has been updated successfully.', $plugin->getName()));
            $allPluginsUpToDate = false;
        }

        if ($allPluginsUpToDate) {
            $output->writeln(sprintf('No update needed. Plugin(s) are up to date'));
        }

        return 0;
    }

    /**
     * @param string $pluginName
     *
     * @return int 0 if everything went fine, or an error code
     */
    private function updatePlugin(InstallerService $pluginManager, $pluginName, InputInterface $input, OutputInterface $output)
    {
        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }

        if (!$plugin->getUpdateVersion()) {
            $output->writeln(sprintf('The plugin %s is up to date.', $pluginName));

            return 0;
        }

        $updateContext = $pluginManager->updatePlugin($plugin);
        $output->writeln(sprintf('Plugin %s has been updated successfully.', $pluginName));

        $this->clearCachesIfRequested($input, $output, $updateContext);

        return 0;
    }
}
