<?php
/**
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 * 
 * Copyright (c) 2012 SOFORT AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */
 
require_once('xml_to_array_exception.php');
require_once('xml_to_array_node.php');

/**
 *
 * XML To Array conversion
 */
class XmlToArray {
	
	/**
	 * Holds start tags in a row.
	 * Used for error reporting and counting the current depth
	 * @var array
	 */
	private $_tagStack = array();
	
	/**
	 * Reference to the current node the parser is at
	 * @var XmlToArrayNode
	 */
	private $_CurrentXmlToArrayNode = null;
	
	/**
	 * Object reference for logging purposes
	 * @var Object
	 */
	private $_Object = null;
	
	/**
	 * stop parsing when maxDepth is exceeded, defaults to no maximum (=0).
	 *
	 * @var $_maxDepth int
	 */
	private $_maxDepth = 0;
	
	private static $_htmlEntityExceptions = array(
		'&euro;' => '€',
	);
	
	/**
	 * Loads XML into array representation.
	 * @param string $input
	 * @param int $maxDepth
	 * @throws XmlToArrayException
	 */
	public function __construct($input, $maxDepth = 20) {
		if (!is_string($input)) throw new XmlToArrayException('No valid input.');
		$this->_maxDepth = $maxDepth;
		
		$XMLParser = xml_parser_create();
		xml_parser_set_option($XMLParser, XML_OPTION_SKIP_WHITE, false);
		xml_parser_set_option($XMLParser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($XMLParser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_set_character_data_handler($XMLParser, array($this, '_contents'));
		xml_set_default_handler($XMLParser, array($this, '_default'));
		xml_set_element_handler($XMLParser, array($this, '_start'), array($this, '_end'));
		xml_set_external_entity_ref_handler($XMLParser, array($this, '_externalEntity'));
		xml_set_notation_decl_handler($XMLParser, array($this, '_notationDecl'));
		xml_set_processing_instruction_handler($XMLParser, array($this, '_processingInstruction'));
		xml_set_unparsed_entity_decl_handler($XMLParser, array($this, '_unparsedEntityDecl'));
		
		if (!xml_parse($XMLParser, $input, true)) {
			$errorCode = xml_get_error_code($XMLParser);
			$message = sprintf('%s. line: %d, char: %d'.($this->_tagStack ? ', tag: %s' : ''),
				xml_error_string($errorCode),
				xml_get_current_line_number($XMLParser),
				xml_get_current_column_number($XMLParser)+1,
				implode('->', $this->_tagStack));
			xml_parser_free($XMLParser);
			throw new XmlToArrayException($message, $errorCode);
		}
		
		xml_parser_free($XMLParser);
	}
	
	
	/**
	 * 
	 * Log messages (debugging purpose)
	 * @param string $msg
	 * @param int $type
	 */
	public function log($msg, $type = 2) {
		if (class_exists('Object')) {
			!($this->_Object instanceof Object) && $this->_Object = new Object();
			return $this->_Object->log($msg, $type);
		}
		
		return false;
	}
	
	
	/**
	 * Returns parsed XML as array structure
	 * @return array
	 */
	public function toArray($simpleStructure = false) {
		return $this->_CurrentXmlToArrayNode->render($simpleStructure);
	}
	
	
	/**
	 * Static entry point
	 * @param string $input
	 * @param bool $simpleStructure
	 * @param int $maxDepth only parse XML to the provided depth
	 * @return array
	 * @throws XmlToArrayException
	 */
	public static function render($input, $simpleStructure = false, $maxDepth = 20) {
		$Instance = new XmlToArray($input, $maxDepth);
		return $Instance->toArray($simpleStructure);
	}
	
	
	/**
	 * Handles cdata of the XML (user data between the tags)
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $data
	 */
	private function _contents($parser, $data) {
		if (trim($data) !== '' && $this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) $this->_CurrentXmlToArrayNode->setData($data);
	}
	
	
	/**
	 * Default handler for all other XML sections not implemented as callback
	 * @param resource $parser a resource handle of the XML parser
	 * @param mixed $data
	 * @throws XmlToArrayException
	 */
	private function _default($parser, $data) {
		$data = trim($data);
		
		if (in_array($data, get_html_translation_table(HTML_ENTITIES))) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(html_entity_decode($data));
		} elseif ($data && isset(self::$_htmlEntityExceptions[$data])) {
			$this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->setData(self::$_htmlEntityExceptions[$data]);
		} elseif ($data && is_string($data) && strpos($data, '<!--') === false && strpos($data, '<?xml') === false) {
			if (getenv('sofortDebug') == 'true') {
				trigger_error('Default data handler used. The data passed was: '.$data, E_USER_WARNING);
				throw new XmlToArrayException('Unknown error occurred');
			}
		}
	}
	
	
	/**
	 * Handler for end tags
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $name
	 */
	private function _end($parser, $name) {
		array_pop($this->_tagStack);
		
		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode) {
			$this->_CurrentXmlToArrayNode->setClosed();
			
			$breaker = 0;
			
			// step up the stack and close all tags as long as current tag is reached in stack
			if ($this->_CurrentXmlToArrayNode->getName() != $name) {
				do {
					$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
					$this->_CurrentXmlToArrayNode->setClosed();
					
					if ($breaker > 100) {
						trigger_error('Had to break out from endless loop.', E_USER_WARNING);
						break;
					}
					
					++$breaker;
				} while($this->_CurrentXmlToArrayNode->getName() != $name);
			} elseif ($this->_CurrentXmlToArrayNode->hasParentXmlToArrayNode()) {
				$this->_CurrentXmlToArrayNode = $this->_CurrentXmlToArrayNode->getParentXmlToArrayNode();
			}
		}
	}
	
	
	/**
	 * 
	 * External Entity, no operation
	 * @param unknown_type $parser
	 * @param unknown_type $openEntityNames
	 * @param unknown_type $base
	 * @param unknown_type $systemId
	 * @param unknown_type $publicId
	 */
	private function _externalEntity($parser , $openEntityNames , $base , $systemId , $publicId) {
		// noop
	}
	
	
	/**
	 * 
	 * Notation Decl, no operation
	 * @param unknown_type $parser
	 * @param unknown_type $notationName
	 * @param unknown_type $base
	 * @param unknown_type $systemId
	 * @param unknown_type $publicId
	 */
	private function _notationDecl($parser , $notationName , $base , $systemId , $publicId) {
		// noop
	}
	
	
	/**
	 * 
	 * Processing Instruction, no operation
	 * @param unknown_type $parser
	 * @param unknown_type $target
	 * @param unknown_type $data
	 */
	private function _processingInstruction($parser, $target, $data) {
		// noop
	}
	
	
	/**
	 * 
	 * Unparsed Entity Decl, no operation
	 * @param unknown_type $parser
	 * @param unknown_type $entityName
	 * @param unknown_type $base
	 * @param unknown_type $systemId
	 * @param unknown_type $publicd
	 * @param unknown_type $notationName
	 */
	private function _unparsedEntityDecl($parser , $entityName , $base , $systemId , $publicd , $notationName) {
		// noop
	}
	
	
	/**
	 * Handler for start tags
	 * @param resource $parser a resource handle of the XML parser
	 * @param string $name
	 * @param array $attributes
	 * @todo add check on max depth
	 */
	private function _start($parser, $name, $attributes) {
		$this->_tagStack[] = $name;
		
		if ($this->_maxDepth && count($this->_tagStack) > $this->_maxDepth) {
			throw new XmlToArrayException('Parse Error: max depth exceeded.', '7005');
		}
		
		$XmlToArrayNode = new XmlToArrayNode($name, $attributes);
		
		if ($this->_CurrentXmlToArrayNode instanceof XmlToArrayNode && $this->_CurrentXmlToArrayNode->isOpen()) {
			$this->_CurrentXmlToArrayNode->addChild($XmlToArrayNode);
			$XmlToArrayNode->setParentXmlToArrayNode($this->_CurrentXmlToArrayNode);
		}
		
		$this->_CurrentXmlToArrayNode = $XmlToArrayNode;
	}
}

?>