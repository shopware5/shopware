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

namespace Shopware\Components;

class DispatchFormatHelper
{
    /**
     * @param string $unFormatted
     * @param bool   $isController
     *
     * @return string
     */
    public function formatNameForRequest($unFormatted, $isController = false)
    {
        $allowedCharacters = 'a-zA-Z0-9_';

        if ($isController) {
            $allowedCharacters .= '\.';
        }

        return preg_replace('#[^' . $allowedCharacters . ']+#', '', $unFormatted);
    }

    /**
     * @param string $unFormatted
     *
     * @return string
     */
    public function formatNameForDispatch($unFormatted)
    {
        $segments = explode('_', $unFormatted);

        foreach ($segments as $key => $segment) {
            $segment = preg_replace('#[A-Z]#', ' $0', $segment);
            $segment = str_replace(['-', '.'], ' ', strtolower($segment));
            $segment = preg_replace('/[^a-z0-9 ]/', '', $segment);
            $segments[$key] = str_replace(' ', '', ucwords($segment));
        }

        return implode('_', $segments);
    }
}
