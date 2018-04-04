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

use Shopware\Models\ProductFeed\ProductFeed;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProductFeedCommand extends ShopwareCommand
{
    const METHOD_LIVE = 0;

    const METHOD_ONLY_CRON = -1;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:product:feeds:list')
            ->setDescription('List product feeds.')
            ->setHelp('The <info>%command.name%</info> list all product feeds.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productFeedRepository = $this->container->get('models')->getRepository('Shopware\Models\ProductFeed\ProductFeed');
        $productFeeds = $productFeedRepository->findAll();

        $rows = [];
        /** @var ProductFeed $productFeed */
        foreach ($productFeeds as $productFeed) {
            $rows[] = [
                $productFeed->getName(),
                $productFeed->getId(),
                $productFeed->getLastExport()->format('Y-m-d H:i:s'),
                $this->formatInterval($productFeed->getInterval()),
                $productFeed->getActive() ? 'Yes' : 'No',
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Product Feed', 'Id', 'Last export', 'Interval', 'Active'])
            ->setRows($rows);

        $table->render();
    }

    private function formatInterval($interval)
    {
        switch ($interval) {
            case self::METHOD_LIVE:
                return 'Live';
            case self::METHOD_ONLY_CRON:
                return 'Only cron';
            default:
                return $interval . 's';
        }
    }
}
