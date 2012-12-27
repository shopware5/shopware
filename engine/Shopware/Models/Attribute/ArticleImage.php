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
 * @ORM\Table(name="s_articles_img_attributes")
 */
class ArticleImage extends ModelEntity
{
    

    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
     protected $id;


    /**
     * @var integer $articleImageId
     *
     * @ORM\Column(name="imageID", type="integer", nullable=true)
     */
     protected $articleImageId;


    /**
     * @var string $attribute1
     *
     * @ORM\Column(name="attribute1", type="string", nullable=false)
     */
     protected $attribute1;


    /**
     * @var string $attribute2
     *
     * @ORM\Column(name="attribute2", type="string", nullable=false)
     */
     protected $attribute2;


    /**
     * @var string $attribute3
     *
     * @ORM\Column(name="attribute3", type="string", nullable=false)
     */
     protected $attribute3;


    /**
     * @var \Shopware\Models\Article\Image
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Image", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imageID", referencedColumnName="id")
     * })
     */
    protected $articleImage;
    

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    

    public function getArticleImageId()
    {
        return $this->articleImageId;
    }

    public function setArticleImageId($articleImageId)
    {
        $this->articleImageId = $articleImageId;
        return $this;
    }
    

    public function getAttribute1()
    {
        return $this->attribute1;
    }

    public function setAttribute1($attribute1)
    {
        $this->attribute1 = $attribute1;
        return $this;
    }
    

    public function getAttribute2()
    {
        return $this->attribute2;
    }

    public function setAttribute2($attribute2)
    {
        $this->attribute2 = $attribute2;
        return $this;
    }
    

    public function getAttribute3()
    {
        return $this->attribute3;
    }

    public function setAttribute3($attribute3)
    {
        $this->attribute3 = $attribute3;
        return $this;
    }
    

    public function getArticleImage()
    {
        return $this->articleImage;
    }

    public function setArticleImage($articleImage)
    {
        $this->articleImage = $articleImage;
        return $this;
    }
    
}