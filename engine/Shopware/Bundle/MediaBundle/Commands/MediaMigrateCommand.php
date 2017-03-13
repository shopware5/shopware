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

namespace Shopware\Bundle\MediaBundle\Commands;

use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Bundle\MediaBundle\MediaMigration;
use Shopware\Bundle\MediaBundle\MediaMigrationResult;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use Shopware\Bundle\MediaBundle\StrategyFactory;
use Shopware\Bundle\MediaBundle\StrategyFilesystem;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MediaMigrateCommand extends ShopwareCommand
{
    /**
     * @var StrategyFilesystem
     */
    private $filesystem;

    /**
     * @var StrategyFactory
     */
    private $strategyFactory;

    /**
     * @var StrategyInterface
     */
    private $currentStrategy;

    public function __construct(StrategyFilesystem $filesystem, StrategyFactory $strategyFactory, StrategyInterface $currentStrategy)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->strategyFactory = $strategyFactory;
        $this->currentStrategy = $currentStrategy;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:media:migrate')
            ->setDescription('Migrate images to another strategy')
            ->addArgument('target-strategy', InputArgument::REQUIRED, 'Target strategy (e.g. md5, plain)')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Source strategy')
            ->addOption('skip-scan', null, InputOption::VALUE_NONE, 'Skips the initial filesystem scan and migrates the files immediately.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $to = $input->getArgument('target-strategy');
        $from = $input->getOption('from');
        $skipScan = $input->getOption('skip-scan');

        if (empty($from)) {
            $from = $this->currentStrategy->getName();
        }

        $mediaMigration = new MediaMigration(
            $this->filesystem->getAdapter(),
            $this->strategyFactory->factory($from),
            $this->strategyFactory->factory($to),
            new ConsoleProgressHelper($output)
        );

        $result = $mediaMigration->run($skipScan);

        $this->displaySummary($output, $result);
    }

    private function displaySummary(OutputInterface $output, MediaMigrationResult $result): void
    {
        $output->writeln('');
        $output->writeln('');

        $table = new Table($output);
        $table->setStyle('borderless');
        $table->setHeaders(['Action', 'Number of items']);
        $table->addRow(['Migrated', $result->migrated]);
        $table->addRow(['Skipped', $result->skipped]);
        $table->render();
    }
}
