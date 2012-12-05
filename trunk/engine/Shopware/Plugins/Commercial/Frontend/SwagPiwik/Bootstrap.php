<?php
/**
 * Piwik - Open source web analytics
 * @link http://www.shopware.de
 * @package Plugins
 * @subpackage Frontend
 * @copyright Copyright (c) 2012, shopware AG
 * @version 1.0.2
 * @author shopware AG (s.kloepper)
 */
class Shopware_Plugins_Frontend_SwagPiwik_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * standard install method - subscribe an event
	 * define destination of piwik-installation
	 * an define the site-id
	 * @return bool
	 */
	public function install()
	{		
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');
		
		$form = $this->Form();
		$form->setElement('text', 'p_url', array('label'=>'Pfad zu Piwik (mit Slash am Ende)','value'=>'www.meinshop.de/piwik/','scope'=> \Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'p_ID', array('label'=>'Seiten-ID Piwik','value'=>'1','scope'=> \Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->save();
		
	 	return true;
	}
	
	/**
	 * Returns the version of this plugin
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return '1.0.2';
	}
	
	/**
	 * Define template and variables
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		$view = $args->getSubject()->View();
		$config = Shopware()->Plugins()->Frontend()->SwagPiwik()->Config();
        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
             return;
         }
		$view->SwagPiwik = $config;
        $view->addTemplateDir($this->Path() . 'Views/');
		$args->getSubject()->View()->extendsTemplate('frontend/plugins/swag_piwik/index.tpl');
	}
	
	/**
	 * standard meta description
	 * @return unknown
	 */
	public function getInfo()
    {
    	return include(dirname(__FILE__).'/Meta.php');
    }
}