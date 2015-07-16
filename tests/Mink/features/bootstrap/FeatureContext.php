<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Event\OutlineExampleEvent;
use Behat\MinkExtension\Context\MinkContext;
use Shopware\Behat\ShopwareExtension\Context\KernelAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Event\SuiteEvent;

class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var KernelInterface
     */
    private static $statickernel;

    /**
     * @var string
     */
    private static $template;

    /**
     * @var array
     */
    private $dirtyConfigElements;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['template'])) {
            throw new \RuntimeException("Template not set. Please start testsuite using the --profile argument.");
        }

        self::$template = $parameters['template'];
        $this->dirtyConfigElements = array();

        $this->useContext('shopware', new ShopwareContext($parameters));
        $this->useContext('account', new AccountContext($parameters));
        $this->useContext('checkout', new CheckoutContext($parameters));
        $this->useContext('listing', new ListingContext($parameters));
        $this->useContext('detail', new DetailContext($parameters));
        $this->useContext('note', new NoteContext($parameters));
        $this->useContext('form', new FormContext($parameters));
        $this->useContext('blog', new BlogContext($parameters));
        $this->useContext('sitemap', new SitemapContext($parameters));
        $this->useContext('special', new SpecialContext($parameters));
        $this->useContext('seo', new SeoContext($parameters));
        $this->useContext('basicSettings', new BasicSettingsContext($parameters));
    }

    /**
     * @BeforeSuite
     */
    public static function prepare(SuiteEvent $event)
    {
        $em = self::$statickernel->getContainer()->get('models');
        $em->generateAttributeModels();

        //refresh s_core_templates
        $last = error_reporting(0);
        self::$statickernel->getContainer()->get('theme_installer')->synchronize();
        error_reporting($last);

        //get the template id
        $sql = sprintf(
            'SELECT id FROM `s_core_templates` WHERE template = "%s"',
            self::$template
        );

        $templateId = self::$statickernel->getContainer()->get('db')->fetchOne($sql);
        if (!$templateId) {
            throw new \RuntimeException(
                sprintf("Unable to find template by name %s", self::$template)
            );
        }

        //set the template for shop "Deutsch" and activate SEPA payment method
        $sql = <<<"EOD"
            UPDATE `s_core_shops` SET `template_id`= $templateId WHERE `id` = 1;
            UPDATE `s_core_paymentmeans` SET `active`= 1;
EOD;
        self::$statickernel->getContainer()->get('db')->exec($sql);

        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = self::$statickernel->getContainer()->get('shopware.plugin_manager');

        // hack to prevent behat error handler kicking in.
        $oldErrorReporting = error_reporting(0);
        $pluginManager->refreshPluginList();
        error_reporting($oldErrorReporting);

        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    /** @BeforeScenario */
    public function before($event)
    {
        if ($event instanceof OutlineExampleEvent && $event->getIteration()) {
            return;
        }

        $password = md5('shopware');

        $sql = <<<"EOD"
            UPDATE s_user SET password = "$password", encoder = "md5", paymentID = 5, failedlogins = 0, lockeduntil = NULL;
            TRUNCATE s_order_basket;
            TRUNCATE s_order_basket_attributes;
            TRUNCATE s_order_notes;
            TRUNCATE s_order_comparisons;
EOD;
        $this->getContainer()->get('db')->exec($sql);
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
        if (self::$template === 'Emotion') {
            $this->getMink()->setDefaultSessionName('selenium2');
        }
    }

    /**
     * @BeforeScenario @jsResponsive
     */
    public function responsiveJs()
    {
        if (self::$template === 'Responsive') {
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

        $this->clearCache();
    }

    /**
     * @AfterScenario @captchaInactive,@configChange
     */
    public function clearConfigValues()
    {
        if(!$this->dirtyConfigElements) {
            return;
        }

        $dirtyElements = implode(',', $this->dirtyConfigElements);
        $this->dirtyConfigElements = array();

        $sql = sprintf('DELETE FROM `s_core_config_values` WHERE `element_id` IN (%s)', $dirtyElements);

        $this->getContainer()->get('db')->exec($sql);

        $this->clearCache();
    }

    /**
     * @BeforeScenario @configChange
     */
    public function clearCache(\Behat\Behat\Event\ScenarioEvent $event = null)
    {
        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->getContainer()->get('shopware.cache_manager');

        $templateCache = $cacheManager->getTemplateCacheInfo();
        if (!array_key_exists('message', $templateCache)) {
            $cacheManager->clearHttpCache();
            $cacheManager->clearTemplateCache();
        }

        $cacheManager->clearConfigCache();
        $cacheManager->clearSearchCache();
        $cacheManager->clearProxyCache();

        if($event) {
            sleep(5);
        }
    }

    /**
     * Sets Kernel instance.
     *
     * @param HttpKernelInterface $kernel HttpKernel instance
     */
    public function setKernel(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
        self::$statickernel = $kernel;
    }

    /**
     * @return \Shopware\Kernel
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
}
