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
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Plugin\Plugin;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstallCommand extends PluginCommand implements CompletionAwareInterface
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
            /** @var ModelRepository $repository */
            $repository = $this->getContainer()->get('models')->getRepository(Plugin::class);
            $queryBuilder = $repository->createQueryBuilder('plugin');
            $result = $queryBuilder->andWhere($queryBuilder->expr()->eq('plugin.capabilityInstall', 'true'))
                ->andWhere($queryBuilder->expr()->isNull('plugin.installed'))
                ->select(['plugin.name'])
                ->getQuery()
                ->getArrayResult();

            return array_column($result, 'name');
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
            ->setName('sw:plugin:install')
            ->setDescription('Installs a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be installed.'
            )
            ->addOption(
                'activate',
                null,
                InputOption::VALUE_NONE,
                'Activate plugin after intallation.'
            )
            ->addOption(
                'no-refresh',
                null,
                InputOption::VALUE_NONE,
                'Do not refresh plugin list.'
            )
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> installs a plugin.
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
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }

        $installationContext = null;

        if ($plugin->getInstalled()) {
            $output->writeln(sprintf('The plugin %s is already installed.', $pluginName));
        } else {
            $installationContext = $pluginManager->installPlugin($plugin);
            $output->writeln(sprintf('Plugin %s has been installed successfully.', $pluginName));
        }

        $activationContext = null;

        if ($input->getOption('activate')) {
            $activationContext = $pluginManager->activatePlugin($plugin);
            $output->writeln(sprintf('Plugin %s has been activated successfully.', $pluginName));
        }

        $this->clearCachesIfRequested($input, $output, $installationContext, $activationContext);
    }
}
