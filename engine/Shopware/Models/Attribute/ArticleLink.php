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
 * Shopware\Models\Attribute\ArticleLink
 *
 * @ORM\Table(name="s_articles_information_attributes")
 * @ORM\Entity
 */
class ArticleLink extends ModelEntity
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
     * @var integer $articleLinkId
     *
     * @ORM\Column(name="informationID", type="integer", nullable=true)
     */
    private $articleLinkId = null;

    /**
     * @var Shopware\Models\Article\Link
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Link", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="informationID", referencedColumnName="id")
     * })
     */
    private $articleLink;

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
     * Set articleLink
     *
     * @param Shopware\Models\Article\Link $articleLink
     * @return ArticleLink
     */
    public function setArticleLink(\Shopware\Models\Article\Link $articleLink = null)
    {
        $this->articleLink = $articleLink;
        return $this;
    }

    /**
     * Get articleLink
     *
     * @return Shopware\Models\Article\Link
     */
    public function getArticleLink()
    {
        return $this->articleLink;
    }

    /**
     * Set articleLinkId
     *
     * @param integer $articleLinkId
     * @return ArticleLink
     */
    public function setArticleLinkId($articleLinkId)
    {
        $this->articleLinkId = $articleLinkId;
        return $this;
    }

    /**
     * Get articleLinkId
     *
     * @return integer
     */
    public function getArticleLinkId()
    {
        return $this->articleLinkId;
    }
}
