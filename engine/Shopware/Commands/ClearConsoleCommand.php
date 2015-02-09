<?php


namespace Shopware\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shopware ClearConsoleCommand Class
 *
 * This class is used to clear the console.
 *
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ClearConsoleCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('clear')
            ->setDescription('Clear the console.')
            ->setHelp(
                <<<EOF
                    The <info>%command.name%</info> clear the console.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        system("clear"); // Clear the console
    }
}

