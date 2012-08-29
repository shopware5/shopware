<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Shopware Twitter Plugin
 *
 * This Plugin is the result of the shopware twitter tutorial.
 */
class Shopware_Plugins_Frontend_Twitter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin. This method will be called if the plugin is to be installed.
     *
     * Creates and subscribe the events and hooks
     * Creates and save the payment row
     * Creates the payment table
     * Creates payment menu item
     *
     * @return bool
     */
    public function install()
    {
        // Create the backend config form
        $this->createForm();
        // Create the translation for the backend config form
        $this->createTranslations();
        // Subscribe to shopware events
        $this->subscribeEvents();
        
        return true;
    }

    /**
     * Removes the plugin from the shop. The plugin has to take care to clean de-install it self.
     * eg. remove added database tables.
     * Any Events or config forms will be removed by the shopware.
     * 
     * @return bool
     */
    public function uninstall()
    {
        // do your clean up here
        return true;
    }

    /**
     * When ever an updated has been performed this method will be called.
     * 
     * @param string $version
     * @return bool
     */
    public function update($version)
    {
        $this->createForm(); //Update form
        $this->createTranslations(); // Update translations

        return true;
    }

    /**
     * When ever the plugin is switched to enabled or the configuration has been changed this method will be called.
     * 
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * When ever the plugin is switched to disabled this method will be called.
     *
     * @return bool
     */
    public function disable()
    {
        return true;
    }

    /**
     * Helper Method for subscribing to events and hooks
     */
    protected function subscribeEvents()
    {
        // subscribe to Enlight_Controller_Action_PostDispatch_Frontend_Index event
        // which
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Index',
            'onPostDispatchIndex'
        );

        return true;
    }

    /**
     * Creates and stores the payment config form.
     * 
     */
    protected function createForm()
    {
        $form = $this->Form();
        
        $form->setElement('text', 'twitterUsername', array(
            'label' => 'Twitter Benutzername',
            'required' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('boolean', 'twitterLive', array(
            'label' => 'Tweets laden',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('boolean', 'twitterShowScrollbar', array(
            'label' => 'Scrollbar verwenden',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('select', 'twitterFetchBehavior',
            array(
                'label'=>'Tweet lade Verhalten',
                'value'=>1,
                'store' => array(
                    array(1, 'Tweets im Interval laden'),
                    array(2, 'Alle Tweets laden'),
                ),
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $form->setElement('text', 'twitterTweetInterval', array(
            'label' => 'Tweet Interval', 
            'value' => 3600,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('boolean', 'twitterLoopResults', array(
            'label' => 'Loop results',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('text', 'twitterMaxTweets', array(
            'label' => 'Anzahl der Tweets', 
            'value' => 4,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('color', 'twitterBackgroundColor', array(
            'label' => 'Hintergrundfarbe des Twitter Plugins',
            'value' => '#333333',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('color', 'twitterForegroundColor', array(
            'label' => 'Textfarbe des Twitter Plugins',
            'value' => '#ffffff',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('color', 'twitterTweetBackgroundColor', array(
            'label' => 'Hintergrundfarbe der Tweets',
            'value' => '#000000',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('color', 'twitterTweetForegroundColor', array(
            'label' => 'Textfarbe des Twitter Plugins',
            'value' => '#ffffff',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        
        $form->setElement('color', 'twitterLinkColor', array(
            'label' => 'Textfarbe der Links',
            'value' => '#4aed05',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    public function createTranslations()
    {
        $form = $this->Form();
        $translations = array(
            'en_GB' => array(
                // Form element name => translation
                'twitterUsername' => 'Twitter Username',
                'twitterShowScrollbar' => 'Use scrollbar',
                'twitterLive' => 'Pull Tweets',
                'twitterFetchBehavior' =>'Tweet loading behavior',
                'twitterTweetInterval' => 'Tweet Interval',
                'twitterLoopResults' => 'Loop results',
                'twitterMaxTweets' => 'Number of tweets',
                'twitterBackgroundColor' => 'Shell background color',
                'twitterForegroundColor' => 'Shell font color',
                'twitterTweetBackgroundColor' => 'Tweet background color',
                'twitterTweetForegroundColor' => 'Tweet font color',
                'twitterLinkColor' => 'Link font color'
            )
        );
        
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        foreach($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            foreach($snippets as $element => $snippet) {
                if($localeModel === null){
                    continue;
                }
                $elementModel = $form->getElement($element);
                if($elementModel === null) {
                    continue;
                }
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);
                $elementModel->addTranslation($translationModel);
            }
        }
    }

    /**
     * Helper Method to register different template directories.,
     * 
     * @return Enlight_Template_Manager
     */
    protected function registerTemplateDir()
    {
        return $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'templates/');
    }

    /**
     * Callback method which is called every time the event 'Enlight_Controller_Action_PostDispatch_Frontend_Index'
     * is fired. This event has been subscribed in $this->subscribeEvents();
     * 
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchIndex(Enlight_Event_EventArgs $args)
    {
        /** @var $action Enlight_Controller_Action */
        $action   = $args->getSubject();
        $request  = $action->Request();
        $response = $action->Response();
        $view     = $action->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
        ) {
            return;
        }

        $view->TwitterConfig = Shopware()->Plugins()->Frontend()->Twitter()->Config(); 
        $this->registerTemplateDir();
        $view->extendsTemplate('frontend/plugins/twitter/index.tpl');
    }

    /**
     * Helper Method the get the name of the Plugin
     * 
     * @return array
     */
    public function getLabel()
    {
        return 'Twitter';
    }

    /**
     * Returns the version of plugin as string.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * This method will be called from the plugin manager to receive basic plugin information
     * 
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => 'Shopware Twitter Plugin'
        );
    }
}
