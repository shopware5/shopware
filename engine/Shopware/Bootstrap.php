<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use Shopware\Components\HttpCache\AppCache;
use Shopware\Components\HttpCache\HttpKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware\Bootstrap
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Bootstrap extends Enlight_Bootstrap
{
    /**
     * Returns the application instance.
     *
     * @return Enlight_Application|Shopware
     */
    public function Application()
    {
        return $this->application;
    }

    /**
     * Run application method
     *
     * @return mixed
     */
    public function run()
    {
        $app = $this->Application();
        $loader = $app->Loader();


        $hookOptions = $app->getOption('hook');
        $proxyDir = rtrim(realpath($hookOptions['proxyDir']), '\\/') . DIRECTORY_SEPARATOR;
        $classMap = $proxyDir . 'ClassMap_' . \Shopware::REVISION . '.php';

        $loader->readClassMap($classMap);

        // @TODO custom httpCache modification do not work anymore after Kernel integration
        if (($config = $app->getOption('httpCache')) !== null && !empty($config['enabled'])) {
            $loader->registerNamespace('Symfony', 'Symfony/');
            $kernel = new HttpKernel($app);
            $cache = new AppCache($kernel, $config);
            $cache->handle(Request::createFromGlobals())
                  ->send();
        } else {
            /** @var $front Enlight_Controller_Front */
            $front = $this->getResource('Front');
            $front->Response()->setHeader(
                'Content-Type',
                'text/html; charset=' . $front->getParam('charset')
            );
            $front->dispatch();
        }

        $loader->saveClassMap($classMap);
    }

    /**
     * Loads the Zend resource and initials the Enlight_Controller_Front class.
     * After the front resource is loaded, the controller path is added to the
     * front dispatcher. After the controller path is set to the dispatcher,
     * the plugin namespace of the front resource is set.
     *
     * @throws Exception
     * @return Enlight_Controller_Front
     */
    protected function initFront()
    {
        $front = parent::initFront();

        try {
            $this->loadResource('Cache');
            $this->loadResource('Db');
            $this->loadResource('Plugins');
        } catch (Exception $e) {
            if ($front->throwExceptions()) {
                throw $e;
            }
            $front->Response()->setException($e);
        }

        return $front;
    }

    protected function initTwig()
    {
        $loader = new Twig_Loader_Filesystem(
            $this->Application()->AppPath() . '/../../templates/_twig'
        );

        $twig = new Twig_Environment($loader, array(
            'debug' => true,
        ));
        $twig->addExtension(new Twig_Extension_Debug());

        $interface = new Enlight_View_Twig($twig);

        return $interface;
    }

    /**
     * Init template method
     *
     * @return Enlight_Template_Manager
     */
    protected function initTemplate()
    {
        $template = parent::initTemplate();

        $template->setEventManager(
            $this->Application()->Events()
        );

        $template->setTemplateDir(array(
            'custom'      => '_local',
            'local'       => '_local',
            'emotion'     => '_default',
            'default'     => '_default',
            'base'        => 'templates',
            'include_dir' => '.',
        ));

        $snippetManager = $this->getResource('Snippets');
        $resource = new Enlight_Components_Snippet_Resource($snippetManager);
        $template->registerResource('snippet', $resource);
        $template->setDefaultResourceType('snippet');

        return $template;
    }

    /**
     * Init database method
     *
     * @return Zend_Db_Adapter_Pdo_Abstract
     */
    protected function initDb()
    {
        return $this->getContainerService('db');
    }

    /**
     * Init session method
     *
     * @return Enlight_Components_Session_Namespace
     */
    protected function initSession()
    {
        $sessionOptions = $this->Application()->getOption('session', array());

        if (!empty($sessionOptions['unitTestEnabled'])) {
            Enlight_Components_Session::$_unitTestEnabled = true;
        }
        unset($sessionOptions['unitTestEnabled']);

        if (Enlight_Components_Session::isStarted()) {
            Enlight_Components_Session::writeClose();
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $this->getResource('Shop');

        $name = 'session-' . $shop->getId();
        $sessionOptions['name'] = $name;

        if (!isset($sessionOptions['save_handler']) || $sessionOptions['save_handler'] == 'db') {
            $config_save_handler = array(
                'db'             => $this->getResource('Db'),
                'name'           => 's_core_sessions',
                'primary'        => 'id',
                'modifiedColumn' => 'modified',
                'dataColumn'     => 'data',
                'lifetimeColumn' => 'expiry'
            );
            Enlight_Components_Session::setSaveHandler(
                new Enlight_Components_Session_SaveHandler_DbTable($config_save_handler)
            );
            unset($sessionOptions['save_handler']);
        }

        Enlight_Components_Session::start($sessionOptions);

        $this->registerResource('SessionID', Enlight_Components_Session::getId());

        $namespace = new Enlight_Components_Session_Namespace('Shopware');

        return $namespace;
    }

    /**
     * Init mail transport
     *
     * @return Zend_Mail_Transport_Abstract
     */
    protected function initMailTransport()
    {
        $options = Shopware()->getOption('mail') ? Shopware()->getOption('mail') : array();
        $config = $this->getResource('Config');

        if (!isset($options['type']) && !empty($config->MailerMailer) && $config->MailerMailer != 'mail') {
            $options['type'] = $config->MailerMailer;
        }
        if (empty($options['type'])) {
            $options['type'] = 'sendmail';
        }

        if ($options['type'] == 'smtp') {
            if (!isset($options['username']) && !empty($config->MailerUsername)) {
                if (!empty($config->MailerAuth)) {
                    $options['auth'] = $config->MailerAuth;
                } elseif (empty($options['auth'])) {
                    $options['auth'] = 'login';
                }
                $options['username'] = $config->MailerUsername;
                $options['password'] = $config->MailerPassword;
            }
            if (!isset($options['ssl']) && !empty($config->MailerSMTPSecure)) {
                $options['ssl'] = $config->MailerSMTPSecure;
            }
            if (!isset($options['port']) && !empty($config->MailerPort)) {
                $options['port'] = $config->MailerPort;
            }
            if (!isset($options['name']) && !empty($config->MailerHostname)) {
                $options['name'] = $config->MailerHostname;
            }
            if (!isset($options['host']) && !empty($config->MailerHost)) {
                $options['host'] = $config->MailerHost;
            }
        }

        if (!Shopware()->Loader()->loadClass($options['type'])) {
            $transportName = ucfirst(strtolower($options['type']));
            $transportName = 'Zend_Mail_Transport_' . $transportName;
        } else {
            $transportName = $options['type'];
        }
        unset($options['type'], $options['charset']);

        if ($transportName == 'Zend_Mail_Transport_Smtp') {
            $transport = Enlight_Class::Instance($transportName, array($options['host'], $options));
        } elseif (!empty($options)) {
            $transport = Enlight_Class::Instance($transportName, array($options));
        } else {
            $transport = Enlight_Class::Instance($transportName);
        }
        Enlight_Components_Mail::setDefaultTransport($transport);

        if (!isset($options['from']) && !empty($config->Mail)) {
            $options['from'] = array('email' => $config->Mail, 'name' => $config->Shopname);
        }

        if (!empty($options['from']['email'])) {
            Enlight_Components_Mail::setDefaultFrom(
                $options['from']['email'],
                !empty($options['from']['name']) ? $options['from']['name'] : null
            );
        }

        if (!empty($options['replyTo']['email'])) {
            Enlight_Components_Mail::setDefaultReplyTo(
                $options['replyTo']['email'],
                !empty($options['replyTo']['name']) ? $options['replyTo']['name'] : null
            );
        }

        return $transport;
    }

    /**
     * Init mail method
     *
     * @return Enlight_Components_Mail
     */
    protected function initMail()
    {
        if (!$this->loadResource('Config') || !$this->loadResource('MailTransport')) {
            return null;
        }

        $options = Shopware()->getOption('mail');
        $config = $this->getResource('Config');

        if (isset($options['charset'])) {
            $defaultCharSet = $options['charset'];
        } elseif (!empty($config->CharSet)) {
            $defaultCharSet = $config->CharSet;
        } else {
            $defaultCharSet = null;
        }

        $mail = new Enlight_Components_Mail($defaultCharSet);

        return $mail;
    }

    /**
     * Init config method
     *
     * @return Shopware_Components_Config
     */
    protected function initConfig()
    {
        return $this->getContainerService('config');
    }

    /**
     * Init snippets method
     *
     * @return Enlight_Components_Snippet_Manager|null
     */
    protected function initSnippets()
    {
        //@TODO logic has to be done in service definition
        if (!$this->issetResource('Db')) {
            return null;
        }
        return $this->getContainerService('snippets');
    }

    /**
     * Init router method
     *
     * @return Enlight_Controller_Router
     */
    protected function initRouter()
    {
        /** @var $front Enlight_Controller_Front */
        $front = $this->getResource('Front');

        return $front->Router();
    }

    /**
     * Init subscriber method
     *
     * @return Shopware_Components_Subscriber
     */
    protected function initSubscriber()
    {
        if (!$this->issetResource('Db')) {
            return null;
        }

        return $this->getContainerService('subscriber');
    }

    /**
     * Init plugins method
     *
     * @return Enlight_Plugin_PluginManager
     */
    protected function initPlugins()
    {
        $this->loadResource('Table');

        $config = Shopware()->getOption('plugins', array());
        if (!isset($config['cache'])) {
            $config['cache'] = $this->getResource('Cache');
        }
        if (!isset($config['namespaces'])) {
            $config['namespaces'] = array('Core', 'Frontend', 'Backend');
        }

        $plugins = $this->Application()->Plugins();
        $events = $this->Application()->Events();
        foreach ($config['namespaces'] as $namespace) {
            $namespace = new Shopware_Components_Plugin_Namespace($namespace);
            $plugins->registerNamespace($namespace);
            $events->registerSubscriber($namespace->Subscriber());
        }

        $loader = $this->Application()->Loader();
        foreach (array('Local', 'Community', 'Default', 'Commercial') as $dir) {
            $loader->registerNamespace('Shopware_Plugins', $this->Application()->AppPath('Plugins_' . $dir));
        }

        return $plugins;
    }

    /**
     * Init locale method
     *
     * @return Zend_Locale
     */
    protected function initLocale()
    {
        $locale = 'de_DE';
        if ($this->hasResource('Shop')) {
            $locale = $this->getResource('Shop')->getLocale()->getLocale();
        }

        return new Zend_Locale($locale);
    }

    /**
     * Init currency method
     *
     * @return Zend_Currency
     */
    protected function initCurrency()
    {
        $currency = 'EUR';
        if ($this->hasResource('Shop')) {
            $currency = $this->getResource('Shop')->getCurrency()->getCurrency();
        }

        return new Zend_Currency($currency, $this->getResource('Locale'));
    }

    /**
     * Init date method
     *
     * @return Zend_Date
     */
    protected function initDate()
    {
        return new Zend_Date($this->getResource('Locale'));
    }

    /**
     * Init cache method
     *
     * @return Zend_Cache_Core
     */
    protected function initCache()
    {
        return $this->getContainerService('cache');
    }

    /**
     * Init table method
     *
     * @return bool
     */
    protected function initTable()
    {
        Zend_Db_Table_Abstract::setDefaultAdapter($this->getResource('Db'));
        Zend_Db_Table_Abstract::setDefaultMetadataCache($this->getResource('Cache'));

        return true;
    }

    /**
     * Init doctrine method
     *
     * @return bool
     */
    public function initDoctrine()
    {
        $this->Application()->Loader()
            ->registerNamespace('Doctrine', 'Doctrine/')
            ->registerNamespace('Symfony', 'Symfony/');

        return true;
    }

    /**
     * Init doctrine method
     *
     * @return \Doctrine\ORM\Configuration
     */
    public function initModelConfig()
    {
        $this->loadResource('Doctrine');

        $config = new Shopware\Components\Model\Configuration(
            $this->Application()->getOption('Model')
        );

        if ($config->getMetadataCacheImpl() === null) {
            $cacheResource = $this->Application()->Cache();
            $config->setCacheResource($cacheResource);
        }

        $hookManager = $this->Application()->Hooks();
        $config->setHookManager($hookManager);

        return $config;
    }

    /**
     * Init doctrine method
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    public function initModelAnnotations()
    {
        $this->loadResource('Models');

        return $this->getResource('ModelAnnotations');
    }

    /**
     * Init doctrine method
     *
     * @return Shopware\Components\Model\ModelManager
     */
    public function initModels()
    {
        /** @var $config \Shopware\Components\Model\Configuration */
        $config = $this->getResource('ModelConfig');

        // register standard doctrine annotations
        Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        // register symfony validation annotions
        Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
            'Symfony\Component\Validator\Constraint'
        );

        $cachedAnnotationReader = $config->getAnnotationsReader();

        $annotationDriver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
            $cachedAnnotationReader,
            array(
                $this->Application()->AppPath('Models'),
                $config->getAttributeDir(),
            )
        );

        $this->Application()->Loader()->registerNamespace(
            'Shopware\Models\Attribute',
            $config->getAttributeDir()
        );

        // create a driver chain for metadata reading
        $driverChain = new Doctrine\ORM\Mapping\Driver\DriverChain();

        // register annotation driver for our application
        $driverChain->addDriver($annotationDriver, 'Shopware\\Models\\');
        $driverChain->addDriver($annotationDriver, 'Shopware\\CustomModels\\');

        $this->registerResource('ModelAnnotations', $annotationDriver);

        $config->setMetadataDriverImpl($driverChain);

        // Create event Manager
        $eventManager = new \Doctrine\Common\EventManager();

        // Create new shopware event subscriber to handle the entity lifecycle events.
        $lifeCycleSubscriber = new \Shopware\Components\Model\EventSubscriber(
            $this->Application()->Events()
        );
        $eventManager->addEventSubscriber($lifeCycleSubscriber);

        $categorySubscriber = new \Shopware\Components\Model\CategorySubscriber();
        $this->registerResource('CategorySubscriber', $categorySubscriber);
        $eventManager->addEventSubscriber($categorySubscriber);

        $eventManager->addEventSubscriber(new \Shopware\Models\Order\OrderHistorySubscriber());

        $categoryDenormalization = new \Shopware\Components\Model\CategoryDenormalization($this->Application()->Db()->getConnection());
        $this->registerResource('CategoryDenormalization', $categoryDenormalization);

        // now create the entity manager and use the connection
        // settings we defined in our application.ini
        $conn = \Doctrine\DBAL\DriverManager::getConnection(
            array('pdo' => $this->Application()->Db()->getConnection()),
            $config,
            $eventManager
        );

        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        $entityManager = Shopware\Components\Model\ModelManager::create($conn, $config, $eventManager);

        return $entityManager;
    }

    /**
     * @return \Shopware_Components_TemplateMail
     */
    public function initTemplateMail()
    {
        $this->loadResource('MailTransport');
        $stringCompiler = new Shopware_Components_StringCompiler(
            $this->getResource('Template')
        );
        $mailer = new Shopware_Components_TemplateMail();
        if ($this->issetResource('Shop')) {
            $mailer->setShop($this->getResource('Shop'));
        }
        $mailer->setModelManager($this->getResource('Models'));
        $mailer->setStringCompiler($stringCompiler);

        return $mailer;
    }

    private function getContainerService($name)
    {
        return $this->Application()->Container()->get($name);
    }
}
