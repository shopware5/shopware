<?php
// Software released under the General Public License (version 2 or later), available at
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/*********************************************************************************
	Computes comments from PHP code
	
	@license	GPL
	@author	 <a href="http://www.tig12.net">Thierry Graff</a>
	@history	 : Creation
********************************************************************************/

class PhpCommentLoader implements CommentLoader{
	
	/**
		Default options for {@link loadComment()}
	*/
	public static $DEFAULT_OPTIONS = array(
		// several possible markers
		'markers' => array(
			Comments::DELIMITER_JAVADOC_COMMENT,
			Comments::DELIMITER_C_MULTILNE_COMMENT,
			Comments::DELIMITER_TW0_SLASHES_WITHOUT_BLANK_LINE,
		),
		'comment-syntax' => Comments::SYNTAX_JAVADOC,
	);
	
	
	/**
		Current options used to retrieve the comment
		Can be modified by {@link setOptions()} 
	*/
	private static $curOptions = array();
	
	// *************************** load ***************************
	/**
		Loads a comment
		@param $ta Token array
		@param $x Index in $ta of the <b>last</b> token representing the comment
		@return A string containing the comment
		@todo NOT IMPLEMENTED
	*/
	public static function loadComment(&$ta, $x){
		
		//For all markers, blank lines between documented element and last comment token are allowed - skip them
		
		
	}// end loadComment
	
	
	// *************************** getOptions ***************************
	/**
		Gets the options used to load the comment
		@todo FINISH IMPLEMENTATION : permit partially filled options
	*/
	public static function getOptions(){
		if(count(self::$options) == 0){
			self::$options = self::$DEFAULT_OPTIONS;
		}
		return self::$options;
	}// end getOptions
	
	
	// *************************** setOptions ***************************
	/**
		Sets the options used to load the comment
	*/
	public static function setOptions($options){
		self::$options = $options;
	}// end setOptions
	
	
}// end class
?>