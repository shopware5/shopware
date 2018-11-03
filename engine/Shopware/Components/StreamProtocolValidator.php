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

class StreamProtocolValidator implements StreamProtocolValidatorInterface
{
    /**
     * @var string[]
     */
    private $allowedProtocols;

    /**
     * @param string[] $allowedProtocols
     */
    public function __construct(array $allowedProtocols = [
        'ftp',
        'ftps',
        'http',
        'https',
        'file',
        'data',
    ])
    {
        $this->allowedProtocols = $allowedProtocols;
    }

    /**
     * @param string $url
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function validate($url)
    {
        $urlArray = parse_url($url);

        if (isset($urlArray['scheme']) && !in_array($urlArray['scheme'], $this->allowedProtocols, true)) {
            throw new \InvalidArgumentException(
                sprintf("Invalid stream protocol '%s'", $urlArray['scheme'])
            );
        }

        return true;
    }
}
