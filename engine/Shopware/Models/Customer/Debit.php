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

namespace   Shopware\Models\Customer;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware customer debit model represents a single debit of a customer.
 *
 * The Shopware customer debit model represents a row of the s_user_debit table.
 * One debit has the follows associations:
 * <code>
 *   - Customer =>  Shopware\Models\Customer\Customer [1:1] [s_user]
 * </code>
 * The user_debit table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_user_debit")
 */
class Debit extends ModelEntity
{
    /**
     * Unique identifier column
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the associated customer. Used as foreign key
     *
     * @var integer $customerId
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * Contains the account name
     *
     * @var string $account
     * @ORM\Column(name="account", type="string", length=30, nullable=false)
     */
    private $account = '';

    /**
     * Contains the code of the bank
     *
     * @var string $bankCode
     * @ORM\Column(name="bankcode", type="string", length=30, nullable=false)
     */
    private $bankCode = '';

    /**
     * Contains the name of the bank
     *
     * @var string $bankName
     * @ORM\Column(name="bankname", type="string", length=255, nullable=false)
     */
    private $bankName = '';

    /**
     * Contains the holder of the account
     *
     * @var string $accountHolder
     * @ORM\Column(name="bankholder", type="string", length=255, nullable=false)
     */
    private $accountHolder = '';

    /**
     * OWNING SIDE
     * The customer property is the owning side of the association between customer and debit.
     * The association is joined over the debit userID and the customer id
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer", inversedBy="debit")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     * @var \Shopware\Models\Customer\Customer
     */
    protected $customer;

    /**
     * Getter function for the unique id identifier property
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter function for the account column property
     *
     * @param string $account
     * @return Debit
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Getter function for the account column property
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Setter function for the bankCode column property
     *
     * @param string $bankCode
     * @return Debit
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
        return $this;
    }

    /**
     * Getter function for the bankCode column property
     *
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * Setter function for the bankName column property
     *
     * @param string $bankName
     * @return Debit
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
        return $this;
    }

    /**
     * Getter function for the bankName column property
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Setter function for the bankHolder column property
     * @param string $accountHolder
     * @return Debit
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
        return $this;
    }

    /**
     * Getter function for the accountHolder column property
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * Returns the instance of the Shopware\Models\Customer\Customer model which
     * contains all data about the customer. The association is defined over
     * the Customer.debit property (INVERSE SIDE) and the Debit.customer (OWNING SIDE) property.
     * The customer data is joined over the s_user.id field.
     *
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Setter function for the customer association property which contains an instance of the Shopware\Models\Customer\Customer model which
     * contains all data about the customer. The association is defined over
     * the Customer.debit property (INVERSE SIDE) and the Debit.customer (OWNING SIDE) property.
     * The customer data is joined over the s_user.id field.
     *
     * @param \Shopware\Models\Customer\Customer $customer
     * @return \Shopware\Models\Customer\Debit
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }
}
