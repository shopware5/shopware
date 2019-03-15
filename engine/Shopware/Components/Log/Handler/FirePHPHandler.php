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
use Monolog\Handler\FirePHPHandler as BaseFirePHPHandler;

/**
 * FirePHPHandler.
 */
class FirePHPHandler extends BaseFirePHPHandler
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
        $firePhpVersion = $request->getHeader('X-FirePHP-Version');
        $userAgent = preg_match('{\bFirePHP/\d+\.\d+\b}', $request->getHeader('User-Agent'));

        return $firePhpVersion || $userAgent;
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
     * Creates message header from record
     *
     * @see createHeader()
     *
     * @return array
     */
    protected function createRecordHeader(array $record)
    {
        $chunkSize = 5000;
        $length = strlen($record['formatted']);

        if ($length < $chunkSize) {
            // Wildfire is extensible to support multiple protocols & plugins in a single request,
            // but we're not taking advantage of that (yet), so we're using "1" for simplicity's sake.
            $header = $this->createHeader(
                [1, 1, 1, self::$messageIndex++],
                $length . '|' . $record['formatted'] . '|'
            );

            return $header;
        }

        $parts = str_split($record['formatted'], $chunkSize);
        $headers = [];
        for ($i = 0; $i < count($parts); ++$i) {
            $part = $parts[$i];
            $isFirst = ($i == 0);
            $isLast = ($i == count($parts) - 1);

            if ($isFirst) {
                $headers[] = $this->createHeader(
                    [1, 1, 1, self::$messageIndex++],
                    $length . '|' . $part . '|\\'
                );
            } elseif ($isLast) {
                $headers[] = $this->createHeader(
                    [1, 1, 1, self::$messageIndex++],
                    '|' . $part . '|'
                );
            } else {
                $headers[] = $this->createHeader(
                    [1, 1, 1, self::$messageIndex++],
                    '|' . $part . '|\\'
                );
            }
        }

        return $headers;
    }

    /**
     * Creates & sends header for a record, ensuring init headers have been sent prior
     *
     * @see sendHeader()
     * @see sendInitHeaders()
     */
    protected function write(array $record)
    {
        // WildFire-specific headers must be sent prior to any messages
        if (!self::$initialized) {
            self::$sendHeaders = $this->headersAccepted();

            foreach ($this->getInitHeaders() as $header => $content) {
                $this->sendHeader($header, $content);
            }

            self::$initialized = true;
        }

        $headers = $this->createRecordHeader($record);

        if (isset($headers[0]) && is_array($headers[0])) {
            foreach ($headers as $header) {
                $this->sendHeader(key($header), current($header));
            }

            return;
        }

        $this->sendHeader(key($headers), current($headers));
    }

    /**
     * Override default behavior since we check the user agent in onKernelResponse
     */
    protected function headersAccepted()
    {
        return true;
    }
}
