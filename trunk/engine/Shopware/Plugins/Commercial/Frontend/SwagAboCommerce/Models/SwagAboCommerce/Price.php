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

namespace Shopware\CustomModels\SwagAboCommerce;

use Doctrine\ORM\Mapping AS ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware SwagAboCommerce Plugin - Price Model
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBundle\Models
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_swag_abo_commerce_prices")
 */
class Price extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var integer $aboArticleId
     *
     * @ORM\Column(name="abo_article_id", type="integer", nullable=true)
     */
    private $aboArticleId;

    /**
     * @var integer $customerGroupId
     *
     * @ORM\Column(name="customer_group_id", type="integer", nullable=true)
     */
    private $customerGroupId = 1;

    /**
     * @var float $price
     *
     * @ORM\Column(name="discount_absolute", type="float", nullable=false)
     */
    private $dicountAbsolute;

    /**
     * @var float $price
     *
     * @ORM\Column(name="discount_percent", type="float", nullable=false)
     */
    private $dicountPercent;

    /**
     * @var integer $durationFrom
     *
     * @ORM\Column(name="duration_from", type="integer", nullable=false)
     */
    private $durationFrom;

    /**
     * @var \Shopware\CustomModels\SwagAboCommerce\Article
     *
     * @ORM\ManyToOne(targetEntity="Shopware\CustomModels\SwagAboCommerce\Article", inversedBy="prices")
     * @ORM\JoinColumn(name="abo_article_id", referencedColumnName="id")
     */
    private $aboArticle;

//    /**
//     * @var \Shopware\Models\Customer\Group
//     *
//     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
//     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
//     */
//    private $customerGroup;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Shopware\CustomModels\SwagAboCommerce\Article $aboArticle
     *
     * @return Price
     */
    public function setAboArticle($aboArticle)
    {
        $this->aboArticle = $aboArticle;

        return $this;
    }

    /**
     * @return \Shopware\CustomModels\SwagAboCommerce\Article
     */
    public function getAboArticle()
    {
        return $this->aboArticle;
    }

    /**
     * @param int $aboArticleId
     *
     * @return Price
     */
    public function setAboArticleId($aboArticleId)
    {
        $this->aboArticleId = $aboArticleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAboArticleId()
    {
        return $this->aboArticleId;
    }

//    /**
//     * @param \Shopware\Models\Customer\Group $customerGroup
//     *
//     * @return Price
//     */
//    public function setCustomerGroup($customerGroup)
//    {
//        $this->customerGroup = $customerGroup;
//
//        return $this;
//    }
//
//    /**
//     * @return \Shopware\Models\Customer\Group
//     */
//    public function getCustomerGroup()
//    {
//        return $this->customerGroup;
//    }

    /**
     * @param int $customerGroupId
     *
     * @return Price
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @param float $dicountAbsolute
     *
     * @return Price
     */
    public function setDicountAbsolute($dicountAbsolute)
    {
        $this->dicountAbsolute = $dicountAbsolute;

        return $this;
    }

    /**
     * @return float
     */
    public function getDicountAbsolute()
    {
        return $this->dicountAbsolute;
    }

    /**
     * @param float $dicountPercent
     *
     * @return Price
     */
    public function setDicountPercent($dicountPercent)
    {
        $this->dicountPercent = $dicountPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getDicountPercent()
    {
        return $this->dicountPercent;
    }

    /**
     * @param int $durationFrom
     *
     * @return Price
     */
    public function setDurationFrom($durationFrom)
    {
        $this->durationFrom = $durationFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getDurationFrom()
    {
        return $this->durationFrom;
    }
}
