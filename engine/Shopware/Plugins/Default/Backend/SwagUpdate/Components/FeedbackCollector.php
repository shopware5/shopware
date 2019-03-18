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

use Shopware\Components\OpenSSLEncryption;
use Shopware\Components\ShopwareReleaseStruct;

class FeedbackCollector
{
    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var OpenSSLEncryption
     */
    private $encryption;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var ShopwareReleaseStruct
     */
    private $release;

    /**
     * @param string $apiEndpoint
     * @param string $uniqueId
     */
    public function __construct($apiEndpoint, OpenSSLEncryption $encryption, $uniqueId, ShopwareReleaseStruct $release)
    {
        $this->apiEndpoint = rtrim($apiEndpoint, '/');
        $this->encryption = $encryption;
        $this->uniqueId = $uniqueId;
        $this->release = $release;
    }

    /**
     * @return \Zend_Http_Response
     */
    public function sendData()
    {
        $data = $this->gatherData();

        return $this->submitData($data);
    }

    /**
     * @return \Zend_Http_Response
     */
    private function submitData(array $data)
    {
        $dataString = json_encode($data);

        $encryptionMethod = 'aes128';
        if ($this->encryption->isEncryptionSupported($encryptionMethod)) {
            $data = $this->encryption->encryptData($dataString, $encryptionMethod);
            $dataString = json_encode($data);
        }

        $apiUrl = $this->apiEndpoint . '/submission';

        $client = new \Zend_Http_Client($apiUrl, [
            'timeout' => 1,
            'useragent' => 'Shopware/' . $this->release->getVersion(),
        ]);

        $response = $client->setRawData($dataString)->setEncType('application/json')->request('POST');

        return $response;
    }

    /**
     * @return array
     */
    private function gatherData()
    {
        $db = Shopware()->Models()->getConnection();
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
                'phpversion' => PHP_VERSION,
                'phpversion_id' => PHP_VERSION_ID,
                'os' => PHP_OS,
                'arch' => php_uname('m'),
                'dist' => php_uname('r'),
                'sapi' => PHP_SAPI,
                'shopware_version' => $this->release->getVersion(),
                'shopware_version_text' => $this->release->getVersionText(),
                'shopware_version_revision' => $this->release->getRevision(),
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
}
