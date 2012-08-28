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
 * Shopware\Models\Attribute\Article
 *
 * @ORM\Table(name="s_articles_attributes")
 * @ORM\Entity
 */
class Article extends ModelEntity
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
     * @var string $attr1
     *
     * @ORM\Column(name="attr1", type="string", length=255, nullable=true)
     */
    private $attr1 = '0';

    /**
     * @var string $attr2
     *
     * @ORM\Column(name="attr2", type="string", length=255, nullable=true)
     */
    private $attr2 = '0';

    /**
     * @var string $attr3
     *
     * @ORM\Column(name="attr3", type="string", length=255, nullable=true)
     */
    private $attr3 = '0';

    /**
     * @var string $attr4
     *
     * @ORM\Column(name="attr4", type="string", length=255, nullable=true)
     */
    private $attr4 = null;

    /**
     * @var string $attr5
     *
     * @ORM\Column(name="attr5", type="string", length=255, nullable=true)
     */
    private $attr5 = null;

    /**
     * @var string $attr6
     *
     * @ORM\Column(name="attr6", type="string", length=255, nullable=true)
     */
    private $attr6 = null;

    /**
     * @var string $attr7
     *
     * @ORM\Column(name="attr7", type="string", length=255, nullable=true)
     */
    private $attr7 = null;

    /**
     * @var string $attr8
     *
     * @ORM\Column(name="attr8", type="string", length=255, nullable=true)
     */
    private $attr8 = '0';

    /**
     * @var string $attr9
     *
     * @ORM\Column(name="attr9", type="text", nullable=true)
     */
    private $attr9 = null;

    /**
     * @var string $attr10
     *
     * @ORM\Column(name="attr10", type="text", nullable=true)
     */
    private $attr10 = null;

    /**
     * @var string $attr11
     *
     * @ORM\Column(name="attr11", type="string", length=200, nullable=true)
     */
    private $attr11 = null;

    /**
     * @var string $attr12
     *
     * @ORM\Column(name="attr12", type="string", length=200, nullable=true)
     */
    private $attr12 = null;

    /**
     * @var string $attr13
     *
     * @ORM\Column(name="attr13", type="string", length=255, nullable=true)
     */
    private $attr13 = '0';

    /**
     * @var string $attr14
     *
     * @ORM\Column(name="attr14", type="string", length=200, nullable=true)
     */
    private $attr14 = null;

    /**
     * @var string $attr15
     *
     * @ORM\Column(name="attr15", type="string", length=30, nullable=true)
     */
    private $attr15 = null;

    /**
     * @var string $attr16
     *
     * @ORM\Column(name="attr16", type="string", length=30, nullable=true)
     */
    private $attr16 = null;

    /**
     * @var date $attr17
     *
     * @ORM\Column(name="attr17", type="date", nullable=true)
     */
    private $attr17 = null;

    /**
     * @var string $attr18
     *
     * @ORM\Column(name="attr18", type="text", nullable=true)
     */
    private $attr18 = null;

    /**
     * @var string $attr19
     *
     * @ORM\Column(name="attr19", type="string", length=255, nullable=true)
     */
    private $attr19 = null;

    /**
     * @var string $attr20
     *
     * @ORM\Column(name="attr20", type="string", length=20, nullable=true)
     */
    private $attr20 = null;

    /**
     * @var integer $articleId
     *
     * @ORM\Column(name="articleID", type="integer", nullable=true)
     */
    private $articleId = null;

    /**
     * @var Shopware\Models\Article\Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     * })
     */
    private $article;

    /**
     * @var integer $articleDetailId
     *
     * @ORM\Column(name="articledetailsID", type="integer", nullable=true)
     */
    private $articleDetailId = null;

    /**
     * @var Shopware\Models\Article\Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="articledetailsID", referencedColumnName="id")
     * })
     */
    private $articleDetail;

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
     * Set attr1
     *
     * @param string $attr1
     * @return Article
     */
    public function setAttr1($attr1)
    {
        $this->attr1 = $attr1;
        return $this;
    }

    /**
     * Get attr1
     *
     * @return string
     */
    public function getAttr1()
    {
        return $this->attr1;
    }

    /**
     * Set attr2
     *
     * @param string $attr2
     * @return Article
     */
    public function setAttr2($attr2)
    {
        $this->attr2 = $attr2;
        return $this;
    }

    /**
     * Get attr2
     *
     * @return string
     */
    public function getAttr2()
    {
        return $this->attr2;
    }

    /**
     * Set attr3
     *
     * @param string $attr3
     * @return Article
     */
    public function setAttr3($attr3)
    {
        $this->attr3 = $attr3;
        return $this;
    }

    /**
     * Get attr3
     *
     * @return string
     */
    public function getAttr3()
    {
        return $this->attr3;
    }

    /**
     * Set attr4
     *
     * @param string $attr4
     * @return Article
     */
    public function setAttr4($attr4)
    {
        $this->attr4 = $attr4;
        return $this;
    }

    /**
     * Get attr4
     *
     * @return string
     */
    public function getAttr4()
    {
        return $this->attr4;
    }

    /**
     * Set attr5
     *
     * @param string $attr5
     * @return Article
     */
    public function setAttr5($attr5)
    {
        $this->attr5 = $attr5;
        return $this;
    }

    /**
     * Get attr5
     *
     * @return string
     */
    public function getAttr5()
    {
        return $this->attr5;
    }

    /**
     * Set attr6
     *
     * @param string $attr6
     * @return Article
     */
    public function setAttr6($attr6)
    {
        $this->attr6 = $attr6;
        return $this;
    }

    /**
     * Get attr6
     *
     * @return string
     */
    public function getAttr6()
    {
        return $this->attr6;
    }

    /**
     * Set attr7
     *
     * @param string $attr7
     * @return Article
     */
    public function setAttr7($attr7)
    {
        $this->attr7 = $attr7;
        return $this;
    }

    /**
     * Get attr7
     *
     * @return string
     */
    public function getAttr7()
    {
        return $this->attr7;
    }

    /**
     * Set attr8
     *
     * @param string $attr8
     * @return Article
     */
    public function setAttr8($attr8)
    {
        $this->attr8 = $attr8;
        return $this;
    }

    /**
     * Get attr8
     *
     * @return string
     */
    public function getAttr8()
    {
        return $this->attr8;
    }

    /**
     * Set attr9
     *
     * @param text $attr9
     * @return Article
     */
    public function setAttr9($attr9)
    {
        $this->attr9 = $attr9;
        return $this;
    }

    /**
     * Get attr9
     *
     * @return text
     */
    public function getAttr9()
    {
        return $this->attr9;
    }

    /**
     * Set attr10
     *
     * @param text $attr10
     * @return Article
     */
    public function setAttr10($attr10)
    {
        $this->attr10 = $attr10;
        return $this;
    }

    /**
     * Get attr10
     *
     * @return text
     */
    public function getAttr10()
    {
        return $this->attr10;
    }

    /**
     * Set attr11
     *
     * @param string $attr11
     * @return Article
     */
    public function setAttr11($attr11)
    {
        $this->attr11 = $attr11;
        return $this;
    }

    /**
     * Get attr11
     *
     * @return string
     */
    public function getAttr11()
    {
        return $this->attr11;
    }

    /**
     * Set attr12
     *
     * @param string $attr12
     * @return Article
     */
    public function setAttr12($attr12)
    {
        $this->attr12 = $attr12;
        return $this;
    }

    /**
     * Get attr12
     *
     * @return string
     */
    public function getAttr12()
    {
        return $this->attr12;
    }

    /**
     * Set attr13
     *
     * @param string $attr13
     * @return Article
     */
    public function setAttr13($attr13)
    {
        $this->attr13 = $attr13;
        return $this;
    }

    /**
     * Get attr13
     *
     * @return string
     */
    public function getAttr13()
    {
        return $this->attr13;
    }

    /**
     * Set attr14
     *
     * @param string $attr14
     * @return Article
     */
    public function setAttr14($attr14)
    {
        $this->attr14 = $attr14;
        return $this;
    }

    /**
     * Get attr14
     *
     * @return string
     */
    public function getAttr14()
    {
        return $this->attr14;
    }

    /**
     * Set attr15
     *
     * @param string $attr15
     * @return Article
     */
    public function setAttr15($attr15)
    {
        $this->attr15 = $attr15;
        return $this;
    }

    /**
     * Get attr15
     *
     * @return string
     */
    public function getAttr15()
    {
        return $this->attr15;
    }

    /**
     * Set attr16
     *
     * @param string $attr16
     * @return Article
     */
    public function setAttr16($attr16)
    {
        $this->attr16 = $attr16;
        return $this;
    }

    /**
     * Get attr16
     *
     * @return string
     */
    public function getAttr16()
    {
        return $this->attr16;
    }

    /**
     * Set attr17
     *
     * @param date $attr17
     * @return Article
     */
    public function setAttr17($attr17)
    {
        $this->attr17 = $attr17;
        return $this;
    }

    /**
     * Get attr17
     *
     * @return date
     */
    public function getAttr17()
    {
        return $this->attr17;
    }

    /**
     * Set attr18
     *
     * @param text $attr18
     * @return Article
     */
    public function setAttr18($attr18)
    {
        $this->attr18 = $attr18;
        return $this;
    }

    /**
     * Get attr18
     *
     * @return text
     */
    public function getAttr18()
    {
        return $this->attr18;
    }

    /**
     * Set attr19
     *
     * @param string $attr19
     * @return Article
     */
    public function setAttr19($attr19)
    {
        $this->attr19 = $attr19;
        return $this;
    }

    /**
     * Get attr19
     *
     * @return string
     */
    public function getAttr19()
    {
        return $this->attr19;
    }

    /**
     * Set attr20
     *
     * @param string $attr20
     * @return Article
     */
    public function setAttr20($attr20)
    {
        $this->attr20 = $attr20;
        return $this;
    }

    /**
     * Get attr20
     *
     * @return string
     */
    public function getAttr20()
    {
        return $this->attr20;
    }

    /**
     * Set article
     *
     * @param Shopware\Models\Article\Article $article
     * @return Article
     */
    public function setArticle(\Shopware\Models\Article\Article $article = null)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Get article
     *
     * @return Shopware\Models\Article\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set articleId
     *
     * @param integer $articleId
     * @return Article
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
        return $this;
    }

    /**
     * Get articleId
     *
     * @return integer
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * Set articleDetail
     *
     * @param Shopware\Models\Article\Detail $articleDetail
     * @return Article
     */
    public function setArticleDetail(\Shopware\Models\Article\Detail $articleDetail = null)
    {
        $this->articleDetail = $articleDetail;
        return $this;
    }

    /**
     * Get articleDetail
     *
     * @return Shopware\Models\Article\Detail
     */
    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    /**
     * Set articleDetailId
     *
     * @param integer $articleDetailId
     * @return Article
     */
    public function setArticleDetailId($articleDetailId)
    {
        $this->articleDetailId = $articleDetailId;
        return $this;
    }

    /**
     * Get articleDetailId
     *
     * @return integer
     */
    public function getArticleDetailId()
    {
        return $this->articleDetailId;
    }
}
