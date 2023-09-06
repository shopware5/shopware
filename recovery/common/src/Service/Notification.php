<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Recovery\Common\Service;

use Exception;
use Shopware\Recovery\Common\HttpClient\Client;

class Notification
{
    private string $apiEndPoint;

    private Client $client;

    private string $uniqueId;

    public function __construct(string $apiEndPoint, string $uniqueId, Client $client)
    {
        $this->apiEndPoint = $apiEndPoint;
        $this->client = $client;
        $this->uniqueId = $uniqueId;
    }

    /**
     * @param string $eventName
     * @param array  $additionalInformation
     *
     * @return array|false
     */
    public function doTrackEvent($eventName, $additionalInformation = [])
    {
        $payload = [
            'additionalData' => $additionalInformation,
            'instanceId' => $this->uniqueId,
            'event' => $eventName,
        ];

        try {
            $response = $this->client->post($this->apiEndPoint . '/tracking/events', json_encode($payload));
        } catch (Exception $ex) {
            return false;
        }

        return json_decode($response->getBody(), true) ?: false;
    }
}
