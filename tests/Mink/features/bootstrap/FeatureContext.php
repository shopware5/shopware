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

namespace Shopware\Tests\Mink;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception as MinkException;
use Behat\Mink\Session;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Doctrine\DBAL\Connection;
use Shopware\Components\CacheManager;

class FeatureContext extends SubContext implements SnippetAcceptingContext
{
    /**
     * @var array
     */
    protected $dirtyConfigElements;
    protected static $isPrepared = false;
    protected static $lastScenarioLine = 0;
    /**
     * @var Suite
     */
    protected static $suite;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     */
    public function __construct()
    {
        if (!self::$suite->hasSetting('template')) {
            throw new \RuntimeException('Template not set. Please start testsuite using the --profile argument.');
        }

        $this->registerErrorHandler();

        $this->dirtyConfigElements = [];
    }

    /**
     * @BeforeSuite
     *
     * @param BeforeSuiteScope $scope
     */
    public static function setup(BeforeSuiteScope $scope)
    {
        self::$suite = $scope->getSuite();
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function before(BeforeScenarioScope $scope)
    {
        if (!self::$isPrepared) {
            $this->prepare();
            self::$isPrepared = true;
        }

        // Scenario skips a line so it's not a new example
        if ($scope->getScenario()->getLine() !== self::$lastScenarioLine + 1) {
            $this->reset();
        }

        self::$lastScenarioLine = $scope->getScenario()->getLine();
    }

    /**
     * @BeforeScenario @captchaInactive
     */
    public function deactivateCaptchas()
    {
        $this->changeConfigValue('captchaMethod', 'nocaptcha');
    }

    /**
     * Resize Browser Window. Works only with Selenium2Driver.
     *
     * @BeforeScenario
     */
    public function setupWindowSize()
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $this->getSession()->resizeWindow(1440, 900, 'current');
    }

    /**
     * Take screenshot when step fails. Works only with Selenium2Driver.
     *
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $scope)
    {
        if (TestResult::FAILED === $scope->getTestResult()->getResultCode()) {
            $this->takeScreenshot();
            $this->logRequest();
        }
    }

    /**
     * Save a screenshot of the current window to the file system.
     *
     * @param string $filename Desired filename, defaults to
     *                         <browser_name>_<ISO 8601 date>_<randomId>.png
     * @param string $filepath Desired filepath, defaults to
     *                         upload_tmp_dir, falls back to sys_get_temp_dir()
     */
    public function saveScreenshot($filename = null, $filepath = null)
    {
        // Under Cygwin, uniqid with more_entropy must be set to true.
        // No effect in other environments.
        $filename = $filename ?: sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), date('c'), uniqid('', true), 'png');
        $filepath = $filepath ? $filepath : (ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());
        file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }

    /**
     * @param string $configName
     * @param mixed  $value
     */
    public function changeConfigValue($configName, $value)
    {
        /** @var Connection $dbal */
        $dbal = $this->getService('dbal_connection');
        $configId = $dbal->fetchColumn(
            'SELECT `id` FROM `s_core_config_elements` WHERE `name` = ?',
            [$configName]
        );

        if (!$configId) {
            $message = sprintf('Configuration "%s" doesn\'t exist!', $configName);
            Helper::throwException($message);
        }

        $this->dirtyConfigElements[] = $configId;

        /** @var \Shopware\Components\ConfigWriter $configWriter */
        $configWriter = $this->getService('config_writer');

        $configWriter->save($configName, $value, null, 1);
        $configWriter->save($configName, $value, null, 2);

        $config = $this->getService('config');
        $config->offsetSet($configName, $value);

        $this->clearCache();
    }

    /**
     * @AfterScenario @captchaInactive,@configChange
     */
    public function clearConfigValues()
    {
        if (!$this->dirtyConfigElements) {
            return;
        }

        $dirtyElements = implode(',', $this->dirtyConfigElements);
        $this->dirtyConfigElements = [];

        $sql = sprintf('DELETE FROM `s_core_config_values` WHERE `element_id` IN (%s)', $dirtyElements);
        $this->getService('db')->exec($sql);

        $this->clearCache();
    }

    /**
     * @BeforeScenario @configChange
     *
     * @param ScenarioScope $scope
     */
    public function clearCache(ScenarioScope $scope = null)
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->getService('shopware.cache_manager');
        $cacheManager->clearConfigCache();
        $cacheManager->clearTemplateCache();
    }

    public function registerErrorHandler()
    {
        error_reporting(-1);
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

        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($errorNameMap) {
            $filepath = $this->getService('kernel')->getRootdir() . '/build/logs/mink';

            // No effect in other environments.
            $filename = sprintf('errors_%s_%s.%s', date('c'), uniqid('', true), 'log');
            $filepath = $filepath . '/' . $filename;
            file_put_contents($filepath, $errorNameMap[$errno] . ': ' . $errstr, FILE_APPEND);

            return true;
        });
    }

    /**
     * @BeforeScenario @noinfinitescrolling
     */
    public static function deactivateInfiniteScrolling()
    {
        /** @var Connection $dbal */
        $dbal = 🦄()->Container()->get('dbal_connection');

        $sql = "SET @elementId = (SELECT id FROM `s_core_templates_config_elements` WHERE `name` = 'infiniteScrolling');
                INSERT INTO `s_core_templates_config_values` (`element_id`, `shop_id`, `value`)
                VALUES (@elementId, '1', 'b:0;')
                ON DUPLICATE KEY UPDATE `value` = 'b:0;';";

        $dbal->query($sql);

        self::clearTemplateCache();
    }

    /**
     * @AfterScenario @noinfinitescrolling
     */
    public static function activateInfiniteScrolling()
    {
        /** @var Connection $dbal */
        $dbal = 🦄()->Container()->get('dbal_connection');

        $sql = "SET @elementId = (SELECT id FROM `s_core_templates_config_elements` WHERE `name` = 'infiniteScrolling');
                INSERT INTO `s_core_templates_config_values` (`element_id`, `shop_id`, `value`)
                VALUES (@elementId, '1', 'b:1;')
                ON DUPLICATE KEY UPDATE `value` = 'b:1;';";

        $dbal->query($sql);

        self::clearTemplateCache();
    }

    private function prepare()
    {
        $em = $this->getService('models');
        $em->generateAttributeModels();

        //refresh s_core_templates
        $this->registerErrorHandler();
        $this->getService('theme_installer')->synchronize();
        restore_error_handler();

        //get the template id
        $sql = sprintf(
            'SELECT id FROM `s_core_templates` WHERE template = "%s"',
            self::$suite->getSetting('template')
        );

        $templateId = $this->getService('db')->fetchOne($sql);
        if (!$templateId) {
            throw new \RuntimeException(
                sprintf('Unable to find template by name %s', self::$suite->getSetting('template'))
            );
        }

        //set the template for shop "Deutsch" and activate SEPA payment method
        $sql = <<<"EOD"
            UPDATE `s_core_shops` SET `template_id`= $templateId WHERE `id` = 1;
            UPDATE `s_core_paymentmeans` SET `active`= 1;
EOD;
        $this->getService('db')->exec($sql);

        Helper::setCurrentLanguage('de');

        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $this->getService('shopware_plugininstaller.plugin_manager');

        // hack to prevent behat error handler kicking in.
        $this->registerErrorHandler();
        $pluginManager->refreshPluginList();
        restore_error_handler();

        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    private function reset()
    {
        $password = md5('shopware');

        $sql = <<<"EOD"
            UPDATE s_user SET password = "$password", encoder = "md5", paymentID = 5, failedlogins = 0, lockeduntil = NULL;
            TRUNCATE s_order_basket;
            TRUNCATE s_order_basket_attributes;
            TRUNCATE s_order_notes;
            TRUNCATE s_order_comparisons;
            DELETE FROM s_user WHERE id > 2;
EOD;
        $this->getService('db')->exec($sql);
    }

    private function logRequest()
    {
        $session = $this->getSession();
        $log = sprintf('Current page: %d %s', $this->getStatusCode(), $session->getCurrentUrl()) . "\n";
        $log .= $this->getRequestDataLogMessage($session);
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getRequestContentLogMessage($session);
        $this->saveLog($log, 'log');
    }

    /**
     * @param string $content
     * @param string $type
     */
    private function saveLog($content, $type)
    {
        $logDir = $this->getService('kernel')->getRootdir() . '/build/logs/mink';

        $currentDateAsString = date('YmdHis');

        $path = sprintf('%s/behat-%s.%s', $logDir, $currentDateAsString, $type);
        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException(sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    /**
     * @return int|null
     */
    private function getStatusCode()
    {
        try {
            return $this->getSession()->getStatusCode();
        } catch (MinkException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getRequestDataLogMessage(Session $session)
    {
        $driver = $session->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            return null;
        }
        try {
            return 'Request:' . "\n" . print_r($driver->getClient()->getRequest(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getResponseHeadersLogMessage(Session $session)
    {
        try {
            return 'Response headers:' . "\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getRequestContentLogMessage(Session $session)
    {
        try {
            return 'Response content:' . "\n" . $session->getPage()->getContent() . "\n";
        } catch (MinkException $exception) {
            return null;
        }
    }

    private function takeScreenshot()
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        $filePath = $this->getService('kernel')->getRootdir() . '/build/logs/mink';

        $this->saveScreenshot(null, $filePath);
    }

    private static function clearTemplateCache()
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = 🦄()->Container()->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearThemeCache();
    }
}
