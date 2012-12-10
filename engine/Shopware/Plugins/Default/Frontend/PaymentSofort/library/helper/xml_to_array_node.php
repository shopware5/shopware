<?php

/**
 * 
 * XML To Array Node
 *
 */
class XmlToArrayNode {
	
	private $_attributes = array();
	private $_children = array();
	private $_data = '';
	private $_name = '';
	private $_open = true;
	private $_ParentXmlToArrayNode = null;
	
	
	/**
	 * 
	 * Constructor for XmlToArrayNode
	 * @param string $name
	 * @param array $attributes
	 */
	public function __construct($name, $attributes) {
		$this->_name = $name;
		$this->_attributes = $attributes;
	}
	
	
	/**
	 * 
	 * Add a child to collection
	 * @param XmlToArrayNode $XmlToArrayNode
	 */
	public function addChild(XmlToArrayNode $XmlToArrayNode) {
		$this->_children[] = $XmlToArrayNode;
	}
	
	
	/**
	 * 
	 * Getter for data, returns an array
	 */
	public function getData() {
		return $this->_data;
	}
	
	
	/**
	 * 
	 * Getter for name, returns the name
	 */
	public function getName() {
		return $this->_name;
	}
	
	
	/**
	 * 
	 * Getter for parent node
	 */
	public function getParentXmlToArrayNode() {
		return $this->_ParentXmlToArrayNode;
	}
	
	
	/**
	 * 
	 * Does it have any children
	 */
	public function hasChildren() {
		return count($this->_children);
	}
	
	
	/**
	 * 
	 * Does it have a node
	 */
	public function hasParentXmlToArrayNode() {
		return $this->_ParentXmlToArrayNode instanceof XmlToArrayNode;
	}
	
	
	/**
	 * 
	 * Is it open, returns _open
	 */
	public function isOpen() {
		return $this->_open;
	}
	
	
	/**
	 * Renders nodes as array
	 * @param bool $simpleStructure pass true to get an array without @data and @attributes fields
	 * @throws XmlToArrayException
	 */
	public function render($simpleStructure) {
		$array = array();
		$multiples = array();
		
		foreach ($this->_children as $Child) {
			$multiples[$Child->getName()] = isset($multiples[$Child->getName()]) ? $multiples[$Child->getName()] + 1 : 0;
		}
		
		foreach ($this->_children as $Child) {
			if ($multiples[$Child->getName()]) {
				if ($simpleStructure && !$Child->hasChildren()) {
					$array[$Child->getName()][] = $Child->getData();
				} else {
					$array[$Child->getName()][] = $Child->render($simpleStructure);
				}
			} elseif ($simpleStructure && !$Child->hasChildren()) {
				$array[$Child->getName()] = $Child->getData();
			} else {
				$array[$Child->getName()] = $Child->render($simpleStructure);
			}
		}
		
		if (!$simpleStructure) {
			$array['@data'] = $this->_data;
			$array['@attributes'] = $this->_attributes;
		}
		
		return $this->_ParentXmlToArrayNode instanceof XmlToArrayNode
			? $array
			: array($this->_name => $simpleStructure && !$this->hasChildren() ? $this->getData() : $array);
	}
	
	
	/**
	 * 
	 * Set it to closed
	 */
	public function setClosed() {
		$this->_open = false;
	}
	
	
	/**
	 * 
	 * Setter for variable data
	 * @param string $data
	 */
	public function setData($data) {
		$this->_data .= $data;
	}
	
	
	/**
	 * 
	 * Setter for parent node
	 * @param XmlToArrayNode $XmlToArrayNode
	 */
	public function setParentXmlToArrayNode(XmlToArrayNode $XmlToArrayNode) {
		$this->_ParentXmlToArrayNode = $XmlToArrayNode;
	}
}
?>