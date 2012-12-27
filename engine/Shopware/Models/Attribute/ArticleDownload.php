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
 * @ORM\Table(name="s_articles_downloads_attributes")
 */
class ArticleDownload extends ModelEntity
{
    

    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
     protected $id;


    /**
     * @var integer $articleDownloadId
     *
     * @ORM\Column(name="downloadID", type="integer", nullable=true)
     */
     protected $articleDownloadId;


    /**
     * @var \Shopware\Models\Article\Download
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Download", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="downloadID", referencedColumnName="id")
     * })
     */
    protected $articleDownload;
    

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    

    public function getArticleDownloadId()
    {
        return $this->articleDownloadId;
    }

    public function setArticleDownloadId($articleDownloadId)
    {
        $this->articleDownloadId = $articleDownloadId;
        return $this;
    }
    

    public function getArticleDownload()
    {
        return $this->articleDownload;
    }

    public function setArticleDownload($articleDownload)
    {
        $this->articleDownload = $articleDownload;
        return $this;
    }
    
}