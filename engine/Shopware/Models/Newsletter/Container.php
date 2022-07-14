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

namespace Shopware\Models\Newsletter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Newsletter\ContainerType\Article;
use Shopware\Models\Newsletter\ContainerType\Banner;
use Shopware\Models\Newsletter\ContainerType\Link;
use Shopware\Models\Newsletter\ContainerType\Text;

/**
 * Shopware container model represents a newsletter container.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_campaigns_containers")
 */
class Container extends ModelEntity
{
    public const TYPE_TEXT = 'ctText';
    public const TYPE_PRODUCTS = 'ctArticles';
    public const TYPE_BANNER = 'ctBanner';
    public const TYPE_LINKS = 'ctLinks';
    public const TYPE_VOUCHER = 'ctVoucher';
    public const TYPE_SUGGEST = 'ctSuggest';

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its text-child
     *
     * @var Text|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\ContainerType\Text", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $text;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its banner child
     *
     * @var Banner|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\ContainerType\Banner", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $banner;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its link children
     *
     * @var ArrayCollection<Link>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\ContainerType\Link", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $links;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its article children
     *
     * @var ArrayCollection<Article>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\ContainerType\Article", mappedBy="container", cascade={"persist",  "remove"})
     */
    protected $articles;

    /**
     * OWNING SIDE
     * Owning side of the newsletter-container association
     *
     * @var Newsletter|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Newsletter\Newsletter", inversedBy="containers")
     * @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     */
    protected $newsletter;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Newsletter ID
     *
     * @var int|null
     *
     * @ORM\Column(name="promotionID", type="integer", length=11, nullable=true)
     */
    private $newsletterId;

    /**
     * value of the container
     *
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * Type of the container (sText, sBanner etc)
     *
     * @var self::TYPE_*
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * Description of the container
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * Position of the container - containers will be selected ordered by position
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", length=11, nullable=false)
     */
    private $position = 0;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param self::TYPE_* $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return self::TYPE_*
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Newsletter $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return Newsletter|null
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Text|null $text
     *
     * @return Container
     */
    public function setText($text)
    {
        $return = $this->setOneToOne($text, Text::class, 'text', 'container');
        $this->setType(self::TYPE_TEXT);

        return $return;
    }

    /**
     * @return Text|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param Article[] $articles
     *
     * @return Container
     */
    public function setArticles($articles)
    {
        $return = $this->setOneToMany($articles, Article::class, 'articles', 'container');
        $this->setType(self::TYPE_PRODUCTS);

        return $return;
    }

    /**
     * @return ArrayCollection<Article>
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param Banner|null $banner
     *
     * @return Container
     */
    public function setBanner($banner)
    {
        $return = $this->setOneToOne($banner, Banner::class, 'banner', 'container');
        $this->setType(self::TYPE_BANNER);

        return $return;
    }

    /**
     * @return Banner|null
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param Link[]|null $links
     *
     * @return Container
     */
    public function setLinks($links)
    {
        $return = $this->setOneToMany($links, Link::class, 'links', 'container');
        $this->setType(self::TYPE_LINKS);

        return $return;
    }

    /**
     * @return ArrayCollection<Link>
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
