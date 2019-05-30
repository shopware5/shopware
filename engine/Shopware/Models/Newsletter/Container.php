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
    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its text-child
     *
     * @var \Shopware\Models\Newsletter\ContainerType\Text
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\ContainerType\Text", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $text;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its banner child
     *
     * @var \Shopware\Models\Newsletter\ContainerType\Banner
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\ContainerType\Banner", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $banner;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its link children
     *
     * @var ArrayCollection<\Shopware\Models\Newsletter\ContainerType\Link>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\ContainerType\Link", mappedBy="container", cascade={"persist", "remove"})
     */
    protected $links;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its article children
     *
     * @var ArrayCollection<\Shopware\Models\Newsletter\ContainerType\Article>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\ContainerType\Article", mappedBy="container", cascade={"persist",  "remove"})
     */
    protected $articles;

    /**
     * OWNING SIDE
     * Owning side of the newsletter-container association
     *
     * @var \Shopware\Models\Newsletter\Newsletter
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
     * @var int
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
     * @var string
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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopware\Models\Newsletter\Newsletter $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return \Shopware\Models\Newsletter\Newsletter
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
     * @param \Shopware\Models\Newsletter\ContainerType\Text $text
     *
     * @return \Shopware\Models\Newsletter\ContainerType\Text
     */
    public function setText($text)
    {
        /** @var \Shopware\Models\Newsletter\ContainerType\Text $return */
        $return = $this->setOneToOne($text, Text::class, 'text', 'container');
        $this->setType('ctText');

        return $return;
    }

    /**
     * @return \Shopware\Models\Newsletter\ContainerType\Text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param \Shopware\Models\Newsletter\ContainerType\Article[] $articles
     *
     * @return \Shopware\Models\Newsletter\ContainerType\Article
     */
    public function setArticles($articles)
    {
        /** @var \Shopware\Models\Newsletter\ContainerType\Article $return */
        $return = $this->setOneToMany($articles, Article::class, 'articles', 'container');
        $this->setType('ctArticles');

        return $return;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Newsletter\ContainerType\Article>
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param \Shopware\Models\Newsletter\ContainerType\Banner|null $banner
     *
     * @return \Shopware\Models\Newsletter\ContainerType\Banner
     */
    public function setBanner($banner)
    {
        /** @var \Shopware\Models\Newsletter\ContainerType\Banner $return */
        $return = $this->setOneToOne($banner, Banner::class, 'banner', 'container');
        $this->setType('ctBanner');

        return $return;
    }

    /**
     * @return \Shopware\Models\Newsletter\ContainerType\Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param \Shopware\Models\Newsletter\ContainerType\Link[]|null $links
     *
     * @return \Shopware\Models\Newsletter\ContainerType\Link
     */
    public function setLinks($links)
    {
        /** @var \Shopware\Models\Newsletter\ContainerType\Link $return */
        $return = $this->setOneToMany($links, Link::class, 'links', 'container');
        $this->setType('ctLinks');

        return $return;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Newsletter\ContainerType\Link>
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
