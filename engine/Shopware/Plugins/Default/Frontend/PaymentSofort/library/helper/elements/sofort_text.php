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

/**
 * 
 * Implementation of simple text
 *
 */
class SofortText extends SofortElement {
	
	public $text;
	
	public $escape = false;
	
	
	/**
	 * 
	 * Constructor for SofortText
	 * @param strng $text
	 * @param boolean $escape
	 * @param boolean $trim
	 */
	public function __construct($text, $escape = false, $trim = true) {
		$this->text = $trim ? trim($text) : $text;
		$this->escape = $escape;
	}
	
	
	/**
	 * Renders the element (override)
	 * (non-PHPdoc)
	 * @see SofortElement::render()
	 */
	public function render() {
		return $this->escape ? htmlspecialchars($this->text) : $this->text;
	}
}
?>