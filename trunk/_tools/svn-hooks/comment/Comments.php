<?php
// Software released under the General Public License (version 2 or later), available at
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/*********************************************************************************
	Common constants of this package
	
	@license	GPL
	@author	 <a href="http://www.tig12.net">Thierry Graff</a>
	@history	2009.02.20 06:16:21 : Creation
********************************************************************************/

interface Comments{
	
	// Markers
	
	/**
		Represents a comment formatted using javadoc syntax
		Ex : <code>&fra;** This is a comment, between one slash followed by two stars and one star followed by on slash *&fra;</code>
	*/
	const DELIMITER_JAVADOC_COMMENT;
	
	/**
		Represents a comment formatted using classical multiple line C comment
		Ex : <code>&fra;* This is a comment, between one slash followed by one star and one star followed by on slash *&fra;</code>
	*/
	const DELIMITER_C_MULTILNE_COMMENT;
	
	/**
		Represents a comment formatted using several single line C comments
		Ex : <pre>// The comment is composed by several lines
		// starting with optional white spaces followed by two slashes
		</pre>
	*/
	const DELIMITER_TW0_SLASHES;
	
	/**
		Same as {@link DELIMITER_TW0_SLASHES}, but // lines
		must be <b>continuous</b> : if a blank line is found, the comment stops there
		Ex : <pre>// This line is not part of the comment because of next blank line
		
		// This is the comment for function toto
		// on two lines
		Function toto() // here the commented element
		</pre>
		
	*/
	const DELIMITER_TW0_SLASHES_WITHOUT_BLANK_LINE;
	
	
	// Comment syntaxes
	
	/**
		Javadoc or javadoc-like comment syntax (description / tags using @)
	*/
	const SYNTAX_JAVADOC;
	
	/**
		NaturalDocs comment syntax
	*/
	const SYNTAX_NATURALDOCS;
	
	
	// *************************** load ***************************
	/**
		Loads a comment
	*/
	public static function loadComment();
	
	
}// end class
?>