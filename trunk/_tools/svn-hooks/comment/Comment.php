<?php
// Software released under the General Public License (version 2 or later), available at
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/*********************************************************************************
	Comment, for code or non-code element
	A comment can contain :
	- A description
	- Tags, of the form : @tagName value
	(the value of a tag is optional).
	The description ends at first encoutered tag.
	Description and tags are optional.
	
	@license	GPL
	@author	 <a href="http://www.tig12.net">Thierry Graff</a>
	@history	2007.05.04, 03h20 : Creation
********************************************************************************/

class Comment{
	
	/** The description of the comment : text before the tags */
	public $description = '';
	
	/** First sentence of the description */
	public $firstSentence = '';
	
	/** Array containing the tags of the comment. 
	Each entry contains an associative array : 
	<code>$tags[$i] = array(['name'] => 'the tag name', ['value'] => 'its value')</code>
	*/
	public $tags = array();
	
	/**
		Informations about the code ordering the creation of this comment.
		Associative array containing two entries :
			- 'file' : relative path of the file containing the comment , in source dir
			- 'line' : start line of the comment.
	*/
	public $client = '';
	
	
	//********************* __construct ******************************
	/**
		Builds a Comment object from a string.
		@param $str The raw string containing the comment.
		                      Must contain initial slashes (/) and asterisks (*)
		@param $client Informations about the code ordering the creation of this comment
		                          Optional, used only for warning messages
		                          See {@link $client} 
	*/
	function __construct($str, $client=''){
		$this->client = $client;
		$clean = self::cleanComment($str);
		// if it starts by a '@', the comment contains only tags ($tagsIdx = 0); otherwise contains a description ($tagsIdx = 1)
		if(substr($clean, 0, 1) == '@')
			$tagsIdx = 0;
		else
			$tagsIdx = 1;
		$pieces = preg_split("/^@/sm", $clean, -1, PREG_SPLIT_NO_EMPTY);
		// the first one is the description
		if(isset($pieces[0]) && $tagsIdx == 1) $this->description = nl2br(trim($pieces[0])); ////// todo here : don't put <br/> within <pre></pre> tags
		// the other ones are tags
		for($i=$tagsIdx; $i < count($pieces); $i++){
			// NOTE : the pattern is "/(\w+)\s*(.*)/s" instead of "/(\w+)\s+(.*)/s" to allow tags without values (like @deprecated)
			preg_match("/(\w+)\s*(.*)/s", trim($pieces[$i]), $matches);
			if(isset($matches[1])) $tag['name'] = $matches[1];
			if(isset($matches[2])) $tag['value'] = nl2br($matches[2]);
			if(isset($matches[1])) $this->tags[] = $tag;
		}
		$this->firstSentence = self::getFirstSentence($this->description);
	}// end __construct																																									 
	
	
	// ===========================================================================
	//															 STATIC
	// ===========================================================================
	
	// *************************** cleanComment ***************************
	/**
		Cleans a javadoc comment, removing the eventual decorative asterisks (*) at beginning of line.
		Adapted to comments retrieved from <code>tokenizer_get_all()</code>
		@param $comment The String to clean
		@return The cleaned comment
	*/
	public static function cleanComment($comment){
		$comment = trim($comment);
		// if simple comment, not javadoc comment
		if(substr($comment, 0, 3) != '/**') return '';
		// get rid of surrounding /** and */
		$pattern = '/\/\*{2,}\s*(.*)\s*\*+\//s'; // this regex doesn't catch all the final * ; I don't know why - so rtrim added
		preg_match($pattern, $comment, $matches);
		if(isset($matches[1])) $comment = rtrim($matches[1], '*');
		// get rid of optional leading asterisks
		$pattern = '/^\s*\*?\s?(.*)\s*$/'; // put leading * optional to trim 
		$lines = explode("\n", $comment);
		$lines2 = preg_replace($pattern, '$1', $lines);
		return trim(implode("\n", $lines2));
	}// end cleanComment
	
	
	// *************************** cleanComment2 ***************************
	/**
		Cleans a javadoc comment, removing the eventual decorative asterisks (*) at beginning of line.
		MORE PERMISSIVE than cleanComment()
		Will be used again when several ways to write comments will be supported
		@param $comment The String to clean
		@return The cleaned comment
		@deprecated
	*/
	public static function cleanComment2($comment){
		$comment = trim($comment);
//		if(substr($comment, 0, 2) == '/*' || substr($comment, 0, 2) == '//') return;
		//
		// Clean the comment string
		$lines = explode("\n", $comment);
		$clean = '';
		for($i=0; $i < count($lines); $i++){
			$line = trim($lines[$i]);
			// handle doc markers
			if(substr($line, 0, 3) == '/**'){
				$line = substr($line, 3);
				if($line == '') continue;
			}
			if(substr($line, -2, 2) == '*/'){
				$line = substr($line, 0, -2);
				if($line == '') continue;
			}
			// Remove * at line begin
// @todo : verify that * is followed by \b
// if(preg_match)
			if(substr($line, 0, 1) == '*'){
				$line = trim(substr($line, 1));
			}
			$clean .= $line . "\n";
		}// end for
		$clean = trim($clean); // remove last \n
		// some comments end with "****************/" ; remove them
		$clean = preg_replace('/(.*)\n\*+$/sm', '$1', $clean);
	}// end cleanComment2
	
	
	//********************* getFirstSentence ******************************
	/**
		Computes the "first sentence" of a comment.
		Javadoc's definitions is : <cite>This sentence ends at the first period that is followed by a blank, tab, or line terminator, or at the first tag</cite>
		(<a href='http://java.sun.com/j2se/javadoc/writingdoccomments/'>How to Write Doc Comments for the Javadoc Tool</a>).
		In practice, many comments have a first sentence followed by a line terminator, but without dot.
		So the definition adopted in phpSimpleDoc is different from javadoc's :
		If a line terminator is found before the first period, the first sentence ends there. Otherwise, javadoc's definition applies.
		@param $comment The text to analyze.
		@return The first phrase or line of a text.
	*/
	public static function getFirstSentence($comment){
		// find first sentence according to javadoc's definition
		$pattern = '/^(.*?)\.\s.*/s';
		preg_match($pattern, $comment, $matches);
		if(count($matches) != 0) $candidate = $matches[1];
		else $candidate = $comment;
		$candidate = trim($candidate);
		// look if $candidate contains a line terminator
		if(strPos($candidate, "\n") !== false){
			$candidate = substr($candidate, 0, strPos($candidate, "\n")+1);
		}
		if(substr($candidate, -1) == '.') $candidate = substr($candidate, 0, -1);
		return $candidate;
	}// end getFirstSentence
	
}// end class
?>