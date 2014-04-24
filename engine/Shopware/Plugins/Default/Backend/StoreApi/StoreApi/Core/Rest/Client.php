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

class Shopware_StoreApi_Core_Rest_Client extends Enlight_Class
{
    const TYPE_GET   = 'GET';
    const TYPE_POST  = 'POST';

    const RESPONSE_FAILED = 'failed';

    /**
     * Holds the rest client
     *
     * @var Zend_Rest_Client
     */
    protected $client;

    /**
     * @var Shopware_Components_StoreConfig
     */
    protected $config;

    /**
     * @param Shopware_Components_StoreConfig $config
     */
    public function setConfig(Shopware_Components_StoreConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Starts the client by using the given rest server url
     *
     * @param $url
     */
    public function startClient($url)
    {
        $this->client = new Zend_Rest_Client($url);

        // Get instance of Zend_Http_Client
        $httpClient = $this->client->getHttpClient();

        // Change the timeout
        $httpClient->setConfig(array(
            "timeout" => 7
        ));
    }

    /**
     * @param $type
     * @param string $url
     * @param array $json
     * @return mixed|Shopware_StoreApi_Exception_Response
     */
    public function call($type, $url, $json)
    {
        if ($this->config) {
            $version = $this->config->getVersion();
            $language = $this->config->getLanguage();
        } else {
            $version = 4000;
            $language = 'DE';
        }

        $json['config'] = array(
            'version'  => $version,
            'language' => $language
        );

        $json = Zend_Json::encode($json);
        $response = $this->client->call($type, $url, $json)->post();

        if ($response->status == self::RESPONSE_FAILED) {
            preg_match('#(.*):(\d*)#', $response->response->message, $error_match);
            return new Shopware_StoreApi_Exception_Response($error_match[1], $error_match[2]);
        } else {
            $preparedResponse = new Shopware_StoreApi_Core_Response_Response($response->response);
            return current($preparedResponse->getCollection());
        }
    }
}
