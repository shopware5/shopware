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

namespace Shopware\Bundle\PluginInstallerBundle\Context;

class RangeDownloadRequest
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $sha1;

    /**
     * @var string
     */
    private $destination;

    /**
     * @param string $uri
     * @param int    $offset
     * @param int    $size
     * @param string $sha1
     * @param string $destination
     */
    public function __construct($uri, $offset, $size, $sha1, $destination)
    {
        $this->uri = $uri;
        $this->offset = $offset;
        $this->size = $size;
        $this->sha1 = $sha1;
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }
}
