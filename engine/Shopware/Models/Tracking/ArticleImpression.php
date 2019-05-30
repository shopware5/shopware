<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Models\Tracking;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Article Impression Statistics
 * <br>
 * This Model represents the database table s_statistics_article_impression. This is used to track
 * every article impression. The clicks and impressions will be accumulated on a daily basis.
 *
 * Indices for s_statistics_article_impression:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - INDEX (articleId)
 * </code>
 *
 * @ORM\Table(name="s_statistics_article_impression")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks()
 */
class ArticleImpression extends ModelEntity
{
    /**
     * Autoincrement Identifier
     *
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * ID of the article which should be tracked
     *
     * @var int
     *
     * @ORM\Column(name="articleId", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * id of the shop which should be tracked
     *
     * @var int
     *
     * @ORM\Column(name="shopId", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * Accumulated number of impressions
     *
     * @var int
     *
     * @ORM\Column(name="impressions", type="integer", nullable=false)
     */
    private $impressions;

    /**
     * @var string
     *
     * @ORM\Column(name="deviceType", type="string", length=50, nullable=true)
     */
    private $deviceType;

    /**
     * @param int                     $articleId
     * @param int                     $shopId
     * @param \DateTimeInterface|null $date
     * @param int                     $impressions
     * @param string                  $deviceType
     */
    public function __construct($articleId, $shopId, $date = null, $impressions = 1, $deviceType = null)
    {
        if ($date === null) {
            $date = new \DateTime();
        }
        $this->setArticleId($articleId);
        $this->setShopId($shopId);
        $this->setDate($date);
        $this->setImpressions($impressions);
        $this->setDeviceType($deviceType);
    }

    /**
     * Get the Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the date
     *
     * @param \DateTimeInterface $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get the date
     *
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the articleId
     *
     * @param int $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * Get the articleId
     *
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * Set the shopId
     *
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * Get the shopId
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Set the impressions
     *
     * @param int $impressions
     */
    public function setImpressions($impressions)
    {
        $this->impressions = $impressions;
    }

    /**
     * Get the impressions
     *
     * @return int
     */
    public function getImpressions()
    {
        return $this->impressions;
    }

    /**
     * Increases the number of impressions
     *
     * @return \Shopware\Models\Tracking\ArticleImpression
     */
    public function increaseImpressions()
    {
        ++$this->impressions;

        return $this;
    }

    /**
     * @param string $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        return $this->deviceType;
    }
}
