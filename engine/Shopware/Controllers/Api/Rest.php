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

use Shopware\Components\Api\Resource;

class Shopware_Controllers_Api_Rest extends Enlight_Controller_Action
{
    protected $apiBaseUrl;

    /**
     * @var Resource\Resource
     */
    protected $resource;

    public function preDispatch()
    {
        if (($this->resource instanceof Resource\Resource) && $this->container->initialized('auth')) {
            $this->resource->setAcl($this->container->get('acl'));
            $this->resource->setRole($this->container->get('auth')->getIdentity()->role);
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->apiBaseUrl = $this->Request()->getScheme()
           . '://'
           . $this->Request()->getHttpHost()
           . $this->Request()->getBaseUrl()
           . '/api/';
    }

    public function postDispatch()
    {
        $data = $this->View()->getAssign();
        $pretty = $this->Request()->getParam('pretty', false);

        array_walk_recursive($data, static function (&$value) {
            // Convert DateTime instances to ISO-8601 Strings
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }
        });

        $data = Zend_Json::encode($data);
        if ($pretty) {
            $data = Zend_Json::prettyPrint($data);
        }

        $this->Response()->headers->set('content-type', 'application/json', true);
        $this->Response()->setContent($data);
    }

    /**
     * Controller Action for the batchAction
     *
     * @throws RuntimeException
     */
    public function batchAction()
    {
        // To support the batch mode, the controller just needs to reference the api resource
        // with the "resource" property
        if (!property_exists($this, 'resource')) {
            throw new RuntimeException('Property "resource" not found.');
        }

        $params = $this->Request()->getPost();

        $this->resource->setResultMode(
            Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $result = $this->resource->batch($params);

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    /**
     * Controller Action for the batchDelete
     *
     * @throws RuntimeException
     */
    public function batchDeleteAction()
    {
        // To support the batch mode, the controller just needs to reference the api resource
        // with the "resource" property
        if (!property_exists($this, 'resource')) {
            throw new RuntimeException('Property "resource" not found.');
        }

        // getParams() returns some additional, irrelevant parameters
        $params = array_merge($this->Request()->getQuery(), $this->Request()->getPost());

        // Remove stack-related parameters
        unset($params['module'], $params['controller'], $params['action']);

        $this->resource->setResultMode(
            Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $result = $this->resource->batchDelete($params);

        $this->View()->assign(['success' => true, 'data' => $result]);
    }
}
