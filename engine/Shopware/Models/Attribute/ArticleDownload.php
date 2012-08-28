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
 * Shopware\Models\Attribute\ArticleDownload
 *
 * @ORM\Table(name="s_articles_downloads_attributes")
 * @ORM\Entity
 */
class ArticleDownload extends ModelEntity
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
     * @var integer $articleDownloadId
     *
     * @ORM\Column(name="downloadID", type="integer", nullable=true)
     */
    private $articleDownloadId = null;

    /**
     * @var Shopware\Models\Article\Download
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Download", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="downloadID", referencedColumnName="id")
     * })
     */
    private $articleDownload;

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
     * Set articleDownload
     *
     * @param Shopware\Models\Article\Download $articleDownload
     * @return ArticleDownload
     */
    public function setArticleDownload(\Shopware\Models\Article\Download $articleDownload = null)
    {
        $this->articleDownload = $articleDownload;
        return $this;
    }

    /**
     * Get articleDownload
     *
     * @return Shopware\Models\Article\Download
     */
    public function getArticleDownload()
    {
        return $this->articleDownload;
    }

    /**
     * Set articleDownloadId
     *
     * @param integer $articleDownloadId
     * @return ArticleDownload
     */
    public function setArticleDownloadId($articleDownloadId)
    {
        $this->articleDownloadId = $articleDownloadId;
        return $this;
    }

    /**
     * Get articleDownloadId
     *
     * @return integer
     */
    public function getArticleDownloadId()
    {
        return $this->articleDownloadId;
    }
}
