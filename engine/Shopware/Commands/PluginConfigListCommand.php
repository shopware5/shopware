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
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PluginConfigListCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'shop') {
            return $this->completeShopIds($context->getCurrentWord());
        }

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
        $this
            ->setName('sw:plugin:config:list')
            ->setDescription('Lists plugin configuration.')
            /* @deprecated since 5.6, to be removed in 6.0 */
            ->addOption(
                'shop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Get configuration for shop id (deprecated)'
            )
            ->addOption(
                'shopId',
                null,
                InputOption::VALUE_OPTIONAL,
                'Get configuration for shop id'
            )
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to list config.'
            )
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> lists a plugin configuration.
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
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        /** @var Shop[] $shop */
        $shops = null;
        $shopId = null;

        if ($input->getOption('shop')) {
            $io = new SymfonyStyle($input, $output);
            $io->warning('Option "--shop" will be replaced by option "--shopId" in the next major version');
            $shopId = $input->getOption('shop');
        } elseif ($input->getOption('shopId')) {
            $shopId = $input->getOption('shopId');
        }

        if ($shopId) {
            /** @var Shop|null $shop */
            $shop = $em->getRepository(Shop::class)->find($shopId);
            if (!$shop) {
                $output->writeln(sprintf('Could not find shop with id %s.', $shopId));

                return 1;
            }
            $shops = [$shop];
        } else {
            $shops = $em->getRepository(Shop::class)->findAll();
        }

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $config = $pluginManager->getPluginConfig($plugin, $shop);

            $output->writeln(sprintf('Plugin configuration for Plugin %s and shop %s:', $pluginName, $shop->getName()));
            $output->writeln(print_r($config, true));
        }
    }
}
