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
 * Shopware\Models\Attribute\Mail
 *
 * @ORM\Table(name="s_core_config_mails_attributes")
 * @ORM\Entity
 */
class Mail extends ModelEntity
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
     * @var integer $mailId
     *
     * @ORM\Column(name="mailID", type="integer", nullable=true)
     */
    private $mailId = null;

    /**
     * @var Shopware\Models\Mail\Mail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Mail\Mail", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mailID", referencedColumnName="id")
     * })
     */
    private $mail;

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
     * Set mail
     *
     * @param Shopware\Models\Mail\Mail $mail
     * @return Mail
     */
    public function setMail(\Shopware\Models\Mail\Mail $mail = null)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * Get mail
     *
     * @return Shopware\Models\Mail\Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set mailId
     *
     * @param integer $mailId
     * @return Mail
     */
    public function setMailId($mailId)
    {
        $this->mailId = $mailId;
        return $this;
    }

    /**
     * Get mailId
     *
     * @return integer
     */
    public function getMailId()
    {
        return $this->mailId;
    }
}
