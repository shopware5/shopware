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

use ReflectionClass;
use Shopware\Components\Migrations\AbstractMigration;
use Shopware\Components\Migrations\Manager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsMigrateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'mode') {
            $meta = new ReflectionClass(AbstractMigration::class);
            $constants = $meta->getConstants();
            $modeConstantKeys = array_filter(array_keys($constants), function ($constantKey) {
                return strpos($constantKey, 'MODUS_') === 0;
            });
            $modeConstantPseudoValues = array_pad([], count($modeConstantKeys), 0);

            return array_intersect_key($constants, array_combine($modeConstantKeys, $modeConstantPseudoValues));
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:migrations:migrate')
            ->setDescription('Runs all migrations');

        $this->addOption(
            'mode',
            null,
            InputOption::VALUE_REQUIRED,
            'Mode to run: Install or Update',
            'update'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('db_connection');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $mode = $input->getOption('mode');

        $migrationManger = new Manager($connection, $rootDir . '/_sql/migrations');
        $migrationManger->run($mode);
    }
}
