<?php declare(strict_types=1);
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

namespace Shopware\Bundle\PluginInstallerBundle\Commands;

use Shopware\Bundle\PluginInstallerBundle\Service\DownloadService;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginZipImportCommand extends ShopwareCommand
{
    /**
     * @var DownloadService
     */
    private $downloadService;

    /**
     * @var InstallerService
     */
    private $installerService;

    public function __construct(DownloadService $downloadService, InstallerService $installerService)
    {
        parent::__construct();
        $this->downloadService = $downloadService;
        $this->installerService = $installerService;
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('sw:plugin:zip-import')
            ->setDescription('Imports a plugin zip file.')
            ->addArgument(
                'zip-file',
                InputArgument::REQUIRED,
                'File of the plugin to be imported.'
            )
            ->addOption(
                'no-refresh',
                null,
                InputOption::VALUE_NONE,
                'Do not refresh plugin list.'
            )
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> imports a plugin zip file.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $zipFile = $input->getArgument('zip-file');

        if (!file_exists($zipFile)) {
            $output->writeln('File does not exist');

            return 1;
        }

        $information = pathinfo($zipFile);
        $pluginFileName = $information['basename'];

        $this->downloadService->extractPluginZip($zipFile, $pluginFileName);
        $output->writeln('Plugin zip file ' . $pluginFileName . ' has been successfully imported');

        if (!$input->getOption('no-refresh')) {
            $this->installerService->refreshPluginList();
            $output->writeln('Successfully refreshed');
        }

        return 0;
    }
}
