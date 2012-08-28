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
 * Shopware\Models\Attribute\Site
 *
 * @ORM\Table(name="s_cms_static_attributes")
 * @ORM\Entity
 */
class Site extends ModelEntity
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
     * @var integer $siteId
     *
     * @ORM\Column(name="cmsStaticID", type="integer", nullable=true)
     */
    private $siteId = null;

    /**
     * @var Shopware\Models\Site\Site
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Site\Site", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmsStaticID", referencedColumnName="id")
     * })
     */
    private $site;

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
     * Set site
     *
     * @param Shopware\Models\Site\Site $site
     * @return Site
     */
    public function setSite(\Shopware\Models\Site\Site $site = null)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * Get site
     *
     * @return Shopware\Models\Site\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set siteId
     *
     * @param integer $siteId
     * @return Site
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * Get siteId
     *
     * @return integer
     */
    public function getSiteId()
    {
        return $this->siteId;
    }
}
