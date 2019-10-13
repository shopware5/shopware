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

namespace Shopware\Components\Controller;

use Enlight_Controller_Front as EnlightFront;
use Enlight_Controller_Request_RequestHttp as EnlightRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FrontController
{
    private $front;

    public function __construct(EnlightFront $front)
    {
        $this->front = $front;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request instanceof EnlightRequest) {
            $request = $this->transformSymfonyRequestToEnlightRequest($request);
        }

        if ($this->front->Request() === null) {
            $this->front->setRequest($request);
            $response = $this->front->dispatch();
        } else {
            $dispatcher = clone $this->front->Dispatcher();
            $response = clone $this->front->Response();

            $response->clearHeaders()
                ->clearBody();

            $response->setStatusCode(Response::HTTP_OK);
            $request->setDispatched();
            $dispatcher->dispatch($request, $response);
        }

        return $response;
    }

    /**
     * @return EnlightRequest
     */
    private function transformSymfonyRequestToEnlightRequest(Request $request)
    {
        // Overwrite superglobals with state of the SymfonyRequest
        $request->overrideGlobals();

        return EnlightRequest::createFromGlobals();
    }
}
