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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

class Payment extends Extendable
{
    /**
     * Unique identifier of the payment struct
     *
     * @var int
     */
    protected $id;

    /**
     * Contains an alphanumeric payment name.
     *
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
     * @var int
     */
    protected $hide;

    /**
     * @var string
     */
    protected $additionalDescription;

    /**
     * @var string
     */
    protected $debitPercent;

    /**
     * @var string
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
     * @var int
     */
    protected $active;

    /**
     * @var int
     */
    protected $esdActive;

    /**
     * @var int
     */
    protected $embediframe;

    /**
     * @var int
     */
    protected $hideProspect;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $pluginID;

    /**
     * @var int
     */
    protected $source;

    /**
     * @var int
     */
    protected $mobileInactive;

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
     * @return int
     */
    public function getHide()
    {
        return $this->hide;
    }

    /**
     * @param int $hide
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
     * @return string
     */
    public function getDebitPercent()
    {
        return $this->debitPercent;
    }

    /**
     * @param string $debitPercent
     */
    public function setDebitPercent($debitPercent)
    {
        $this->debitPercent = $debitPercent;
    }

    /**
     * @return string
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * @param string $surcharge
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
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getEsdActive()
    {
        return $this->esdActive;
    }

    /**
     * @param int $esdActive
     */
    public function setEsdActive($esdActive)
    {
        $this->esdActive = $esdActive;
    }

    /**
     * @return int
     */
    public function getEmbediframe()
    {
        return $this->embediframe;
    }

    /**
     * @param int $embediframe
     */
    public function setEmbediframe($embediframe)
    {
        $this->embediframe = $embediframe;
    }

    /**
     * @return int
     */
    public function getHideProspect()
    {
        return $this->hideProspect;
    }

    /**
     * @param int $hideProspect
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
    public function getPluginID()
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID($pluginID)
    {
        $this->pluginID = $pluginID;
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
     * @return int
     */
    public function getMobileInactive()
    {
        return $this->mobileInactive;
    }

    /**
     * @param int $mobileInactive
     */
    public function setMobileInactive($mobileInactive)
    {
        $this->mobileInactive = $mobileInactive;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
