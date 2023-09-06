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
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PluginConfigSetCommand extends ShopwareCommand implements CompletionAwareInterface
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
            $repository = $this->getContainer()->get(ModelManager::class)->getRepository(Plugin::class);
            $queryBuilder = $repository->createQueryBuilder('plugin');
            $result = $queryBuilder->andWhere($queryBuilder->expr()->eq('plugin.capabilityEnable', 'true'))
                ->select(['plugin.name'])
                ->getQuery()
                ->getArrayResult();

            return array_column($result, 'name');
        }

        if ($argumentName === 'key') {
            $pluginName = $context->getWordAtIndex($context->getWordIndex() - 1);
            $pluginManager = $this->container->get(InstallerService::class);
            try {
                $plugin = $pluginManager->getPluginByName($pluginName);
            } catch (Exception $e) {
                return [];
            }

            $shopRepository = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class);

            $shops = $shopRepository->findAll();

            $result = [];

            foreach ($shops as $shop) {
                $configKeys = array_keys($pluginManager->getPluginConfig($plugin, $shop));

                if (empty($result)) {
                    $result = $configKeys;
                } else {
                    $result = array_intersect($result, $configKeys);
                }
            }

            return $result;
        }

        if ($argumentName === 'value') {
            if (stripos('true', $context->getCurrentWord()) === 0) {
                return ['true'];
            }

            if (stripos('false', $context->getCurrentWord()) === 0) {
                return ['false'];
            }

            if (stripos('null', $context->getCurrentWord()) === 0) {
                return ['null'];
            }

            if (strpos($context->getCurrentWord(), '[') === 0
                && stripos($context->getCurrentWord(), ']') === false) {
                return ["{$context->getCurrentWord()}]"];
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:config:set')
            ->setDescription('Sets plugin configuration.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin.'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Configuration key.'
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Configuration value. Can be true, false, null, an integer or an array specified with brackets: [value,anothervalue]. Everything else will be interpreted as string.'
            )
            ->addOption(
                'shop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set configuration for shop id (deprecated)'
            )
            ->addOption(
                'shopId',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set configuration for shop id'
            )
        ;
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

        $em = $this->container->get(ModelManager::class);

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
        } else {
            /** @var Shop $shop */
            $shop = $em->getRepository(Shop::class)->findOneBy(['default' => true]);
        }

        $rawValue = $input->getArgument('value');
        $value = $this->castValue($rawValue);

        if (preg_match('/^\[(.+,?)*\]$/', $value, $matches) && \count($matches) == 2) {
            $value = explode(',', $matches[1]);
            $value = array_map(function ($val) {
                return $this->castValue($val);
            }, $value);
        }

        $pluginManager->saveConfigElement($plugin, $input->getArgument('key'), $value, $shop);
        $output->writeln(sprintf('Plugin configuration for Plugin %s saved.', $pluginName));

        return 0;
    }

    /**
     * Casts a given string into the proper type.
     * Works only for some types, see return.
     *
     * @param string $value
     *
     * @return bool|int|string|null
     */
    private function castValue($value)
    {
        if ($value === 'null') {
            return null;
        }
        if ($value === 'false') {
            return false;
        }
        if ($value === 'true') {
            return true;
        }
        if (preg_match('/^\d+$/', $value)) {
            return (int) $value;
        }

        return $value;
    }
}
