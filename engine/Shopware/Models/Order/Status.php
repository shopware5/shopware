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

namespace Shopware\Models\Order;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware order status model represents the status of an order (payment or order state).
 *
 * The Shopware order status model represents a row of the s_core_states table.
 * The s_core_states table has the follows indices:
 * <code>
 *  - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_states")
 */
class Status extends ModelEntity
{
    /**
     * Consts defining the group
     */
    const GROUP_STATE   = 'state';
    const GROUP_PAYMENT = 'payment';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string $description
     * @deprecated Use 'name' in conjunction with the 'backend/static/*' snippet namespaces instead
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string $group
     *
     * @ORM\Column(name="`group`", type="string", length=25, nullable=false)
     */
    private $group;

    /**
     * @var integer $sendMail
     *
     * @ORM\Column(name="mail", type="integer", nullable=false)
     */
    private $sendMail;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Mail\Mail", mappedBy="status")
     * @var \Shopware\Models\Mail\Mail
     */
    protected $mail;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set description
     * @deprecated Use getName() + snippets instead
     *
     * @param string $description
     * @return Status
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     * @deprecated Use getName() + snippets instead
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Status
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set group
     *
     * @param string $group
     * @return Status
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set sendMail
     *
     * @param integer $sendMail
     * @return Status
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
        return $this;
    }

    /**
     * Get sendMail
     *
     * @return integer
     */
    public function getSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param \Shopware\Models\Mail\Mail|array|null $mail
     * @return \Shopware\Models\Mail\Mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return $this;
    }
}
