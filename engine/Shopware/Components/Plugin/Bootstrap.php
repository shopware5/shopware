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

use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Config\ElementTranslation;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\FormTranslation;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Menu\Menu;
use Shopware\Models\Menu\Repository as MenuRepository;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Template;
use Shopware\Models\Widget\Widget;

/**
 * Shopware Plugin Bootstrap
 */
abstract class Shopware_Components_Plugin_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * @var Enlight_Config|null
     */
    protected $info;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var Shopware_Components_Plugin_Namespace
     */
    protected $collection;

    /**
     * Constructor method
     *
     * @param string              $name
     * @param Enlight_Config|null $info
     */
    public function __construct($name, $info = null)
    {
        $this->info = new Enlight_Config($this->getInfo(), true);
        if ($info instanceof Enlight_Config) {
            $info->setAllowModifications();
            $updateVersion = null;
            $updateSource = null;

            if ($this->hasInfoNewerVersion($this->info, $info)) {
                $updateVersion = $this->info->get('version');
                $updateSource = $this->info->get('source');
            }

            $this->info->merge($info);
            if ($updateVersion !== null) {
                $this->info->set('updateVersion', $updateVersion);
                $this->info->set('updateSource', $updateSource);
            }
        }
        $this->info->set('capabilities', $this->getCapabilities());
        parent::__construct($name);
    }

    /**
     * Returns whether or not $updatePluginInfo contains a newer version than $currentPluginInfo
     *
     * @param \Enlight_Config $currentPluginInfo
     * @param \Enlight_Config $updatePluginInfo
     *
     * @return bool
     */
    public function hasInfoNewerVersion(Enlight_Config $updatePluginInfo, Enlight_Config $currentPluginInfo)
    {
        $currentVersion = $currentPluginInfo->get('version');
        $updateVersion = $updatePluginInfo->get('version');

        if (empty($updateVersion)) {
            return false;
        }

        // Exception for Pre-Installed Plugins
        if ($currentVersion == '1' && $updateVersion == '1.0.0') {
            return false;
        }

        return version_compare($updateVersion, $currentVersion, '>');
    }

    /**
     * Install plugin method
     *
     * @return bool|array
     */
    public function install()
    {
        return !empty($this->info->capabilities['install']);
    }

    /**
     * Uninstall plugin method
     *
     * @return bool|array
     */
    public function uninstall()
    {
        return !empty($this->info->capabilities['install']);
    }

    /**
     * Secure uninstall plugin method
     *
     * @return bool|array
     */
    public function secureUninstall()
    {
        if (empty($this->info->capabilities['secureUninstall']) || empty($this->info->capabilities['install'])) {
            return false;
        }

        return true;
    }

    /**
     * Update plugin method
     *
     * @param string $version
     *
     * @return bool|array
     */
    public function update($version)
    {
        if (empty($this->info->capabilities['update']) || empty($this->info->capabilities['install'])) {
            return false;
        }

        return $this->install();
    }

    /**
     * Enable plugin method
     *
     * @return bool|array
     */
    public function enable()
    {
        return !empty($this->info->capabilities['enable']);
    }

    /**
     * Disable plugin method
     *
     * @return bool|array
     */
    public function disable()
    {
        return !empty($this->info->capabilities['enable']);
    }

    /**
     * @return Enlight_Config
     */
    final public function Info()
    {
        return $this->info;
    }

    /**
     * @return string
     */
    final public function Path()
    {
        $return = '';

        if ($this->info instanceof Enlight_Config) {
            $return = $this->info->path;
        } else {
            $reflection = new \ReflectionClass($this);

            if ($fileName = $reflection->getFileName()) {
                $return = dirname($fileName) . DIRECTORY_SEPARATOR;
            }
        }

        return $return;
    }

    /**
     * Returns plugin config
     *
     * @return \Enlight_Config
     */
    public function Config()
    {
        return $this->Collection()->getConfig($this->name);
    }

    /**
     * @return Plugin
     */
    final public function Plugin()
    {
        if ($this->plugin === null) {
            /** @var Plugin $plugin */
            $plugin = Shopware()->Models()->getRepository(Plugin::class)
                ->findOneBy(['id' => $this->getId()]);
            $this->plugin = $plugin;
        }

        return $this->plugin;
    }

    /**
     * @return \Shopware\Models\Form\Repository
     */
    final public function Forms()
    {
        /** @var \Shopware\Models\Form\Repository $return */
        $return = Shopware()->Models()->getRepository(Form::class);

        return $return;
    }

    /**
     * Returns plugin form
     *
     * @return Form
     */
    final public function Form()
    {
        if (!$this->hasForm()) {
            $this->form = $this->initForm();
        }

        return $this->form;
    }

    /**
     * @return bool
     */
    final public function hasForm()
    {
        if ($this->form === null && $this->getName() !== null) {
            $formRepository = $this->Forms();
            /** @var Form $form */
            $form = $formRepository->findOneBy(['name' => $this->getName()]);
            $this->form = $form;
        }
        if ($this->form === null && $this->getId() !== null) {
            $formRepository = $this->Forms();
            /** @var Form $form */
            $form = $formRepository->findOneBy(['pluginId' => $this->getId()]);
            $this->form = $form;
        }

        return $this->form !== null;
    }

    /**
     * Returns shopware menu
     *
     * @return MenuRepository
     */
    final public function Menu()
    {
        return Shopware()->Models()->getRepository(Menu::class);
    }

    /**
     * Create a new menu item instance
     *
     * @return Menu|null
     */
    public function createMenuItem(array $options)
    {
        if (!isset($options['label'])) {
            return null;
        }

        $item = new Menu();
        $item->fromArray($options);
        $plugin = $this->Plugin();
        $plugin->getMenuItems()->add($item);
        $item->setPlugin($plugin);

        return $item;
    }

    /**
     * @return ModelRepository
     */
    final public function Payments()
    {
        return Shopware()->Models()->getRepository(Payment::class);
    }

    /**
     * Create a new payment instance
     *
     * @param string|array $options
     * @param string|null  $description
     * @param string|null  $action
     *
     * @return Payment
     */
    public function createPayment($options, $description = null, $action = null)
    {
        /** @var \Shopware\Components\Plugin\PaymentInstaller $installer */
        $installer = $this->get('shopware.plugin_payment_installer');

        if (is_string($options)) {
            $options = ['name' => $options];
        }
        if ($description !== null) {
            $options['description'] = $description;
        }
        if ($action !== null) {
            $options['action'] = $action;
        }

        return $installer->createOrUpdate($this->getName(), $options);
    }

    /**
     * Create a new template
     *
     * @param array|string $options
     *
     * @return Template
     */
    public function createTemplate($options)
    {
        if (is_string($options)) {
            $options = ['template' => $options];
        }
        /** @var Template|null $template */
        $template = $this->Payments()->findOneBy(['template' => $options['template']]);
        if ($template === null) {
            $template = new Template();
            if (!isset($options['name'])) {
                $options['name'] = ucfirst($options['template']);
            }
        }
        $template->fromArray($options);
        $plugin = $this->Plugin();
        $plugin->getTemplates()->add($template);
        $template->setPlugin($plugin);

        return $template;
    }

    /**
     * Create cron job method
     *
     * @param string $name
     * @param string $action
     * @param int    $interval
     * @param int    $active
     * @param bool   $disableOnError
     */
    public function createCronJob($name, $action, $interval = 86400, $active = 1, $disableOnError = true)
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => $name,
                'action' => $action,
                'next' => new \DateTime(),
                'start' => null,
                '`interval`' => $interval,
                'active' => $active,
                'disable_on_error' => $disableOnError ? 1 : 0,
                'end' => new \DateTime(),
                'pluginID' => $this->getId(),
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]
        );
    }

    /**
     * Creates a new widget
     *
     * @param string $name
     */
    public function createWidget($name)
    {
        $widget = new Widget();
        $widget->setName($name);
        $widget->setPlugin($this->Plugin());

        $this->Plugin()->getWidgets()->add($widget);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeEvent($event, $listener = null, $position = null)
    {
        if ($listener === null) {
            /** @var Enlight_Event_Handler $handler */
            $handler = $event;
            $this->Collection()->Subscriber()->registerListener($handler);
        } else {
            /** @var string $eventName */
            $eventName = $event;
            parent::subscribeEvent($eventName, $listener, $position);
        }

        return $this;
    }

    /**
     * Helper function to register a plugin controller.
     *
     * If the default event listener is used for the registration of a plugin controller, the following requirements must be fulfilled:
     *  1. The plugin directory must contain a 'Controllers' subdirectory.
     *  2. The 'Controllers' directory must contain a subdirectory which corresponds to the module (Frontend, Backend, Widgets or API)
     *  3. The controller must be filed in this module directory.
     *  4. The controller file must have the same name as the controller class.
     *
     * If all the requirements are fulfilled, the controller is registered automatically.
     * Additionally, the following plugin namespaces/directories are registered, if available:
     *  1. The 'Views' plugin directory is added as a template directory.
     *  2. The 'Snippets' plugin directory is added as a config directory.
     *  3. The 'Components' plugin directory is added as a component namespace.
     *
     * @example
     * <code>
     *   public function install() {
     *       $this->registerController('Frontend', 'Example1');
     *       return true;
     *   }
     * </code>
     *
     * @param string $module   - Possible values: Frontend, Backend, Widgets, Api
     * @param string $name     - The name of the controller
     * @param string $listener - Name of the event listener function which will be called
     *
     * @throws Exception
     *
     * @return $this
     */
    public function registerController($module, $name, $listener = 'getDefaultControllerPath')
    {
        if (empty($module)) {
            throw new Exception('Register controller requires a module name');
        }
        if (empty($name)) {
            throw new Exception('Register controller requires a controller name');
        }
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_' . $module . '_' . $name,
            $listener
        );

        return $this;
    }

    /**
     * Standard event listener function for plugin controllers.
     * If the default event listener is used for the registration of a plugin controller, the following requirements must be fulfilled:
     *  1. The plugin directory must contain a 'Controller' subdirectory.
     *  2. The "Controllers" directory must contain a subdirectory which corresponds to the module (Frontend, Backend, Widgets or API)
     *  3. The controller must be filed in the module directory.
     *  4. The controller file must have the same name as the controller class.
     *
     * If all the requirements are fulfilled, the controller is registered automatically.
     * Additionally, the following plugin namespaces/directories are registered, if available:
     *  1. The 'Views' plugin directory is added as a template directory.
     *  2. The 'Snippets' plugin directory is added as a config directory.
     *  3. The 'Components' plugin directory is added as a component namespace.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getDefaultControllerPath(Enlight_Event_EventArgs $arguments)
    {
        $eventName = $arguments->getName();
        $eventName = str_replace('Enlight_Controller_Dispatcher_ControllerPath_', '', $eventName);

        $parts = explode('_', $eventName);

        $module = $parts[0];
        $controller = $parts[1];

        $path = $this->Path() . 'Controllers/' . ucfirst($module) . '/' . ucfirst($controller) . '.php';

        if (!file_exists($path)) {
            throw new Enlight_Exception(sprintf('Controller "%s" can\'t load failure', $controller));
        }

        //register plugin model directory
        if (file_exists($this->Path() . 'Models')) {
            $this->registerCustomModels();
        }

        //register plugin views directory
        if (file_exists($this->Path() . 'Views')) {
            Shopware()->Template()->addTemplateDir(
                $this->Path() . 'Views/'
            );
        }

        //register plugin snippet directory
        if (file_exists($this->Path() . 'Snippets')) {
            Shopware()->Snippets()->addConfigDir(
                $this->Path() . 'Snippets/'
            );
        }

        //register plugin component directory
        if (file_exists($this->Path() . 'Components')) {
            Shopware()->Loader()->registerNamespace(
                'Shopware_Components',
                $this->Path() . 'Components/'
            );
        }

        return $path;
    }

    /**
     * Returns capabilities
     */
    public function getCapabilities()
    {
        return [
            'install' => true,
            'update' => true,
            'enable' => true,
            'secureUninstall' => false,
        ];
    }

    /**
     * Returns plugin id
     *
     * @final
     *
     * @return int
     */
    public function getId()
    {
        return $this->Collection()->getPluginId($this->name);
    }

    /**
     * Returns plugin version
     *
     * @return string|null
     */
    public function getVersion()
    {
        return null;
    }

    /**
     * Returns plugin name
     *
     * @return string
     */
    public function getLabel()
    {
        return isset($this->info->label) ? $this->info->label : $this->getName();
    }

    /**
     * Returns plugin name
     *
     * @final
     *
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * Returns plugin source
     *
     * @final
     *
     * @return string|null
     */
    final public function getSource()
    {
        return $this->info ? $this->info->source : null;
    }

    /**
     * Returns plugin info
     *
     * @return array
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * Subscribe hook method
     *
     * @param Enlight_Hook_HookHandler $handler
     *
     * @return \Shopware_Components_Plugin_Bootstrap
     */
    public function subscribeHook($handler)
    {
        /** @var \Shopware_Components_Plugin_Bootstrap $return */
        $return = $this->subscribeEvent($handler);

        return $return;
    }

    /**
     * Helper function to enable the http cache for a single shopware controller.
     *
     * @param int   $cacheTime
     * @param array $cacheIds
     */
    public function enableControllerCache($cacheTime = 3600, $cacheIds = [])
    {
        $httpCache = $this->HttpCache();
        if ($httpCache) {
            $httpCache->enableControllerCache($cacheTime, $cacheIds);
        }
    }

    /**
     * Helper function to disable the http cache for a single shopware controller
     */
    public function disableControllerCache()
    {
        $httpCache = $this->HttpCache();
        if ($httpCache) {
            $httpCache->disableControllerCache();
        }
    }

    /**
     * Creates a new component which can be used in the backend emotion
     * module.
     *
     * @param array $options {
     *
     *     @var string $name               Required; Logical name of the component
     *     @var string $template           Required; Template class name which will be loaded in the frontend
     *     @var string $xType              Required; Ext JS xtype for the backend module component
     *     @var string $cls                Optional; $cls Css class which used in the frontend emotion
     *     @var string $convertFunction    Optional; Data convert function which allows to convert the saved backend data
     *     @var string $description        optional; Description field for the component, which displayed in the backend module.
     * }
     *
     * @return Component
     */
    public function createEmotionComponent(array $options)
    {
        /** @var \Shopware\Components\Emotion\ComponentInstaller $installer */
        $installer = $this->get('shopware.emotion_component_installer');

        $component = $installer->createOrUpdate($this->getName(), $options['name'], $options);

        // Register post dispatch of backend and widgets emotion controller to load the template extensions of the plugin
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion', 'extendsEmotionTemplates');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion', 'extendsEmotionTemplates');

        return $component;
    }

    /**
     * Event listener of the post dispatch event of the backend and widgets emotion controller
     * to load the plugin emotion template extensions.
     */
    public function extendsEmotionTemplates(Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        if (file_exists($this->Path() . '/Views/emotion_components/')) {
            $view->addTemplateDir($this->Path() . '/Views/emotion_components/');
        }

        if ($args->getSubject()->Request()->getModuleName() !== 'backend') {
            return;
        }

        $backendPath = $this->getExistingBackendEmotionPath();
        if ($backendPath === false) {
            return;
        }

        $directoryIterator = new \DirectoryIterator($backendPath);
        $regex = new \RegexIterator($directoryIterator, '/^.+\.js$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $path = 'backend/' . $file[0];
            $view->extendsBlock(
                'backend/Emotion/app',
                PHP_EOL . '{include file="' . $path . '"}',
                'append'
            );
        }
    }

    /**
     * Removes the snippets present in the plugin's ini files
     * from the database
     *
     * @param bool $removeDirty if true, the snippets changed by the
     *                          shop owner will also be removed
     */
    public function removeSnippets($removeDirty = false)
    {
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path() . 'Snippets/', $removeDirty);
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path() . 'snippets/', $removeDirty);
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path() . 'Resources/snippet/', $removeDirty);
    }

    /**
     * Adds translations to the form and its elements. The accepted array format
     * accepts a special 'plugin_form' key for the form translation. All other
     * keys will be matched to element names.
     *
     * Example $translations array:
     * <code>
     * array(
     'en_GB' => array(
     'plugin_form' => array(
     'label' => 'Recently viewed items'
     ),
     'show' => array(
     'label' => 'Display recently viewed items'
     ),
     'thumb' => array(
     'label' => 'Thumbnail size',
     'description' => 'Index of the thumbnail size of the associated album to use. Starts at 0'
     )
     )
     * )
     * </code>
     *
     * @param array $translations
     */
    public function addFormTranslations($translations)
    {
        $form = $this->Form();

        foreach ($translations as $localeCode => $translationSet) {
            /** @var Locale|null $locale */
            $locale = Shopware()->Models()->getRepository(Locale::class)->findOneBy(['locale' => $localeCode]);
            if (empty($locale)) {
                continue;
            }

            // First process the form translations
            if (array_key_exists('plugin_form', $translationSet)) {
                $isUpdate = false;
                $translationArray = $translationSet['plugin_form'];
                foreach ($form->getTranslations() as $existingTranslation) {
                    // Check if translation for this locale already exists
                    if ($existingTranslation->getLocale()->getLocale() != $localeCode) {
                        continue;
                    }
                    if (array_key_exists('label', $translationArray)) {
                        $existingTranslation->setLabel($translationArray['label']);
                    }
                    if (array_key_exists('description', $translationArray)) {
                        $existingTranslation->setDescription($translationArray['description']);
                    }
                    $isUpdate = true;
                    break;
                }
                if (!$isUpdate) {
                    $formTranslation = new FormTranslation();
                    if (array_key_exists('label', $translationArray)) {
                        $formTranslation->setLabel($translationArray['label']);
                    }
                    if (array_key_exists('description', $translationArray)) {
                        $formTranslation->setDescription($translationArray['description']);
                    }
                    $formTranslation->setLocale($locale);
                    $form->addTranslation($formTranslation);
                }
                unset($translationSet['plugin_form']);
            }

            // Then the element translations
            foreach ($translationSet as $targetName => $translationArray) {
                $isUpdate = false;
                $element = $form->getElement($targetName);
                foreach ($element->getTranslations() as $existingTranslation) {
                    // Check if translation for this locale already exists
                    if ($existingTranslation->getLocale()->getLocale() != $localeCode) {
                        continue;
                    }
                    if (array_key_exists('label', $translationArray)) {
                        $existingTranslation->setLabel($translationArray['label']);
                    }
                    if (array_key_exists('description', $translationArray)) {
                        $existingTranslation->setDescription($translationArray['description']);
                    }
                    $isUpdate = true;
                    break;
                }
                if (!$isUpdate) {
                    $elementTranslation = new ElementTranslation();
                    if (array_key_exists('label', $translationArray)) {
                        $elementTranslation->setLabel($translationArray['label']);
                    }
                    if (array_key_exists('description', $translationArray)) {
                        $elementTranslation->setDescription($translationArray['description']);
                    }
                    $elementTranslation->setLocale($locale);
                    $element->addTranslation($elementTranslation);
                }
            }
        }
    }

    /**
     * Helper function to get access on the http cache plugin.
     * Notice if the Http Cache plugin isn't installed, this function
     * returns null.
     *
     * @return \Shopware_Plugins_Core_HttpCache_Bootstrap|null
     */
    protected function HttpCache()
    {
        /** @var \Shopware_Plugins_Core_HttpCache_Bootstrap $httpCache */
        $httpCache = Shopware()->Plugins()->Core()->HttpCache();

        if (!$httpCache instanceof self) {
            return null;
        }

        /** @var Plugin $plugin */
        $plugin = Shopware()->Models()->find(Plugin::class, $httpCache->getId());

        if (!$plugin->getActive() || !$plugin->getInstalled()) {
            return null;
        }

        return $httpCache;
    }

    /**
     * Check if a list of given plugins is currently available
     * and active
     *
     * @return bool
     */
    protected function assertRequiredPluginsPresent(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';
            $test = $this->get('dbal_connection')->fetchColumn($sql, [$plugin]);
            if (!$test) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a given version is greater or equal to
     * the currently installed shopware version.
     *
     * @since 4.1.3 introduced assertMinimumVersion($requiredVersion)
     *
     * @param string $requiredVersion string Format: 3.5.4 or 3.5.4.21111
     *
     * @return bool
     */
    protected function assertMinimumVersion($requiredVersion)
    {
        $version = Shopware()->Config()->version;

        if ($version === '___VERSION___') {
            return true;
        }

        return version_compare($version, $requiredVersion, '>=');
    }

    /**
     * Register the custom model dir
     */
    protected function registerCustomModels()
    {
        Shopware()->Loader()->registerNamespace(
            'Shopware\CustomModels',
            $this->Path() . 'Models/'
        );
    }

    /**
     * @param string $route
     * @param int    $time
     *
     * @return bool
     */
    protected function addHttpCacheRoute($route, $time, array $invalidateTags = [])
    {
        $cacheRouteInstaller = $this->get('shopware.http_cache.route_installer');

        return $cacheRouteInstaller->addHttpCacheRoute($route, $time, $invalidateTags);
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function removeHttpCacheRoute($route)
    {
        $cacheRouteInstaller = $this->get('shopware.http_cache.route_installer');

        return $cacheRouteInstaller->removeHttpCacheRoute($route);
    }

    /**
     * @return Form
     */
    private function initForm()
    {
        $info = $this->Info();
        $formRepository = $this->Forms();
        $form = new Form();
        $form->setPluginId($this->getId());
        $form->setName($info->name);
        $form->setLabel($info->label);
        $form->setDescription($info->description);

        /** @var Form $parent */
        $parent = $formRepository->findOneBy([
            'name' => strpos($this->name, 'Payment') !== false ? 'Payment' : 'Other',
        ]);
        $form->setParent($parent);
        Shopware()->Models()->persist($form);

        return $form;
    }

    /**
     * @return bool|string
     */
    private function getExistingBackendEmotionPath()
    {
        $backendPath = $this->Path() . '/Views/emotion_components/backend/';

        if (file_exists($backendPath)) {
            return $backendPath;
        }

        $backendPath = $this->Path() . '/Views/backend/emotion_components/';
        if (file_exists($backendPath)) {
            return $backendPath;
        }

        return false;
    }
}
