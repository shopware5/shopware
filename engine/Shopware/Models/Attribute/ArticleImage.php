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
 * Shopware\Models\Attribute\ArticleImage
 *
 * @ORM\Table(name="s_articles_img_attributes")
 * @ORM\Entity
 */
class ArticleImage extends ModelEntity
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
     * @var string $attribute1
     *
     * @ORM\Column(name="attribute1", type="string", length=255, nullable=true)
     */
    private $attribute1 = null;

    /**
     * @var string $attribute2
     *
     * @ORM\Column(name="attribute2", type="string", length=255, nullable=true)
     */
    private $attribute2 = null;

    /**
     * @var string $attribute3
     *
     * @ORM\Column(name="attribute3", type="string", length=255, nullable=true)
     */
    private $attribute3 = null;

    /**
     * @var integer $articleImageId
     *
     * @ORM\Column(name="imageID", type="integer", nullable=true)
     */
    private $articleImageId = null;

    /**
     * @var Shopware\Models\Article\Image
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Image", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imageID", referencedColumnName="id")
     * })
     */
    private $articleImage;

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
     * Set articleImage
     *
     * @param Shopware\Models\Article\Image $articleImage
     * @return ArticleImage
     */
    public function setArticleImage(\Shopware\Models\Article\Image $articleImage = null)
    {
        $this->articleImage = $articleImage;
        return $this;
    }

    /**
     * Get articleImage
     *
     * @return Shopware\Models\Article\Image
     */
    public function getArticleImage()
    {
        return $this->articleImage;
    }

    /**
     * Set articleImageId
     *
     * @param integer $articleImageId
     * @return ArticleImage
     */
    public function setArticleImageId($articleImageId)
    {
        $this->articleImageId = $articleImageId;
        return $this;
    }

    /**
     * Get articleImageId
     *
     * @return integer
     */
    public function getArticleImageId()
    {
        return $this->articleImageId;
    }

    /**
     * @param string $attribute1
     */
    public function setAttribute1($attribute1)
    {
        $this->attribute1 = $attribute1;
    }

    /**
     * @return string
     */
    public function getAttribute1()
    {
        return $this->attribute1;
    }

    /**
     * @param string $attribute2
     */
    public function setAttribute2($attribute2)
    {
        $this->attribute2 = $attribute2;
    }

    /**
     * @return string
     */
    public function getAttribute2()
    {
        return $this->attribute2;
    }

    /**
     * @param string $attribute3
     */
    public function setAttribute3($attribute3)
    {
        $this->attribute3 = $attribute3;
    }

    /**
     * @return string
     */
    public function getAttribute3()
    {
        return $this->attribute3;
    }
}
