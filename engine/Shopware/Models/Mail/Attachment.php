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

namespace Shopware\Models\Mail;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware attachment model represents a single attachment
 *
 * Associations:
 * <code>
 *   - Shop => Shopware\Models\Shop\Shop    [n:1]   [s_core_shops]
 *   - Mail => Shopware\Models\Mail\Mail    [n:1]   [s_core_config_mails]
 *   - Mail => Shopware\Models\Media\Media  [n:1]   [s_core_config_mails]
 * </code>
 *
 * Indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `name` (`name`, `supportID`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_config_mails_attachments")
 * @ORM\HasLifecycleCallbacks()
 */
class Attachment extends File
{
    /**
     * The role property is the owning side of the association between attachment and shop.
     *
     * @var \Shopware\Models\Shop\Shop
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shopID", referencedColumnName="id", nullable=true)
     */
    protected $shop;

    /**
     * Bidirectional
     * The role property is the owning side of the association between attachment and mail.
     *
     * @var \Shopware\Models\Mail\Mail
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Mail\Mail", inversedBy="attachments")
     * @ORM\JoinColumn(name="mailID", referencedColumnName="id")
     */
    protected $mail;

    public function __construct(\Shopware\Models\Mail\Mail $mail, \Shopware\Models\Media\Media $media, \Shopware\Models\Shop\Shop $shop = null)
    {
        $this->mail = $mail;
        $this->media = $media;
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return \Shopware\Models\Media\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return int|null
     */
    public function getShopId()
    {
        if ($this->getShop() !== null) {
            return $this->getShop()->getId();
        }

        return null;
    }

    public function setShop(\Shopware\Models\Shop\Shop $shop)
    {
        $this->shop = $shop;
    }
}
