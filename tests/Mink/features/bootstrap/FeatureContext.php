<?php
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

        //set the template for shop "Deutsch"
        $sql = sprintf(
            'UPDATE `s_core_shops` SET `template_id`= %d WHERE `id` = 1',
            $templateId
        );

        self::$statickernel->getContainer()->get('db')->exec($sql);

        //activate SEPA payment method
        $sql = 'UPDATE `s_core_paymentmeans` SET `active`= 1 WHERE `id` = 6';
        self::$statickernel->getContainer()->get('db')->exec($sql);


        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = self::$statickernel->getContainer()->get('shopware_plugininstaller.plugin_Manager');

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
        if ($event instanceof Behat\Behat\Event\OutlineExampleEvent && $event->getIteration()) {
            return;
        }

        //set "shopware" as password for all users and make sure they can login, additionally set "Vorkasse" as payment method
        $sql = sprintf(
            'UPDATE s_user SET password = "%s", encoder = "md5", paymentID = 5, failedlogins = 0, lockeduntil = NULL',
            md5('shopware')
        );

        $this->getContainer()->get('db')->exec($sql);

        //Remove all articles from basket
        $sql = 'TRUNCATE s_order_basket_attributes';
        $this->getContainer()->get('db')->exec($sql);

        $sql = 'DELETE FROM s_order_basket';
        $this->getContainer()->get('db')->exec($sql);

        $sql = 'ALTER TABLE s_order_basket AUTO_INCREMENT = 1';
        $this->getContainer()->get('db')->exec($sql);

        //Remove all articles from notes and comparisons
        $sql = 'TRUNCATE s_order_notes';
        $this->getContainer()->get('db')->exec($sql);

        $sql = 'TRUNCATE s_order_comparisons';
        $this->getContainer()->get('db')->exec($sql);
    }

    /**
     * @BeforeScenario @captchaInactive
     */
    public function deactivateCaptchas()
    {
        //uses a small bug in shopware, which deactivate all captchas when the font color is empty
        $sql = 'INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES
            (843, 1, \'s:0:"";\'),
            (843, 2, \'s:0:"";\')'
        ;
        $this->getContainer()->get('db')->exec($sql);
        $this->clearCache();
    }

    /**
     * @AfterScenario @captchaInactive
     */
    public function reactivateCaptchas()
    {
        $sql = 'DELETE FROM `s_core_config_values` WHERE `element_id` = 843';
        $this->getContainer()->get('db')->exec($sql);
        $this->clearCache();
    }

    /**
     *
     */
    private function clearCache()
    {
        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->getContainer()->get('shopware.cache_manager');

        $cacheManager->clearHttpCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearConfigCache();
        $cacheManager->clearSearchCache();
        $cacheManager->clearProxyCache();
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
     * @return Shopware\Kernel
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
