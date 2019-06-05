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

namespace Shopware\Models\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ArticleDownload as ProductDownloadAttribute;

/**
 * @ORM\Table(name="s_articles_downloads")
 * @ORM\Entity()
 */
class Download extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Article
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="downloads")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     */
    protected $article;

    /**
     * INVERSE SIDE
     *
     * @var ProductDownloadAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleDownload", mappedBy="articleDownload", cascade={"persist"})
     */
    protected $attribute;

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
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    private $file;

    /**
     * @var float
     *
     * @deprecated since 5.5.9 and will be removed in 5.7. Use media_service to get size of file
     *
     * @todo remove in 5.7
     *
     * @ORM\Column(name="size", type="float", nullable=false)
     */
    private $size = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Article $article
     *
     * @return Download
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param string $name
     *
     * @return Download
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $file
     *
     * @return Download
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param float $size
     *
     * @return Download
     *
     * @deprecated since 5.5.9 and will be removed in 5.7 without alternative
     *
     * @todo remove in 5.7
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return float
     *
     * @deprecated since 5.5.9 and will be removed in 5.7. Use media_service to get size of file
     *
     * @todo remove in 5.7
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return ProductDownloadAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductDownloadAttribute|array|null $attribute
     *
     * @return Download
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductDownloadAttribute::class, 'attribute', 'articleDownload');
    }
}
