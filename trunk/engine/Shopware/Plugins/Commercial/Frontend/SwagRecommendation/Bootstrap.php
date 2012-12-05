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
 * @subpackage Recommendation
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_SwagRecommendation_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{

		$this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Index','onPostDispatchIndex');
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Listing','onPostDispatchListing');
	 	$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_Sliders','onGetControllerPath');
	 	$this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_RecommendationAdmin','onGetControllerPathBackend');

	 	$form = $this->Form();

		$form->setElement('text', 'max_banner', array('label'=>'Limit Banner','value'=>'12', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'max_supplier', array('label'=>'Limit Hersteller','value'=>'255', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'supplier_page', array('label'=>'Hersteller pro Slider','value'=>'4', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));

	    $form->setElement('text', 'max_simlar_articles', array('label'=>'"Ähnliche Interessen" Limit','value'=>'20', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'page_similar_articles', array('label'=>'"Ähnliche Interessen" pro Seite','value'=>'3', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'max_new_articles', array('label'=>'Neue Artikel Limit','value'=>'20', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));
		$form->setElement('text', 'page_new_articles', array('label'=>'Neue Artikel pro Seite','value'=>'3', 'scope'=>\Shopware\Models\Config\Element::SCOPE_SHOP));

	 	$parent = $this->Menu()->findOneBy('label', 'Marketing');

		$this->createMenuItem(array(
			'label' => 'Sliders',
			'onclick' => 'openAction(\'RecommendationAdmin\');',
			'class' => 'sprite-block',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
		));

        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_recommendations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `categoryID` int(11) NOT NULL,
          `banner_active` int(1) NOT NULL,
          `new_active` int(1) NOT NULL,
          `bought_active` int(1) NOT NULL,
          `supplier_active` int(1) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `categoryID_2` (`categoryID`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
        ");

	 	return true;
	}

    /*
     * Get backend controller
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
	public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Controllers/Backend/RecommendationAdmin.php';
    }

    /**
     * Get frontend controller
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Controllers/Frontend/Sliders.php';
    }

    /**
     * Handle listing sliders
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchListing(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$category = $request->sCategory;
		$config = Shopware()->Plugins()->Frontend()->SwagRecommendation()->Config();

		$getElements = Shopware()->Db()->fetchRow("
		SELECT * FROM s_plugin_recommendations WHERE categoryID = ?
		",array($category));

		if (!empty($getElements["id"])){
			if ($getElements["banner_active"]){
				$banner = Shopware()->Modules()->Marketing()->sBanner($category,$config->max_banner);
				$view->banners = $banner;
				$view->banner_active = 1;
			}
			if ($getElements["supplier_active"]){
				$suppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($category,$config->max_supplier);
				$suppliers = array_chunk($suppliers,$config->supplier_page);
				$view->suppliers = $suppliers;
				$view->supplier_active = 1;
			}
			if ($getElements["bought_active"]){
				$view->bought_active = 1;
			}
			if ($getElements["new_active"]){
				$view->new_active = 1;
			}
            $view->extendsTemplate(dirname(__FILE__).'/Views/recommendation/blocks_listing.tpl');
		}

		if(!$request->isDispatched()||$response->isException()) {
			return;
		}

	}

    /**
     * Handle index / homepage sliders
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchIndex(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$category = Shopware()->Shop()->get('parentID');
		$config = Shopware()->Plugins()->Frontend()->SwagRecommendation()->Config();
		// Check which elements should be loaded
		$getElements = Shopware()->Db()->fetchRow("
		SELECT * FROM s_plugin_recommendations WHERE categoryID = ?
		",array($category));

		if (!empty($getElements["id"])){
			if ($getElements["banner_active"]){
				$banner = Shopware()->Modules()->Marketing()->sBanner($category,$config->max_banner);
				$view->banners = $banner;
				$view->banner_active = 1;
			}
			if ($getElements["supplier_active"]){
				$suppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($category,$config->max_supplier);
				$suppliers = array_chunk($suppliers,$config->supplier_page);
				$view->suppliers = $suppliers;
				$view->supplier_active = 1;
			}
			if ($getElements["bought_active"]){
				$view->bought_active = 1;
			}
			if ($getElements["new_active"]){
				$view->new_active = 1;
			}
			$view->extendsTemplate(dirname(__FILE__).'/Views/recommendation/blocks_index.tpl');
		}

		if(!$request->isDispatched()||$response->isException()) {
			return;
		}
	}
}
