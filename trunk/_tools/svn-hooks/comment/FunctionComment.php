<?php
// Software released under the General Public License (version 2 or later), available at
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/*********************************************************************************
Comment associated to a function or class method.
In adition to the tags handled by its parents,
it understands the following tags : @param, @return (or @returns), @throws (or @throw)

@license	GPL
@author	 <a href="http://www.tig12.net">Thierry Graff</a>
@history	2007.05.04, 03h20 : Creation
@history	2008.03.12 : Split from create class auCommonComment
********************************************************************************/

class FunctionComment extends CommonComment{
	
	/**
		Associative array containing the <code>@param</code> tags of the comment.
		 Understood syntaxes :
		1 : <code>@param type $name description</code>
		2 : <code>@param $name description</code>
		In all cases, description is optional
		Each entry contains 3 elements :
		- <code>$params[$i]['name']</code> contains the name of the parameter
		- <code>$params[$i]['type']</code> contains the type of the parameter, or nothing if not specified
		- <code>$params[$i]['description']</code> contains its description, or nothing if not specified
	*/
	public $tags_param = array();
	
	/**
		String Contents of tag <code>@return</code> or <code>@returns</code> (incorrect syntax but admitted)
	*/
	public $tags_return = '';
	
	/** Contents of tags <code>@throws</code> or <code>@exception</code> or <code>@throw</code> (incorrect syntax but admitted) */
	public $tags_throws = array();
	
	//********************* __construct ******************************
	/**
		@param $str The raw string containing the comment, with slashes (/) and asterisks (*)
								(as returned from getDocComment() by reflection methods)
	*/
	function __construct($str, $client=''){
		parent::__construct($str, $client);
		// superclass (CodeComment) has already put the non-identified tags in $tags_other
		$tags_other2 = array(); // will contain the non-identified tags
		// scan tags_other to search for specific tags
		for($i=0; $i < count($this->tags_other); $i++){
			$tag = $this->tags_other[$i];
			switch($tag['name']){
				case 'param' :
					$this->fillParamTag($tag);
				break;
				case 'return' : 
				case 'returns' : 
					$this->tags_return = $tag['value'];
				break;
				case 'throws' : 
				case 'exception' : 
				case 'throw' : 
					$this->tags_throws[] = $tag['value'];
				break;
				default :
					$tags_other2[] = $tag;
			}
		}// end for
		// tags_other take only the non identified tags
		$this->tags_other = $tags_other2;
	}// end __construct
	
	
	// *************************** fillParamTag ***************************
	/**
		Parses a <code>@param</code> tag
		Fills $this->tags_param
		If invalid format, adds an entry to {@link Report::$comments} 
		@param $tag Associative array containing two entries ('name' and 'value').
	*/
	private function fillParamTag($tag){
		$pattern = '/\s*(.*?)\s*(\$\w+)\s*(.*)\s*/s';
		preg_match($pattern, $tag['value'], $matches);
		if(count($matches) == 0){
			$report['type'] = 'Invalid';
			$report['message'] = trim($tag['value']);
			$report['file'] = '';
			$report['line'] = '';
			if($this->client != ''){
				$report['file'] = $this->client['file'];
				$report['line'] = $this->client['line'];
			}
			//Report::$comments[] = $report;
			return;
		}
		$param['name'] = $matches[2];
		$param['type'] = $matches[1];
		$param['description'] = $matches[3];
		$this->tags_param[] = $param;
	}// end fillParamTag
	
	
}// end class
?>