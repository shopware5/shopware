<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Commands;

use Exception;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUninstallCommand extends PluginCommand implements CompletionAwareInterface
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
            $repository = $this->getContainer()->get(ModelManager::class)->getRepository(Plugin::class);
            $queryBuilder = $repository->createQueryBuilder('plugin');
            $result = $queryBuilder->andWhere($queryBuilder->expr()->eq('plugin.capabilityEnable', 'true'))
                ->andWhere($queryBuilder->expr()->isNotNull('plugin.installed'))
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
            ->setName('sw:plugin:uninstall')
            ->setDescription('Uninstalls a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be uninstalled.'
            )
            ->addOption(
                'secure',
                'S',
                InputOption::VALUE_NONE,
                'Keep the saved data of the plugin. (if supported)'
            )
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> uninstalls a plugin.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var InstallerService $pluginManager */
        $pluginManager = $this->container->get(InstallerService::class);
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }

        if (!$plugin->getInstalled()) {
            $output->writeln(sprintf('The plugin %s is already uninstalled.', $pluginName));

            return 1;
        }

        $removeData = !(bool) $input->getOption('secure');

        $uninstallationContext = $pluginManager->uninstallPlugin($plugin, $removeData);
        $output->writeln(sprintf('Plugin %s has been uninstalled successfully.', $pluginName));

        $this->clearCachesIfRequested($input, $output, $uninstallationContext);

        return 0;
    }
}
