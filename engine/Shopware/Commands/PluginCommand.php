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

use Shopware\Components\CacheManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PluginCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'clear-cache',
            'c',
            InputOption::VALUE_NONE,
            'Clear any neccessary caches'
        );
    }

    /**
     * @param InstallContext[] ...$contexts
     */
    protected function clearCachesIfRequested(InputInterface $input, OutputInterface $output, ...$contexts)
    {
        if (!empty($input->getOption('clear-cache'))) {
            $this->clearCaches($output, ...$contexts);
        } elseif (!empty($this->getScheduledCaches(...$contexts))) {
            $output->writeln([
                'Consider sw:cache:clear to clear all relevant caches and see the latest changes.',
                'Try the --clear-cache option to do so automatically.',
            ]);
        }
    }

    /**
     * @param InstallContext[] ...$contexts
     */
    protected function clearCaches(OutputInterface $output, ...$contexts)
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $cacheTags = $this->getScheduledCaches(...$contexts);
        if ($cacheManager->clearByTags($cacheTags)) {
            $output->writeln(sprintf('Caches cleared (%s).', join(', ', $cacheTags)));
        }
    }

    /**
     * @param InstallContext[] ...$contexts
     *
     * @return string[]
     */
    protected function getScheduledCaches(...$contexts)
    {
        $tags = [];

        foreach ($contexts as $context) {
            if (!$context instanceof InstallContext || !array_key_exists('cache', $context->getScheduled())) {
                continue;
            }

            $tags = array_merge($tags, $context->getScheduled()['cache']);
        }

        return array_unique($tags);
    }
}
