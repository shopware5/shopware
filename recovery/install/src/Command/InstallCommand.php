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

namespace Shopware\Recovery\Install\Command;

use Pimple\Container;
use Shopware\Recovery\Common\DumpIterator;
use Shopware\Recovery\Common\IOHelper;
use Shopware\Recovery\Install\DatabaseFactory;
use Shopware\Recovery\Install\DatabaseInteractor;
use Shopware\Recovery\Install\Service\AdminService;
use Shopware\Recovery\Install\Service\ConfigWriter;
use Shopware\Recovery\Install\Service\CurrencyService;
use Shopware\Recovery\Install\Service\DatabaseService;
use Shopware\Recovery\Install\Service\LicenseInstaller;
use Shopware\Recovery\Install\Service\LicenseUnpackService;
use Shopware\Recovery\Install\Service\LocaleSettingsService;
use Shopware\Recovery\Install\Service\ShopService;
use Shopware\Recovery\Install\Service\ThemeService;
use Shopware\Recovery\Install\Service\WebserverCheck;
use Shopware\Recovery\Install\Struct\AdminUser;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;
use Shopware\Recovery\Install\Struct\LicenseUnpackRequest;
use Shopware\Recovery\Install\Struct\Shop;
use Shopware\Recovery\Install\Struct\ShopwareEdition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class InstallCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var IOHelper
     */
    private $IOHelper;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install');
        $this->setDescription('Installs shopware');
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->IOHelper = $ioService = new IOHelper(
            $input,
            $output,
            $this->getHelper('question')
        );

        /** @var $container Container */
        $container = $this->container = $this->getApplication()->getContainer();

        if (!$ioService->isInteractive()) {
            $ioService->writeln("<error>Non interactive installation is not supported.</error>");

            return 1;
        }

        $this->printStartMessage($ioService);

        if ($this->isConfigured()) {
            $connectionInfo = $this->createConnectionInfoFromConfig(SW_PATH . '/config.php');
            $connectionInfo = $this->interactDatabaseConfig($ioService, $connectionInfo);
        } else {
            $connectionInfo = $this->interactDatabaseConfig($ioService);
        }

        $conn = $this->initDatabaseConnection($connectionInfo, $container);
        $databaseService = new DatabaseService($conn);

        $skipImport = $databaseService->containsShopwareSchema() && $this->shouldSkipImport();
        if (!$skipImport) {
            $this->importDatabase();
            $this->importSnippets();
        }

        $locales = ['de_DE', 'en_GB'];
        $shop = $this->askForShopInformation($locales);

        $currencies = ['EUR', 'USD', 'GBP'];
        $currency = $this->askForCurrencyInformation($currencies);
        $shop->currency = $currency;

        if (!$this->webserverCheck($ioService, $container, $shop)) {
            $ioService->writeln("Could not verify");
            if (!$this->IOHelper->askConfirmation("Continue?")) {
                return 1;
            }
        }

        $adminUser = $this->askAdminInformation($locales);

        $shopService = new ShopService($conn);
        $shopService->updateShop($shop);
        $shopService->updateConfig($shop);

        $currencyService = new CurrencyService($conn);
        $currencyService->updateCurrency($shop);

        $currencyService = new LocaleSettingsService($conn, $container);
        $currencyService->updateLocaleSettings($shop->locale);

        $adminService = new AdminService($conn);
        $adminService->createAdmin($adminUser);
        $adminService->addWidgets($adminUser);

        $this->activateResponsiveTheme();

        $this->IOHelper->cls();
        $this->IOHelper->writeln("<info>=== License Information ===</info>");

        /** @var $licenseService LicenseUnpackService */
        $licenseService = $container->offsetGet('license.service');

        /** @var $licenseInstaller LicenseInstaller */
        $licenseInstaller = $container->offsetGet('license.installer');

        $this->askShopwareEdition($shop, $licenseService, $licenseInstaller);

        /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
        $systemLocker = $this->container->offsetGet('system.locker');
        $systemLocker();

        $ioService->writeln("<info>Shop successfully installed.</info>");
    }

    /**
     *
     */
    private function importDatabase()
    {
        /** @var $conn \PDO */
        $conn = $this->getContainer()->offsetGet('db');

        $this->IOHelper->cls();
        $this->IOHelper->writeln("<info>=== Import Database ===</info>");

        $preSql = <<<'EOT'
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
';
EOT;
        $conn->query($preSql);

        /** @var $dump DumpIterator */
        $dump = $this->getContainer()->offsetGet('database.dump_iterator');
        $this->dumpProgress($conn, $dump);
    }

    private function importSnippets()
    {
        $this->IOHelper->writeln("<info>=== Import Snippets ===</info>");

        /** @var $conn \PDO */
        $conn = $this->getContainer()->offsetGet('db');

        $preSql = '
           SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
           SET time_zone = "+00:00";
           SET FOREIGN_KEY_CHECKS = 0;
           SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = "de_DE");
           SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = "en_GB");
       ';

        $conn->query($preSql);

        /** @var $dump DumpIterator */
        $dump = $this->getContainer()->offsetGet('database.snippet_dump_iterator');
        $this->dumpProgress($conn, $dump);
    }

    /**
     * @param \PDO         $conn
     * @param DumpIterator $dump
     */
    private function dumpProgress(\PDO $conn, DumpIterator $dump)
    {
        $totalCount = $dump->count();

        $progress = $this->IOHelper->createProgressBar($totalCount);
        $progress->setRedrawFrequency(20);
        $progress->start();

        foreach ($dump as $sql) {
            // Execute each query one by one
            // https://bugs.php.net/bug.php?id=61613
            $conn->exec($sql);
            $progress->advance();
        }
        $progress->finish();
        $this->IOHelper->writeln("");
    }

    /**
     * @param Shop                 $shop
     * @param LicenseUnpackService $licenseService
     * @param LicenseInstaller     $licenseInstaller
     */
    private function askShopwareEdition(Shop $shop, LicenseUnpackService $licenseService, LicenseInstaller $licenseInstaller)
    {
        $shopwareEdition = $this->askEdition();
        if (!$shopwareEdition->isCommercial()) {
            return;
        }

        $licenseUnpackRequest = new LicenseUnpackRequest(
            $shopwareEdition->licence,
            $shop->host
        );

        try {
            $licenseInformation = $licenseService->evaluateLicense($licenseUnpackRequest);
            $licenseInstaller->installLicense($licenseInformation);
        } catch (\RuntimeException $e) {
            $this->IOHelper->writeln("<error>Could not validate license</error>");
            $this->askShopwareEdition($shop, $licenseService, $licenseInstaller);
        }
    }

    /**
     * @return ShopwareEdition
     */
    private function askEdition()
    {
        $choices = [
            'ce' => 'Shopware Community Edition (License: AGPL)',
            'cm' => 'Shopware Commercial Version (License: Commercial / License key required) e.g. Professional, Professional Plus, Enterprise'
        ];

        $hint =  "For PE/EB/EC a Commercial License key is required)";
        $question = new ChoiceQuestion('Please select your edition' . "\n" . $hint, $choices);
        $question->setErrorMessage('Edition %s is invalid.');
        $edition = $this->IOHelper->ask($question);

        $flip = array_flip($choices);
        $edition = strtoupper($flip[$edition]);

        $license = null;
        if ($edition != ShopwareEdition::CE) {
            $license = $this->askLicence();
        }

        $shopwareEdition = ShopwareEdition::createFromEditionAndLicence($edition, $license);

        return $shopwareEdition;
    }

    /**
     * @return string
     */
    private function askLicence()
    {
        return $this->IOHelper->askMultiLineQuestion(
            new Question('Please provide licence. An empty line will exit the input: ' . "\n")
        );
    }

    /**
     * @param  IOHelper  $IOHelper
     * @param  Container $container
     * @param  Shop      $shop
     * @return bool
     */
    protected function webserverCheck(IOHelper $IOHelper, Container $container, Shop $shop)
    {
        /** @var $webserverCheck WebserverCheck */
        $webserverCheck = $container->offsetGet('webserver.check');
        $pingUrl = $webserverCheck->buildPingUrl($shop);
        try {
            $IOHelper->writeln("Checking ping to: " . $pingUrl);
            $webserverCheck->checkPing($shop);
        } catch (\Exception $e) {
            $IOHelper->writeln("Could not verify webserver" . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param IOHelper $ioService
     */
    private function printStartMessage(IOHelper $ioService)
    {
        $version = $this->container->offsetGet('shopware.version');

        $ioService->cls();
        $ioService->printBanner();
        $ioService->writeln(sprintf("<info>Welcome to the Shopware %s installer</info>", $version));
        $ioService->writeln("");
        $ioService->ask(new Question('Press return to start installation.'));
        $ioService->cls();
    }

    /**
     * @param  string[]          $locales
     * @return string
     * @throws \RuntimeException
     */
    protected function askForAdminLocale($locales)
    {
        $question = new ChoiceQuestion('Please select your admin locale', $locales);
        $question->setErrorMessage('Locale %s is invalid.');

        $shopLocale = $this->IOHelper->ask($question);

        return $shopLocale;
    }

    /**
     * @param  string[]          $locales
     * @return string
     * @throws \RuntimeException
     */
    protected function askForShopShopLocale($locales)
    {
        $question = new ChoiceQuestion("Please select your shop locale", $locales);
        $question->setErrorMessage('Locale %s is invalid.');

        $shopLocale = $this->IOHelper->ask($question);

        return $shopLocale;
    }

    /**
     * @param  IOHelper                      $IOHelper
     * @param  DatabaseConnectionInformation $defaultConnectionInformation
     * @return DatabaseConnectionInformation
     */
    protected function interactDatabaseConfig(
        IOHelper $IOHelper,
        DatabaseConnectionInformation $defaultConnectionInformation = null
    ) {
        if ($defaultConnectionInformation === null) {
            $defaultConnectionInformation = new DatabaseConnectionInformation([
                'hostname' => 'localhost',
                'port'    => 3306,
            ]);
        }

        $IOHelper->writeln("<info>=== Database configuration ===</info>");
        $databaseInteractor = new DatabaseInteractor($IOHelper);

        $databaseConnectionInformation = $databaseInteractor->askDatabaseConnectionInformation(
            $defaultConnectionInformation
        );

        $databaseFactory = new DatabaseFactory();

        do {
            $pdo = null;
            try {
                $pdo = $databaseFactory->createPDOConnection($databaseConnectionInformation);
            } catch (\PDOException $e) {
                $IOHelper->writeln('');
                $IOHelper->writeln(sprintf("Got database error: %s", $e->getMessage()));
                $IOHelper->writeln('');

                $databaseConnectionInformation = $databaseInteractor->askDatabaseConnectionInformation(
                    $databaseConnectionInformation
                );
            }
        } while (!$pdo);

        $databaseService = new DatabaseService($pdo);

        $databaseNames = $databaseService->getAvailableDatabaseNames();

        $defaultChoice = null;
        if ($defaultConnectionInformation->databaseName) {
            if (in_array($defaultConnectionInformation->databaseName, $databaseNames)) {
                $defaultChoice = array_search($defaultConnectionInformation->databaseName, $databaseNames);
            }
        }

        $choices = $databaseNames;
        array_unshift($choices, '[create new database]');
        $question = new ChoiceQuestion('Please select your database', $choices, $defaultChoice);
        $question->setErrorMessage('Database %s is invalid.');
        $databaseName = $databaseInteractor->askQuestion($question);

        if ($databaseName === $choices[0]) {
            $databaseName = $databaseInteractor->createDatabase($pdo);
        }

        $pdo->exec("USE $databaseName");

        if (!$databaseInteractor->continueWithExistingTables($databaseName, $pdo)) {
            $IOHelper->writeln("Installation aborted.");

            exit;
        }

        $databaseConnectionInformation->databaseName = $databaseName;

        /** @var $configWriter ConfigWriter */
        $configWriter = $this->container->offsetGet('config.writer');
        $configWriter->writeConfig($databaseConnectionInformation);

        return $databaseConnectionInformation;
    }

    /**
     * @param  string                        $configPath
     * @return DatabaseConnectionInformation
     */
    protected function createConnectionInfoFromConfig($configPath)
    {
        $config = require $configPath;

        $info = new DatabaseConnectionInformation();
        $info->username     = $config['db']['username'];
        $info->hostname     = $config['db']['host'];
        $info->port         = $config['db']['port'];
        $info->databaseName = $config['db']['dbname'];
        $info->password     = $config['db']['password'];

        return $info;
    }

    /**
     * @param  string[]  $locales
     * @return AdminUser
     */
    protected function askAdminInformation($locales)
    {
        $this->IOHelper->cls();
        $this->IOHelper->writeln("<info>=== Admin Information ===</info>");

        $question = new Question('Admin username (demo): ', 'demo');
        $adminUsername = $this->IOHelper->ask($question);

        $question = new Question('Admin full name (Demo-Admin): ', 'Demo-Admin');
        $adminName = $this->IOHelper->ask($question);

        $question = new Question('Admin email (your.email@shop.com): ', 'your.email@shop.com');
        $adminEmail = $this->IOHelper->ask($question);

        $question = new Question('Admin password (demo): ', 'demo');
        $adminPassword = $this->IOHelper->ask($question);

        $adminLocale = $this->askForAdminLocale($locales);

        $adminUser = new AdminUser();
        $adminUser->username = $adminUsername;
        $adminUser->email    = $adminEmail;
        $adminUser->password = $adminPassword;
        $adminUser->locale   = $adminLocale;
        $adminUser->name     = $adminName;

        return $adminUser;
    }

    /**
     * @param  string[] $locales
     * @return Shop
     */
    protected function askForShopInformation($locales)
    {
        $this->IOHelper->cls();
        $this->IOHelper->writeln("<info>=== Shop Information ===</info>");

        $shopLocale = $this->askForShopShopLocale($locales);

        $host     = $this->IOHelper->ask('Shop Host (localhost): ', 'localhost');
        $basePath = $this->IOHelper->ask('Shop base path: ');
        $name     = $this->IOHelper->ask('Shop name (Demoshop): ', 'Demoshop');
        $email    = $this->IOHelper->ask('Shop email (your.email@shop.com): ', 'your.email@shop.com');

        $shop = new Shop();
        $shop->name     = $name;
        $shop->email    = $email;
        $shop->host     = $host;
        $shop->basePath = $basePath;
        $shop->locale   = $shopLocale;

        return $shop;
    }

    /**
     * @param  string[] $currencies
     * @return string currency
     */
    protected function askForCurrencyInformation($currencies)
    {
        $this->IOHelper->cls();
        $this->IOHelper->writeln("<info>=== Currency Information ===</info>");

        $question = new ChoiceQuestion("Please select your shop currency", $currencies);
        $question->setErrorMessage('Currency %s is invalid.');

        $currency = $this->IOHelper->ask($question);

        return $currency;
    }

    private function activateResponsiveTheme()
    {
        /** @var ThemeService $themeService */
        $themeService = $this->container->offsetGet('theme.service');
        $themeService->activateResponsiveTheme();

        return;
    }

    /**
     * @param DatabaseConnectionInformation $connectionInfo
     * @param Container $container
     * @return \PDO
     */
    protected function initDatabaseConnection(DatabaseConnectionInformation $connectionInfo, Container $container)
    {
        $databaseFactory = new DatabaseFactory();
        $conn = $databaseFactory->createPDOConnection($connectionInfo);
        $container->offsetSet('db', $conn);

        return $conn;
    }

    /**
     * @return bool
     */
    protected function isConfigured()
    {
        if (!is_file(SW_PATH . '/config.php')) {
            return false;
        }

        $content = file_get_contents(SW_PATH . '/config.php');

        return stripos('%db.user%', $content) === false;
    }

    /**
     * @return bool
     */
    protected function shouldSkipImport()
    {
        $question = new ConfirmationQuestion(
            'The database already contains shopware tables. Skip import? (yes/no) [yes]', true
        );
        $skipImport = $this->IOHelper->ask($question);

        return (bool)$skipImport;
    }
}
