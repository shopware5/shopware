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
 * @package    Shopware_Components_HttpCache
 * @subpackage HttpCache
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Enlight_Controller_Response_ResponseHttp as ControllerResponse,
    Enlight_Controller_Request_RequestHttp as ControllerRequest;

/**
 * Shopware Application
 *
 * todo@all: Documentation
 * <code>
 * $httpCacheKernel = new Shopware\Components\HttpCache\HttpKernel($app);
 * $httpCacheKernel->handle($request);
 * </code>
 */
class HttpKernel implements HttpKernelInterface
{
    /**
     * @var \Shopware
     */
    protected $app;

    /**
     * @param \Shopware $app
     */
    public function __construct(\Shopware $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $type
     * @param bool $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $front = $this->app->Front();
        $front->returnResponse(true);
        $front->throwExceptions(!$catch);

        $request->headers->set('Surrogate-Capability', 'shopware="ESI/1.0"');

        $request = $this->createRequest($request);

        if($front->Request() === null) {
            $front->setRequest($request);
            $response = $front->dispatch();
        } else {
            $dispatcher = clone $front->Dispatcher();
            $response = clone $front->Response();
            $response->clearHeaders()
                ->clearRawHeaders()
                ->clearBody();
            $response->setHttpResponseCode(200);
            $request->setDispatched(true);
            $dispatcher->dispatch($request, $response);
        }

        $response = $this->createResponse($response);

        return $response;
    }

    /**
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return \Enlight_Controller_Request_RequestHttp
     */
    public function createRequest(Request $request)
    {
        $request->overrideGlobals();
        $request = new ControllerRequest(
            str_replace(" ","+",$request->getUri())
        );

        $request->setQuery($request->getQuery());
        return $request;
    }

    /**
     * @param \Enlight_Controller_Response_ResponseHttp $response
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(ControllerResponse $response)
    {
        $rawHeaders = $response->getHeaders();
        $headers = array();
        foreach($rawHeaders as $header) {
            if(!isset($headers[$header['name']]) || !empty($header['replace'])) {
                $headers[$header['name']] = array($header['value']);
            } else {
                $headers[$header['name']][] = $header['value'];
            }
        }
        //todo@hl Maybe transform to symfony
        $response->sendCookies();

        return new Response(
            $response->getBody(),
            $response->getHttpResponseCode(),
            $headers
        );
    }

    /**
     * @return \Shopware
     */
    public function getApp()
    {
        return $this->app;
    }
}
