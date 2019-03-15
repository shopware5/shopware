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

namespace Shopware\Components\Log\Handler;

use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Monolog\Handler\ChromePHPHandler as BaseChromePhpHandler;

/**
 * ChromePhpHandler.
 */
class ChromePhpHandler extends BaseChromePhpHandler
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var \Enlight_Controller_Response_ResponseHttp|null
     */
    private $response;

    public function setUp(Request $request, Response $response)
    {
        if (!$this->acceptsRequest($request)) {
            $this->sendHeaders = false;
            $this->headers = [];

            return;
        }

        $this->response = $response;
        foreach ($this->headers as $header => $content) {
            $this->response->headers->set($header, $content, true);
        }

        $this->headers = [];
    }

    /**
     * Adds the headers to the response once it's created
     */
    public function onRouteStartUp(\Enlight_Controller_EventArgs $args)
    {
        $response = $args->getResponse();
        $request = $args->getRequest();

        $this->setUp($request, $response);
    }

    /**
     * @return bool
     */
    public function acceptsRequest(Request $request)
    {
        return (bool) preg_match('{\bChrome/\d+[\.\d+]*\b}', $request->getHeader('User-Agent'));
    }

    /**
     * {@inheritdoc}
     */
    protected function sendHeader($header, $content)
    {
        if (!$this->sendHeaders) {
            return;
        }

        if ($this->response) {
            $this->response->headers->set($header, $content, true);
        } else {
            $this->headers[$header] = $content;
        }
    }

    /**
     * Override default behavior since we check the user agent in onKernelResponse
     */
    protected function headersAccepted()
    {
        return true;
    }
}
