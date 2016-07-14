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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Table(name="s_articles_information")
 * @ORM\Entity
 */
class Link extends ModelEntity
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
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     * @var
     */
    private $articleId;

    /**
     * @Assert\NotBlank
     *
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @Assert\Url
     *
     * @var string $link
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * @var string $target
     *
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    private $target = '_blank';

    /**
     * OWNING SIDE
     * @var \Shopware\Models\Article\Article
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="links")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     */
    protected $article;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleLink", mappedBy="articleLink", cascade={"persist"})
     * @var \Shopware\Models\Attribute\ArticleLink
     */
    protected $attribute;

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
     * Set article
     *
     * @param Article $article
     * @return Link
     */
    public function setArticle($article)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Get article id
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Link
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Link
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set target
     *
     * @param string $target
     * @return Link
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return \Shopware\Models\Attribute\ArticleLink
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\ArticleLink|array|null $attribute
     * @return \Shopware\Models\Attribute\ArticleLink
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\ArticleLink', 'attribute', 'articleLink');
    }
}
