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
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Emotion\Library\Field;

/**
 * Shopware Plugin Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Components\Plugin\Bootstrap
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
    public final function Info()
    {
        return $this->info;
    }

    /**
     * @return string
     */
    public final function Path()
    {
        return $this->info->path;
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
    public final function Plugin()
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
    public final function Forms()
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
    public final function Form()
    {
        if (!$this->hasForm()) {
            $this->form = $this->initForm();
        }

        return $this->form;
    }

    /**
     * @return bool
     */
    public final function hasForm()
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
    public final function Menu()
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
        if (isset($options['parent'])
            && $options['parent'] instanceof \Shopware\Models\Menu\Menu
        ) {
            $parentId = $options['parent']->getId();
        } else {
            $parentId = null;
            unset($options['parent']);
        }
        $item = $this->Menu()->findOneBy(
            array(
                'label'    => $options['label'],
                'parentId' => $parentId
            )
        );
        if ($item === null) {
            $item = new Shopware\Models\Menu\Menu();
        }
        $item->fromArray($options);
        $plugin = $this->Plugin();
        $plugin->getMenuItems()->add($item);
        $item->setPlugin($plugin);

        return $item;
    }

    /**
     * @return Shopware\Components\Model\ModelRepository
     */
    public final function Payments()
    {
        return Shopware()->Models()->getRepository(
            'Shopware\Models\Payment\Payment'
        );
    }

    /**
     * Create a new payment instance
     *
     * @param   array $options
     * @param   null  $description
     * @param   null  $action
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
     * Subscribes a plugin event.
     *
     * {@inheritDoc}
     *
     * @param string|Enlight_Event_Handler $event
     * @param string                       $listener
     * @param integer                      $position
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
     * Returns capabilities
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'update'  => true,
            'enable'  => true,
            'dummy'   => false
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
        return $this->info->source;
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
            'label'   => $this->getLabel()
        );
    }

    /**
     * @deprecated Will be executed automatically.
     */
    public function deleteForm()
    {

    }

    /**
     * @deprecated Will be executed automatically.
     */
    public function deleteConfig()
    {

    }

    /**
     * @deprecated Use the event subscriber direct
     *
     * @param      $event
     * @param      $listener
     * @param null $position
     *
     * @return Enlight_Event_Handler_Plugin
     */
    public function createEvent($event, $listener, $position = null)
    {
        $handler = new Enlight_Event_Handler_Plugin(
            $event, $this->collection, $this, $listener, $position
        );

        return $handler;
    }

    /**
     * @deprecated Use the event subscriber (Event: class::method::type)
     *
     * @param        $class
     *
     * @deprecated
     *
     * @param        $method
     * @param        $listener
     * @param   null $type
     * @param   null $position
     *
     * @return  Enlight_Event_Handler_Plugin
     */
    public function createHook($class, $method, $listener, $type = null, $position = null)
    {
        $handler = new Enlight_Event_Handler_Plugin(
            $class . '::' . $method . '::' . $type,
            $this->collection, $this, $listener, $position
        );

        return $handler;
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
     * Subscribe cron method
     *
     * @deprecated Use the createCronJob method
     */
    public function subscribeCron(
        $name,
        $action,
        $interval = 86400,
        $active = true,
        $next = null,
        $start = null,
        $end = null
    ) {
        $this->createCronJob($name, $action, $interval, $active);
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
     * @deprectated 4.1.3 Use assertMinimumVersion instead
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
     * @param int   $cacheTime
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
     * module. This function is required for the subsequent function like
     * the createEmotionComponentCheckboxField which expects a already
     * created emotion component.
     *
     * @param $name
     * @param $cls
     * @param $template
     * @param string $xType
     * @param null $convertFunction
     * @param string $description
     *
     * @return Component
     */
    public function createEmotionComponent(
        $name,
        $cls,
        $template,
        $xType = '',
        $convertFunction = null,
        $description = ''
    ) {
        $component = new Component();
        $component->setName($name);
        $component->setXType($xType);
        $component->setCls($cls);
        $component->setTemplate($template);
        $component->setConvertFunction($convertFunction);
        $component->setDescription($description);
        $component->setPluginId($this->getId());

        $this->Application()->Models()->persist($component);
        $this->Application()->Models()->flush($component);

        return $component;
    }


    /**
     * Creates a checkbox field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentCheckboxField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'checkboxfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a combobox field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $store
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $displayField
     * @param string $valueField
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentComboboxField(
        Component $component,
        $name,
        $fieldLabel,
        $store = '',
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $displayField = '',
        $valueField = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'combobox',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'store' => $store,
            'valueField' => $valueField,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'displayField' => $displayField,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a date field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentDateField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'datefield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a display field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentDisplayField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'displayfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText'=> $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a hidden field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param string $valueType
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentHiddenField(
        Component $component,
        $name,
        $valueType = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'hiddenfield',
            'name' => $name,
            'valueType' => $valueType,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a html editor field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentEditorField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'htmleditor',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a number field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentNumberField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'numberfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a radio field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentRadioField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'radiofield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a text field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentTextField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'textfield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a text area field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentTextareaField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'textareafield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Create a time field for the passed emotion component widget.
     *
     * @param Component $component
     * @param $name
     * @param $fieldLabel
     * @param string $supportText
     * @param string $helpTitle
     * @param string $helpText
     * @param string $defaultValue
     * @param bool $allowBlank
     *
     * @return Field
     */
    protected function createEmotionComponentTimeField(
        Component $component,
        $name,
        $fieldLabel,
        $supportText = '',
        $helpTitle = '',
        $helpText = '',
        $defaultValue = '',
        $allowBlank = false
    ) {
        return $this->createEmotionField($component, array(
            'componentId' => $component->getId(),
            'xType' => 'timefield',
            'name' => $name,
            'fieldLabel' => $fieldLabel,
            'supportText' => $supportText,
            'helpTitle' => $helpTitle,
            'helpText' => $helpText,
            'defaultValue' => $defaultValue,
            'allowBlank' => $allowBlank
        ));
    }


    /**
     * Internal helper function which creates a single emotion component field.
     *
     * @param Component $component
     * @param array $data
     *
     * @return Field
     * @throws Exception
     */
    private function createEmotionField(Component $component, array $data)
    {
        if (!($component instanceof Component)) {
            throw new \Exception("The passed component object has to be an instance of \\Shopware\\Models\\Emotion\\Library\\Component");
        }

        $defaults = array(
            'fieldLabel' => '',
            'valueType' => '',
            'store' => '',
            'supportText' => '',
            'helpTitle' => '',
            'helpText' => '',
            'defaultValue' => '',
            'displayField' => '',
            'valueField' => '',
            'allowBlank' => ''
        );

        $data = array_merge($defaults, $data);

        $field = new Field();
        $field->fromArray($data);
        $this->Application()->Models()->persist($field);
        $this->Application()->Models()->flush($field);

        return $field;
    }

}
