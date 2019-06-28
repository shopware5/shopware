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

namespace Shopware\Components\Log\Processor;

/**
 * ShopwareEnvironmentProcessor.
 */
class ShopwareEnvironmentProcessor
{
    /**
     * Adds request, shop and session info
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        if (Shopware()->Front() && $request = Shopware()->Front()->Request()) {
            $record['extra']['request'] = [
                'uri' => $request->getRequestUri(),
                'method' => $request->getMethod(),
                'query' => $this->filterRequestUserData($request->getQuery()),
                'post' => $this->filterRequestUserData($request->getPost()),
            ];
        } elseif (!empty($_SERVER['REQUEST_URI'])) {
            $record['extra']['request'] = [
                'uri' => $_SERVER['REQUEST_URI'],
                'method' => $_SERVER['REQUEST_METHOD'],
                'query' => $this->filterRequestUserData($_GET),
                'post' => $this->filterRequestUserData($_POST),
            ];
        } else {
            $record['extra']['request'] = 'Could not process request data';
        }

        if (Shopware()->Container()->has('shop')) {
            if ($session = Shopware()->Session()) {
                $record['extra']['session'] = $session;
            }
            if ($shop = Shopware()->Shop()) {
                $record['extra']['shopId'] = Shopware()->Shop()->getId() ?: null;
                $record['extra']['shopName'] = Shopware()->Shop()->getName() ?: null;
            }
        } else {
            $record['extra']['shop'] = 'No shop data available';
        }

        if (!empty($_SESSION) && is_object($_SESSION['Shopware']['Auth'])) {
            $record['extra']['session'] = [
                'userId' => $_SESSION['Shopware']['Auth']->id,
                'roleId' => $_SESSION['Shopware']['Auth']->roleID,
            ];
        } else {
            $record['extra']['session'] = 'No session data available';
        }

        return $record;
    }

    /**
     * Filters sensitive data from GET and POST
     *
     * @param array $data
     */
    private function filterRequestUserData($data)
    {
        $blacklist = [
            'password',
            'passwordConfirmation',
            'currentPassword',
        ];

        foreach ($blacklist as $elem) {
            $this->recursiveUnset($data, $elem);
        }

        return $data;
    }

    /**
     * Recursively searches for an unwanted key, and unsets every instance of it
     *
     * @param array  $array       nested array to search
     * @param string $unwantedKey unwanted key
     */
    private function recursiveUnset(&$array, $unwantedKey)
    {
        unset($array[$unwantedKey]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveUnset($value, $unwantedKey);
            }
        }
    }
}
