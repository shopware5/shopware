<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Tests\General\Helpers;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception as MinkException;
use Behat\Mink\Session;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Components\CacheManager;
use Shopware\Components\ConfigWriter;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme\Installer;
use Shopware\Kernel;
use Shopware_Components_Config;

class FeatureContext extends SubContext implements SnippetAcceptingContext
{
    /**
     * @var string[]
     */
    protected array $dirtyConfigElements = [];

    protected static bool $isPrepared = false;

    protected static int $lastScenarioLine = 0;

    /**
     * Contains tags for features which rely on account state being persistent for all the contained scenarios
     *
     * @var string[]
     */
    protected static array $doNotResetFeatureTags = [
        'accountaddressmanagement',
        'checkoutadressmanagement',
    ];

    /**
     * @var Suite
     */
    protected static $suite;

    /**
     * Initializes context.
     * Every scenario gets its own context object.
     */
    public function __construct()
    {
        if (!self::$suite->hasSetting('template')) {
            Helper::throwException('Template not set. Please start testsuite using the --profile argument.');
        }

        $this->registerErrorHandler();

        $this->dirtyConfigElements = [];
    }

    /**
     * @BeforeSuite
     */
    public static function setup(BeforeSuiteScope $scope): void
    {
        self::$suite = $scope->getSuite();
    }

    /**
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope): void
    {
        if (!self::$isPrepared) {
            $this->prepare();
            self::$isPrepared = true;
        }

        $isResettable = \count(array_intersect(self::$doNotResetFeatureTags, $scope->getFeature()->getTags())) < 1;

        // Scenario skips a line, so it's not a new example
        if ($isResettable && $scope->getScenario()->getLine() !== self::$lastScenarioLine + 1) {
            $this->reset();
        }

        self::$lastScenarioLine = $scope->getScenario()->getLine();
    }

    /**
     * @BeforeScenario @captchaInactive
     */
    public function deactivateCaptchas(): void
    {
        $this->changeConfigValue('captchaMethod', 'nocaptcha');
    }

    /**
     * @BeforeScenario @searchWithoutMinLength
     */
    public function changeMinimumSearchLength(): void
    {
        $this->changeConfigValue('minsearchlenght', 0);
    }

    /**
     * Resize Browser Window. Works only with Selenium2Driver.
     *
     * @BeforeScenario
     */
    public function setupWindowSize(): void
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        if (!$this->getSession()->isStarted()) {
            $this->getSession()->start();
        }

        $this->getSession()->resizeWindow(1920, 1080, 'current');
    }

    /**
     * Take screenshot when step fails. Works only with Selenium2Driver.
     *
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $scope): void
    {
        if ($scope->getTestResult()->getResultCode() === TestResult::FAILED) {
            $this->takeScreenshot();
            $this->logRequest();
        }
    }

    /**
     * Save a screenshot of the current window to the file system.
     *
     * @param string|null $filename Desired filename, defaults to
     *                              <browser_name>_<ISO 8601 date>_<randomId>.png
     * @param string|null $filepath Desired filepath, defaults to
     *                              upload_tmp_dir, falls back to sys_get_temp_dir()
     */
    public function saveScreenshot(?string $filename = null, ?string $filepath = null): void
    {
        $generatedFilename = sprintf('%s_%s.%s', $this->getMinkParameter('browser_name'), $this->getDateTimeWithRandomString(), 'png');
        $filename = $filename ?: $generatedFilename;
        $filepath = $filepath ?: (ini_get('upload_tmp_dir') ?: sys_get_temp_dir());
        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }

    /**
     * @param bool|int|string $value
     */
    public function changeConfigValue(string $configName, $value): void
    {
        $dbal = $this->getService(Connection::class);
        $configId = $dbal->fetchOne(
            'SELECT `id` FROM `s_core_config_elements` WHERE `name` = ?',
            [$configName]
        );

        if (!$configId) {
            $message = sprintf('Configuration "%s" doesn\'t exist!', $configName);
            Helper::throwException($message);
        }

        $this->dirtyConfigElements[] = $configId;

        $configWriter = $this->getService(ConfigWriter::class);

        $configWriter->save($configName, $value);
        $configWriter->save($configName, $value, null, 2);

        $config = $this->getService(Shopware_Components_Config::class);
        $config->offsetSet($configName, $value);

        $this->clearCache();
    }

    /**
     * @AfterScenario @captchaInactive,@configChange,@searchWithoutMinLength
     */
    public function clearConfigValues(): void
    {
        if (!$this->dirtyConfigElements) {
            return;
        }

        $dirtyElements = implode(',', $this->dirtyConfigElements);
        $this->dirtyConfigElements = [];

        $sql = sprintf('DELETE FROM `s_core_config_values` WHERE `element_id` IN (%s)', $dirtyElements);
        $this->getService(Connection::class)->executeStatement($sql);

        $this->clearCache();
    }

    /**
     * @BeforeScenario @configChange
     */
    public function clearCache(): void
    {
        $cacheManager = $this->getService(CacheManager::class);
        $cacheManager->clearConfigCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearOpCache();
        $cacheManager->clearProxyCache();
    }

    public function registerErrorHandler(): void
    {
        error_reporting(-1);

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $errorNameMap = [
                E_ERROR => 'E_ERROR',
                E_WARNING => 'E_WARNING',
                E_PARSE => 'E_PARSE',
                E_NOTICE => 'E_NOTICE',
                E_CORE_ERROR => 'E_CORE_ERROR',
                E_CORE_WARNING => 'E_CORE_WARNING',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                E_USER_ERROR => 'E_USER_ERROR',
                E_USER_WARNING => 'E_USER_WARNING',
                E_USER_NOTICE => 'E_USER_NOTICE',
                E_STRICT => 'E_STRICT',
                E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                E_DEPRECATED => 'E_DEPRECATED',
                E_USER_DEPRECATED => 'E_USER_DEPRECATED',
                E_ALL => 'E_ALL',
            ];
            $filepath = $this->getService(Kernel::class)->getRootDir() . '/build/logs/mink';

            $filename = sprintf('errors_%s.%s', $this->getDateTimeWithRandomString(), 'log');
            $filepath .= '/' . $filename;
            file_put_contents($filepath, $errorNameMap[$errno] . ': ' . $errstr, FILE_APPEND);

            return true;
        });
    }

    /**
     * @BeforeScenario @noinfinitescrolling
     */
    public static function deactivateInfiniteScrolling(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);

        $sql = "SET @elementId = (SELECT id FROM `s_core_templates_config_elements` WHERE `name` = 'infiniteScrolling');
                INSERT INTO `s_core_templates_config_values` (`element_id`, `shop_id`, `value`)
                VALUES (@elementId, '1', 'b:0;')
                ON DUPLICATE KEY UPDATE `value` = 'b:0;';";

        $dbal->executeQuery($sql);

        self::clearTemplateCache();
    }

    /**
     * @AfterScenario @noinfinitescrolling
     */
    public static function activateInfiniteScrolling(): void
    {
        $dbal = Shopware()->Container()->get(Connection::class);

        $sql = "SET @elementId = (SELECT id FROM `s_core_templates_config_elements` WHERE `name` = 'infiniteScrolling');
                INSERT INTO `s_core_templates_config_values` (`element_id`, `shop_id`, `value`)
                VALUES (@elementId, '1', 'b:1;')
                ON DUPLICATE KEY UPDATE `value` = 'b:1;';";

        $dbal->executeQuery($sql);

        self::clearTemplateCache();
    }

    private function prepare(): void
    {
        $em = $this->getService(ModelManager::class);
        $em->generateAttributeModels();

        // refresh s_core_templates
        $this->registerErrorHandler();
        $this->getService(Installer::class)->synchronize();
        restore_error_handler();

        // get the template id
        $sql = sprintf(
            'SELECT id FROM `s_core_templates` WHERE template = "%s"',
            self::$suite->getSetting('template')
        );

        $templateId = $this->getService(Connection::class)->fetchOne($sql);
        if (!$templateId) {
            Helper::throwException(sprintf('Unable to find template by name %s', self::$suite->getSetting('template')));
        }

        // set the template for shop "Deutsch" and activate SEPA payment method
        $sql = <<<"EOD"
            UPDATE `s_core_shops` SET `template_id`= $templateId WHERE `id` = 1;
            UPDATE `s_core_paymentmeans` SET `active`= 1;
EOD;
        $this->getService(Connection::class)->executeStatement($sql);

        Helper::setCurrentLanguage('de');

        $pluginManager = $this->getService(InstallerService::class);

        // hack to prevent behat error handler kicking in.
        $this->registerErrorHandler();
        $pluginManager->refreshPluginList();
        restore_error_handler();

        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    private function reset(): void
    {
        $password = md5('shopware');

        $sql = <<<"EOD"
            UPDATE s_user SET password = "$password", encoder = "md5", paymentID = 5, failedlogins = 0, lockeduntil = NULL;

            SET FOREIGN_KEY_CHECKS = 0;

            TRUNCATE s_order_basket;
            TRUNCATE s_order_basket_attributes;
            TRUNCATE s_order_notes;
            TRUNCATE s_order_comparisons;
            DELETE FROM s_user WHERE id > 2;

            SET FOREIGN_KEY_CHECKS = 1;
EOD;
        $this->getService(Connection::class)->executeStatement($sql);
    }

    private function logRequest(): void
    {
        $session = $this->getSession();
        $log = sprintf('Current page: %d %s', $this->getStatusCode(), $session->getCurrentUrl()) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getRequestContentLogMessage($session);
        $this->saveLog($log);
    }

    private function saveLog(string $content): void
    {
        $logDir = $this->getService(Kernel::class)->getRootDir() . '/build/logs/mink';

        $path = sprintf('%s/behat-%s.%s', $logDir, $this->getDateTime(), 'log');
        if (!file_put_contents($path, $content)) {
            Helper::throwException(sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    private function getStatusCode(): ?int
    {
        try {
            return $this->getSession()->getStatusCode();
        } catch (MinkException $exception) {
            return null;
        }
    }

    private function getResponseHeadersLogMessage(Session $session): ?string
    {
        try {
            return "Response headers:\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    private function getRequestContentLogMessage(Session $session): ?string
    {
        try {
            return "Response content:\n" . $session->getPage()->getContent() . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    private function takeScreenshot(): void
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $filePath = $this->getService(Kernel::class)->getRootDir() . '/build/logs/mink';

        $this->saveScreenshot(null, $filePath);
    }

    private static function clearTemplateCache(): void
    {
        $cacheManager = Shopware()->Container()->get(CacheManager::class);
        $cacheManager->clearConfigCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearThemeCache();
    }

    private function getDateTimeWithRandomString(): string
    {
        return $this->getDateTime() . '_' . uniqid('', true);
    }

    private function getDateTime(): string
    {
        return (new DateTime())->format('Y-m-d--H-i-s');
    }
}
