<?php
/**
 * Shopware 4.0 - Dispatch
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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Backend, Newsletter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

namespace   Shopware\Models\Newsletter;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\ORM\Mapping AS ORM;

/**
 * Shopware container model represents a newsletter container.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_campaigns_containers")
 */
class Container extends ModelEntity
{
    /**
     * Autoincrement ID
     *
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Newsletter ID
     *
     * @var integer $newsletterId
     *
     * @ORM\Column(name="promotionID", type="integer", length=11, nullable=true)
     */
    private $newsletterId = null;

    /**
     * value of the container
     *
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * Type of the container (sText, sBanner etc)
     *
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the association between the container and its child
     * As a container can have various child-types, one would probably want to have a additional
     * getChild() method returning the correct child depending on the containers type
     *
     * As right now only simple text-newsletters are supported, there is just the text-child
     *
     * @var \Shopware\Models\Newsletter\ContainerType\Text
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\ContainerType\Text", mappedBy="container", cascade={"persist", "update", "remove"})
     */
    protected $text;

    /**
     * Description of the container
     *
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * Position of the container - containers will be selected ordered by position
     *
     * @var integer $position
     * @ORM\Column(name="position", type="integer", length=11, nullable=false)
     */
    private $position = 0;

    /**
     * OWNING SIDE
     * Owning side of the newsletter-container association
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Newsletter\Newsletter", inversedBy="containers")
     * @ORM\JoinColumn(name="promotionID", referencedColumnName="id")
     * @var \Shopware\Models\Newsletter\Newsletter
     */
    protected $newsletter;

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
    public function getDescription()
    {
        return $this->description;
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
    public function getPosition()
    {
        return $this->position;
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
     * @param \Shopware\Models\Newsletter\Newsletter $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
//        $this->setManyToOne($newsletter, '\Shopware\Models\Newsletter\Newsletter', 'newsletter');
    }

    /**
     * @return \Shopware\Models\Newsletter\Newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \Shopware\Models\Newsletter\ContainerType\Text $text
     * @return \Shopware\Models\Newsletter\ContainerType\Text
     */
    public function setText($text)
    {
        $return = $this->setOneToOne($text, '\Shopware\Models\Newsletter\ContainerType\Text', 'text', 'container');
        $this->setType('ctText');
        return $return;
    }

    /**
     * @return \Shopware\Models\Newsletter\ContainerType\Text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}




