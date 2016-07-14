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

namespace Shopware\Components\Console;

use Symfony\Component\Console\Shell as BaseShell;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shell extends BaseShell
{
    /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader()
    {
        return <<<EOF
<info>
         _
     ___| |__   ___  _ ____      ____ _ _ __ ___
    / __| '_ \ / _ \| '_ \ \ /\ / / _` | '__/ _ \
    \__ \ | | | (_) | |_) \ V  V / (_| | | |  __/
    |___/_| |_|\___/| .__/ \_/\_/ \__,_|_|  \___|
                    |_|
</info>
EOF
        .parent::getHeader();
    }
}
