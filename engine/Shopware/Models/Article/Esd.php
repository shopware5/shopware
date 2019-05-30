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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ArticleEsd as ProductEsdAttribute;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_articles_esd")
 * @ORM\HasLifecycleCallbacks()
 */
class Esd extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var ProductEsdAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleEsd", mappedBy="articleEsd", cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\EsdSerial", mappedBy="esd")
     */
    protected $serials;

    /**
     * OWNING SIDE
     *
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="esds")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     */
    protected $article;

    /**
     * OWNING SIDE
     *
     * @var Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="esd")
     * @ORM\JoinColumn(name="articleDetailsID", referencedColumnName="id")
     */
    protected $articleDetail;

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
     * @var int
     *
     * @ORM\Column(name="articleDetailsID", type="integer", nullable=false)
     */
    private $articleDetailId;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    private $file = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="serials", type="boolean", nullable=false)
     */
    private $hasSerials = false;

    /**
     * @var bool notification
     *
     * @ORM\Column(name="notification", type="boolean", nullable=false)
     */
    private $notification = false;

    /**
     * @var int
     *
     * @ORM\Column(name="maxdownloads", type="integer", nullable=false)
     */
    private $maxdownloads = 0;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     */
    private $date;

    public function __construct()
    {
        $this->serials = new ArrayCollection();
    }

    /**
     * @return ProductEsdAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductEsdAttribute|array|null $attribute
     *
     * @return Esd
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductEsdAttribute::class, 'attribute', 'articleEsd');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ArrayCollection $serials
     */
    public function setSerials($serials)
    {
        $this->serials = $serials;
    }

    /**
     * @return ArrayCollection
     */
    public function getSerials()
    {
        return $this->serials;
    }

    /**
     * @param Article $article
     *
     * @throws \RuntimeException
     */
    public function setArticle($article)
    {
        throw new \RuntimeException('Article should be set implicit with setArticleDetail');
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    public function setArticleDetail(Detail $articleDetail)
    {
        $this->articleDetail = $articleDetail;
        $this->article = $articleDetail->getArticle();
    }

    /**
     * @return Detail
     */
    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    /**
     * @param \DateTimeInterface|string|null $date
     *
     * @return Esd
     */
    public function setDate($date = null)
    {
        if ($date !== null && !($date instanceof \DateTimeInterface)) {
            $this->date = new \DateTime($date);
        } else {
            $this->date = $date;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param bool $hasSerials
     */
    public function setHasSerials($hasSerials)
    {
        $this->hasSerials = $hasSerials;
    }

    /**
     * @return bool
     */
    public function getHasSerials()
    {
        return $this->hasSerials;
    }

    /**
     * @param int $maxdownloads
     */
    public function setMaxdownloads($maxdownloads)
    {
        $this->maxdownloads = $maxdownloads;
    }

    /**
     * @return int
     */
    public function getMaxdownloads()
    {
        return $this->maxdownloads;
    }

    /**
     * @param bool $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return bool
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Sets date on pre persist
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->date = new \DateTime('now');
    }
}
