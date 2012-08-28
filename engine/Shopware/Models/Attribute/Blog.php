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
 * Shopware\Models\Attribute\Blog
 *
 * @ORM\Table(name="s_blog_attributes")
 * @ORM\Entity
 */
class Blog extends ModelEntity
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
     * @var string $attribute4
     *
     * @ORM\Column(name="attribute4", type="string", length=255, nullable=true)
     */
    private $attribute4 = null;

    /**
     * @var string $attribute5
     *
     * @ORM\Column(name="attribute5", type="string", length=255, nullable=true)
     */
    private $attribute5 = null;

    /**
     * @var string $attribute6
     *
     * @ORM\Column(name="attribute6", type="string", length=255, nullable=true)
     */
    private $attribute6 = null;

    /**
     * @var integer $blogId
     *
     * @ORM\Column(name="blog_id", type="integer", nullable=true)
     */
    private $blogId = null;

    /**
     * @var Shopware\Models\Blog\Blog
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Blog\Blog", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     * })
     */
    private $blog;

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
     * Set attribute1
     *
     * @param string $attribute1
     * @return Category
     */
    public function setAttribute1($attribute1)
    {
        $this->attribute1 = $attribute1;
        return $this;
    }
/**
     * Get attribute1
     ** @return string
     */
    public function getAttribute1()
    {
        return $this->attribute1;
    }

    /**
     * Set attribute2
     *
     * @param string $attribute2
     * @return Category
     */
    public function setAttribute2($attribute2)
    {
        $this->attribute2 = $attribute2;
        return $this;
    }

    /**
     * Get attribute2
     *
     * @return string
     */
    public function getAttribute2()
    {
        return $this->attribute2;
    }

    /**
     * Set attribute3
     *
     * @param string $attribute3
     * @return Category
     */
    public function setAttribute3($attribute3)
    {
        $this->attribute3 = $attribute3;
        return $this;
    }

    /**
     * Get attribute3
     *
     * @return string
     */
    public function getAttribute3()
    {
        return $this->attribute3;
    }

    /**
     * Set attribute4
     *
     * @param string $attribute4
     * @return Category
     */
    public function setAttribute4($attribute4)
    {
        $this->attribute4 = $attribute4;
        return $this;
    }

    /**
     * Get attribute4
     *
     * @return string
     */
    public function getAttribute4()
    {
        return $this->attribute4;
    }

    /**
     * Set attribute5
     *
     * @param string $attribute5
     * @return Category
     */
    public function setAttribute5($attribute5)
    {
        $this->attribute5 = $attribute5;
        return $this;
    }

    /**
     * Get attribute5
     *
     * @return string
     */
    public function getAttribute5()
    {
        return $this->attribute5;
    }

    /**
     * Set attribute6
     *
     * @param string $attribute6
     * @return Category
     */
    public function setAttribute6($attribute6)
    {
        $this->attribute6 = $attribute6;
        return $this;
    }

    /**
     * Get attribute6
     *
     * @return string
     */
    public function getAttribute6()
    {
        return $this->attribute6;
    }


    /**
     * @return \Shopware\Models\Blog\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set blog
     *
     * @param \Shopware\Models\Blog\Blog $blog
     * @return Blog
     */
    public function setBlog(\Shopware\Models\Blog\Blog $blog = null)
    {
        $this->blog = $blog;
        return $this;
    }
}
