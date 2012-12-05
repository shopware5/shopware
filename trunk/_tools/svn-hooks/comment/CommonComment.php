<?php
// Software released under the General Public License (version 2 or later), available at
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/*********************************************************************************
Comment handling the most common tags.
It understands the following tags : @deprecated (or @deprec), @see (or @link), @todo, @author, @since, @version.

@license	GPL
@author	 <a href="http://www.tig12.net">Thierry Graff</a>
@history	2008.03.12 : Creation
********************************************************************************/

class CommonComment extends Comment{
	
	/** Boolean indicating if the comment contains a <code>@deprecated</code> or <code>@deprec</code> tag. */
	public $tags_deprecated = false;
	
	/** Contents of tags <code>@see</code> and	<code>@link</code>*/
	public $tags_see = array();
	
	/** Contents of tags <code>@todo</code> */
	public $tags_todo = array();
	
	/** Contents of tags <code>@author</code> */
	public $tags_author = array();
	
	/** Contents of tags <code>@since</code> */
	public $tags_since = array();
	
	/** Contents of tag <code>@version</code> */
	public $tags_version = array();
	
	/** Tags different from tags handled by <code>$tag_*</code> instance variables. */
	public $tags_other = array();
	
	//********************* __construct ******************************
	/**
		@param $str The raw string containing the comment, with slashes (/) and asterisks (*)
								(as returned from getDocComment() by reflection methods)
	*/
	function __construct($str, $client=''){
		parent::__construct($str, $client); // parent puts all tags in $this->tags
		// scan $this->tags to search for the tags it handles
		for($i=0; $i < count($this->tags); $i++){
			$tag = $this->tags[$i];
			switch($tag['name']){
				case 'deprecated' : 
				case 'deprec' : 
					$this->tags_deprecated = true;
				break;
				case 'see' : 
				case 'link' : 
					$this->tags_see[] = $tag['value'];
				break;
				case 'author' : 
					$this->tags_author[] = $tag['value'];
				break;
				case 'since' : 
					$this->tags_since[] = $tag['value'];
				break;
				case 'version' : 
					$this->tags_version[] = $tag['value'];
				break;
				case 'todo' : 
					$this->tags_todo[] = $tag['value'];
				break;
				default :
					$this->tags_other[] = $tag;
			}
		}// end for
		
	}// end __construct
	
}// end class
?>