<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2011-2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     $Author$
 */
namespace   Shopware\Models\Emotion\Library;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\ORM\Mapping AS ORM;

/**
 *
 * Associations:
 * <code>
 *
 * </code>
 *
 *
 * Indices:
 * <code>
 *
 * </code>
 *
 * @category   Shopware
 * @package    Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_library_component")
 */
class Component extends ModelEntity
{
    /**
     * Unique identifier field of the grid model.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    /**
     *
     *
     * @var string $convertFunction
     *
     * @ORM\Column(name="convert_function", type="string", length=255, nullable=true)
     */
    private $convertFunction = null;
    /**
     * Contains the component description which displayed in the backend
     * module of
     * @var
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Contains the template file which used to display the component data.
     *
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * Contains the css class for the component
     * @var string $cls
     * @ORM\Column(name="cls", type="string", length=255, nullable=false)
     */
    private $cls;

    /**
     * The xType for the backend module.
     *
     * @var string $xType
     *
     * @ORM\Column(name="x_type", type="string", length=255, nullable=false)
     */
    private $xType;

    /**
     * Contains the plugin id which added this component to the library
     * @var integer $pluginId
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId = null;

    /**
     * INVERSE SIDE
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Field", mappedBy="component", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $fields;

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Library\Field models.
     * Each component has a field configuration to configure the component data over the
     * backend module. For example: The "Article" component has an "id" field
     * with xtype: 'emotion-article-search' (the shopware article suggest search with a individual configuration for the
     * backend module) to configure which article has to been displayed.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $fields
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setFields($fields)
    {
        return $this->setOneToMany($fields, '\Shopware\Models\Emotion\Library\Field', 'fields', 'component');
    }

    /**
     * Unique identifier field of the grid model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Contains the name of the grid which can be configured in the
     * backend emotion module.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Contains the component description which displayed in the backend
     * module of
     * @return
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Contains the component description which displayed in the backend
     * module of
     * @param  $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Contains the template file which used to display the component data.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Contains the template file which used to display the component data.
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getCls()
    {
        return $this->cls;
    }

    /**
     * @param string $cls
     */
    public function setCls($cls)
    {
        $this->cls = $cls;
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
     * @return string
     */
    public function getXType()
    {
        return $this->xType;
    }

    /**
     * @param string $xType
     */
    public function setXType($xType)
    {
        $this->xType = $xType;
    }

    /**
     * @return string
     */
    public function getConvertFunction()
    {
        return $this->convertFunction;
    }

    /**
     * @param string $convertFunction
     */
    public function setConvertFunction($convertFunction)
    {
        $this->convertFunction = $convertFunction;
    }
}
