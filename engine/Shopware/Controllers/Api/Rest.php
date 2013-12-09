<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 */
class Shopware_Controllers_Api_Rest extends Enlight_Controller_Action
{
    protected $apiBaseUrl;

    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->throwExceptions(false);

        // todo@bc set url in shopware specific way
        $serverUrlHelper = new Zend_View_Helper_ServerUrl();
        $this->apiBaseUrl = $serverUrlHelper->serverUrl() . $this->Request()->getBaseUrl() . '/api/';
    }

    public function postDispatch()
    {
        $data = $this->View()->getAssign();
        $pretty = $this->Request()->getParam('pretty', false);

        array_walk_recursive($data, function (&$value) {
            // Convert DateTime instances to ISO-8601 Strings
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }
        });

        $data = Zend_Json::encode($data);
        if($pretty) {
            $data = Zend_Json::prettyPrint($data);
        }

        $this->response()->setHeader('Content-type', 'application/json', true);
        $this->response()->setBody($data);
    }
}
