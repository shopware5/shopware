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
 * Implementation of an HTML Tag
 *
 */
class SofortHtmlTag extends SofortTag {
	
	private static $selfClosingTags = array('base', 'meta', 'link', 'hr', 'br', 'param', 'img', 'area', 'input', 'col');
	
	
	/**
	 * 
	 * Constructor for SofortHtmlTag
	 * @param string $tagname
	 * @param array $attributes
	 * @param array $children
	 */
	public function __construct($tagname, array $attributes = array(), $children = array()) {
		$tagname = strtolower($tagname);
		$loweredAttributes = array();
		
		foreach ($attributes as $key => $value) {
			$loweredAttributes[strtolower($key)] = $value;
		}
		
		parent::__construct($tagname, $loweredAttributes, $children);
	}
	
	
	/**
	 * Renders the element (override)
	 * @see SofortTag::_render()
	 */
	protected function _render($output, $attributes) {
		return in_array($this->tagname, self::$selfClosingTags) ? "<{$this->tagname}{$attributes} />" : "<{$this->tagname}{$attributes}>{$output}</{$this->tagname}>";
	}
}
?>