<?php
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

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class PictureStruct implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $remoteLink;

    /**
     * @var bool
     */
    private $cover = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getRemoteLink()
    {
        return $this->remoteLink;
    }

    /**
     * @param string $remoteLink
     */
    public function setRemoteLink($remoteLink)
    {
        $this->remoteLink = $remoteLink;
    }

    /**
     * @return bool
     */
    public function isCover()
    {
        return $this->cover;
    }

    /**
     * @param bool $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }
}
