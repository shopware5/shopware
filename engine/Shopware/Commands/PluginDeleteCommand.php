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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class PluginDeleteCommand extends ShopwareCommand implements CompletionAwareInterface
{
    public function deletePath($path)
    {
        $fs = new Filesystem();

        try {
            $fs->remove($path);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }

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
                ->andWhere($queryBuilder->expr()->neq('plugin.active', 'true'))
                ->andWhere($queryBuilder->expr()->isNull('plugin.installed'))
                ->andWhere($queryBuilder->expr()->neq('plugin.source', ':source'))
                ->setParameter('source', 'Default')
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
        $this
            ->setName('sw:plugin:delete')
            ->setDescription('Deletes a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be deleted.'
            )
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> deletes a plugin.
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

        if ($plugin->getInstalled()) {
            $output->writeln('The Plugin has to be uninstalled first.');

            return 1;
        }

        $pluginPath = $pluginManager->getPluginPath($pluginName);

        $message = null;
        if ($plugin->getSource() === 'Default') {
            $message = "'Default' Plugins may not be deleted.";
        } elseif (!$this->deletePath($pluginPath)) {
            $message = 'Plugin path "' . $pluginPath . '" could not be deleted.';
        } else {
            Shopware()->Models()->remove($plugin);
            Shopware()->Models()->flush();
        }

        if ($message) {
            $output->writeln($message);

            return 1;
        }
        $output->writeln(sprintf('Plugin %s has been deleted successfully.', $pluginName));

        return 0;
    }
}
