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

use Shopware\Components\Install\Database;
use Shopware\Components\Migrations\Manager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseSetupCommand extends ShopwareCommand implements CompletionAwareInterface
{
    private $validSteps = [
        'drop',
        'create',
        'clear',
        'import',
        'importDemodata',
        'setupShop',
    ];

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'steps') {
            return $this->validSteps;
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
        $this->setName('sw:database:setup')
            ->setDescription('Setups shopware database');

        $this->addOption(
            'steps',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf('Valid steps: %s.', implode(', ', $this->validSteps))
        );

        $this->addOption(
            'shop-url',
            null,
            InputOption::VALUE_OPTIONAL,
            'Shop-URL e.G. https://example.com:8080/myshop'
        );

        $this->addOption(
            'host',
            null,
            InputOption::VALUE_OPTIONAL,
            'Shop Hostname (deprecated, use shop-url option instead)'
        );

        $this->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Shop Basepath (deprecated, use shop-url option instead)'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('steps')) {
            $io->error('Parameter --steps not given');

            return 1;
        }

        $dbConfig = $this->getContainer()->getParameter('shopware.db');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $connection = $this->createConnection($dbConfig);
        $database = new Database($connection);

        $steps = $input->getOption('steps');
        /** @var string[] $steps */
        $steps = array_filter(explode(',', $steps));

        foreach ($steps as $step) {
            if (!in_array($step, $this->validSteps, true)) {
                $io->error(
                    sprintf("Unknown install step (%s). Valid steps: %s\n", $step, implode(', ', $this->validSteps))
                );

                return 1;
            }
        }

        while ($step = array_shift($steps)) {
            switch ($step) {
                case 'drop':
                    $io->comment('Drop database');
                    $database->dropDatabase($dbConfig['dbname']);
                    break;

                case 'create':
                    $io->comment('Create database');
                    $database->createDatabase($dbConfig['dbname']);
                    break;

                case 'clear':
                    $io->comment('Clear database');
                    $database->emptyDatabase($dbConfig['dbname']);
                    break;

                case 'import':
                    $io->comment('Import database');
                    $database->importFile($dbConfig['dbname'], $rootDir . '/_sql/install/latest.sql');

                    $migrationManger = new Manager($connection, $rootDir . '/_sql/migrations');
                    $migrationManger->run();
                    break;

                case 'importDemodata':
                    $io->comment('Import demodata');
                    $database->importFile($dbConfig['dbname'], $rootDir . '/_sql/demo/latest.sql');
                    break;

                case 'setupShop':
                    $io->comment('Setup shop');
                    $url = $this->parseUrl($input);
                    if (!empty($url)) {
                        $database->setupShop($url, $dbConfig['dbname']);
                    }

                    break;

                default:
                    $io->error(sprintf("Unknown install step (%s). Valid steps: %s\n", $step, implode(', ', $this->validSteps)));

                    return 1;
            }
        }

        $io->success('Database was successfully created.');
    }

    /**
     * @return string
     */
    private function buildConnectionString(array $dbConfig)
    {
        if (!isset($dbConfig['host']) || empty($dbConfig['host'])) {
            $dbConfig['host'] = 'localhost';
        }

        $connectionSettings = [
            'host=' . $dbConfig['host'],
        ];

        if (!empty($dbConfig['socket'])) {
            $connectionSettings[] = 'unix_socket=' . $dbConfig['socket'];
        }

        if (!empty($dbConfig['port'])) {
            $connectionSettings[] = 'port=' . $dbConfig['port'];
        }

        if (!empty($dbConfig['charset'])) {
            $connectionSettings[] = 'charset=' . $dbConfig['charset'];
        }

        return implode(';', $connectionSettings);
    }

    /**
     * @return \PDO
     */
    private function createConnection(array $dbConfig)
    {
        $password = isset($dbConfig['password']) ? $dbConfig['password'] : '';
        $connectionString = $this->buildConnectionString($dbConfig);

        try {
            $conn = new \PDO(
                'mysql:' . $connectionString,
                $dbConfig['username'],
                $password
            );

            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Reset sql_mode "STRICT_TRANS_TABLES" that will be default in MySQL 5.6
            $conn->exec('SET @@session.sql_mode = ""');
        } catch (\PDOException $e) {
            echo 'Could not connect to database: ' . $e->getMessage();
            exit(1);
        }

        return $conn;
    }

    /**
     * @return string
     */
    private function parseUrl(InputInterface $input)
    {
        $url = trim($input->getOption('shop-url'));

        if (!empty($url)) {
            if (parse_url($url) === false) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid Shop URL (%s).', $url)
                );
            }

            return $url;
        }

        $host = trim($input->getOption('host'));
        if (empty($host)) {
            return '';
        }

        $path = $input->getOption('path');
        $path = !empty($path) ? $path : '';
        if ($path === '/') {
            $path = '';
        }

        if (!empty($path)) {
            $path = trim($path, '/');
            $path = '/' . $path;
        }

        $url = 'http://' . $host . $path;

        return $url;
    }
}
