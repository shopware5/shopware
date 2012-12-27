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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Attribute
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @author     shopware AG
 */


namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_attributes")
 */
class Article extends ModelEntity
{
    

    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
     protected $id;


    /**
     * @var integer $articleId
     *
     * @ORM\Column(name="articleID", type="integer", nullable=true)
     */
     protected $articleId;


    /**
     * @var integer $articleDetailId
     *
     * @ORM\Column(name="articledetailsID", type="integer", nullable=true)
     */
     protected $articleDetailId;


    /**
     * @var string $attr1
     *
     * @ORM\Column(name="attr1", type="string", nullable=true)
     */
     protected $attr1;


    /**
     * @var string $attr2
     *
     * @ORM\Column(name="attr2", type="string", nullable=true)
     */
     protected $attr2;


    /**
     * @var string $attr3
     *
     * @ORM\Column(name="attr3", type="string", nullable=true)
     */
     protected $attr3;


    /**
     * @var string $attr4
     *
     * @ORM\Column(name="attr4", type="string", nullable=true)
     */
     protected $attr4;


    /**
     * @var string $attr5
     *
     * @ORM\Column(name="attr5", type="string", nullable=true)
     */
     protected $attr5;


    /**
     * @var string $attr6
     *
     * @ORM\Column(name="attr6", type="string", nullable=true)
     */
     protected $attr6;


    /**
     * @var string $attr7
     *
     * @ORM\Column(name="attr7", type="string", nullable=true)
     */
     protected $attr7;


    /**
     * @var string $attr8
     *
     * @ORM\Column(name="attr8", type="string", nullable=true)
     */
     protected $attr8;


    /**
     * @var string $attr9
     *
     * @ORM\Column(name="attr9", type="text", nullable=true)
     */
     protected $attr9;


    /**
     * @var string $attr10
     *
     * @ORM\Column(name="attr10", type="text", nullable=true)
     */
     protected $attr10;


    /**
     * @var string $attr11
     *
     * @ORM\Column(name="attr11", type="string", nullable=true)
     */
     protected $attr11;


    /**
     * @var string $attr12
     *
     * @ORM\Column(name="attr12", type="string", nullable=true)
     */
     protected $attr12;


    /**
     * @var string $attr13
     *
     * @ORM\Column(name="attr13", type="string", nullable=true)
     */
     protected $attr13;


    /**
     * @var string $attr14
     *
     * @ORM\Column(name="attr14", type="string", nullable=true)
     */
     protected $attr14;


    /**
     * @var string $attr15
     *
     * @ORM\Column(name="attr15", type="string", nullable=true)
     */
     protected $attr15;


    /**
     * @var string $attr16
     *
     * @ORM\Column(name="attr16", type="string", nullable=true)
     */
     protected $attr16;


    /**
     * @var date $attr17
     *
     * @ORM\Column(name="attr17", type="date", nullable=true)
     */
     protected $attr17;


    /**
     * @var string $attr18
     *
     * @ORM\Column(name="attr18", type="text", nullable=true)
     */
     protected $attr18;


    /**
     * @var string $attr19
     *
     * @ORM\Column(name="attr19", type="string", nullable=true)
     */
     protected $attr19;


    /**
     * @var string $attr20
     *
     * @ORM\Column(name="attr20", type="string", nullable=true)
     */
     protected $attr20;


    /**
     * @var \Shopware\Models\Article\Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     * })
     */
    protected $article;
    

    /**
     * @var \Shopware\Models\Article\Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="articledetailsID", referencedColumnName="id")
     * })
     */
    protected $articleDetail;
    

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    

    public function getArticleId()
    {
        return $this->articleId;
    }

    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
        return $this;
    }
    

    public function getArticleDetailId()
    {
        return $this->articleDetailId;
    }

    public function setArticleDetailId($articleDetailId)
    {
        $this->articleDetailId = $articleDetailId;
        return $this;
    }
    

    public function getAttr1()
    {
        return $this->attr1;
    }

    public function setAttr1($attr1)
    {
        $this->attr1 = $attr1;
        return $this;
    }
    

    public function getAttr2()
    {
        return $this->attr2;
    }

    public function setAttr2($attr2)
    {
        $this->attr2 = $attr2;
        return $this;
    }
    

    public function getAttr3()
    {
        return $this->attr3;
    }

    public function setAttr3($attr3)
    {
        $this->attr3 = $attr3;
        return $this;
    }
    

    public function getAttr4()
    {
        return $this->attr4;
    }

    public function setAttr4($attr4)
    {
        $this->attr4 = $attr4;
        return $this;
    }
    

    public function getAttr5()
    {
        return $this->attr5;
    }

    public function setAttr5($attr5)
    {
        $this->attr5 = $attr5;
        return $this;
    }
    

    public function getAttr6()
    {
        return $this->attr6;
    }

    public function setAttr6($attr6)
    {
        $this->attr6 = $attr6;
        return $this;
    }
    

    public function getAttr7()
    {
        return $this->attr7;
    }

    public function setAttr7($attr7)
    {
        $this->attr7 = $attr7;
        return $this;
    }
    

    public function getAttr8()
    {
        return $this->attr8;
    }

    public function setAttr8($attr8)
    {
        $this->attr8 = $attr8;
        return $this;
    }
    

    public function getAttr9()
    {
        return $this->attr9;
    }

    public function setAttr9($attr9)
    {
        $this->attr9 = $attr9;
        return $this;
    }
    

    public function getAttr10()
    {
        return $this->attr10;
    }

    public function setAttr10($attr10)
    {
        $this->attr10 = $attr10;
        return $this;
    }
    

    public function getAttr11()
    {
        return $this->attr11;
    }

    public function setAttr11($attr11)
    {
        $this->attr11 = $attr11;
        return $this;
    }
    

    public function getAttr12()
    {
        return $this->attr12;
    }

    public function setAttr12($attr12)
    {
        $this->attr12 = $attr12;
        return $this;
    }
    

    public function getAttr13()
    {
        return $this->attr13;
    }

    public function setAttr13($attr13)
    {
        $this->attr13 = $attr13;
        return $this;
    }
    

    public function getAttr14()
    {
        return $this->attr14;
    }

    public function setAttr14($attr14)
    {
        $this->attr14 = $attr14;
        return $this;
    }
    

    public function getAttr15()
    {
        return $this->attr15;
    }

    public function setAttr15($attr15)
    {
        $this->attr15 = $attr15;
        return $this;
    }
    

    public function getAttr16()
    {
        return $this->attr16;
    }

    public function setAttr16($attr16)
    {
        $this->attr16 = $attr16;
        return $this;
    }
    

    public function getAttr17()
    {
        return $this->attr17;
    }

    public function setAttr17($attr17)
    {
        $this->attr17 = $attr17;
        return $this;
    }
    

    public function getAttr18()
    {
        return $this->attr18;
    }

    public function setAttr18($attr18)
    {
        $this->attr18 = $attr18;
        return $this;
    }
    

    public function getAttr19()
    {
        return $this->attr19;
    }

    public function setAttr19($attr19)
    {
        $this->attr19 = $attr19;
        return $this;
    }
    

    public function getAttr20()
    {
        return $this->attr20;
    }

    public function setAttr20($attr20)
    {
        $this->attr20 = $attr20;
        return $this;
    }
    

    public function getArticle()
    {
        return $this->article;
    }

    public function setArticle($article)
    {
        $this->article = $article;
        return $this;
    }
    

    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    public function setArticleDetail($articleDetail)
    {
        $this->articleDetail = $articleDetail;
        return $this;
    }
    
}