<?php
require_once('array_to_xml_exception.php');

/**
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 * 
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */


/**
 * 
 * Array To XML conversion
 *
 */
class ArrayToXml {
	/**
	 * Maximum allowed depth
	 * @var int
	 */
	private $_maxDepth = 4;
	
	/**
	 * Represents the parsed array structure
	 * @var string
	 */
	private $_parsedData = null;
	
	
	/**
	 * Loads array into XML representation.
	 * @param array $input
	 * @param int $maxDepth
	 * @throws ArrayToXmlException
	 */
	public function __construct(array $input, $maxDepth = 10, $trim = true) {
		if ($maxDepth > 50) {
			throw new ArrayToXmlException('Max depth too high.');
		}
		
		$this->_maxDepth = $maxDepth;
		
		if (count($input) == 1) {
			$tagName = key($input);
			$SofortTag = new SofortTag($tagName, $this->_extractAttributesSection($input[$tagName]), $this->_extractDataSection($input[$tagName], $trim));
			$this->_render($input[$tagName], $SofortTag, 1, $trim);
			$this->_parsedData = $SofortTag->render();
		} elseif(!$input) {
			$this->_parsedData = '';
		} else {
			throw new ArrayToXmlException('No valid input.');
		}
	}
	
	
	/**
	 * Returns parsed array as XML structure
	 * Pass both params as null to exclude prolog <?xml version="version" encoding="encoding" ?>
	 * @param string $version
	 * @param string $encoding
	 */
	public function toXml($version = '1.0', $encoding = 'UTF-8') {
		return !$version && !$encoding
			? $this->_parsedData
			: "<?xml version=\"{$version}\" encoding=\"{$encoding}\" ?>\n{$this->_parsedData}";
	}
	
	
	/**
	 * static entry point. Options are:
	 *  - version: (default 1.0) version string to put in xml prolog
	 *  - encoding: (default UTF-8) use the specified encoding
	 *  - trim: (default true) Trim values
	 *  - depth: (default 10) Maximum depth to parse the given array, throws exception when exceeded
	 *
	 * @param array $input the input array
	 * @param array $options set additional options to pass to XmlToArray
	 * @throws ArrayToXmlException
	 */
	public static function render(array $input, array $options = array()) {
		$options = array_merge(array(
				'version' => '1.0',
				'encoding' => 'UTF-8',
				'trim' => true,
				'depth' => 10,
			),
			$options
		);
		
		$Instance = new ArrayToXml($input, $options['depth'], $options['trim']);
		return $Instance->toXml($options['version'], $options['encoding']);
	}
	
	
	/**
	 * Checks if current depth is exceeded
	 * @param int $currentDepth
	 * @throws ArrayToXmlException if depth is exceeded
	 */
	private function _checkDepth($currentDepth) {
		if ($this->_maxDepth && $currentDepth > $this->_maxDepth) {
			throw new ArrayToXmlException("Max depth ({$this->_maxDepth}) exceeded");
		}
	}
	
	
	/**
	 * Creates a new XML node
	 * @param string $name
	 * @param array $attributes
	 * @param array $children
	 * @return Tag
	 */
	private function _createNode($name, array $attributes, array $children) {
		return new SofortTag($name, $attributes, $children);
	}
	
	
	/**
	 * Creates a new text node
	 * @param string $text
	 * @param bool $trim
	 * @return Text
	 */
	private function _createTextNode($text, $trim) {
		return new SofortText($text, true, $trim);
	}
	
	
	/**
	 * Extracts the attributes section from a XmlToArray'd structure
	 * @param mixed $node
	 * @return array
	 */
	private function _extractAttributesSection(&$node) {
		$attributes = array();
		
		if (is_array($node) && isset($node['@attributes']) && $node['@attributes']) {
			$attributes = is_array($node['@attributes']) ? $node['@attributes'] : array($node['@attributes']);
			unset($node['@attributes']);
		} elseif (is_array($node) && isset($node['@attributes'])) {
			unset($node['@attributes']);
		}
		
		return $attributes;
	}
	
	
	/**
	 * Extracts the data section from a XmlToArray'd structure
	 * @param mixed $node
	 * @return array
	 */
	private function _extractDataSection(&$node, $trim) {
		$children = array();
		
		if (is_array($node) && isset($node['@data']) && $node['@data']) {
			$children = array($this->_createTextNode($node['@data'], $trim));
			unset($node['@data']);
		} elseif (is_array($node) && isset($node['@data'])) {
			unset($node['@data']);
		}
		
		return $children;
	}
	
	
	/**
	 * Recursivly renders a XmlToArray'd structure into an object notation
	 * @param mixed $input
	 * @param Tag $ParentTag
	 * @param int $currentDepth
	 * @param bool $trim
	 * @throws ArrayToXmlException if depth is exceeded
	 */
	private function _render($input, SofortTag $ParentTag, $currentDepth, $trim) {
		$this->_checkDepth($currentDepth);
		
		if (is_array($input)) {
			foreach ($input as $tagName => $data) {
				$attributes = $this->_extractAttributesSection($data);
				
				if (is_array($data) && is_int(key($data))) {
					$this->_checkDepth($currentDepth+1);
					
					foreach ($data as $line) {
						if (is_array($line)) {
							$Tag = $this->_createNode($tagName, $this->_extractAttributesSection($line), $this->_extractDataSection($line, $trim));
							$ParentTag->children[] = $Tag;
							$this->_render($line, $Tag, $currentDepth+1, $trim);
						} else {
							$ParentTag->children[] = $this->_createNode($tagName, $attributes, array($this->_createTextNode($line, $trim)));
						}
					}
				} elseif (is_array($data)) {
					$Tag = $this->_createNode($tagName, $attributes, $this->_extractDataSection($data, $trim));
					$ParentTag->children[] = $Tag;
					$this->_render($data, $Tag, $currentDepth+1, $trim);
				} elseif (is_numeric($tagName)) {
					$ParentTag->children[] = $this->_createTextNode($data, $trim);
				} else {
					$ParentTag->children[] = $this->_createNode($tagName, $attributes, array($this->_createTextNode($data, $trim)));
				}
			}
			
			return;
		}
		
		$ParentTag->children[] = $this->_createTextNode($input, $trim);
	}
}

?>