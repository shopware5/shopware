<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Components\Theme\Generator;
use Shopware\Components\Theme\Installer;
use Shopware\Models\Shop\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThemeCreateCommand extends ShopwareCommand
{
    private $repository;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:create')
            ->setDescription('Creates a theme.')
            ->addArgument(
                'parent',
                InputArgument::REQUIRED,
                'Name of the theme which should be extended.'
            )
            ->addArgument(
                'template',
                InputArgument::REQUIRED,
                'Name of the theme directory.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the theme to be created.'
            )
            ->addArgument(
                'description',
                InputArgument::OPTIONAL,
                'Description of the theme to be created.'
            )
            ->addArgument(
                'author',
                InputArgument::OPTIONAL,
                'Author of the theme to be created.'
            )
            ->addArgument(
                'license',
                InputArgument::OPTIONAL,
                'Licence of the theme to be created.'
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> creates a theme.
EOF
            );
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Generator $themeGenerator */
        $themeGenerator  = $this->container->get('theme_generator');
        $arguments = $input->getArguments();

        // Disable error reporting for shopware menu legacy hack
        $this->registerErrorHandler($output);

        /** @var Installer $themeInstaller */
        $themeInstaller = $this->container->get('theme_installer');
        $themeInstaller->synchronize();


        if ($this->getRepository()->findOneByTemplate($arguments['template'])) {
            $output->writeln('A theme with that name already exists');
            return 1;
        }

        /** @var Template $parent */
        $parent = $this->getRepository()->findOneByTemplate($arguments['parent']);

        if (!$parent instanceof Template) {
            $output->writeln(sprintf('Shop template by template name %s not found',
                $arguments['parent']));
            return 1;
        }

        $themeGenerator->generateTheme($arguments, $parent);

        $output->writeln(sprintf('Theme %s has been created successfully.', $arguments['name']));
    }

    /**
     * Helper function to get the repository of the configured model.
     * @return \Shopware\Models\Shop\Template
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Template');
        }
        return $this->repository;
    }
}
