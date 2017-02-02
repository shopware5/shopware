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

use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Commands
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaOptimizeCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:media:optimize')
            ->setHelp('The <info>%command.name%</info> optimizes your uploaded media using external tools. You can check the availability using the <info>--info</info> option.')
            ->setDescription('Optimize uploaded media without quality loss.')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to your media folder', null)
            ->addOption('info', 'i', InputOption::VALUE_NONE, 'Display available tools')
            ->addOption('skip-scan', null, InputOption::VALUE_NONE, 'Skips the initial filesystem scan.')
            ->addOption('modified', 'm', InputOption::VALUE_REQUIRED, 'Limits the files modify date to the provided time string.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optimizerService = $this->getContainer()->get('shopware_media.optimizer_service');

        if ($input->getOption('info')) {
            $this->displayCapabilities($output, $optimizerService->getOptimizers());
            return;
        }

        $finder = $this->createMediaFinder($input, $output);

        $numberOfFiles = 0;
        if (!$input->getOption('skip-scan')) {
            $numberOfFiles = $finder->count();
        }

        $progress = new ProgressBar($output, $numberOfFiles);

        foreach ($finder->getIterator() as $file) {
            $progress->advance();

            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(' - ' . $file->getRelativePathname());
            }

            try {
                $optimizerService->optimize($file->getRealPath());
            } catch (OptimizerNotFoundException $exception) {
                // empty catch intended since no optimizer is available
            }
        }

        $progress->finish();
    }

    /**
     * @param OutputInterface $output
     * @param OptimizerInterface[] $capabilities
     */
    private function displayCapabilities(OutputInterface $output, array $capabilities)
    {
        $table = new Table($output);
        $table->setHeaders(['Optimizer', 'Runnable', 'Supported mime-types']);
        foreach ($capabilities as $optimizer) {
            $table->addRow([
                $optimizer->getName(),
                $optimizer->isRunnable() ? 'Yes' : 'No',
                implode(', ', $optimizer->getSupportedMimeTypes())
            ]);
        }
        $table->render();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Finder
     */
    private function createMediaFinder(InputInterface $input, OutputInterface $output)
    {
        $mediaPath = $input->getArgument('path') ?: $this->getContainer()->get('kernel')->getRootDir() . '/media';
        $realPath = realpath($mediaPath);

        if (!is_dir($realPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exists.', $mediaPath));
        }

        $output->writeln(sprintf('<info>Searching for files in:</info> %s', $realPath));

        $finder = new Finder();
        $finder
            ->files()
            ->in($realPath);

        if ($input->getOption('modified')) {
            $finder->date($input->getOption('modified'));
        }

        return $finder;
    }
}
