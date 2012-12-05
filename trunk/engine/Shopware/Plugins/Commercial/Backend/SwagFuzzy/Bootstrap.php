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
 * @package    Shopware_Plugins_Backend_SwagFuzzy
 * @subpackage Result
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Plugin that extends the default search and add fuzzy logic to find
 * misspelled terms.
 *
 * More features:
 * - Add table configuration tab to search settings
 * - Add property filter in search frontend
 * - Add similar search requests & similar search terms to frontend
 */
class Shopware_Plugins_Backend_SwagFuzzy_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin
     * @return bool
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.3')) {
            throw new Exception("This plugin requires Shopware 4.0.3 or a later version");
        }

        // Check license
        $this->checkLicense(true);

        // Select proper search adapter
        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Search_SelectAdapter',
            'onSelectAdapter'
        );

        // Modify result object
        $this->subscribeEvent(
            'Shopware_Controllers_Frontend_Search_ModifySearchResult',
            'onModifySearchResult'
        );

        // Modify template
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Frontend_Search',
            'onLoadFrontendSearch'
        );

        // Add config fields to backend configuration
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onLoadBackendConfig'
        );

        // Add config parameters
        $form = $this->Form();

        $form->setElement('checkbox', 'showSimilarSearchRequests', array(
            'label' => 'Display similar search requests',
            'required' => false,
            'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('checkbox', 'showMatchingKeywords', array(
            'label' => 'Display matching / similar keywords',
            'required' => false,
            'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        // Clear template-cache
        return array(
            'success' => true,
            'invalidateCache' => array(
                'config',
                'backend',
                'search'
            )
        );
    }

    /**
     * Check license
     * @param bool $throwException
     * @return bool
     * @throws Exception
     */
    final public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagFuzzy';
        if (!isset($r)) {
            $s = base64_decode('0It2RaCd+n+kdfbDW3cJXeoszuI=');
            $c = base64_decode('CXo/Eek+yvkg95a3Dt0GkqoH1JQ=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c . $u . $r, true);
        }
        if (!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    /**
     * Override backend settings / add search table configuration tab
     * @param Enlight_Event_EventArgs $args
     */
    public function onLoadBackendConfig(Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        if (!$view->hasTemplate()) {
            return;
        }
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        $view->extendsTemplate("Backend/Config/override.tpl");
    }

    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return "Intelligente Suche";
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion()
    {
        return "1.0.0";
    }

    /**
     * Event-listener that switches to our fuzzy search & result adapters
     * @param Enlight_Event_EventArgs $args
     * @return Shopware_Components_Search_Adapter_Fuzzy
     */
    public function onSelectAdapter(Enlight_Event_EventArgs $args)
    {
        // Check license
        $this->checkLicense(true);

        require_once(dirname(__FILE__) . "/Components/Search/Adapter/Fuzzy.php");
        require_once(dirname(__FILE__) . "/Components/Search/Result/Fuzzy.php");

        return new Shopware_Components_Search_Adapter_Fuzzy(
            Shopware()->Db(), Shopware()->Cache(),
            new Shopware_Components_Search_Result_Fuzzy(),
            Shopware()->Config()
        );
    }

    /**
     * Modify search result array - add property filters & similar search requests to result array
     * @param Enlight_Event_EventArgs $args
     */
    public function onModifySearchResult(Enlight_Event_EventArgs $args)
    {
        $controller = $args->getSubject();

        /** @var $resultObj Shopware_Components_Search_Result_Fuzzy */
        $resultObj = $args->getSearch()->getAdapter()->getResult();
        if (!$resultObj) {
            return;
        }
        $results = $controller->View()->sSearchResults;

        $results['sPropertyGroups'] = $resultObj->getAffectedProperties();
        $results['sPropertyOptions'] = $resultObj->getAffectedPropertyOptions();
        $results['sPropertyValues'] = $resultObj->getAffectedPropertyValues();

        if($this->Config()->showMatchingKeywords) {
            $results['searchSimilarTerms'] = $resultObj->getResultMatchingKeywords();
        }
        if($this->Config()->showSimilarSearchRequests) {
            $results['searchSimilarRequests'] = $resultObj->getResultMatchingSearchRequests();
        }

        $controller->View()->sSearchResults = $results;
    }

    /**
     * Override frontend search template - add block to display similar
     * search requests
     * @param Enlight_Event_EventArgs $args
     */
    public function onLoadFrontendSearch(Enlight_Event_EventArgs $args)
    {
        if ($args->getRequest()->getActionName() == "defaultSearch") {
            $this->Application()->Template()->addTemplateDir(
                $this->Path() . 'Views/'
            );
            $args->getSubject()->View()->extendsTemplate("search/extends.tpl");
        }
    }
}