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

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;
use Shopware\Commands\ShopwareCommand;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * This command allows to optimize all media files at once by executing the relevant optimization commands available.
 */
class MediaOptimizeCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'path') {
            // TODO set path for shell completion. Hint: the exit code gets checked in the generated completion bash script
            exit(ShellPathCompletion::PATH_COMPLETION_EXIT_CODE);
        }

        return [];
    }

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
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force optimization of files on remote file system adapters without asking first')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optimizerService = $this->getContainer()->get('shopware_media.cdn_optimizer_service');
        $mediaService = $this->getContainer()->get('shopware_media.media_service');

        if ($this->hasRunnableOptimizer() === false) {
            $output->writeln('<error>No runnable optimizer found. Consider installing one of the following optimizers.</error>');
            $this->displayCapabilities($output, $optimizerService->getOptimizers());

            return 1;
        }

        if ($input->getOption('info')) {
            $this->displayCapabilities($output, $optimizerService->getOptimizers());

            return 0;
        }

        if ($mediaService->getAdapterType() !== 'local' && !$input->getOption('force')) {
            $output->writeln("<error>Using the sw:media:optimize-command with remote filesystem adapters (you are using adapter '{$mediaService->getAdapterType()}') is discouraged!</error>
Due to the nature of the task, all files will be downloaded, optimized and uploaded again.
This can take a very long time, depending on the number of files that need to be optimized.
"
            );

            $doProceed = $this->getHelper('question')->ask(
                $input,
                $output,
                new ConfirmationQuestion('Do you still wish to proceed? (y/N) ', false));

            if (!$doProceed) {
                return 1;
            }
        }

        $path = 'media';
        if ($input->getArgument('path')) {
            if (strpos($input->getArgument('path'), 'media') !== 0) {
                $output->writeln('<error>Only subpaths of "media"-directory supported.</error>');

                return 1;
            }
            $path = $input->getArgument('path');
        }

        $numberOfFiles = 0;
        if (!$input->getOption('skip-scan')) {
            // Do not count directories, the many sub-dirs would otherwise throw off the progressbar
            $numberOfFiles = count(array_filter($mediaService->getFilesystem()->listContents($path, true), function (array $element) {
                return $element['type'] === 'file';
            }));
        }

        $progress = new ProgressBar($output, $numberOfFiles);

        $this->optimizeFiles($path, $mediaService, $optimizerService, $progress, $output);

        $progress->finish();

        return 0;
    }

    /**
     * @param string $directory
     */
    private function optimizeFiles(
        $directory,
        MediaServiceInterface $mediaService,
        OptimizerServiceInterface $optimizerService,
        ProgressBar $progressBar,
        OutputInterface $output)
    {
        /** @var array $contents */
        $contents = $mediaService->getFilesystem()->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->optimizeFiles($item['path'], $mediaService, $optimizerService, $progressBar, $output);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    $progressBar->advance();
                    continue;
                }

                $progressBar->setMessage($item['path'], 'filename');

                if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                    $output->writeln(' - ' . $item['path']);
                }

                try {
                    $optimizerService->optimize($item['path']);
                } catch (FileNotFoundException $exception) {
                    $output->writeln(' => ' . $exception->getMessage());
                } catch (OptimizerNotFoundException $exception) {
                    // Empty catch intended since no optimizer is available
                } catch (FileExistsException $exception) {
                    $output->writeln(' => ' . $exception->getMessage());
                }

                $progressBar->advance();
            }
        }
    }

    /**
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
                implode(', ', $optimizer->getSupportedMimeTypes()),
            ]);
        }
        $table->render();
    }

    /**
     * @return bool
     */
    private function hasRunnableOptimizer()
    {
        $optimizerService = $this->getContainer()->get('shopware_media.optimizer_service');

        foreach ($optimizerService->getOptimizers() as $optimizer) {
            if ($optimizer->isRunnable()) {
                return true;
            }
        }

        return false;
    }
}
