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
use Shopware\Recovery\Install\Struct\Currency;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;
use Shopware\Recovery\Install\Struct\LicenseUnpackRequest;
use Shopware\Recovery\Install\Struct\Locale;
use Shopware\Recovery\Install\Struct\Shop;
use Shopware\Recovery\Install\Struct\ShopwareEdition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
        $this->setDescription('Installs and does the initial configuration of Shopware');

        $this->addDbOptions();
        $this->addShopOptions();
        $this->addAdminOptions();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->IOHelper = new IOHelper(
            $input,
            $output,
            $this->getHelper('question')
        );

        /** @var Container $container */
        $container = $this->container = $this->getApplication()->getContainer();

        $container->offsetGet('shopware.notify')->doTrackEvent('Installer started');

        if ($this->IOHelper->isInteractive()) {
            $this->printStartMessage();
        }

        $connectionInfo = new DatabaseConnectionInformation();
        $connectionInfo = $this->getConnectionInfoFromConfig(SW_PATH . '/config.php', $connectionInfo);
        $connectionInfo = $this->getConnectionInfoFromArgs($input, $connectionInfo);
        $connectionInfo = $this->getConnectionInfoFromInteractiveShell(
            $this->IOHelper,
            $connectionInfo
        );

        /** @var ConfigWriter $configWriter */
        $configWriter = $this->container->offsetGet('config.writer');
        $configWriter->writeConfig($connectionInfo);

        $conn = $this->initDatabaseConnection($connectionInfo, $container);
        $databaseService = new DatabaseService($conn);

        $databaseService->createDatabase($connectionInfo->databaseName);
        $databaseService->selectDatabase($connectionInfo->databaseName);

        $skipImport = $databaseService->containsShopwareSchema()
            && $input->getOption('no-skip-import')
            && $this->shouldSkipImport();

        if (!$skipImport) {
            $this->importDatabase();
            $this->importSnippets();
        }

        $shop = new Shop();
        $shop = $this->getShopInfoFromArgs($input, $shop);
        $shop = $this->getShopInfoFromInteractiveShell($shop);

        if ($this->IOHelper->isInteractive() && !$this->webserverCheck($container, $shop)) {
            $this->IOHelper->writeln('Could not verify');
            if (!$this->IOHelper->askConfirmation('Continue?')) {
                return 1;
            }
        }

        $adminUser = new AdminUser();
        if (!$input->getOption('skip-admin-creation')) {
            $adminUser = $this->getAdminInfoFromArgs($input, $adminUser);
            $adminUser = $this->getAdminInfoFromInteractiveShell($adminUser);
        }

        $shopService = new ShopService($conn, $container['uniqueid.generator']);
        $shopService->updateShop($shop);
        $shopService->updateConfig($shop);

        $currencyService = new CurrencyService($conn);
        $currencyService->updateCurrency($shop);

        $localeService = new LocaleSettingsService($conn, $container);
        $localeService->updateLocaleSettings($shop->locale);

        if (!$input->getOption('skip-admin-creation')) {
            $adminService = new AdminService($conn);
            $adminService->createAdmin($adminUser);
            $adminService->addWidgets($adminUser);
        }

        $this->activateResponsiveTheme();

        if ($this->IOHelper->isInteractive()) {
            $this->IOHelper->cls();
            $this->IOHelper->writeln('<info>=== License Information ===</info>');

            /** @var LicenseUnpackService $licenseService */
            $licenseService = $container->offsetGet('license.service');

            /** @var LicenseInstaller $licenseInstaller */
            $licenseInstaller = $container->offsetGet('license.installer');

            $this->askShopwareEdition($shop, $licenseService, $licenseInstaller);
        }

        /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
        $systemLocker = $this->container->offsetGet('system.locker');
        $systemLocker();

        $container->offsetGet('uniqueid.persister')->store();

        $additionalInformation = [
            'method' => 'console',
        ];

        $container->offsetGet('shopware.notify')->doTrackEvent('Installer finished', $additionalInformation);

        if ($this->IOHelper->isInteractive()) {
            $this->IOHelper->writeln('<info>Shop successfully installed.</info>');
        }
    }

    /**
     * @return bool
     */
    protected function webserverCheck(Container $container, Shop $shop)
    {
        /** @var WebserverCheck $webserverCheck */
        $webserverCheck = $container->offsetGet('webserver.check');
        $pingUrl = $webserverCheck->buildPingUrl($shop);
        try {
            $this->IOHelper->writeln('Checking ping to: ' . $pingUrl);
            $webserverCheck->checkPing($shop);
        } catch (\Exception $e) {
            $this->IOHelper->writeln('Could not verify web server' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param string[] $locales
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function askForAdminLocale($locales)
    {
        $question = new ChoiceQuestion('Please select your admin locale', $locales);
        $question->setErrorMessage('Locale %s is invalid.');

        $shopLocale = $this->IOHelper->ask($question);

        return $shopLocale;
    }

    /**
     * @param string[] $locales
     *
     * @return string
     */
    protected function askForShopShopLocale($locales, $default = null)
    {
        $question = new ChoiceQuestion('Please select your shop locale', $locales, $default);
        $question->setErrorMessage('Locale %s is invalid.');

        $shopLocale = $this->IOHelper->ask($question);

        return $shopLocale;
    }

    /**
     * @return AdminUser
     */
    protected function getAdminInfoFromArgs(InputInterface $input, AdminUser $adminUser)
    {
        $adminUser->username = $input->getOption('admin-username');
        $adminUser->email = $input->getOption('admin-email');
        $adminUser->password = $input->getOption('admin-password');
        $adminUser->locale = $input->getOption('admin-locale');
        $adminUser->name = $input->getOption('admin-name');

        if ($adminUser->locale && !in_array($adminUser->locale, Locale::getValidLocales())) {
            throw new \RuntimeException('Invalid admin-locale provided');
        }

        return $adminUser;
    }

    /**
     * @return AdminUser
     */
    protected function getAdminInfoFromInteractiveShell(AdminUser $adminUser)
    {
        if (!$this->IOHelper->isInteractive()) {
            return $adminUser;
        }
        $this->IOHelper->cls();
        $this->IOHelper->writeln('<info>=== Admin Information ===</info>');

        $question = new Question('Admin username (demo): ', 'demo');
        $adminUsername = $this->IOHelper->ask($question);

        $question = new Question('Admin full name (Demo-Admin): ', 'Demo-Admin');
        $adminName = $this->IOHelper->ask($question);

        $question = new Question('Admin email (your.email@shop.com): ', 'your.email@shop.com');
        $adminEmail = $this->IOHelper->ask($question);

        $question = new Question('Admin password (demo): ', 'demo');
        $adminPassword = $this->IOHelper->ask($question);

        $adminLocale = $this->askForAdminLocale(Locale::getValidLocales());

        $adminUser->username = $adminUsername;
        $adminUser->email = $adminEmail;
        $adminUser->password = $adminPassword;
        $adminUser->locale = $adminLocale;
        $adminUser->name = $adminName;

        return $adminUser;
    }

    /**
     * @return Shop
     */
    protected function getShopInfoFromInteractiveShell(Shop $shop)
    {
        if (!$this->IOHelper->isInteractive()) {
            return $shop;
        }

        $this->IOHelper->cls();
        $this->IOHelper->writeln('<info>=== Shop Information ===</info>');

        $shop->locale = $this->askForShopShopLocale(Locale::getValidLocales(), $shop->locale);
        $shop->host = $this->IOHelper->ask(sprintf('Shop host (%s): ', $shop->host), $shop->host);
        $shop->basePath = $this->IOHelper->ask(sprintf('Shop base path (%s): ', $shop->basePath), $shop->basePath);
        $shop->name = $this->IOHelper->ask(sprintf('Shop name (%s): ', $shop->name), $shop->name);
        $shop->email = $this->IOHelper->ask(sprintf('Shop email (%s): ', $shop->email), $shop->email);

        $question = new ChoiceQuestion(
            sprintf('Shop currency (%s): ', $shop->currency),
            Currency::getValidCurrencies(),
            $shop->currency
        );
        $question->setErrorMessage('Currency %s is invalid.');
        $shop->currency = $this->IOHelper->ask($question);

        return $shop;
    }

    /**
     * @return Shop
     */
    protected function getShopInfoFromArgs(InputInterface $input, Shop $shop)
    {
        $shop->name = $input->getOption('shop-name');
        $shop->email = $input->getOption('shop-email');
        $shop->host = $input->getOption('shop-host');
        $shop->basePath = $input->getOption('shop-path');
        $shop->locale = $input->getOption('shop-locale');
        $shop->currency = $input->getOption('shop-currency');

        if ($shop->locale && !in_array($shop->locale, Locale::getValidLocales())) {
            throw new \RuntimeException('Invalid shop-locale provided');
        }

        return $shop;
    }

    /**
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
    protected function shouldSkipImport()
    {
        if (!$this->IOHelper->isInteractive()) {
            return true;
        }

        $question = new ConfirmationQuestion(
            'The database already contains shopware tables. Skip import? (yes/no) [yes]', true
        );
        $skipImport = $this->IOHelper->ask($question);

        return (bool) $skipImport;
    }

    /**
     * @return DatabaseConnectionInformation
     */
    protected function getConnectionInfoFromInteractiveShell(
        IOHelper $IOHelper,
        DatabaseConnectionInformation $connectionInfo
    ) {
        if (!$IOHelper->isInteractive()) {
            return $connectionInfo;
        }

        $IOHelper->writeln('<info>=== Database configuration ===</info>');
        $databaseInteractor = new DatabaseInteractor($IOHelper);

        $databaseConnectionInformation = $databaseInteractor->askDatabaseConnectionInformation(
            $connectionInfo
        );

        $databaseFactory = new DatabaseFactory();

        do {
            $pdo = null;
            try {
                $pdo = $databaseFactory->createPDOConnection($databaseConnectionInformation);
            } catch (\PDOException $e) {
                $IOHelper->writeln('');
                $IOHelper->writeln(sprintf('Got database error: %s', $e->getMessage()));
                $IOHelper->writeln('');

                $databaseConnectionInformation = $databaseInteractor->askDatabaseConnectionInformation(
                    $databaseConnectionInformation
                );
            }
        } while (!$pdo);

        $databaseService = new DatabaseService($pdo);

        $omitSchemas = ['information_schema', 'mysql', 'sys', 'performance_schema'];
        $databaseNames = $databaseService->getSchemas($omitSchemas);

        $defaultChoice = null;
        if ($connectionInfo->databaseName) {
            if (in_array($connectionInfo->databaseName, $databaseNames)) {
                $defaultChoice = array_search($connectionInfo->databaseName, $databaseNames);
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

        $databaseService->selectDatabase($databaseName);

        if (!$databaseInteractor->continueWithExistingTables($databaseName, $pdo)) {
            $IOHelper->writeln('Installation aborted.');

            exit;
        }

        $databaseConnectionInformation->databaseName = $databaseName;

        return $databaseConnectionInformation;
    }

    /**
     * @param string $configPath
     *
     * @return DatabaseConnectionInformation
     */
    protected function getConnectionInfoFromConfig($configPath, DatabaseConnectionInformation $connectionInfo)
    {
        if (!$configuration = $this->loadConfiguration($configPath)) {
            return $connectionInfo;
        }

        $connectionInfo->username = $configuration['db']['username'];
        $connectionInfo->hostname = $configuration['db']['host'];
        $connectionInfo->port = $configuration['db']['port'];
        $connectionInfo->databaseName = $configuration['db']['dbname'];
        $connectionInfo->password = $configuration['db']['password'];

        return $connectionInfo;
    }

    /**
     * @return DatabaseConnectionInformation
     */
    protected function getConnectionInfoFromArgs(InputInterface $input, DatabaseConnectionInformation $connectionInfo)
    {
        $connectionInfo->username = $input->getOption('db-user');
        $connectionInfo->hostname = $input->getOption('db-host');
        $connectionInfo->port = $input->getOption('db-port');
        $connectionInfo->databaseName = $input->getOption('db-name');
        $connectionInfo->socket = $input->getOption('db-socket');
        $connectionInfo->password = $input->getOption('db-password');

        return $connectionInfo;
    }

    /**
     * Loads config.php content as an array, or false if the file doesn't exist
     *
     * @param string $configPath
     *
     * @return array|bool
     */
    protected function loadConfiguration($configPath)
    {
        if (!is_file($configPath)) {
            return false;
        }

        $content = require $configPath;

        return $content;
    }

    private function addDbOptions()
    {
        $this
            ->addOption(
                'db-host',
                null,
                InputOption::VALUE_REQUIRED,
                'Database host',
                'localhost'
            )
            ->addOption(
                'db-port',
                null,
                InputOption::VALUE_REQUIRED,
                'Database port',
                '3306'
            )
            ->addOption(
                'db-socket',
                null,
                InputOption::VALUE_REQUIRED,
                'Database socket'
            )
            ->addOption(
                'db-user',
                null,
                InputOption::VALUE_REQUIRED,
                'Database user'
            )
            ->addOption(
                'db-password',
                null,
                InputOption::VALUE_REQUIRED,
                'Database password'
            )
            ->addOption(
                'db-name',
                null,
                InputOption::VALUE_REQUIRED,
                'Database name'
            )

            ->addOption(
                'no-skip-import',
                null,
                InputOption::VALUE_NONE,
                'Import database data even if a valid schema already exists'
            )
        ;
    }

    private function addShopOptions()
    {
        $this
            ->addOption(
                'shop-locale',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop locale'
            )
            ->addOption(
                'shop-host',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop host',
                'localhost'
            )
            ->addOption(
                'shop-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop path'
            )
            ->addOption(
                'shop-name',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop name',
                'Demo shop'
            )
            ->addOption(
                'shop-email',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop email address',
                'your.email@shop.com'
            )
            ->addOption(
                'shop-currency',
                null,
                InputOption::VALUE_REQUIRED,
                'Shop currency'
            )
        ;
    }

    private function addAdminOptions()
    {
        $this
            ->addOption(
                'skip-admin-creation',
                null,
                InputOption::VALUE_NONE,
                'If provided, no admin user will be created.'
            )

            ->addOption(
                'admin-username',
                null,
                InputOption::VALUE_REQUIRED,
                'Administrator username'
            )
            ->addOption(
                'admin-password',
                null,
                InputOption::VALUE_REQUIRED,
                'Administrator password'
            )
            ->addOption(
                'admin-email',
                null,
                InputOption::VALUE_REQUIRED,
                'Administrator email address'
            )
            ->addOption(
                'admin-locale',
                null,
                InputOption::VALUE_REQUIRED,
                'Administrator locale'
            )
            ->addOption(
                'admin-name',
                null,
                InputOption::VALUE_REQUIRED,
                'Administrator name'
            )
        ;
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        return $this->container;
    }

    /**
     * Import database
     */
    private function importDatabase()
    {
        /** @var \PDO $conn */
        $conn = $this->getContainer()->offsetGet('db');

        $this->IOHelper->cls();
        $this->IOHelper->writeln('<info>=== Import Database ===</info>');

        $preSql = <<<'EOT'
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
';
EOT;
        $conn->query($preSql);

        /** @var DumpIterator $dump */
        $dump = $this->getContainer()->offsetGet('database.dump_iterator');
        $this->dumpProgress($conn, $dump);
    }

    private function importSnippets()
    {
        $this->IOHelper->writeln('<info>=== Import Snippets ===</info>');

        /** @var \PDO $conn */
        $conn = $this->getContainer()->offsetGet('db');

        $preSql = '
           SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
           SET time_zone = "+00:00";
           SET FOREIGN_KEY_CHECKS = 0;
           SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = "de_DE");
           SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = "en_GB");
       ';

        $conn->query($preSql);

        /** @var DumpIterator $dump */
        $dump = $this->getContainer()->offsetGet('database.snippet_dump_iterator');
        $this->dumpProgress($conn, $dump);
    }

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
        $this->IOHelper->writeln('');
    }

    private function askShopwareEdition(
        Shop $shop,
        LicenseUnpackService $licenseService,
        LicenseInstaller $licenseInstaller
    ) {
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
            $this->IOHelper->writeln('<error>Could not validate license</error>');
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
            'cm' => 'Shopware Commercial Version (License: Commercial / License key required) e.g. Professional, Professional Plus, Enterprise',
        ];

        $hint = 'For PE/EB/EC a Commercial License key is required)';
        $question = new ChoiceQuestion('Please select your edition' . "\n" . $hint, $choices);
        $question->setErrorMessage('Edition %s is invalid.');
        $edition = $this->IOHelper->ask($question);

        $edition = strtoupper($edition);

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

    private function printStartMessage()
    {
        $version = $this->container->offsetGet('shopware.version');

        $this->IOHelper->cls();
        $this->IOHelper->printBanner();
        $this->IOHelper->writeln(sprintf('<info>Welcome to the Shopware %s installer</info>', $version));
        $this->IOHelper->writeln('');
        $this->IOHelper->ask(new Question('Press return to start installation.'));
        $this->IOHelper->cls();
    }

    private function activateResponsiveTheme()
    {
        /** @var ThemeService $themeService */
        $themeService = $this->container->offsetGet('theme.service');
        $themeService->activateResponsiveTheme();
    }
}
