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

namespace Shopware\Models\Blog;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Media\Media as MediaModel;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_blog_media")
 */
class Media extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Blog
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Blog\Blog", inversedBy="media")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * OWNING SIDE
     *
     * @var MediaModel
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Media\Media", inversedBy="blogMedia")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=false)
     */
    protected $media;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="blog_id", type="integer", nullable=false)
     */
    private $blogId;

    /**
     * @var int
     *
     * @ORM\Column(name="media_id", type="integer", nullable=false)
     */
    private $mediaId;

    /**
     * @var bool
     *
     * @ORM\Column(name="preview", type="boolean", nullable=false)
     */
    private $preview;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param bool $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param Blog $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    /**
     * @return MediaModel
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param MediaModel $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }
}
