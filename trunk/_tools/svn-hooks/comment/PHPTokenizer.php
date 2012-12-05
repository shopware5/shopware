<?php
// This file is part of phpSimpleDoc - released under the terms of GNU General Public License version 3 or later.
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/******************************************************************************
Utilities to analyze an array of PHP tokens, as returned BY <code>token_get_all()</code>

@license	GPL
@author	 <a href="http://www.tig12.net">Thierry Graff</a>
@history	2008.05.07, 18h19 : Creation from refactorization
********************************************************************************/

class ppTokenArray{
	
	// *************************** getLine ***************************
	/**
		Returns the line of an element of a Token array returned by <code>token_get_all()</code>, or -1 if not found.
		Useful for elements like ',' or '{' expressed by a string in the TA
		("normal" elements are expressed by an array containing the line)
		@param $ta Token array
		@param $x Index in TA array
		@todo IMPLEMENT CORRECTLY : current version returns line of preceeding element
							 Should check also for following elements, and make an ~ average
	*/
	public static function getLine(&$ta, $x){
		if(!isset($ta[$x])) return -1;
		if(is_array($ta[$x])) return $ta[$x][2];
		while(true){
			$x--;
			if($x < 0) return -1;
			if(is_array($ta[$x])) return $ta[$x][2];
		}
	}// end getLine
	
	
	// *************************** getBalance ***************************
	/**
		Finds a portion of a token array, between a balanced set of symbols (ex : parentheses, curly braces)
		Works only for symbols NOT corresponding to T_* constants (when the entry of token array has 1 element, not 3)
		@param $ta Token array
		@param $x Index in TA containing an open symbol
		@param $openSymbol In general '{' or '('
		@param $closeSymbol In general '}' or ')'
		@return Mixed Index in TA containing the matching close symbol
														false if not found
	*/
	public static function getBalance(&$ta, $x, $openSymbol, $closeSymbol){
		if($ta[$x] != $openSymbol) return false;
		$nbOpen = 1;
		$nbClose = 0;
		$count = count($ta);
		while($nbOpen != $nbClose){
			$x++;
			if($x >= $count) return false;
			if($ta[$x] == $openSymbol){
				$nbOpen++;
			}
			if($ta[$x] == $closeSymbol){
				$nbClose++;
			}
		}
		return $x;
	}// end getBalance
	
	
	// *************************** getBalancedCurlyBraces ***************************
	/**
		Finds a portion of a token array, between a balanced set of curly braces.
		{@link getBalance()} doesn't work in this case because of	<code>T_CURLY_OPEN</code>
		and <code>T_DOLLAR_OPEN_CURLY_BRACES</code>, which correspond to '<code>}</code>'
		(absence of <code>T_CURLY_CLOSE</code>)
		@param $ta Token array
		@param $x Index in TA containing an open symbol
		@return Mixed Index in TA containing the matching '}'
								 false if not found
	*/
	public static function getBalancedCurlyBraces(&$ta, $x){
		$test = is_array($ta[$x]) && isset($ta[$x][0]);
		if( $ta[$x] != '{' || ( $test && $ta[$x][0] != T_CURLY_OPEN	&& $ta[$x][0] != T_DOLLAR_OPEN_CURLY_BRACES ) ){
Log::warn("getBalancedCurlyBraces RETURN FALSE", __FILE__, __LINE__);
				return false;
		}
		$nbOpen = 1;
		$nbClose = 0;
		$count = count($ta);
		while($nbOpen != $nbClose){
			$x++;
			if($x >= $count) return false;
			$test = is_array($ta[$x]) && isset($ta[$x][0]);
			if($ta[$x] == '{' || ( $test && ($ta[$x][0] == T_CURLY_OPEN || $ta[$x][0] == T_DOLLAR_OPEN_CURLY_BRACES) )){
				$nbOpen++;
			}
			if($ta[$x] == '}'){
				$nbClose++;
			}
		}
		return $x;
	}// end getBalancedCurlyBraces
	
	
	// *************************** getSequence	***************************
	/**
		Returns a portion of token array that must contain a given set of tokens
		From a given index, looks before and after $x
		Before, stops if it finds a token not in <code>$before</code>, or a doc comment.
		After, stops	if it finds a token not in <code>$after</code>, or <code>$stop</code>.
		@param $ta Token array
		@param $x Index in TA from where to search
		@param $before Array of possible tokens before $x
		@param $after Array of possible tokens after $x
		@param $stop Token which ends the sequence
		Can be a single token -	ex : "{" - or an array of tokens - ex : array('{', ';')	
		@return Associative array :
									$result['string'] : String expression of the sequence
									$result['comment'] : Contents of the doc comment, or '' if no comment.
									$result['commentLine'] : Line number of the comment, or -1 if no comment.
									$result['firstIndex'] : First index in TA of the sequence
									$result['lastIndex'] : Last index in TA of the sequence, or false if not found
		@optimize Should first search the end of the sequence, and then identify the comment
							(if return false, then comment not parsed).
	*/
	public static function getSequence(&$ta, $x, $before, $after, $stop){
		if(!is_array($stop)) $stop = array($stop);
		$res['string'] = $ta[$x][1];
		$res['comment'] = '';
		$res['commentLine'] = -1;
		$res['lastIndex'] = false;
		//
		// 1 - search the beginning of the sequence
		//
		$i = $x; // index to loop in TA
		while(true){
			$i--;
			if($i < 0) break;
			// WARNING, bug of token_get_all() : next line should be :
			// if(is_array($ta[$i]) && ($ta[$i][0] == T_DOC_COMMENT){
			// But if the doc comment starts by more than 2 asterisks, identified by token_get_all() as a T_COMMENT
			// So this function considers a doc comment even if a normal comment is present.
			// auComment::cleanComment has the responsability to return empty string when a T_COMMENT is found
			// instead of a T_DOC_COMMENT
			if(is_array($ta[$i]) && ($ta[$i][0] == T_DOC_COMMENT || $ta[$i][0] == T_COMMENT)){
				$res['comment'] = $ta[$i][1];
				if($res['comment'] == '')
					$res['commentLine'] = -1;
				else
					$res['commentLine'] = $ta[$i][2];
				$res['firstIndex'] = $i; // useless ?
				break;
			}
			if(is_array($ta[$i]) && $ta[$i][0] == T_WHITESPACE){
				$res['string'] = $ta[$i][1] . $res['string'];
				continue;
			}
			//
			if(is_array($ta[$i])){
				$tokenIndex = $ta[$i][0];
				$tokenValue = $ta[$i][1];
			}
			else{
				$tokenIndex = $ta[$i];
				$tokenValue = $ta[$i];
			}
			if(in_array($tokenIndex, $before)){
				$res['string'] = $tokenValue . $res['string'];
				$res['firstIndex'] = $i; // will change on next iteration
			}
			else{
				break;
			}
		}// end while
		//
		// 2 - search the end of the sequence
		//
		$i = $x; // index to loop in TA
// @fixme : should be : $count = count($ta) -1; ????
		$count = count($ta);
		while(true){
			$i++;
			if($i > $count) break; // should never happen (for tokens representing valid code)
			if(is_array($ta[$i]) && $ta[$i][0] == T_WHITESPACE){
				$res['string'] .= $ta[$i][1];
				continue;
			}
			//
			if(is_array($ta[$i])){
				$tokenIndex = $ta[$i][0];
				$tokenValue = $ta[$i][1];
			}
			else{
				$tokenIndex = $ta[$i];
				$tokenValue = $ta[$i];
			}
			if(in_array($tokenValue, $stop)){
				$res['lastIndex'] = $i;
				break;
			}
			if(in_array($tokenIndex, $after)){
				$res['string'] .= $tokenValue;
			}
			else{
				break;
			}
		}// end while
// @todo SUPPRESS THIS ???
		// remove multiple spaces
		$res['string'] = trim($res['string']);
		while(strPos($res['string'], '	') !== false){
			$res['string'] = str_replace('	', ' ', $res['string']);
		}
		//
		return $res;
	}// end getSequence
	
	
	// *************************** getSequence2	***************************
	/**
		Returns a portion of token array that can contain any tokens
		Used only to parse global variables
		From a given index, looks before and after $x
		Before, looks for a doc comment.
		After, takes all the tokens until a <code>$stop</code> token is found.
		$result['lastIndex'] = false means that $stop never met
		@param $ta Token array
		@param $x Index in TA from where to search
		@param $stop Token which ends the sequence
		@return Associative array :
									$result['string'] : String expression of the sequence
									$result['comment'] : Contents of the doc comment, or '' if no comment.
									$result['commentLine'] : Line number of the comment, or -1 if no comment.
									$result['firstIndex'] : First index in TA of the sequence
									$result['lastIndex'] : Last index in TA of the sequence, or false if not found
		@optimize Should first search the end of the sequence, and then identify the comment
						 (put lastIndex to false and return, then save up comment parsing).
	*/
	public static function getSequence2(&$ta, $x, $stop){
		$res['string'] = $ta[$x][1];
		$res['comment'] = '';
		$res['commentLine'] = -1;
		$res['lastIndex'] = false;
		//
		// 1 - search the beginning of the sequence
		//
		$i = $x; // index to loop in TA
		while(true){
			$i--;
			if($i < 0) break;
			// WARNING, bug of token_get_all() : next line should be :
			// if(is_array($ta[$i]) && ($ta[$i][0] == T_DOC_COMMENT){
			// But if the doc comment starts by more than 2 asterisks, identified by token_get_all() as a T_COMMENT
			// So this function considers a doc comment even if a normal comment is present.
			// auComment::cleanComment has the responsability to return empty string when a T_COMMENT is found
			// instead of a T_DOC_COMMENT
			if(is_array($ta[$i]) && ($ta[$i][0] == T_DOC_COMMENT || $ta[$i][0] == T_COMMENT)){
				$res['comment'] = $ta[$i][1];
				if($res['comment'] == '')
					$res['commentLine'] = -1;
				else
					$res['commentLine'] = $ta[$i][2];
				$res['firstIndex'] = $i; // useless ?
				break;
			}
			else if(is_array($ta[$i]) && $ta[$i][0] == T_WHITESPACE){
				continue;
			}
			else{
				break;
			}
		}// end while
		//
		// 2 - search the end of the sequence
		//
		$i = $x; // index to loop in TA
		$count = count($ta);
		while(true){
			$i++;
			if($i > $count - 1){
				// should never happen (for tokens representing valid code)
				break;
			}
			if(is_array($ta[$i]) && $ta[$i][0] == T_WHITESPACE){
				$res['string'] .=  $ta[$i][1];
				continue;
			}
			//
			if(is_array($ta[$i])){
				$tokenIndex = $ta[$i][0];
				$tokenValue = $ta[$i][1];
			}
			else{
				$tokenIndex = $ta[$i];
				$tokenValue = $ta[$i];
			}
			if($tokenValue == $stop){
				$res['lastIndex'] = $i;
				break;
			}
			$res['string'] .= $tokenValue;
		}// end while
// @todo SUPPRESS THIS ???
		// remove multiple spaces
		$res['string'] = trim($res['string']);
		while(strPos($res['string'], '	') !== false){
			$res['string'] = str_replace('	', ' ', $res['string']);
		}
		//
		return $res;
	}// end getSequence2
	
	
}//end class

// This file is part of phpSimpleDoc - released under the terms of GNU General Public License version 3 or later.
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/******************************************************************************
Parses declaration of PHP elements (classes, interfaces, functions etc.).
Most of the methods use regular expressions ; some use token array.

@license	GPL
@author	 <a href="http://www.tig12.net">Thierry Graff</a>
@history	2008.02.26, 06h45 : Creation
********************************************************************************/

class ppParsePhpCode{
	
	/** 
	The php label applies to constant, variable, function and class names.
	From PHP manual, <a href='http://www.php.net/manual/en/language.constants.php'>Constants</a> :
	<cite>A valid constant name starts with a letter or underscore, followed by any number of letters, numbers, or underscores.
	As a regular expression, it would be expressed thus :
	<code>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*</code></cite>
	 */
	public static $phpIdentifierPattern;
	
	/** Boolean Indicating if initialization of static variables has been done */
	public static $initOK = false;
	
	// *************************** init ***************************
	/**
		Static initializer ; initializes the patterns used in this class
	*/
	public static function init(){
		self::$phpIdentifierPattern = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
		self::$initOK = true;
	}// end init 
	
	
	//**************************************************************
	//																						Object code
	//**************************************************************
	
	//********************* parseClassDeclaration ******************************
	/**
		Parses a class declaration
		@param String $sourceCode PHP source code containing the class declaration
		@return Associative array :
						$return['modifiers'] : "abstract" or empty string.
						$return['name'] : The class name
						$return['extends'] : name of parent class, or empty string if not found.
						$return['implements'] : names of implemented interfaces, or empty string if not found.
	*/
	public static function parseClassDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$extendsPattern = 'extends\s+(?P<extends>' . self::$phpIdentifierPattern . ')';
		$implementsPattern = 'implements\s+(?P<implements>' . self::$phpIdentifierPattern . '(?:\s*,\s*' . self::$phpIdentifierPattern . ')*)';
		$pattern = '/'
						 . '(?P<modifiers>.*?)\s*' // note : warning here : modifiers needs to be ungreedy if class name ends with 'class' 
						 . 'class\s+' . '(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '(?:\s+' . $extendsPattern . ')?'
						 . '(?:\s+' . $implementsPattern . ')?'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['modifiers'] = '';
		$res['name'] = '';
		$res['extends'] = '';
		$res['implements'] = '';
		if(count($matches) > 1){
			if(isset($matches['modifiers'])) $res['modifiers'] = $matches['modifiers'];
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			if(isset($matches['extends'])) $res['extends'] = $matches['extends'];
			if(isset($matches['implements'])) $res['implements'] = $matches['implements'];
		}
		return $res;
	}// end parseClassDeclaration
	
	
	//********************* parseInterfaceDeclaration ******************************
	/**
		Parses an interface declaration
		@param String $sourceCode PHP source code containing the interface declaration
		@return Associative array :
						$return['name'] : Name of the interface.
						$return['extends'] : contents of clause exends, or empty string if not found.
	*/
	public static function parseInterfaceDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$extendsPattern = 'extends\s+(?P<extends>' . self::$phpIdentifierPattern . '(?:\s*,\s*' . self::$phpIdentifierPattern . ')*)';
		$pattern = '/'
						 . 'interface\s+' . '(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '(?:\s+' . $extendsPattern . ')?'
						 . '\s*'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['name'] = '';
		$res['extends'] = '';
		if(count($matches) > 1){
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			if(isset($matches['extends'])) $res['extends'] = $matches['extends'];
		}
		return $res;
	}// end parseInterfaceDeclaration
	
	
	//********************* parseClassMethodDeclaration ******************************
	/**
		Parses a method declaration
		@param String $sourceCode	PHP source code containing the class method declaration
		@return Associative array :
						$return['modifiers']
						$return['returnsRef'] (true or false)
						$return['name']
						$return['parameters']
	*/
	public static function parseClassMethodDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$pattern = '/'
						 . '(?P<modifiers>.*)'
						 . 'function\s+' . '(?P<returnsRef>&?\s*)' . '(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '\s*\((?P<parameters>.*)\)'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['modifiers'] = '';
		$res['returnsRef'] = '';
		$res['name'] = '';
		$res['parameters'] = '';
		if(count($matches) > 1){
			if(isset($matches['modifiers'])) $res['modifiers'] = $matches['modifiers'];
			$res['returnsRef'] = ( trim($matches['returnsRef']) == '&' ? true : false );
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			if(isset($matches['parameters'])) $res['parameters'] = $matches['parameters'];
		}
		return $res;
	}// end parseClassMethodDeclaration
	
	
	// *************************** isAbstractClassMethodDeclaration ***************************
	/**
		Finds if a string is an abstract class method declaration.
		This cannot be done only by looking if $decl contains 'abstract' :
		ex 1 : public function __construct(Zend_Controller_Request_Abstract $request)
		ex 2 : public function __construct($abstract)
		@param $decl A string containing a valid function declaration
		@return Boolean True if $decl is an abstract class method declaration.
									Flase otherwise (false also if the declaration is not valid)
		@optimize strToLower applied twice on same elements
	*/
	public static function isAbstractClassMethodDeclaration($decl){
		$a = preg_split('/\b/', $decl);
		// find the index in $a containing 'function'
		for($i=0; $i < count($a); $i++){
			if(strToLower($a[$i]) == 'function'){
				$idx_function = $i;
				break;
			}
		}
		if(!isset($idx_function)){
			// this should never happen if $decl is a valid method declaration
			return false;
		}
		// find the index in $a containing 'function'
		for($i=0; $i < count($a); $i++){
			if(strToLower($a[$i]) == 'abstract'){
				$idx_abstract = $i;
				break;
			}
		}
		if(!isset($idx_abstract)){
			return false;
		}
		else{
			if($idx_abstract < $idx_function) return true;
			else return false;
		}
	}// end isAbstractClassMethodDeclaration
	
	
	//********************* parseClassFieldDeclaration ******************************
	/**
		Parses the declaration of a class field (= property) from source code
		@param String $sourceCode	PHP source code containing the class field declaration
		@return	Associative array :
						$return['modifiers']
						$return['name']
						$return['value'] : default value of the field, or empty string if not found
	*/
	public static function parseClassFieldDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$pattern = '/'
						 . '(?P<modifiers>.*?)'
						 . '\s*\$(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '(?:\s*=\s*(?P<value>.*))?' // optional value
						 . '\s*'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['modifiers'] = '';
		$res['name'] = '';
		$res['value'] = '';
		if(count($matches) > 1){
			if(isset($matches['modifiers'])) $res['modifiers'] = trim($matches['modifiers']);
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			if(isset($matches['value'])) $res['value'] = $matches['value'];
		}
		return $res;
	}// end parseClassFieldDeclaration
	
	
	//********************* parseClassConstantDeclaration ******************************
	/**
		Parses the declaration of a class constant
		@param String $sourceCode	PHP source code containing the class constant declaration
		@return	Associative array :
						$return['name']
						$return['value']
	*/
	public static function parseClassConstantDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$pattern = '/'
						 . 'const'
						 . '\s*(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '(?:\s*=\s*(?P<value>.*))?' // optional value
						 . '\s*'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['name'] = '';
		$res['value'] = '';
		if(count($matches) > 1){
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			if(isset($matches['value'])) $res['value'] = $matches['value'];
		}
		return $res;
	}// end parseClassConstantDeclaration
	
	
	//**************************************************************
	//												Non-object code
	//**************************************************************
	
	//********************* parseFunctionDeclaration ******************************
	/**
		Parses the declaration of a non-object function from source code
		@param String $sourceCode	PHP source code containing the function declaration
		@return Associative array :
						$return['returnsRef'] (true or false)
						$return['name']
						$return['parameters']
	*/
	public static function parseFunctionDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$pattern = '/'
						 . 'function\s+'
						 . '(?P<returnsRef>&?\s*)'
						 . '(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '\s*\((?P<parameters>.*)\)'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		$res['returnsRef'] = '';
		$res['name'] = '';
		$res['parameters'] = '';
		if(count($matches) > 1){
			if(isset($matches['name'])) $res['name'] = $matches['name'];
			$res['returnsRef'] = ( trim($matches['returnsRef']) == '&' ? true : false );
			if(isset($matches['parameters'])) $res['parameters'] = $matches['parameters'];
		}
		return $res;
	}// end parseFunctionDeclaration
	
	
	//********************* parseConstantDeclaration ******************************
	/**
		Parses the declaration of a constant from token array
		(much simpler than doing it with regex)
		@param $ta Array of tokens containing the constant declaration
		@return Each entry is an associative array :
						$return['name'] : Name of the constant
						$return['value'] : Value of the constant
						$return['caseInsensitive'] : Boolean, indicates if the constant was declared insensitive
	*/
	public static function parseConstantDeclaration(&$ta){
		$res['name'] = '';
		$res['value'] = '';
		$res['caseInsensitive'] = false;
		$ta2 = array();
		for($i=0; $i < count($ta); $i++){
			if(is_array($ta[$i]) && $ta[$i][0] != T_WHITESPACE) $ta2[] = $ta[$i];
		}
		if(isset($ta2[1])){
			$res['name'] = $ta2[1][1];
		}
		if(isset($ta2[2])){
			$res['value'] = $ta2[2][1];
		}
		if(isset($ta2[3])){
			if($ta2[3][0] == T_STRING && strToLower($ta2[3][0]) == 'true')
				$res['caseInsensitive'] = true;
		}
		return $res;
	}// end parseConstantDeclaration
	
	
	//********************* parseVariableDeclaration ******************************
	/**
		Parses the declaration of a variable from source code
		@param String $sourceCode	PHP source code containing the variable declaration
		@return	Associative array if $sourceCode corresponds to a var declaration :
						$return['name']
						$return['value'] : default value of the field, or empty string if not found
						false otherwise
	*/
	public static function parseVariableDeclaration(&$sourceCode){
		if(!self::$initOK) self::init();
		$pattern = '/'
						 . '\s*\$(?P<name>' . self::$phpIdentifierPattern . ')'
						 . '(?:\s*=\s*(?P<value>.*))?' // optional value
						 . '\s*'
						 . '/si';
		preg_match($pattern, $sourceCode, $matches);
		if(count($matches) > 1){
			$res['name'] = ( isset($matches['name']) ? $matches['name'] : '' );
			$res['value'] = ( isset($matches['value']) ? $matches['value'] : '' );
			return $res;
		}
		else{
			return false;
		}
	}// end parseVariableDeclaration
	
	
	// *************************** parseFunctionParameters ***************************
	/**
		Parses the parameters of a function or class method
		Note about the algo :
		The token array contains sequences [paramType] paramName [= paramValue],
		[T_STRING] T_VARIABLE [= (T_LNUMBER | T_DNUMBER | T_STRING | T_CONSTANT_ENCAPSED_STRING | T_ARRAY)+],
		The token array is split using T_VARIABLE, to isolate portions containing parameters
		Each portion is corrected, to retrieve the optional type (a T_STRING) of the parameter
		The end of each portion is trimmed to remove the "," and the eventual type of the next parameter
		@param $ta Part of a function declaration, containing the parameters
		@return Associative array ; each element is an assoc array containing :
						array(
							'type' => 'int',
							'name' => '$toto',
							'value' => '',
							)
							If type or value are not part of parameter declaration, they are initialized to an empty string.
							For parameters with empty string as default value
							(<code>$param1 = ''</code> or <code>$param1 = ""</code>)
							have a value of <code>''</code> or <code>""</code> (two single or double quotes)
	*/
	public static function parseFunctionParameters(&$ta){
		// clean the array : remove spaces, and identify the positions containing T_VARIABLE tokens
		$ta2 = array();
		for($i=0; $i < count($ta); $i++){
			if(is_array($ta[$i]) && $ta[$i][0] == T_WHITESPACE) continue;
			if(is_array($ta[$i])){
				$tokenIndex = $ta[$i][0];
				$tokenValue = $ta[$i][1];
			}
			else{
				$tokenIndex = $ta[$i];
				$tokenValue = $ta[$i];
			}
			$ta2[] = array($tokenIndex, $tokenValue);
		}// end for i
		// clean ta2 : contains 3 useless elements at the beginning : "function functionName(" in the beginning : T_FUNCTION, T_STRING, "("
		array_shift($ta2); array_shift($ta2); array_shift($ta2);
		// clean ta2 : contains ')' at the end ; remove it;
		array_pop($ta2);
		if(count($ta2) == 0) return array(); // no parameters
		//
		// now $ta2 contains only parameter stuff
		//
		// clean ta2 : add a ',' at the beginning of ta2 for easier algo
		array_unshift($ta2, array(',', ','));
		// identify positions of comas (,)
		$comaPositions = array(); // positions of T_VARIABLE in $ta2
		for($i=0; $i < count($ta2); $i++){
			if($ta2[$i][0] == ',') $comaPositions[] = $i;
		}
		// $subarrays : sub-arrays of $ta2, delimited by comas
		// each element of $subarrays contains declaration of one parameter
		$subarrays = array();
		for($i=0; $i < count($comaPositions); $i++){
			if(isset($comaPositions[$i+1])){
				$subarrays[] = array_slice($ta2, $comaPositions[$i]+1, $comaPositions[$i+1] - $comaPositions[$i] -1);
			}
			else{
				$subarrays[] = array_slice($ta2, $comaPositions[$i]+1);
			}
		}// end for i
		//
		// compute the result
		//
		$res = array();
		for($i=0; $i < count($subarrays); $i++){
			$curRes = array('name'=>'', 'defaultValue'=>'', 'type'=>'', 'isRef'=>false);
			$j = 0; // current position in curArray
			while(true){ // not a real loop ; used only for the possibility to break, instead of a goto
				// type
				if(!isset($subarrays[$i][$j])) break; // useless for legal code ///// TODO return false
				if($subarrays[$i][$j][0] == T_STRING || $subarrays[$i][$j][0] == T_ARRAY){ // todo : see possibilities (php language specifications)
					$curRes['type'] = $subarrays[$i][$j][1];
					$j++;
				}
				// reference
				if(!isset($subarrays[$i][$j])) break; // useless for legal code ///// TODO return false
				if($subarrays[$i][$j][0] == '&'){
					$curRes['isRef'] = true;
					$j++;
				}
				// name
				if(!isset($subarrays[$i][$j])) break; // useless for legal code ///// TODO return false
				if($subarrays[$i][$j][0] == T_VARIABLE){
					$curRes['name'] = $subarrays[$i][$j][1];
					$j++;
				}
				// equal sign
				if(!isset($subarrays[$i][$j])) break;
				if($subarrays[$i][$j][0] == '='){
					if(!isset($subarrays[$i][$j+1])) break;
					$tmp = array_slice($subarrays[$i], $j+1);
					for($k=0; $k < count($tmp); $k++){
						$curRes['defaultValue'] .= $tmp[$k][1];
					}
				}
				break;
			}// end while
			$res[] = $curRes;
		}// end for($i=0; $i < count($subarrays); $i++)
		//
		return $res;
	}// end parseFunctionParameters
	
	
	//========================================================================
	//														Auxiliary functions
	//========================================================================
	
	// *************************** getVisibility ***************************
	/**
		Returns 'public', 'protected' or 'private'
		If tested modifiers doesn't contain any of these keywords, returns 'public'
		@param String $modifiers The modifiers to test
		@return String 
	*/
	public static function getVisibility($modifiers){
			$pattern = '/\b(public|protected|private)\b/i';
			preg_match($pattern, $modifiers, $matches);
			if(isset($matches[0])) return strToLower($matches[0]);
			return 'public'; // default if nothing found
	}// end getVisibility
	
	
	// *************************** isAbstract ***************************
	/**
		Returns true if modifiers contains "abstract" keyword, false otherwise.
		@param String $modifiers The modifiers to test
		@return Boolean
	*/
	public static function isAbstract($modifiers){
			$pattern = '/\babstract\b/i';
			preg_match($pattern, $modifiers, $matches);
			if(isset($matches[0])) return true;
			return false;
	}// end isAbstract
	
	
	// *************************** isStatic ***************************
	/**
		Returns true if modifiers contains "static" keyword, false otherwise.
		@param String $modifiers The modifiers to test
		@return Boolean
	*/
	public static function isStatic($modifiers){
			$pattern = '/\bstatic\b/i';
			preg_match($pattern, $modifiers, $matches);
			if(isset($matches[0])) return true;
			return false;
	}// end isStatic
	
	
	// *************************** isFinal ***************************
	/**
		Returns true if modifiers contains "final" keyword, false otherwise.
		@param String $modifiers The modifiers to test
		@return Boolean
	*/
	public static function isFinal($modifiers){
			$pattern = '/\bfinal\b/i';
			preg_match($pattern, $modifiers, $matches);
			if(isset($matches[0])) return true;
			return false;
	}// end isFinal
	
	
}//end class
?>