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

use Doctrine\ORM\AbstractQuery;
use RuntimeException;
use Shopware\Components\CacheManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeCacheGenerateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'shopId') {
            $shopIdKeys = array_map(static function ($key) {
                return $key + 1;
            }, array_keys($context->getWords(), '--shopId'));
            $combinedArray = array_combine($shopIdKeys, array_pad([], \count($shopIdKeys), 0));
            if (!\is_array($combinedArray)) {
                throw new RuntimeException('Arrays could not be combined');
            }
            $selectedShopIds = array_intersect_key($context->getWords(), $combinedArray);

            return array_diff($this->completeShopIds($context->getCurrentWord()), array_map('\intval', $selectedShopIds));
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
            ->setName('sw:theme:cache:generate')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The Id of the shop (multiple Ids -> shopId={1,2})')
            ->addOption('current', 'c', InputOption::VALUE_NONE, 'Compile from current asset timestamp')
            ->setDescription('Generates theme caches.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->container->get(ModelManager::class)->getRepository(Shop::class);

        $shopIds = $input->getOption('shopId');
        $current = (bool) $input->getOption('current');

        $shopsWithThemes = $repository->getShopsWithThemes()->getResult(AbstractQuery::HYDRATE_OBJECT);

        if (!empty($shopIds)) {
            $shopsWithThemes = array_filter($shopsWithThemes, function (Shop $shop) use ($shopIds) {
                return \in_array($shop->getId(), $shopIds);
            });
        }

        if (empty($shopsWithThemes)) {
            $output->writeln('No theme shops found');

            return 0;
        }

        $compiler = $this->container->get('theme_compiler');

        foreach ($shopsWithThemes as $shop) {
            if (!$current) {
                $output->writeln(sprintf('Generating new theme cache for shop "%s" ...', $shop->getName()));
                $compiler->compile($shop);
                continue;
            }

            $timestamp = $this->container->get('theme_timestamp_persistor')->getCurrentTimestamp($shop->getId());
            $output->writeln(sprintf('Generating theme cache for shop "%s" from current timestamp %s', $shop->getName(), $timestamp));
            $compiler->recompile($shop);
        }

        if ($current) {
            return 0;
        }

        $cacheManager = $this->container->get(CacheManager::class);
        $output->writeln('Clearing HTTP cache ...');
        $cacheManager->clearHttpCache();

        return 0;
    }
}
