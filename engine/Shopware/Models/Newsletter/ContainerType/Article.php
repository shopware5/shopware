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

namespace Shopware\Models\Newsletter\ContainerType;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;

/**
 * Shopware text model represents a text container type.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_campaigns_articles")
 */
class Article extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     * Owning side of relation between container type 'article' and parent container
     *
     * @var \Shopware\Models\Newsletter\Container
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Newsletter\Container", inversedBy="articles")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    protected $container;

    /**
     * OWNING SIDE
     * Owning side of the uni-direction relation between article-Container and article ordernumber
     *
     * @var \Shopware\Models\Article\Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="articleordernumber", referencedColumnName="ordernumber")
     */
    protected $articleDetail;

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
     * ID of the container this model belongs to
     *
     * @var int
     *
     * @ORM\Column(name="parentID", type="integer", length=11, nullable=true)
     */
    private $containerId = null;

    /**
     * Ordernumber of the product
     *
     * @var string
     *
     * @ORM\Column(name="articleordernumber", type="string", length=255, nullable=false)
     */
    private $number = '';

    /**
     * Name of the article
     * "Zufall" for random articles - else the product's name
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=16777215, nullable=false)
     */
    private $name;

    /**
     * Type of the container - "random" or "fix"
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * Position of this container
     *
     * @var int
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=false)
     */
    private $position;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Shopware\Models\Newsletter\Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
        $container->setType('ctArticles');
    }

    /**
     * @return \Shopware\Models\Newsletter\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @param \Shopware\Models\Article\Detail $articleDetail
     */
    public function setArticleDetail($articleDetail)
    {
        $this->articleDetail = $articleDetail;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getArticleDetail()
    {
        /** @var \Shopware\Models\Article\Detail $detail */
        $detail = $this->fetchLazy($this->articleDetail, ['number' => $this->number]);

        return $detail;
    }
}
