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

namespace Shopware\Models\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;

/**
 * Shopware Notification Model
 *
 * This is the model for s_articles_notification table.
 * The model contains a single row of s_articles_notification.
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_notification")
 * @ORM\HasLifecycleCallbacks
 */
class Notification extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="notifications")
     * @ORM\JoinColumn(name="ordernumber", referencedColumnName="ordernumber")
     */
    protected $articleDetail;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Customer", inversedBy="notifications")
     * @ORM\JoinColumn(name="mail", referencedColumnName="email")
     */
    protected $customer;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=false)
     */
    private $articleNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255, nullable=false)
     */
    private $mail;

    /**
     * @var int
     *
     * @ORM\Column(name="send", type="integer", nullable=false)
     */
    private $send;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="shoplink", type="string", length=255, nullable=false)
     */
    private $shopLink;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Notification
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set send
     *
     * @param int $send
     *
     * @return Notification
     */
    public function setSend($send)
    {
        $this->send = $send;

        return $this;
    }

    /**
     * Get send
     *
     * @return int
     */
    public function getSend()
    {
        return $this->send;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Notification
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set shopLink
     *
     * @param string $shopLink
     *
     * @return Notification
     */
    public function setShopLink($shopLink)
    {
        $this->shopLink = $shopLink;

        return $this;
    }

    /**
     * Get shopLink
     *
     * @return string
     */
    public function getShopLink()
    {
        return $this->shopLink;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getArticleDetail()
    {
        return $this->fetchLazy($this->articleDetail, ['number' => $this->articleNumber]);
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->fetchLazy($this->customer, ['email' => $this->mail]);
    }
}
