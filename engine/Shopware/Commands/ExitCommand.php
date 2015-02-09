<?php

namespace Shopware\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shopware ThumbnailCleanupCommand Class
 *
 * This class is used as a command to delete thumbnails from defined
 * media albums. If no album is defined, all album thumbnails will be removed.
 *
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ExitCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('exit')
            ->setDescription('Exit the console')
            ->setHelp(
                <<<EOF
                    The <info>%command.name%</info> exit the console.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        exit(0); // Exit the console
    }
}

