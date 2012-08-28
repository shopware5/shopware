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
 * Shopware\Models\Attribute\ArticleEsd
 *
 * @ORM\Table(name="s_articles_esd_attributes")
 * @ORM\Entity
 */
class ArticleEsd extends ModelEntity
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
     * @var integer $articleEsdId
     *
     * @ORM\Column(name="esdID", type="integer", nullable=true)
     */
    private $articleEsdId = null;

    /**
     * @var Shopware\Models\Article\Esd
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Esd", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="esdID", referencedColumnName="id")
     * })
     */
    private $articleEsd;

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
     * Set articleEsd
     *
     * @param Shopware\Models\Article\Esd $articleEsd
     * @return ArticleEsd
     */
    public function setArticleEsd(\Shopware\Models\Article\Esd $articleEsd = null)
    {
        $this->articleEsd = $articleEsd;
        return $this;
    }

    /**
     * Get articleEsd
     *
     * @return Shopware\Models\Article\Esd
     */
    public function getArticleEsd()
    {
        return $this->articleEsd;
    }

    /**
     * Set articleEsdId
     *
     * @param integer $articleEsdId
     * @return ArticleEsd
     */
    public function setArticleEsdId($articleEsdId)
    {
        $this->articleEsdId = $articleEsdId;
        return $this;
    }

    /**
     * Get articleEsdId
     *
     * @return integer
     */
    public function getArticleEsdId()
    {
        return $this->articleEsdId;
    }
}
