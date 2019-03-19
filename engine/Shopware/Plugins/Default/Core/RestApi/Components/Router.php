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

namespace ShopwarePlugins\RestApi\Components;

use Enlight_Controller_Request_RequestHttp as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Router
{
    public function assembleRoute(Request $request, Response $response)
    {
        $path = $request->getPathInfo();

        $path = explode('/', trim($path, '/'));
        $path = array_pad($path, 7, null);

        array_shift($path);
        $tmp = array_shift($path);
        $matches = [];
        if (preg_match('/^v([1-9])$/', $tmp, $matches) === 1) {
            $version = (int) $matches[1];
            $type = array_shift($path);
        } else {
            $version = 1;
            $type = $tmp;
        }

        $id = !empty($path[0]) ? $path[0] : false;
        $subType = !empty($path[1]) ? $path[1] : false;
        $subId = !empty($path[2]) ? $path[2] : false;

        $request->setControllerName($type);
        $request->setParam('id', $id);
        $request->setParam('subId', $subId);
        $request->setParam('version', $version);

        $method = strtoupper($request->getParam('_method', $request->getMethod()));
        $action = 'invalid';

        if ($method === 'GET' && $id === false) {
            $action = 'index';
            $response->setStatusCode(SymfonyResponse::HTTP_OK);
        } elseif ($method === 'GET') {
            $action = 'get';
            $response->setStatusCode(SymfonyResponse::HTTP_OK);
        } elseif ($method === 'PUT' && $id === false) {
            $action = 'batch';
            $response->setStatusCode(SymfonyResponse::HTTP_OK);
        } elseif ($method === 'PUT') {
            $action = 'put';
        } elseif ($method === 'POST') {
            $action = 'post';
            // Set default http status code for successful request
            $response->setStatusCode(SymfonyResponse::HTTP_CREATED);
        } elseif ($method === 'DELETE' && $id === false) {
            $action = 'batchDelete';
            $response->setStatusCode(SymfonyResponse::HTTP_OK);
        } elseif ($method === 'DELETE') {
            $response->setStatusCode(SymfonyResponse::HTTP_OK);
            $action = 'delete';
        }

        if ($action === 'invalid') {
            $request->setControllerName('index');
            $request->setActionName($action);

            return;
        }

        if (!$subType) {
            $request->setActionName($action);

            return;
        }

        if ($action === 'get' && $subId === false) {
            $subAction = $subType . 'Index';
        } else {
            $subAction = $subType;
        }

        $action .= ucfirst($subAction);
        $request->setActionName($action);
    }
}
