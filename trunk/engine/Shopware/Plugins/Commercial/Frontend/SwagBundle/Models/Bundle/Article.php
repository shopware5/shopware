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

namespace Shopware\CustomModels\Bundle;
use Shopware\Components\Model\ModelEntity,
 Doctrine\ORM\Mapping AS ORM,
 Symfony\Component\Validator\Constraints as Assert,
 Doctrine\Common\Collections\ArrayCollection;

/**
 * Bundle article model.
 * This model is used to assign an article/variant to the bundle as new bundle article position.
 * All assigned articles are defines the whole bundle.
 * The different article position can be de-/selected if the bundle is defined as selectedable bundle.
 * The bundle discount and the bundle price are depend to the defined bundle article positions.
 * The main article on which the bundle are defined isn't defined as Shopware\CustomModels\Bundle\Article.
 * To get the main article as normal bundle position use the Shopware_Components_Bundle::getBundleMainArticle
 * function.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Models\Bundle
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_bundles_articles")
 */
class Article extends ModelEntity
{
    /**
     * Unique identifier for a single bundle article
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The article detail id of the selected article variant.
     * Can be defined over the backend module.
     * Used as foreign key for the article detail association.
     * Has no getter and setter. Only defined to have access on the order number in queries without joining the s_articles_details.
     *
     * @ORM\Column(name="article_detail_id", type="integer", nullable=false)
     * @var string $articleDetailId
     */
    private $articleDetailId;

    /**
     * Id of the bundle.
     * Used as foreign key for the bundle association.
     * Has no getter and setter.
     * Only defined to have access on the bundle id in queries without joining the s_articles_bundles.
     *
     * @ORM\Column(name="bundle_id", type="integer", nullable=false)
     * @var integer $bundleId
     */
    private $bundleId;

    /**
     * Flag for configurator articles.
     * If the configurable flag is set to true, the customer can configure the article variant in the store front like a
     * normal configurator article over the groups and options.
     * If the configurable flag is set to false, the customer has no opportunity to configure the article variant in the
     * frontend.
     *
     * @ORM\Column(name="configurable", type="boolean", nullable=false)
     * @var boolean
     */
    private $configurable = false;

    /**
     * Contains the quantity of the bundled article.
     * The bundle article quantity can be configured over the backend module.
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     * @var int $quantity
     */
    private $quantity = 1;


    /**
     * The $bundle property contains the instance of \Shopware\CustomModels\Bundle\Bundle of the parent bundle.
     *
     * @ORM\ManyToOne(targetEntity="Shopware\CustomModels\Bundle\Bundle", inversedBy="articles")
     * @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     * @var \Shopware\CustomModels\Bundle\Bundle
     */
    protected $bundle;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="article_detail_id", referencedColumnName="id")
     * @var \Shopware\Models\Article\Detail
     */
    protected $articleDetail;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function getConfigurable()
    {
        return $this->configurable;
    }

    /**
     * @param boolean $configurable
     */
    public function setConfigurable($configurable)
    {
        $this->configurable = $configurable;
    }

    /**
     * @return \Shopware\CustomModels\Bundle\Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param \Shopware\CustomModels\Bundle\Bundle $bundle
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    /**
     * @param \Shopware\Models\Article\Detail $articleDetail
     */
    public function setArticleDetail($articleDetail)
    {
        $this->articleDetail = $articleDetail;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }
}
