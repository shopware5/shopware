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

namespace Shopware\Models\Newsletter;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware Data model represents a non-customer shop visitor contact data.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_campaigns_maildata")
 */
class ContactData extends ModelEntity
{
    /**
     * The actual email address
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    protected $email;

    /**
     * ID of the newsletter-group this data belongs to
     *
     * @var int
     *
     * @ORM\Column(name="groupID", type="integer", nullable=false)
     */
    protected $groupId;

    /**
     * @var string
     *
     * @ORM\Column(name="salutation", type="string", nullable=true)
     */
    protected $salutation;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", nullable=true)
     */
    protected $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    protected $city;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    protected $added;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="double_optin_confirmed", type="datetime", nullable=false)
     */
    protected $doubleOptinConfirmed;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    protected $deleted;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @param \DateTimeInterface $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \DateTimeInterface $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDoubleOptinConfirmed()
    {
        return $this->doubleOptinConfirmed;
    }

    /**
     * @param \DateTimeInterface $doubleOptinConfirmed
     */
    public function setDoubleOptinConfirmed($doubleOptinConfirmed)
    {
        $this->doubleOptinConfirmed = $doubleOptinConfirmed;
    }
}
