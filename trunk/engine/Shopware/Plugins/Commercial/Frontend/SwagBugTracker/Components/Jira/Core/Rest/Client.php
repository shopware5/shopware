<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Jira
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 */

namespace Shopware\Components\Jira\Core\Rest;

use \Guzzle\Http\Message\Response;
use \Guzzle\Http\Message\RequestInterface;
use \Guzzle\Http\Message\BadResponseException;

use \Shopware\Components\Jira\API\Exception\InternalException;
use \Shopware\Components\Jira\API\Exception\ForbiddenException;
use \Shopware\Components\Jira\API\Exception\NotFoundException;
use \Shopware\Components\Jira\API\Exception\UnauthorizedException;
use \Shopware\Components\Jira\API\Exception\InvalidArgumentException;

/**
 * Simple rest client class that abstracts the used http library, so that we are
 * able to switch the concrete implementation.
 */
class Client
{
    /**
     * Internally used guzzle http client.
     *
     * @var \Guzzle\Http\Client
     */
    private $client;

    /**
     * Set of default headers.
     *
     * @var string[]
     */
    private $headers = array(
        'Content-Type'      => 'application/json',
        'X-Atlassian-Token' => 'nocheck'
    );

    /**
     * Instantiates a new rest client instance.
     *
     * @param \Guzzle\Http\Client $client
     */
    public function __construct(\Guzzle\Http\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Sends a <b>GET</b> request to the given <b>$uri</b>.
     *
     * @param string $uri
     *
     * @return \stdClass
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function get($uri)
    {
        $request = $this->client->get(
            $uri,
            $this->headers
        );
        return $this->send($request);
    }

    /**
     * Sends a <b>POST</b> request to the given <b>$uri</b>.
     *
     * @param string $uri
     * @param array $data
     *
     * @return \stdClass
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function post($uri, array $data = array())
    {
        return $this->send(
            $this->client->post(
                $uri,
                $this->headers,
                json_encode($data)
            )
        );
    }

    /**
     * Sends a <b>PUT</b> request to the given <b>$uri</b>.
     *
     * @param string $uri
     * @param array $data
     *
     * @return \stdClass
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function put($uri, array $data = array())
    {
        return $this->send(
            $this->client->put(
                $uri,
                $this->headers,
                json_encode($data)
            )
        );
    }

    /**
     * Sends a <b>GET</b> request to the given <b>$uri</b>.
     *
     * @param string $uri
     *
     * @return mixed
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function delete($uri)
    {
        return $this->send(
            $this->client->delete(
                $uri,
                $this->headers
            )
        );
    }

    /**
     * Sends the given request and returns the response as an unserialized json
     * object.
     *
     * @param \Guzzle\Http\Message\RequestInterface $request
     *
     * @return \stdClass
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     * @throws \Shopware\Components\Jira\API\Exception\InvalidArgumentException
     * @throws \Shopware\Components\Jira\API\Exception\InternalException;
     */
    private function send(RequestInterface $request)
    {
        try
        {
            $response = $request->send();
        }
        catch (\Exception $e)
        {
            if (false === is_object($e->getResponse())) {
                throw new InternalException('An unknown error occurred.', 500, $e);
            }

            $message = $this->getErrors($e->getResponse());

            switch ($e->getResponse()->getStatusCode()) {
                case 400:
                    throw new InvalidArgumentException($message, 400, $e);

                case 401:
                    throw new UnauthorizedException($message, 401, $e);

                case 403:
                    throw new ForbiddenException($message, 403, $e);

                case 404:
                    throw new NotFoundException($message, 404, $e);
            }
            throw new InternalException($message, $e->getCode(), $e);
        }
        return json_decode($response->getBody(true));
    }

    /**
     * Creates a single string with all error messages available in the given
     * <b>$response</b> object.
     *
     * @param \Guzzle\Http\Message\Response $response
     *
     * @return string
     */
    private function getErrors(Response $response)
    {
        $data = json_decode($response->getBody(true));

        $messages = array();
        if (isset($data->errors)) {
            foreach (get_object_vars($data->errors) as $message) {
                $messages[] = $message;
            }
        }
        return join(',', $messages);
    }
}