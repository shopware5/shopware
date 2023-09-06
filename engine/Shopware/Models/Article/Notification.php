<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Article;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;
use Shopware\Models\Attribute\ArticleNotification as ProductNotificationAttribute;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware Notification Model
 *
 * This is the model for s_articles_notification table.
 * The model contains a single row of s_articles_notification.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_articles_notification")
 * @ORM\HasLifecycleCallbacks()
 */
class Notification extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="notifications")
     * @ORM\JoinColumn(name="ordernumber", referencedColumnName="ordernumber", nullable=false)
     */
    protected $articleDetail;

    /**
     * OWNING SIDE
     *
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Customer", inversedBy="notifications")
     * @ORM\JoinColumn(name="mail", referencedColumnName="email", nullable=false)
     */
    protected $customer;

    /**
     * INVERSE SIDE
     *
     * @var ProductNotificationAttribute|null
     *
     * @Assert\Valid()
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleNotification", mappedBy="articleNotification", cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
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
     * @var DateTimeInterface
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Notification
     */
    public function setDate(DateTimeInterface $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
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
     * @return int
     */
    public function getSend()
    {
        return $this->send;
    }

    /**
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
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
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
     * @return string
     */
    public function getShopLink()
    {
        return $this->shopLink;
    }

    /**
     * @param string $articleNumber
     */
    public function setArticleNumber($articleNumber)
    {
        $this->articleNumber = $articleNumber;
    }

    /**
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @return ProductNotificationAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param array|ProductNotificationAttribute|array|null $attribute
     *
     * @return Notification
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductNotificationAttribute::class, 'attribute', 'articleNotification');
    }

    /**
     * @return Detail
     */
    public function getArticleDetail()
    {
        /** @var Detail $return */
        $return = $this->fetchLazy($this->articleDetail, ['number' => $this->articleNumber]);

        return $return;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        /** @var Customer $return */
        $return = $this->fetchLazy($this->customer, ['email' => $this->mail]);

        return $return;
    }
}
