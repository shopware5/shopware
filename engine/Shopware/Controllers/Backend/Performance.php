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
 * @package    Shopware_Controllers
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Performance Controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_Performance extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Stores a list of all needed config data
     * @var array
     */
    protected $configData = array();

	protected function initAcl()
	{
	}


    public function init()
    {
        $this->configData = $this->prepareConfigData();

        parent::init();
    }

    /**
     * Some methods for testing purpose
     */
    public function getTopSellerCountAction() { $this->View()->assign(array('success' => true, 'total' => 100000)); }
    public function initTopSellerAction() { sleep(1); $this->View()->assign(array('success' => true, 'total' => 100000)); }

    /**
     * This action creates/updates the configuration
     */
    public function saveConfigAction()
    {
        
        $data = $this->Request()->getParams();

        $data = $this->prepareDataForSaving($data);
        $this->saveConfigData($data);

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));
    }

    /**
     * Iterates the given data array and persists all config variables
     * @param $data
     */
    public function saveConfigData($data)
    {
        foreach ($data as $values) {
            foreach ($values as $configKey => $value) {
                $this->saveConfig($configKey, $value);
            }
        }
    }

    /**
     * General helper method which triggers the prepare...ConfigForSaving methods
     *
     * @param $data
     * @return array
     */
    public function prepareDataForSaving($data)
    {
        $output = array();
        $output['httpCache'] = $this->prepareHttpCacheConfigForSaving($data['httpCache'][0]);
        $output['topSeller'] = $this->prepareTopSellerConfigForSaving($data['topSeller'][0]);

        return $output;
    }

    /**
     * Prepare the TopSeller config array for storage
     * @param $data
     * @return mixed
     */
    public function prepareTopSellerConfigForSaving($data)
    {
        unset($data['id']);

        return $data;
    }

    /**
     * Prepare the http config array so that it can easily be saved
     * @param $data
     * @return mixed
     */
    public function prepareHttpCacheConfigForSaving($data)
    {
        $lines = array();
        foreach ($data['cacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['cacheControllers'] = implode("\n", $lines);

        $lines = array();
        foreach ($data['noCacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['noCacheControllers'] = implode("\n", $lines);

        unset($data['id']);

        return $data;

    }

    /**
     * Helper method to persist a given config value
     */
    public function saveConfig($name, $value)
    {
        $shopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        /** @var $element Shopware\Models\Config\Element */
        $element = $elementRepository->findOneBy(array('name' => $name));
        foreach ($element->getValues() as $valueModel) {
            Shopware()->Models()->remove($valueModel);
        }

        $values = array();
        // Do not save default value
        if ($value !== $element->getValue()) {
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        Shopware()->Models()->flush($element);
    }

    /**
     * Reads all config data and prepares it for our models
     * @return array
     */
    protected function prepareConfigData()
    {
        return array(
            'httpCache' => $this->prepareHttpCacheConfig(),
            'topSeller' => $this->prepareTopSellerConfig(),
            'seo'       => $this->prepareSeoConfig(),
        );
    }

    protected function prepareSeoConfig()
    {
        return array(
            'routerurlcache'     => (int) Shopware()->Config()->routerurlcache,
            'routercache'        => (int) Shopware()->Config()->routercache,
            'routerlastupdate'   => (int) Shopware()->Config()->routerlastupdate,
            'seoRefreshStrategy' => Shopware()->Config()->seoRefreshStrategy
        );
    }

    protected function prepareTopSellerConfig()
    {
        return array(
            'topSellerActive'           => (int) Shopware()->Config()->topSellerActive,
            'topSellerValidationTime'   => (int) Shopware()->Config()->topSellerValidationTime,
            'chartinterval'             => (int) Shopware()->Config()->chartinterval,
            'topSellerRefreshStrategy'  => Shopware()->Config()->topSellerRefreshStrategy,
            'topSellerPseudoSales'      => (int) Shopware()->Config()->topSellerPseudoSales
        );
    }

    protected function prepareHttpCacheConfig()
    {
        $controllers = Shopware()->Config()->cacheControllers;
        $cacheControllers = array();
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $cacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        $controllers = Shopware()->Config()->noCacheControllers;
        $noCacheControllers = array();
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $noCacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        return array(
            'cacheControllers' => $cacheControllers,
            'noCacheControllers' => $noCacheControllers,
            'proxyBan' => Shopware()->Config()->proxyBan,
            'admin' => Shopware()->Config()->admin,
            'proxy' => Shopware()->Config()->proxy
        );
    }

    /**
     *
     */
    public function getConfigAction()
    {
        $this->View()->assign(array(
            'success' => true,
            'data' => $this->configData
        ));
    }
}