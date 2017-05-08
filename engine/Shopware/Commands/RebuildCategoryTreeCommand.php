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

use Assert\Assertion;
use Shopware\Components\Model\CategoryDenormalization;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RebuildCategoryTreeCommand builds up the ro category tree of shopware
 */
class RebuildCategoryTreeCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:rebuild:category:tree')
            ->setDescription('Rebuild the category tree')
            ->addOption(
                'offset',
                'o',
                InputOption::VALUE_REQUIRED,
                'Offset to start with. Default: 0',
                0
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Categories to build per batch. Default: 3000',
                3000
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will rebuild your category tree.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progress = $input->getOption('offset');
        $limit = $input->getOption('limit');

        Assertion::integerish($progress);
        Assertion::integerish($limit);

        /** @var CategoryDenormalization $component */
        $component = Shopware()->Container()->get('CategoryDenormalization');

        // Cleanup before the first call
        if ($progress == 0) {
            $output->writeln('Removing orphans');
            $component->removeOrphanedAssignments();
            $output->writeln('Rebuild path info');
            $component->rebuildCategoryPath();
            $output->writeln('Removing assignments');
            $component->removeAllAssignments();
        }

        // Get total number of assignments to build
        $output->write('Counting…');
        $count = $component->rebuildAllAssignmentsCount();
        $output->writeln("\rCounted {$count} items");

        /** @var ProgressHelper $progressHelper */
        $progressHelper = $this->getHelper('progress');
        $progressHelper->setFormat(ProgressHelper::FORMAT_VERBOSE);
        $progressHelper->start($output, $count);
        $progressHelper->advance($progress);

        // create the assignments
        while ($progress < $count) {
            $component->rebuildAllAssignments($limit, $progress);
            $progress += $limit;
            $progressHelper->advance($limit);
        }
        $progressHelper->finish();

        $output->writeln("\rDone");
    }
}
