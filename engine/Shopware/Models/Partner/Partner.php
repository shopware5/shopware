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

namespace Shopware\Models\Partner;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Standard Export Model Entity
 *
 * @ORM\Table(name="s_emarketing_partner")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Partner extends ModelEntity
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
     * @var string $idCode
     *
     * @ORM\Column(name="idcode", type="string", length=255, nullable=false)
     */
    private $idCode;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="datum", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string $company
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=false)
     */
    private $company;

    /**
     * @var string $contact
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=false)
     */
    private $contact;

    /**
     * @var string $street
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    private $street;

    /**
     * @var string $zipCode
     *
     * @ORM\Column(name="zipCode", type="string", length=15, nullable=false)
     */
    private $zipCode;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=50, nullable=false)
     */
    private $phone;

    /**
     * @var string $fax
     *
     * @ORM\Column(name="fax", type="string", length=50, nullable=false)
     */
    private $fax;

    /**
     * @var string $countryName
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=false)
     */
    private $countryName;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var string $web
     *
     * @ORM\Column(name="web", type="string", length=255, nullable=false)
     */
    private $web;

    /**
     * @var string $profile
     *
     * @ORM\Column(name="profil", type="text", nullable=false)
     */
    private $profile;

    /**
     * @var float $fix
     *
     * @ORM\Column(name="fix", type="float", nullable=false)
     */
    private $fix = 0;

    /**
     * @var float $percent
     *
     * @ORM\Column(name="percent", type="float", nullable=false)
     */
    private $percent = 0;

    /**
     * @var integer $cookieLifeTime
     *
     * @ORM\Column(name="cookieLifeTime", type="integer", nullable=false)
     */
    private $cookieLifeTime = 0;

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active = 0;

    /**
     * @var integer $customerId
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $customerId;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Order", mappedBy="partner")
     * @ORM\JoinColumn(name="idcode", referencedColumnName="partnerID")
     */
    private $orders;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Partner", mappedBy="partner", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Partner
     */
    protected $attribute;

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
     * Set idCode
     *
     * @param string $idCode
     * @return Partner
     */
    public function setIdCode($idCode)
    {
        $this->idCode = $idCode;

        return $this;
    }

    /**
     * Get idCode
     *
     * @return string
     */
    public function getIdCode()
    {
        return $this->idCode;
    }

    /**
     * Set datum
     *
     * @param \DateTime|string $date
     * @return Partner
     */
    public function setDate($date)
    {
        if ($date !== null && !($date instanceof \DateTime)) {
            $this->date = new \DateTime($date);
        } else {
            $this->date = $date;
        }
        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Partner
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return Partner
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return Partner
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     * @return Partner
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Partner
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Partner
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Partner
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set country
     *
     * @param string $countryName
     * @return Partner
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Partner
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set web
     *
     * @param string $web
     * @return Partner
     */
    public function setWeb($web)
    {
        $this->web = $web;

        return $this;
    }

    /**
     * Get web
     *
     * @return string
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * Set profile
     *
     * @param string $profile
     * @return Partner
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set fix
     *
     * @param float $fix
     * @return Partner
     */
    public function setFix($fix)
    {
        $this->fix = $fix;

        return $this;
    }

    /**
     * Get fix
     *
     * @return float
     */
    public function getFix()
    {
        return $this->fix;
    }

    /**
     * Set percent
     *
     * @param float $percent
     * @return Partner
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set cookieLifeTime
     *
     * @param integer $cookieLifeTime
     * @return Partner
     */
    public function setCookieLifeTime($cookieLifeTime)
    {
        $this->cookieLifeTime = $cookieLifeTime;

        return $this;
    }

    /**
     * Get cookieLifeTime
     *
     * @return integer
     */
    public function getCookieLifeTime()
    {
        return $this->cookieLifeTime;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return Partner
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get orders
     *
     * @return mixed
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set orders
     * @param $orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Set customerId
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Get customerId
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return \Shopware\Models\Attribute\Partner
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Partner|array|null $attribute
     * @return \Shopware\Models\Partner\Partner
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Partner', 'attribute', 'partner');
    }
}
