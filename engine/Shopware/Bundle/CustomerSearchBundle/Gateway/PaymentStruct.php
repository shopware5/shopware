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

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class PaymentStruct extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $hide;

    /**
     * @var string
     */
    protected $additionalDescription;

    /**
     * @var float
     */
    protected $debitPercent;

    /**
     * @var float
     */
    protected $surcharge;

    /**
     * @var string
     */
    protected $surchargeString;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $allowEsd;

    /**
     * @var string
     */
    protected $embediframe;

    /**
     * @var bool
     */
    protected $hideProspect;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $pluginId;

    /**
     * @var int
     */
    protected $source;

    /**
     * @var bool
     */
    protected $allowOnMobile;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return bool
     */
    public function hide()
    {
        return $this->hide;
    }

    /**
     * @param bool $hide
     */
    public function setHide($hide)
    {
        $this->hide = $hide;
    }

    /**
     * @return string
     */
    public function getAdditionalDescription()
    {
        return $this->additionalDescription;
    }

    /**
     * @param string $additionalDescription
     */
    public function setAdditionalDescription($additionalDescription)
    {
        $this->additionalDescription = $additionalDescription;
    }

    /**
     * @return float
     */
    public function getDebitPercent()
    {
        return $this->debitPercent;
    }

    /**
     * @param float $debitPercent
     */
    public function setDebitPercent($debitPercent)
    {
        $this->debitPercent = $debitPercent;
    }

    /**
     * @return float
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * @param float $surcharge
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;
    }

    /**
     * @return string
     */
    public function getSurchargeString()
    {
        return $this->surchargeString;
    }

    /**
     * @param string $surchargeString
     */
    public function setSurchargeString($surchargeString)
    {
        $this->surchargeString = $surchargeString;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function allowEsd()
    {
        return $this->allowEsd;
    }

    /**
     * @param bool $esdActive
     */
    public function setAllowEsd($esdActive)
    {
        $this->allowEsd = $esdActive;
    }

    /**
     * @return string
     */
    public function getEmbediframe()
    {
        return $this->embediframe;
    }

    /**
     * @param string $embediframe
     */
    public function setEmbediframe($embediframe)
    {
        $this->embediframe = $embediframe;
    }

    /**
     * @return bool
     */
    public function hideProspect()
    {
        return $this->hideProspect;
    }

    /**
     * @param bool $hideProspect
     */
    public function setHideProspect($hideProspect)
    {
        $this->hideProspect = $hideProspect;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param int $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param int $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return bool
     */
    public function allowOnMobile()
    {
        return $this->allowOnMobile;
    }

    /**
     * @param bool $allowOnMobile
     */
    public function setAllowOnMobile($allowOnMobile)
    {
        $this->allowOnMobile = $allowOnMobile;
    }
}
