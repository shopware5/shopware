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

namespace ShopwarePlugins\SwagUpdate\Components;

use ShopwarePlugins\SwagUpdate\Components\Struct\Version;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class UpdateCheck
{
    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var bool
     */
    private $verifySignature;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @param string $apiEndpoint
     * @param string $channel
     * @param bool   $verifySignature
     * @param string $publicKey
     */
    public function __construct($apiEndpoint, $channel, $verifySignature, $publicKey)
    {
        $this->apiEndpoint     = rtrim($apiEndpoint, '/');
        $this->channel         = $channel;
        $this->verifySignature = $verifySignature;
        $this->publicKey       = $publicKey;
    }

    /**
     * @param bool $verify
     */
    public function setVerifyResponseSignature($verify = true)
    {
        $this->verifySignature = $verify;
    }

    /**
     * @param string $signature
     * @param string $body
     *
     * @throws \Exception
     */
    private function verifyBody($signature, $body)
    {
        if (!function_exists('openssl_verify')) {
            return;
        }

        $verificator = new OpenSsl();
        $verificator->setPublicKey($this->publicKey);

        if (!$verificator->verify($body, $signature)) {
            throw new \Exception('Signature is not valid');
        }
    }

    /**
     * @param string $shopwareVersion
     * @param array  $params
     *
     * @return Version|null
     */
    public function checkUpdate($shopwareVersion, $params = array())
    {
        $url = $this->apiEndpoint . '/release/update';

        $client = new \Zend_Http_Client($url, array(
            'timeout'   => 5,
            'useragent' => 'Shopware/' . \Shopware::VERSION
        ));

        $client->setParameterGet('shopware_version', $shopwareVersion);
        $client->setParameterGet('channel', $this->channel);
        $client->setParameterGet($params);

        try {
            $response = $client->request();
        } catch (\Zend_Http_Client_Exception $e) {
            // Do not show exception to user if request times out
            return null;
        }

        $body = $response->getBody();

        $verified = false;
        if (!empty($this->publicKey) && $this->verifySignature) {
            $signature = $response->getHeader('X-Shopware-Signature');
            $this->verifyBody($signature, $body);
            $verified = true;
        }

        if ($body != '') {
            $json = \Zend_Json::decode($body, true);
        } else {
            $json = null;
        }
        $json['signagure_verified'] = $verified;

        if ($response->getStatus() == '404') {
            return null;
        }

        return new Version($json);
    }
}
