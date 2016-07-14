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

namespace Shopware\Models\Plugin;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="s_core_licenses")
 * @ORM\Entity
 */
class License extends ModelEntity
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var
     * @ORM\Column(name="module", type="string", nullable=false)
     */
    private $module;
    /**
     * @var
     * @ORM\Column(name="host", type="string", nullable=false)
     */
    private $host;
    /**
     * @var
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;

    /**
     * @var
     * @ORM\Column(name="license", type="string", nullable=false)
     */
    private $license;

    /**
     * @var
     * @ORM\Column(name="version", type="string", nullable=false)
     */
    private $version;
    /**
     * @var
     * @ORM\Column(name="notation", type="string", nullable=false)
     */
    private $notation;

    /**
     * @var
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var
     * @ORM\Column(name="source", type="integer", nullable=false)
     */
    private $source;

    /**
     * @var
     * @ORM\Column(name="added", type="date", nullable=true)
     */
    private $added;

    /**
     * @var
     * @ORM\Column(name="creation", type="date", nullable=true)
     */
    private $creation;

    /**
     * @var
     * @ORM\Column(name="expiration", type="date", nullable=true)
     */
    private $expiration;

    /**
     * @var
     * @ORM\Column(name="active", type="integer", nullable=true)
     */
    private $active;

    /**
     * @var
     * @ORM\Column(name="plugin_id", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="licenses")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    private $plugin;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param  $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param  $creation
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    }

    /**
     * @return
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param  $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param  $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param  $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param  $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @return
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param  $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param  $notation
     */
    public function setNotation($notation)
    {
        $this->notation = $notation;
    }

    /**
     * @return
     */
    public function getNotation()
    {
        return $this->notation;
    }

    /**
     * @param  $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param  $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param  $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return
     */
    public function getVersion()
    {
        return $this->version;
    }
}
