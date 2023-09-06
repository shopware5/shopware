<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Commands;

use PDO;
use ReflectionClass;
use RuntimeException;
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
                return str_starts_with($constantKey, 'MODUS_');
            });
            $modeConstantPseudoValues = array_pad([], \count($modeConstantKeys), 0);
            $combined = array_combine($modeConstantKeys, $modeConstantPseudoValues);
            if (!\is_array($combined)) {
                throw new RuntimeException('Arrays could not be combined');
            }

            return array_intersect_key($constants, $combined);
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
        $connection = $this->getContainer()->get(PDO::class);

        $rootDir = $this->container->getParameter('kernel.root_dir');

        if (!\is_string($rootDir)) {
            throw new RuntimeException('Parameter kernel.root_dir has to be an string');
        }

        $mode = $input->getOption('mode');

        $migrationManger = new Manager($connection, $rootDir . '/_sql/migrations');
        $migrationManger->run($mode);

        return 0;
    }
}
