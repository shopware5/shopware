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


namespace Shopware\Models\Translation;

use Shopware\Models\Shop\Shop;
use Shopware\Components\Model\ModelEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Translation
 *
 * @category  Shopware
 * @package   Shopware\Models
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_translations")
 */
class Translation extends ModelEntity
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
     * @var string $name
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objecttype", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string $name
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectdata", type="string", nullable=false)
     */
    private $data;

    /**
     * @var int $key
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectkey", type="integer", nullable=false)
     */
    private $key;

    /**
     * Foreign-Key for the local Association.
     * Has no getter and setter function to prevent inconsistent data
     * @var integer $shopId
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="objectlanguage", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var Shop
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="objectlanguage", referencedColumnName="id")
     */
    protected $shop;

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }
}
