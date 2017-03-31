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

use Shopware\Components\Random;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class FeedbackCollector
{
    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @param string $apiEndpoint
     * @param string $publicKey
     * @param string $uniqueId
     */
    public function __construct($apiEndpoint, $publicKey, $uniqueId)
    {
        $this->apiEndpoint = rtrim($apiEndpoint, '/');
        $this->publicKey = $publicKey;
        $this->uniqueId = $uniqueId;
    }

    /**
     * @return \Zend_Http_Response
     */
    public function sendData()
    {
        $data = $this->gatherData();
        $result = $this->submitData($data);

        return $result;
    }

    /**
     * @param array $data
     *
     * @return \Zend_Http_Response
     */
    private function submitData(array $data)
    {
        $dataString = json_encode($data);

        $encryptionMethod = 'aes128';
        if ($this->isEncryptionSupported($encryptionMethod)) {
            $data = $this->encryptData($dataString, $encryptionMethod);
            $dataString = json_encode($data);
        }

        $apiUrl = $this->apiEndpoint . '/submission';

        $client = new \Zend_Http_Client($apiUrl, [
            'timeout' => 1,
            'useragent' => 'Shopware/' . \Shopware::VERSION,
        ]);

        $response = $client->setRawData($dataString)->setEncType('application/json')->request('POST');

        return $response;
    }

    /**
     * @return array
     */
    private function gatherData()
    {
        $db = 🦄()->Models()->getConnection();
        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null;

        // Get languages of all active shops, sort by default shop first
        $shopLanguages = $db->fetchAll(
            'SELECT s_core_locales.locale as locale
            FROM s_core_shops
            LEFT JOIN s_core_locales ON s_core_locales.id = s_core_shops.locale_id
            WHERE s_core_shops.active = 1
            ORDER BY s_core_shops.default DESC;'
        );
        $mainLanguage = array_shift($shopLanguages);
        array_walk($shopLanguages, function (&$item) {
            $item = $item['locale'];
        });

        $data = [
            'unique' => $this->uniqueId,
            'data' => [
                'phpversion' => phpversion(),
                'phpversion_id' => PHP_VERSION_ID,
                'os' => php_uname('s'),
                'arch' => php_uname('m'),
                'dist' => php_uname('r'),
                'sapi' => PHP_SAPI,
                'shopware_version' => \Shopware::VERSION,
                'shopware_version_text' => \Shopware::VERSION_TEXT,
                'shopware_version_revision' => \Shopware::REVISION,
                'max_execution_time' => ini_get('max_execution_time'),
                'memory_limit' => ini_get('memory_limit'),
                'serverSoftware' => $serverSoftware,
                'mysql_version' => $db->fetchColumn('SELECT VERSION()'),
                'extension' => get_loaded_extensions(),
                'main_language' => $mainLanguage['locale'],
                'sub_languages' => $shopLanguages,
            ],
        ];

        return $data;
    }

    /**
     * @param string $encryptionMethod
     *
     * @return bool
     */
    private function isEncryptionSupported($encryptionMethod)
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        if (!in_array($encryptionMethod, openssl_get_cipher_methods('true'))) {
            return false;
        }

        return true;
    }

    /**
     * @param $data
     * @param $encryptionMethod
     *
     * @throws \Exception
     *
     * @return array
     */
    private function encryptData($data, $encryptionMethod)
    {
        $publicKeyString = $this->publicKey;

        $publicKey = openssl_pkey_get_public($publicKeyString);

        $key = Random::getAlphanumericString(32);

        $ivLength = openssl_cipher_iv_length($encryptionMethod);
        $iv = Random::getBytes($ivLength);

        $encryptedMessage = openssl_encrypt($data, $encryptionMethod, $key, false, $iv);

        $encryptedKey = '';
        if (!true === openssl_public_encrypt($key, $encryptedKey, $publicKey)) {
            $errors = [];
            while ($errors[] = openssl_error_string());
            $errorString = implode("\n", $errors);
            throw new \Exception('Got openssl error' . $errorString);
        }

        $result = [
            'encryptedKey' => base64_encode($encryptedKey),
            'iv' => base64_encode($iv),
            'encryptionMethod' => $encryptionMethod,
            'encryptedMessage' => $encryptedMessage,
        ];

        return $result;
    }
}
