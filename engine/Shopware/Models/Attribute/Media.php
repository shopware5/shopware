<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\Media
 *
 * @ORM\Table(name="s_media_attributes")
 * @ORM\Entity
 */
class Media extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $mediaId
     *
     * @ORM\Column(name="mediaID", type="integer", nullable=true)
     */
    private $mediaId = null;

    /**
     * @var Shopware\Models\Media\Media
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Media\Media", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mediaID", referencedColumnName="id")
     * })
     */
    private $media;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set media
     *
     * @param Shopware\Models\Media\Media $media
     * @return Media
     */
    public function setMedia(\Shopware\Models\Media\Media $media = null)
    {
        $this->media = $media;
        return $this;
    }

    /**
     * Get media
     *
     * @return Shopware\Models\Media\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set mediaId
     *
     * @param integer $mediaId
     * @return Media
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
        return $this;
    }

    /**
     * Get mediaId
     *
     * @return integer
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }
}
