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

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Enlight_Controller_Response_ResponseHttp as EnlightResponse;
use Enlight_Controller_Request_RequestHttp as EnlightRequest;

/**
 * Shopware Application
 *
 * <code>
 * $httpCacheKernel = new Shopware\Components\HttpCache\HttpKernel($app);
 * $httpCacheKernel->handle($request);
 * </code>
 *
 * @category  Shopware
 * @package   Shopware\Components\HttpCache
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  int                                        $type
     * @param  bool                                       $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $front = $this->app->Front();
        $front->returnResponse(true);
        $front->throwExceptions(!$catch);

        $request->headers->set('Surrogate-Capability', 'shopware="ESI/1.0"');

        $request = $this->transformSymfonyRequestToEnlightRequest($request);

        if ($front->Request() === null) {
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

        $response = $this->transformEnlightResponseToSymfonyResponse($response);

        return $response;
    }

    /**
     * @param SymfonyRequest $request
     * @return EnlightRequest
     */
    public function transformSymfonyRequestToEnlightRequest(SymfonyRequest $request)
    {
        // Overwrite superglobals with state of the SymfonyRequest
        $request->overrideGlobals();

        // Create englight request from global state
        $enlightRequest = new EnlightRequest();

        return $enlightRequest;
    }

    /**
     * @param EnlightResponse $response
     * @return SymfonyResponse
     */
    public function transformEnlightResponseToSymfonyResponse(EnlightResponse $response)
    {
        $rawHeaders = $response->getHeaders();
        $headers = array();
        foreach ($rawHeaders as $header) {
            if (!isset($headers[$header['name']]) || !empty($header['replace'])) {
                $headers[$header['name']] = array($header['value']);
            } else {
                $headers[$header['name']][] = $header['value'];
            }
        }


        $symfonyResponse = new SymfonyResponse(
            $response->getBody(),
            $response->getHttpResponseCode(),
            $headers
        );

        foreach ($response->getCookies() as $cookieName => $cookieContent) {
            $sfCookie = new Cookie(
                $cookieName,
                $cookieContent['value'],
                $cookieContent['expire'],
                $cookieContent['path'],
                (bool) $cookieContent['secure'],
                (bool) $cookieContent['httpOnly']
            );

            $symfonyResponse->headers->setCookie($sfCookie);
        }

        return $symfonyResponse;
    }

    /**
     * @return \Shopware
     */
    public function getApp()
    {
        return $this->app;
    }
}
