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

namespace Shopware\Components\Privacy;

/**
 * Class to strip the last half of an IPv4 or IPv6 address
 */
class IpAnonymizer implements IpAnonymizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function anonymize($ip)
    {
        return preg_replace([
            '/\.\d*\.\d*$/', // Matches the last two parts of an IPv4
            '/[\da-f]*:[\da-f]*:[\da-f]*$/', // Matches the last three parts of an IPv6
        ], [
            '.0.0',
            '::',
        ], (string) $ip);
    }
}
