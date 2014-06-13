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
        self::$template = $parameters['template'];

        $this->useContext('shopware', new ShopwareContext($parameters));
        $this->useContext('account',  new AccountContext($parameters));
        $this->useContext('checkout', new CheckoutContext($parameters));
        $this->useContext('listing',  new ListingContext($parameters));
        $this->useContext('detail',   new DetailContext($parameters));
        $this->useContext('note',     new NoteContext($parameters));
        $this->useContext('form',     new FormContext($parameters));
        $this->useContext('blog',     new BlogContext($parameters));
        $this->useContext('sitemap',  new SitemapContext($parameters));
        $this->useContext('special',  new SpecialContext($parameters));
    }

    /**
     * @BeforeSuite
     */
    public static function prepare(SuiteEvent $event)
    {
        //refresh s_core_templates
        $last = error_reporting(0);
        self::$statickernel->getContainer()->get('theme_installer')->synchronize();
        error_reporting($last);

        //get the template id
        $sql = sprintf(
            'SELECT COALESCE(parent_id, id) as id, template FROM `s_core_templates` WHERE template = "%s"',
            self::$template
        );

        $templateId = self::$statickernel->getContainer()->get('db')->fetchOne($sql);

        //set the template for shop "Deutsch"
        $sql = sprintf(
            'UPDATE `s_core_shops` SET `template_id`= %d WHERE `id` = 1',
            $templateId
        );

        self::$statickernel->getContainer()->get('db')->exec($sql);
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
