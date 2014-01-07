<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

include(dirname(__FILE__)."/SimpleDom.php");

/**
 *  Shopware XML Component
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 */
class Shopware_Components_Xml_SimpleXml
{
    /** XML - File
     * @var $filename
     */
    protected $filename;

    /**
     * @var
     */
    protected $namespace;

    /**
     * @var
     */
    public $SimpleXML;

    /**
     * Class Constructor - supports chaining
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * @param  $node
     * @param array $filter
     * @return
     */
    public function getXmlAtNode($node,$filter= array())
    {
        if ($this->SimpleXML->getName()==$node) {
            return $this->SimpleXML;
        }
        foreach ($this->SimpleXML->children() as $child) {
            if ($child->getName()==$node) {
                if (!empty($filter["attribute"])) {
                    $attributes = $child->attributes();
                    $name = (array) $attributes[$filter["attribute"]];
                    $name = $name[0];
                    if ($name == $filter["value"]) {
                        return $child;
                    }
                    continue;
                } else {
                    return $child;
                }
            }
        }
    }

    /**
     * Set namespace for this xml
     * @param  $namespace
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get namespace from this xml
     * @return
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @throws Exception
     * @param  $file
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function loadFile($file)
    {
        if (!is_file($file)) {
            throw new Exception("File $file not found");
        }
        $this->filename = $file;
        $this->SimpleXML = simpledom_load_file($file);
        return $this;
    }

    /**
     * Create new xml
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function create()
    {
        $this->SimpleXML = new SimpleDOM('<'.$this->getNamespace().'/>');
        return $this;
    }

    /**
     * @throws Exception
     * @param SimpleDOM $root
     * @param  $node
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function set(SimpleDOM $root,$node)
    {
        if (!is_array($node)) {
            throw new Exception("\$node is not an array");
        }

        if (!empty($node["@attributes"])) {
            $attributes = $node["@attributes"];
            unset($node["@attributes"]);
        } else {
            $attributes = array();
        }
        foreach ($node as $key => $value) {

            if (!empty($value["@attributes"])) {
                $attributes = $value["@attributes"];
            } else {
                $attributes = array();
            }

            if (is_string($value)) {
                $root->addChild($key,$value);
            } elseif (is_array($value)) {
                $childNode = $root->addChild($key);
                if (isset($attributes)) {
                    foreach ($attributes as $attrKey => $attrValue) {
                        $childNode->addAttribute($attrKey,$attrValue);
                    }
                    unset($attributes);
                }
                $this->set($childNode,$value);
            }

        }
        return $this;
    }

    /**
     * Set filename for xml
     * @param  $filename
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Save xml and make it beautiful
     * @return Shopware_Components_Xml_SimpleXml
     */
    public function save()
    {
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->SimpleXML->asXML());

        file_put_contents($this->filename,$dom->saveXML());
        return $this;
    }

    /**
     * Check if a certain attribute exists in panel
     * @param  $node
     * @param  $attribut
     * @param  $value
     * @return bool
     */
    public function attributeExists($node,$attribut,$value)
    {
        $panels = $this->getXmlAtNode($node);
        foreach ($panels as $panel) {
            $attributes = $panel->attributes();
            $name = (array) $attributes[$attribut];
            $name = $name[0];
            if ($name == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert a xml-node to array
     * @param  $node
     * @return string
     */
    public function nodeToArray($node)
    {
        // Read attributes
        $result = "";
        if (method_exists($node,"attributes")) {
        $attributes = (array) $node->attributes();
        if (isset($attributes["@attributes"])) {
            foreach ($attributes["@attributes"] as $key => $value) {
                $node->removeAttribute($key);
                $result[$key] = $value;
            }
        }
        }

        $checkTyp = (array) $node;
        if (isset($checkTyp[0]) && is_string($checkTyp[0])) {
            return (string) $checkTyp[0];
        }
        // Read childs
        foreach ($node as $key => $value) {
            if (is_object($value)) {
                $value = $this->nodeToArray($value);
            }
            if (isset($result[$key])) {
                $key .= md5(uniqid(rand()));
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Print out xml
     * @return void
     */
    public function render()
    {
         echo $this->SimpleXML->asXML();
    }
}
