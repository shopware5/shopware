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

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

class MetaStruct implements \JsonSerializable
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $sha1;

    /**
     * @var string
     */
    private $binaryVersion;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $technicalName;

    /**
     * @param string $uri
     * @param string $size
     * @param string $sha1
     * @param string $binaryVersion
     * @param string $fileName
     */
    public function __construct($uri, $size, $sha1, $binaryVersion, $fileName, $technicalName)
    {
        $this->uri = $uri;
        $this->size = $size;
        $this->sha1 = $sha1;
        $this->binaryVersion = $binaryVersion;
        $this->fileName = $fileName;
        $this->technicalName = $technicalName;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
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
    public function getBinaryVersion()
    {
        return $this->binaryVersion;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
