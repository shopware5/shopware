<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Suite\Suite;
use Shopware\Kernel;
use Behat\MinkExtension\Context\MinkContext;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FeatureContext extends MinkContext implements KernelAwareContext//, \Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var array
     */
    protected $dirtyConfigElements;

    /**
     * @var Suite
     */
    protected static $suite;

    /**
     * @var int
     */
    private static $scenarioIncrement;

    /**
     * @var int
     */
    private static $exampleIncrement;

    /**
     * @var int
     */
    private static $lastExampleLine;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     */
    public function __construct()
    {
        if (!self::$suite->hasSetting('template')) {
            throw new \RuntimeException("Template not set. Please start testsuite using the --profile argument.");
        }

        $this->registerErrorHandler();
        $this->dirtyConfigElements = array();
    }

    /**
     * @BeforeSuite
     */
    public static function setup(BeforeSuiteScope $scope)
    {
        self::$suite = $scope->getSuite();
        self::$scenarioIncrement = 0;
        self::$exampleIncrement = 0;
        self::$lastExampleLine = 0;
    }

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        $this->increaseIncrements($scope->getScenario());

        if ($this->isFirstScenario()) {
            $this->prepare($this->getContainer());
        }

        if ($this->isFirstExample()) {
            $this->reset($this->getContainer());
        }
    }

    /**
     * @param ScenarioInterface $scenario
     */
    private function increaseIncrements(ScenarioInterface $scenario)
    {
        if ($scenario->getNodeType() === 'Scenario') {
            $this->increaseScenarioIncrement();
            return;
        }

        if (self::$lastExampleLine !== $scenario->getLine() - 1) {
            $this->increaseScenarioIncrement();
        }

        self::$exampleIncrement++;
        self::$lastExampleLine = $scenario->getLine();
    }

    /**
     *
     */
    private function increaseScenarioIncrement()
    {
        self::$scenarioIncrement++;
        self::$exampleIncrement = 0;
    }

    /**
     * @return bool
     */
    private function isFirstScenario()
    {
        return ((self::$scenarioIncrement === 1) && $this->isFirstExample());
    }

    /**
     * @return bool
     */
    private function isFirstExample()
    {
        return (self::$exampleIncrement < 2);
    }

    /**
     * @param ContainerInterface $container
     * @throws \Exception
     */
    private function prepare(ContainerInterface $container)
    {
        $em = $container->get('models');
        $em->generateAttributeModels();

        //refresh s_core_templates
        $this->registerErrorHandler();
        $container->get('theme_installer')->synchronize();
        restore_error_handler();

        //get the template id
        $sql = sprintf(
            'SELECT id FROM `s_core_templates` WHERE template = "%s"',
            self::$suite->getSetting('template')
        );

        $templateId = $container->get('db')->fetchOne($sql);
        if (!$templateId) {
            throw new \RuntimeException(
                sprintf("Unable to find template by name %s", self::$suite->getSetting('template'))
            );
        }

        //set the template for shop "Deutsch" and activate SEPA payment method
        $sql = <<<"EOD"
            UPDATE `s_core_shops` SET `template_id`= $templateId WHERE `id` = 1;
            UPDATE `s_core_paymentmeans` SET `active`= 1;
EOD;
        $container->get('db')->exec($sql);

        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $container->get('shopware_plugininstaller.plugin_manager');

        // hack to prevent behat error handler kicking in.
        $this->registerErrorHandler();
        $pluginManager->refreshPluginList();
        restore_error_handler();

        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * @param string $technicalName
     * @throws \Exception
     */
    protected function installPlugin($technicalName)
    {
        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $this->getContainer()->get('shopware_plugininstaller.plugin_manager');
        $plugin = $pluginManager->getPluginByName($technicalName);

        if (!$plugin) {
            $plugin = $this->downloadPlugin($technicalName);
        }

        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * @param string $technicalName
     * @return null
     */
    private function downloadPlugin($technicalName)
    {
        return null;
    }

    /**
     * @param string $technicalName
     * @throws \Exception
     */
    protected function deactivatePlugin($technicalName)
    {
        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $this->getContainer()->get('shopware_plugininstaller.plugin_manager');
        $plugin = $pluginManager->getPluginByName($technicalName);
        $pluginManager->deactivatePlugin($plugin);
    }

    /**
     * @param ContainerInterface $container
     * @throws \Exception
     */
    private function reset(ContainerInterface $container)
    {
        $password = md5('shopware');

        $sql = <<<"EOD"
            UPDATE s_user SET password = "$password", encoder = "md5", paymentID = 5, failedlogins = 0, lockeduntil = NULL;
            TRUNCATE s_order_basket;
            TRUNCATE s_order_basket_attributes;
            TRUNCATE s_order_notes;
            TRUNCATE s_order_comparisons;
EOD;
        $container->get('db')->exec($sql);
    }

    /**
     * @BeforeScenario @captchaInactive
     */
    public function deactivateCaptchas()
    {
        $this->changeConfigValue('captchaColor', '');
    }

    /**
     * @BeforeScenario @jsEmotion
     */
    public function emotionJs()
    {
        if (self::$suite->getSetting('template') === 'Emotion') {
            $this->getMink()->setDefaultSessionName('selenium2');
        }
    }

    /**
     * @BeforeScenario @jsResponsive
     */
    public function responsiveJs()
    {
        if (self::$suite->getSetting('template') === 'Responsive') {
            $this->getMink()->setDefaultSessionName('selenium2');
        }
    }

    /**
     * @param string $configName
     * @param mixed $value
     * @throws \Exception
     */
    public function changeConfigValue($configName, $value)
    {
        //get the template id
        $sql = sprintf(
            'SELECT `id` FROM `s_core_config_elements` WHERE `name` = "%s"',
            $configName
        );

        $configId = $this->getContainer()->get('db')->fetchOne($sql);
        if (!$configId) {
            $message = sprintf('Configuration "%s" doesn\'t exist!', $configName);
            Helper::throwException($message);
        }

        $this->dirtyConfigElements[] = $configId;

        /** @var \Shopware\Components\ConfigWriter $configWriter */
        $configWriter = $this->getContainer()->get('config_writer');

        $configWriter->save($configName, $value, null, 1);
        $configWriter->save($configName, $value, null, 2);

        $config = $this->getContainer()->get('config');
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

        $this->getContainer()->get('db')->exec($sql);

        $this->clearCache();
    }

    /**
     * @BeforeScenario @configChange
     */
    public function clearCache(ScenarioScope $scope = null)
    {
        $cacheManager = $this->getContainer()->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
        $cacheManager->clearTemplateCache();
    }

    /**
     * Sets Kernel instance.
     *
     * @param Kernel $kernel HttpKernel instance
     */
    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns HttpKernel service container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    public function registerErrorHandler()
    {
        error_reporting(-1);

        $errorNameMap = array(
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_ALL               => 'E_ALL',
        );

        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($errorNameMap) {
            if ($errno === E_RECOVERABLE_ERROR) {
                return true;
            }
            $errorName = isset($errorNameMap[$errno]) ? $errorNameMap[$errno] : $errno;
            $message = sprintf("Error: %s, \nFile: %s\nLine: %s, Message:\n%s\n", $errorName, $errfile, $errline, $errstr);
            // do not trigger internal
            return true;
        });
    }
}
