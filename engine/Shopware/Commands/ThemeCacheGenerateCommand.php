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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CacheManager;
use Shopware\Components\Theme\Compiler;
use Shopware\Models\Shop\Repository;
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
            $shopIdKeys = array_map(function ($key) { return $key + 1; }, array_keys($context->getWords(), '--shopId'));
            $selectedShopIds = array_intersect_key($context->getWords(), array_combine($shopIdKeys, array_pad([], count($shopIdKeys), 0)));

            return array_diff($this->completeShopIds($context->getCurrentWord()), array_map('intval', $selectedShopIds));
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
        /** @var Repository $repository */
        $repository = $this->container->get('models')->getRepository(Shop::class);

        $shopIds = $input->getOption('shopId');
        $current = (bool) $input->getOption('current');

        /** @var Shop[] $shopsWithThemes */
        $shopsWithThemes = $repository->getShopsWithThemes()->getResult(AbstractQuery::HYDRATE_OBJECT);

        if (!empty($shopIds)) {
            $shopsWithThemes = array_filter($shopsWithThemes, function (Shop $shop) use ($shopIds) {
                return in_array($shop->getId(), $shopIds);
            });
        }

        if (empty($shopsWithThemes)) {
            $output->writeln('No theme shops found');

            return null;
        }

        /** @var Compiler $compiler */
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
            return null;
        }

        /** @var CacheManager $cacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $output->writeln('Clearing HTTP cache ...');
        $cacheManager->clearHttpCache();
    }
}
