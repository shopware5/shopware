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

namespace Shopware\Bundle\EsBackendBundle\Commands;

use Shopware\Bundle\EsBackendBundle\EsBackendIndexer;
use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexPopulateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:backend:index:populate')
            ->setDescription('Reindex all documents for the backend')
            ->addOption('no-evaluation', null, InputOption::VALUE_NONE, 'Disable evaluation for each index')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Abort indexing if an error occurs')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EsBackendIndexer $indexer */
        $indexer = $this->container->get('shopware_es_backend.indexer');

        $helper = new ConsoleProgressHelper($output);
        $evaluation = $this->container->get('shopware_elastic_search.console.console_evaluation_helper');
        $evaluation->setOutput($output)
            ->setActive(!$input->getOption('no-evaluation'))
            ->setStopOnError($input->getOption('stop-on-error'));

        $indexer->index($helper);
    }
}
