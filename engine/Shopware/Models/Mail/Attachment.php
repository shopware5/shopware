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

namespace Shopware\Models\Mail;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Media\Media;
use Shopware\Models\Shop\Shop;

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
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shopID", referencedColumnName="id", nullable=true)
     */
    protected $shop;

    /**
     * Bidirectional
     * The role property is the owning side of the association between attachment and mail.
     *
     * @var Mail
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Mail\Mail", inversedBy="attachments")
     * @ORM\JoinColumn(name="mailID", referencedColumnName="id", nullable=false)
     */
    protected $mail;

    public function __construct(Mail $mail, Media $media, ?Shop $shop = null)
    {
        $this->mail = $mail;
        $this->media = $media;
        $this->shop = $shop;
    }

    /**
     * @return Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return Shop|null
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

    public function setShop(?Shop $shop = null)
    {
        $this->shop = $shop;
    }
}
