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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Config\ElementTranslation;
use Shopware\Models\Config\FormTranslation;
use Shopware\Models\Widget\Widget;

/**
 * Shopware Plugin Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Components\Plugin\Bootstrap
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
abstract class Shopware_Components_Plugin_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * @var Enlight_Config
     */
    protected $info;

    /**
     * @var Shopware\Models\Plugin\Plugin
     */
    protected $plugin;

    /**
     * @var Shopware\Models\Config\Form
     */
    protected $form;

    /**
     * @var Shopware_Components_Plugin_Namespace
     */
    protected $collection;

    /**
     * Constructor method
     *
     * @param                     $name
     * @param Enlight_Config|null $info
     */
    public function __construct($name, $info = null)
    {
        $this->info = new Enlight_Config($this->getInfo(), true);
        if ($info instanceof Enlight_Config) {
            $info->setAllowModifications(true);
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
     * Helper function to get access on the http cache plugin.
     * Notice if the Http Cache plugin isn't installed, this function
     * returns null.
     *
     * @return Shopware_Plugins_Core_HttpCache_Bootstrap|null
     */
    protected function HttpCache()
    {
        $httpCache = Shopware()->Plugins()->Core()->HttpCache();

        if (!$httpCache instanceof Shopware_Components_Plugin_Bootstrap) {
            return null;
        }

        /**@var $plugin \Shopware\Models\Plugin\Plugin */
        $plugin = Shopware()->Models()->find('\Shopware\Models\Plugin\Plugin', $httpCache->getId());

        if (!$plugin->getActive() || !$plugin->getInstalled()) {
            return null;
        }

        return $httpCache;
    }


    /**
     * Returnswhether or not $updatePluginInfo contains a newer version than $currentPluginInfo
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
        if ($currentVersion == "1" && $updateVersion == "1.0.0") {
            return false;
        }

        return version_compare($updateVersion, $currentVersion, '>');
    }

    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        return !empty($this->info->capabilities['install']);
    }

    /**
     * Uninstall plugin method
     *
     * @return bool
     */
    public function uninstall()
    {
        return !empty($this->info->capabilities['install']);
    }

    /**
     * Secure uninstall plugin method
     *
     * @return bool
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
     * @return bool
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
     * @return bool
     */
    public function enable()
    {
        return !empty($this->info->capabilities['enable']);
    }

    /**
     * Disable plugin method
     *
     * @return bool
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
     * @return Shopware\Models\Plugin\Plugin
     */
    final public function Plugin()
    {
        if ($this->plugin === null) {
            $repo = Shopware()->Models()->getRepository(
                'Shopware\Models\Plugin\Plugin'
            );
            $this->plugin = $repo->findOneBy(
                array(
                    'id' => $this->getId()
                )
            );
        }

        return $this->plugin;
    }

    /**
     * @return Shopware\Components\Model\ModelRepository
     */
    final public function Forms()
    {
        return Shopware()->Models()->getRepository(
            'Shopware\Models\Config\Form'
        );
    }

    /**
     * Returns plugin form
     *
     * @return Shopware\Models\Config\Form
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
            $this->form = $formRepository->findOneBy(
                array(
                    'name' => $this->getName()
                )
            );
        }
        if ($this->form === null && $this->getId() !== null) {
            $formRepository = $this->Forms();
            $this->form = $formRepository->findOneBy(
                array(
                    'pluginId' => $this->getId()
                )
            );
        }

        return $this->form !== null;
    }

    /**
     * @return Shopware\Models\Config\Form
     */
    private function initForm()
    {
        $info = $this->Info();
        $formRepository = $this->Forms();
        $form = new \Shopware\Models\Config\Form;
        $form->setPluginId($this->getId());
        $form->setName($info->name);
        $form->setLabel($info->label);
        $form->setDescription($info->description);
        $parent = $formRepository->findOneBy(
            array(
                'name' => strpos($this->name, 'Payment') !== false ? 'Payment' : 'Other'
            )
        );
        $form->setParent($parent);
        $this->Application()->Models()->persist($form);

        return $form;
    }

    /**
     * Returns shopware menu
     *
     * @return Shopware\Models\Menu\Repository
     */
    final public function Menu()
    {
        return Shopware()->Models()->getRepository(
            'Shopware\Models\Menu\Menu'
        );
    }

    /**
     * Create a new menu item instance
     *
     * @param array $options
     *
     * @return Shopware\Models\Menu\Menu|null
     */
    public function createMenuItem(array $options)
    {
        if (!isset($options['label'])) {
            return null;
        }

        $item = new Shopware\Models\Menu\Menu();
        $item->fromArray($options);
        $plugin = $this->Plugin();
        $plugin->getMenuItems()->add($item);
        $item->setPlugin($plugin);

        return $item;
    }

    /**
     * @return Shopware\Components\Model\ModelRepository
     */
    final public function Payments()
    {
        return Shopware()->Models()->getRepository(
            'Shopware\Models\Payment\Payment'
        );
    }

    /**
     * Create a new payment instance
     *
     * @param   array $options
     * @param   null $description
     * @param   null $action
     *
     * @return  \Shopware\Models\Payment\Payment
     */
    public function createPayment($options, $description = null, $action = null)
    {
        if (is_string($options)) {
            $options = array('name' => $options);
        }
        $payment = $this->Payments()->findOneBy(
            array(
                'name' => $options['name']
            )
        );
        if ($payment === null) {
            $payment = new \Shopware\Models\Payment\Payment();
            $payment->setName($options['name']);
            $this->Application()->Models()->persist($payment);
        }
        $payment->fromArray($options);
        if ($description !== null) {
            $payment->setDescription($description);
        }
        if ($action !== null) {
            $payment->setAction($action);
        }
        $plugin = $this->Plugin();
        $plugin->getPayments()->add($payment);
        $payment->setPlugin($plugin);
        Shopware()->Models()->flush($payment);

        return $payment;
    }

    /**
     * Create a new template
     *
     * @param   array|string $options
     *
     * @return  \Shopware\Models\Shop\Template
     */
    public function createTemplate($options)
    {
        if (is_string($options)) {
            $options = array('template' => $options);
        }
        $template = $this->Payments()->findOneBy(
            array(
                'template' => $options['template']
            )
        );
        if ($template === null) {
            $template = new \Shopware\Models\Shop\Template();
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
     */
    public function createCronJob($name, $action, $interval = 86400, $active = 1)
    {
        $sql = '
            INSERT INTO s_crontab (`name`, `action`, `next`, `start`, `interval`, `active`, `end`, `pluginID`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ';
        Shopware()->Db()->query(
            $sql,
            array(
                $name,
                $action,
                new Zend_Date(),
                null,
                $interval,
                $active,
                new Zend_Date(),
                $this->getId()
            )
        );
    }

    /**
     * Creates a new widget
     *
     * @param $name
     */
    public function createWidget($name)
    {
        $widget = new Widget();
        $widget->setName($name);
        $widget->setPlugin($this->Plugin());

        $this->Plugin()->getWidgets()->add($widget);
    }

    /**
     * Subscribes a plugin event.
     *
     * {@inheritDoc}
     *
     * @param string|Enlight_Event_Handler $event
     * @param string $listener
     * @param integer $position
     *
     * @return Enlight_Plugin_Bootstrap_Config
     */
    public function subscribeEvent($event, $listener = null, $position = null)
    {
        if ($listener === null) {
            $this->Collection()->Subscriber()->registerListener($event);
        } else {
            parent::subscribeEvent($event, $listener, $position);
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
     * @param string $module - Possible values: Frontend, Backend, Widgets, Api
     * @param string $name - The name of the controller
     * @param string $listener - Name of the event listener function which will be called
     * @return $this
     * @throws Exception
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
     * @param Enlight_Event_EventArgs $arguments
     * @throws Exception
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
            throw new Enlight_Exception('Controller "' . $controller . '" can\'t load failure');
        }

        //register plugin model directory
        if (file_exists($this->Path() . 'Models')) {
            $this->registerCustomModels();
        }

        //register plugin views directory
        if (file_exists($this->Path() . 'Views')) {
            $this->Application()->Template()->addTemplateDir(
                $this->Path() . 'Views/'
            );
        }

        //register plugin snippet directory
        if (file_exists($this->Path() . 'Snippets')) {
            $this->Application()->Snippets()->addConfigDir(
                $this->Path() . 'Snippets/'
            );
        }

        //register plugin component directory
        if (file_exists($this->Path() . 'Components')) {
            $this->Application()->Loader()->registerNamespace(
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
        return array(
            'install' => true,
            'update' => true,
            'enable' => true,
            'secureUninstall' => false
        );
    }

    /**
     * Returns plugin id
     *
     * @final
     * @return int
     */
    public function getId()
    {
        return $this->Collection()->getPluginId($this->name);
    }

    /**
     * Returns plugin version
     *
     * @return string
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
     * @return string
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
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel()
        );
    }

    /**
     * Subscribe hook method
     *
     * @param Enlight_Hook_HookHandler $handler
     *
     * @return Shopware_Components_Plugin_Bootstrap
     */
    public function subscribeHook($handler)
    {
        return $this->subscribeEvent($handler);
    }

    /**
     * Check if a list of given plugins is currently available
     * and active
     *
     * @param array $plugins
     *
     * @return bool
     */
    protected function assertRequiredPluginsPresent(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';
            $test = Shopware()->Db()->fetchOne($sql, array($plugin));
            if (empty($test)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a given version is greater or equal to
     * the currently installed shopware version.
     *
     * Attention: If your target shopware version may
     * include a version less than 4.1.3 you have to
     * use assertVersionGreaterThen().
     *
     * @since 4.1.3 introduced assertMinimumVersion($requiredVersion)
     *
     * @param  string $requiredVersion string Format: 3.5.4 or 3.5.4.21111
     *
     * @return bool
     */
    protected function assertMinimumVersion($requiredVersion)
    {
        $version = $this->Application()->Config()->version;

        if ($version === '___VERSION___') {
            return true;
        }

        return version_compare($version, $requiredVersion, '>=');
    }

    /**
     * Alias for assertMinimumVersion().
     *
     * Check if a given version is greater or equal to
     * the currently installed shopware version.
     *
     * @deprecated 4.1.3 Use assertMinimumVersion instead
     *
     * @param  $requiredVersion string Format: 3.5.4 or 3.5.4.21111
     *
     * @return bool
     */
    protected function assertVersionGreaterThen($requiredVersion)
    {
        return $this->assertMinimumVersion($requiredVersion);
    }

    /**
     * Register the custom model dir
     */
    protected function registerCustomModels()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware\CustomModels',
            $this->Path() . 'Models/'
        );
        $this->Application()->ModelAnnotations()->addPaths(
            array(
                $this->Path() . 'Models/'
            )
        );
    }


    /**
     * Helper function to enable the http cache for a single shopware controller.
     *
     * @param int $cacheTime
     * @param array $cacheIds
     */
    public function enableControllerCache($cacheTime = 3600, $cacheIds = array())
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
     *     @type string $name               Required; Logical name of the component
     *     @type string $template           Required; Template class name which will be loaded in the frontend
     *     @type string $xType              Required; Ext JS xtype for the backend module component
     *     @type string $cls                Optional; $cls Css class which used in the frontend emotion
     *     @type string $convertFunction    Optional; Data convert function which allows to convert the saved backend data
     *     @type string $description        Optional; Description field for the component, which displayed in the backend module.
     * }
     *
     * @return Component
     */
    public function createEmotionComponent(array $options)
    {
        $config = array_merge(array(
            'convertFunction' => null,
            'description' => '',
            'cls' => '',
            'xtype' => 'emotion-components-base'
        ), $options);

        $component = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Library\Component')->findOneBy(
            array(
                'name' => $options['name'],
                'pluginId' => $this->getId()
            )
        );
        if (!$component) {
            $component = new Component();
        }

        $component->fromArray($config);

        $component->setPluginId($this->getId());
        $component->setPlugin($this->Plugin());

        //saves the component automatically if the plugin is saved
        $this->Plugin()->getEmotionComponents()->add($component);

        //register post dispatch of backend and widgets emotion controller to load the template extensions of the plugin
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion', 'extendsEmotionTemplates');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion', 'extendsEmotionTemplates');
        return $component;
    }


    /**
     * Event listener of the post dispatch event of the backend and widgets emotion controller
     * to load the plugin emotion template extensions.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function extendsEmotionTemplates(Enlight_Event_EventArgs $args)
    {
        /**@var $view Enlight_View_Default*/
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
        $regex = new \RegexIterator($directoryIterator,  '/^.+\.js$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $path = 'backend/' . $file[0];
            $view->extendsBlock('backend/Emotion/app', '{include file="'. $path .'"}', 'append');
        }
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

    /**
     * Removes the snippets present in the plugin's ini files
     * from the database
     *
     * @param bool $removeDirty if true, the snippets changed by the
     * shop owner will also be removed
     */
    public function removeSnippets($removeDirty = false)
    {
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path().'Snippets/', $removeDirty);
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path().'snippets/', $removeDirty);
        $this->get('shopware.snippet_database_handler')->removeFromDatabase($this->Path().'Resources/snippet/', $removeDirty);
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
            $locale = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale')
                ->findOneBy(array('locale' => $localeCode));
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
     * @param string $route
     * @param int $time
     * @param array $invalidateTags
     * @return bool
     */
    protected function addHttpCacheRoute($route, $time, $invalidateTags = [])
    {
        /**@var $writer \Shopware\Components\ConfigWriter*/
        $writer = $this->get('config_writer');

        $value = $writer->get('cacheControllers', 'HttpCache');
        if (empty($value)) {
            return false;
        }

        $value = $this->explodeHttpCacheRoutes($value);
        $value = $this->addOrUpdateHttpCacheRoute($route, $time, $value);
        $value = $this->implodeHttpCacheRoutes($value);
        $writer->save('cacheControllers', $value, 'HttpCache');

        if (empty($invalidateTags)) {
            return true;
        }

        $value = $writer->get('noCacheControllers', 'HttpCache');
        $value = $this->explodeHttpCacheRoutes($value);
        foreach ($invalidateTags as $tag) {
            $value = $this->addNoCacheTag($route, strtolower($tag), $value);
        }
        $value = $this->implodeHttpCacheRoutes($value);
        $writer->save('noCacheControllers', $value, 'HttpCache');

        return true;
    }

    /**
     * @param string $route
     * @return bool
     */
    protected function removeHttpCacheRoute($route)
    {
        /**@var $writer \Shopware\Components\ConfigWriter*/
        $writer = $this->get('config_writer');

        //remove cached controller
        $value = $writer->get('cacheControllers', 'HttpCache');
        if (empty($value)) {
            return false;
        }

        $value = $this->explodeHttpCacheRoutes($value);
        $new = array_filter($value, function ($row) use ($route) {
            return ($row['route'] != $route);
        });

        $new = $this->implodeHttpCacheRoutes($new);
        $writer->save('cacheControllers', $new, 'HttpCache');

        //remove no cache tags
        $value = $writer->get('noCacheControllers', 'HttpCache');
        $value = $this->explodeHttpCacheRoutes($value);
        $new = array_filter($value, function ($row) use ($route) {
            return ($row['route'] != $route);
        });

        $new = $this->implodeHttpCacheRoutes($new);
        $writer->save('noCacheControllers', $new, 'HttpCache');

        return true;
    }

    /**
     * @param  string $value
     * @return array
     */
    private function explodeHttpCacheRoutes($value)
    {
        $value = explode("\n", $value);

        $value = array_map(function ($row) {
            $row = explode(' ', $row);
            if (empty($row[0])) {
                return null;
            }
            return ['route' => $row[0], 'time' => $row[1]];
        }, $value);

        $value = array_filter($value);
        return $value;
    }

    /**
     * @param string $route
     * @param int $time
     * @param array $value
     * @return array
     */
    private function addOrUpdateHttpCacheRoute($route, $time, $value)
    {
        $exist = false;
        foreach ($value as &$row) {
            if ($row['route'] != $route) {
                continue;
            }

            $exist = true;
            if ($row['time'] == (int) $time) {
                continue;
            }

            $row['time'] = $time;
        }

        if ($exist == false) {
            $value[] = ['route' => $route, 'time' => $time];
        }

        return $value;
    }

    /**
     * @param string $route
     * @param string $tag
     * @param array $value
     * @return array
     */
    private function addNoCacheTag($route, $tag, $value)
    {
        $exist = false;
        foreach ($value as $row) {
            if ($row['route'] != $route) {
                continue;
            }

            if ($row['time'] != $tag) {
                continue;
            }

            $exist = true;
        }

        if ($exist == false) {
            $value[] = ['route' => $route, 'time' => $tag];
        }

        return $value;
    }

    /**
     * @param array $value
     * @return string
     */
    private function implodeHttpCacheRoutes($value)
    {
        $value = array_map(function ($row) {
            return implode(' ', $row);
        }, $value);

        return implode("\n", $value);
    }
}
