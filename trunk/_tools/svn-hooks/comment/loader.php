<?php
// This file is part of phpSimpleDoc - released under the terms of GNU General Public License version 3 or later.
// Full copyright notice applying to this file can be found in file copyright.txt, distributed with this file.
/******************************************************************************
Loads most PHP code elements, using PHP token array.

@license	GPL
@author	 <a href="http://www.tig12.net">Thierry Graff</a>
@history	2008.02.15, 07:47 : Creation
@history	2008.04.04 : Use PHP_Parser
@history	2008.04.11 : Use only php tokens

@todo on main loop (loadData) : if $j = false, add an entry to report - indicate that the file has no API doc
@todo add T_INLINE_HTML if global HTML + field 'hasInlineHtml' to files
@todo report to be done inside the functions, to get the line numbers
********************************************************************************/

class ldeMainLoader {
	
	/** 
		Indicates that a variable was encountered, in a context that is not a variable declaration
	*/
	const NOT_A_DECLARATION = 'not-a-declaration';
	
	/**
		Index of current file in <code>$this->docu->data['files']</code>
		Auxiliary variable for data loading
	*/
	protected $curFileIndex;
	
	/**
		Relative path of current file in <code>$this->docu->data['files']</code>
	*/
	protected $curFileRelativePath;
	
	/**
		Absolute path to the sub directory of outputDir, corresponding to files[$curFileIndex]
	*/
	protected $curOutputDir; // useless so far
	
	/**
		Index of current class in <code>$this->docu->data['classes']</code>
		(used for classes and interfaces)
		Auxiliary variable for data loading
	*/
	protected $curClassIndex;
	
	/**
		Name of current class.
	*/
	protected $curClassName;
	
	/**
		Current token array
	*/
	private $curTA;
	
	/**
		Auxiliary var of parseVariable(), to cache variable names already identified.
	*/
	private $knownVars = array();
	
	
	//********************* loadData ******************************
	/**
		Loads the data using <code>token_get_all()</code>
		Data are loaded in <code>$this->docu->data</code> field.
		@pre Documentor's $data must have been initialized
	*/
	public function loadData($source){
			$this->curTA = token_get_all($source);
			//print_r($this->curTA);exit;
			
			$i = 0;
			$this->curFileIndex = $i;
			// Additionnal informations on the field
			$this->computeFileDocComment();
			//
			// main loop on tokens
			//
			for($j=0; $j < count($this->curTA); $j++){
				
				//
				if(!is_array($this->curTA[$j])) continue;
				
				//
				if($this->curTA[$j][0] == T_INLINE_HTML){
					$this->docu->data['files'][$i]['hasInlineHtml'] = true;
				}
				//                                                                                       
				if($this->curTA[$j][0] == T_CLASS){
					
					$j = $this->loadClass($j);
					if($j === false){
						// @todo : report
						continue 2; // skip to next file
					}
				}
				//
				if($this->curTA[$j][0] == T_INTERFACE){
					$j = $this->loadInterface($j);
					if($j === false){
						// @todo : report
						continue 2; // skip to next file
					}
				}
				//
				if($this->curTA[$j][0] == T_FUNCTION){
					
					$j = $this->loadFunction($j);
					if($j === false){
						// @todo : report
						continue 2; // skip to next file
					}
				}
				//
				if($this->curTA[$j][0] == T_STRING && strCaseCmp($this->curTA[$j][1], 'define') == 0){
					$j = $this->loadConstant($j);
					if($j === false){
						// @todo : report
						continue 2; // skip to next file
					}
				}
				//
				if($this->curTA[$j][0] == T_VARIABLE){
					$j = $this->loadVariable($j);
					if($j === false){
						// @todo : report
						continue 2; // skip to next file
					}
				}
				// todo : require ?
			}// end for j
	
		unset($this->curTA);
		/*$this->docu->data['allClasses'] = uArrays::natcaseksort($this->docu->data['allClasses']);
		$this->docu->data['classNames'] = array_keys($this->docu->data['allClasses']);
		$this->docu->data['normalClasses'] = uArrays::natcaseksort($this->docu->data['normalClasses']);
		$this->docu->data['abstractClasses'] = uArrays::natcaseksort($this->docu->data['abstractClasses']);
		$this->docu->data['interfaces'] = uArrays::natcaseksort($this->docu->data['interfaces']);
		$this->docu->data['nonInterfaces'] = uArrays::natcaseksort($this->docu->data['nonInterfaces']);
		$this->docu->data['functions'] = uArrays::natcaseksort($this->docu->data['functions']);
		$this->docu->data['constants'] = uArrays::natcaseksort($this->docu->data['constants']);
		$this->docu->data['variables'] = uArrays::natcaseksort($this->docu->data['variables']);
Log::info("After loadData()" . Log::dumpMem());*/
//echo "<br/>ldeMainLoader - nb of classes = " . count($this->docu->data['classes']);
//echo "<pre>"; print_r($this->docu->data); echo "</pre>";
//echo "<pre>"; print_r($this->docu->data['classes']); echo "</pre>";
/* 
foreach(array('classConstants') as $type){
	$count = count($this->docu->data[$type]);
	echo "<h2>$count $type</h2>";
	for($i=0; $i < $count; $i++){
		echo "<br/>$i : " . $this->docu->data[$type][$i]['name'];
		//echo " ==> " . $this->docu->data[$type][$i]['classIndex'];
	}
}
*/
//exit;
	}// end loadData
	
	
	//========================================================================
	//												Loading object code
	//========================================================================
	
	// *************************** loadClass ***************************
	/**
		Computes a class from an index in curTA
		@param $x The index in curTA containing the T_CLASS token
		@return index of curTA containing the last token of class body ("}")
									or false if unable to parse
	*/
	private function loadClass($x){
//echo "<br/>loading class - x = $x";
//uTokenArray::dumpSlice($this->curTA, $x, 5, 20);
		$this->curClassIndex = count($this->docu->data['classes']); // because class not already added to docu
		// Get the sequence of tokens containing the declaration
		$before = array(T_ABSTRACT, T_FINAL);
		$after = array(T_STRING, T_EXTENDS, T_IMPLEMENTS, ',');
		$stop = '{';
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		$declaration = ppParsePhpCode::parseClassDeclaration($seq['string']);
		$end = ppTokenArray::getBalancedCurlyBraces($this->curTA, $seq['lastIndex']);
		if($end === false){
			$message = "Unable to parse class " . $declaration['name'];
			Log::warn($message);
			return false;
		}
		//
		$class['name'] = $declaration['name'];
		$this->curClassName = $class['name'];
		$class['index'] = $this->curClassIndex;
		$class['fileIndex'] = $this->curFileIndex;
		$class['packageIndex'] = ''; // filled by ldePackages
		$class['type'] = 'class';
		$class['startLine'] = $this->curTA[$x][2];
		$class['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$relativePath = str_replace($this->docu->userConfig['essential']['sourceDir'] . '/', '', $this->docu->data['files'][$this->curFileIndex]['absolutePath']);
		$class['relativePath'] = $relativePath;
		$class['absolutePath'] = $this->docu->data['files'][$this->curFileIndex]['absolutePath'];
		// prefix to add to a href link to reach top dir of generated doc
		//$class['prefix'] = uDirs::relativePath(dirname($class['absolutePath']), $this->docu->userConfig['essential']['sourceDir']);
		//
		$class['declaringInstruction'] = $seq['string'];
		$class['modifiers'] = $declaration['modifiers'];
		//
		$class['extends'] = $declaration['extends']; // array for interfaces, string for classes
		//
		//$class['implements'] = uArrays::explode(', ', $declaration['implements']);
		if(ppParsePhpCode::isAbstract($class['modifiers'])){
			$class['isAbstract'] = true;
			$class['type'] = 'abstract';
		}
		else{
			$class['isAbstract'] = false;
		}
		$class['isInterface'] = false;
		$class['isFinal'] = ppParsePhpCode::isFinal($class['modifiers']);
		$class['isException'] = false; //computed later
		//
		$class['methods']['instance'][$this->curClassName] = array();
		$class['methods']['static'][$this->curClassName] = array();
		$class['fields']['instance'][$this->curClassName] = array();
		$class['fields']['static'][$this->curClassName] = array();
		$class['constants'][$this->curClassName] = array();
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$class['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		//
		$this->docu->data['files'][$this->curFileIndex]['classes'][] = $this->curClassIndex;
		$this->docu->data['classes'][] = $class; // important to do it before loading class elements
		$this->docu->data['allClasses'][$class['name']] = $class['index'];
		$this->docu->data['nonInterfaces'][$class['name']] = $class['index'];
		if($class['isAbstract']) $this->docu->data['abstractClasses'][$class['name']] = $class['index'];
		//
		// loop on class tokens to retrieve class elements
		//
		for($i=$x; $i < $end; $i++){
				if($this->curTA[$i][0] == T_FUNCTION){
					$i = $this->loadClassMethod($i);
					if($i === false) return false;
				}
				if($this->curTA[$i][0] == T_VARIABLE){
					$i = $this->loadClassField($i);
					if($i === false) return false;
				}
				if($this->curTA[$i][0] == T_CONST){
					$i = $this->loadClassConstant($i);
					if($i === false) return false;
				}
		}// end for
//echo "<pre>=== class : {$class['name']} : "; print_r($this->docu->data['classes'][$this->curClassIndex]['methods']); echo "</pre>";
		return $end;
	}// end loadClass
	
	
	// *************************** loadInterface ***************************
	/**
		Computes an interface from an index in curTA
		@param $x The index in curTA containing the T_INTERFACE token
		@return index of curTA containing the last token of interface declaration (";")
									 or false if unable to parse
 */
	private function loadInterface($x){
		$this->curClassIndex = count($this->docu->data['classes']); // bacause class not already added to docu
		// Get the sequence of tokens containing the declaration
		$before = array();
		$after = array(T_STRING, T_EXTENDS, ',');
		$stop = '{';
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		if($seq['lastIndex'] === false){
			$message = "Unable to parse interface " . $this->curClassName;
			Log::warn($message);
			return false;
		}
		$declaration = ppParsePhpCode::parseInterfaceDeclaration($seq['string']);
		$end = ppTokenArray::getBalancedCurlyBraces($this->curTA, $seq['lastIndex']);
		if($end === false) return false;
		//
		$interface['name'] = $declaration['name'];
		$this->curClassName = $interface['name'];
		$interface['index'] = $this->curClassIndex;
		$interface['fileIndex'] = $this->curFileIndex;
		$interface['packageIndex'] = ''; // filled by ldePackages
		$interface['type'] = 'interface';
		$interface['startLine'] = $this->curTA[$x][2];
		$interface['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$relativePath = str_replace($this->docu->userConfig['essential']['sourceDir'] . '/', '', $this->docu->data['files'][$this->curFileIndex]['absolutePath']);
		$interface['relativePath'] = $relativePath;
		$interface['absolutePath'] = $this->docu->data['files'][$this->curFileIndex]['absolutePath'];
		// prefix to add to a href link to reach top dir of generated doc
		$interface['prefix'] = uDirs::relativePath(dirname($interface['absolutePath']), $this->docu->userConfig['essential']['sourceDir']);
		//
		$interface['declaringInstruction'] = $seq['string'];
		$interface['modifiers'] = '';
		//
		$interface['extends'] = uArrays::explode(',', $declaration['extends']); // array for interfaces, string for classes
		$interface['implementingClasses'] = array(); // computed in ldeImplementation
		//
		$interface['isInterface'] = true;
		$interface['isAbstract'] = false;
		$interface['isFinal'] = false;
		$interface['isException'] = false;
		//
		$interface['methods']['instance'][$this->curClassName] = array();
		$interface['methods']['static'][$this->curClassName] = array();
		$interface['constants'][$this->curClassName] = array();
		// no fields for interfaces
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$interface['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		//
		$this->docu->data['files'][$this->curFileIndex]['interfaces'][] = $interface['index'];
		$this->docu->data['classes'][] = $interface; // important to do it before loading class elements
		$this->docu->data['interfaces'][$interface['name']] = $interface['index'];
		$this->docu->data['allClasses'][$interface['name']] = $interface['index'];
		//
		// loop on interface tokens to retrieve class elements
		for($i=$x; $i < $end; $i++){
				if($this->curTA[$i][0] == T_FUNCTION){
					$i = $this->loadClassMethod($i, true);
					if($i === false) return false;
				}
				if($this->curTA[$i][0] == T_CONST){
					$i = $this->loadClassConstant($i);
					if($i === false) return false;
				}
		}// end for
//echo "<pre>=== interface : "; print_r($interface); echo "</pre>";
		return $end;
	}// end loadInterface
	
	
	// *************************** loadClassMethod ***************************
	/**
		Computes a class method from an index in curTA
		@param $x The index in curTA containing the T_FUNCTION token
		@param $isInterfaceMethod true if the method is declared in an interface
		@return index of curTA containing the last token of declaration ("}")
									or false if unable to parse
	*/
	private function loadClassMethod($x, $isInterfaceMethod=false){
		$before = array(T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FINAL, T_ABSTRACT, T_STATIC);
		$after = array('(', ')', ',', '=', '-', '&',
								T_VARIABLE,
								T_LNUMBER, T_DNUMBER,
								T_STRING, T_CONSTANT_ENCAPSED_STRING,
								T_ARRAY, T_DOUBLE_ARROW,
								T_DOUBLE_COLON, 
								T_FILE, T_LINE, T_CLASS_C, T_METHOD_C, T_FUNC_C,
								T_COMMENT, T_DOC_COMMENT,
								);
		$stop = array('{', ';'); // ';' for abstract methods and interface methods 
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		// need to look for abstract methods and interface methods
		if($isInterfaceMethod || ppParsePhpCode::isAbstractClassMethodDeclaration($seq['string']) === true){
			if($seq['lastIndex'] === false){
			$message = "Unable to parse class method in " . $this->curClassName;
			Log::warn($message);
			return false;
			}
			$end = $seq['lastIndex'];
		}
		else{
			$end = ppTokenArray::getBalancedCurlyBraces($this->curTA, $seq['lastIndex']);
			if($end === false){
				$message = "Unable to parse class method in $this->curClassName";
				Log::warn($message);
				return false;
			}
		}
		$declaration = ppParsePhpCode::parseClassMethodDeclaration($seq['string']);
		//
		$method['name'] = $declaration['name'];
		$method['index'] = count($this->docu->data['classMethods']);
		$method['classIndex'] = $this->curClassIndex;
		$method['fileIndex'] = $this->curFileIndex;
		$method['startLine'] = $this->curTA[$x][2];
		$method['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$method['declaringInstruction'] = $seq['string'];
		$method['modifiers'] = $declaration['modifiers'];
		$method['visibility'] = ppParsePhpCode::getVisibility($method['modifiers']);
		$method['isStatic'] = ppParsePhpCode::isStatic($method['modifiers']);
		$method['isAbstract'] = ppParsePhpCode::isAbstract($method['modifiers']);
		$method['isFinal'] = ppParsePhpCode::isFinal($method['modifiers']);
		$method['returnsRef'] = $declaration['returnsRef'];
		//
		$method['overrides'] = '';
		$method['overridenBy'] = array();
		$method['specifies'] = array(); // useful only for interfaces
		$method['specifiedBy'] = ''; // useful only for classes
		//
		$method['parameters'] = ppParsePhpCode::parseFunctionParameters(array_slice($this->curTA, $x, $seq['lastIndex']-$x));
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$method['commentObject'] = new FunctionComment($seq['comment'], $commentClient);
		//
		$this->docu->data['classMethods'][] = $method;
		//
		// Associate the method with current class
		if($method['isStatic']) $this->docu->data['classes'][$this->curClassIndex]['methods']['static'][$this->curClassName][] = $method['index'];
		else														 $this->docu->data['classes'][$this->curClassIndex]['methods']['instance'][$this->curClassName][] = $method['index'];
//echo "<br/>=== method <pre>"; print_r($method); echo "</pre>";
		return $end;
	}// end loadClassMethod
	
	
	// *************************** loadClassField ***************************
	/**
		Computes a class field from an index in curTA
		@param $x The index in curTA containing the T_VARIABLE token
		@return index of curTA containing the last token of declaration (";")
									or false if unable to parse
	*/
	private function loadClassField($x){
		$before = array(T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_VAR);
		$after = array('(', ')', ',', '=', '-',
								 T_VARIABLE, 
								 T_LNUMBER, T_DNUMBER,
								 T_STRING, T_CONSTANT_ENCAPSED_STRING,
								 T_ARRAY, T_DOUBLE_ARROW,
								 T_DOUBLE_COLON, 
								 T_FILE, T_LINE, T_CLASS_C, T_METHOD_C, T_FUNC_C,
								 T_COMMENT, T_DOC_COMMENT,
								 );
		$stop = array(';');
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		if($seq['lastIndex'] === false){
			$message = "Unable to parse class field in " . $this->curClassName;
			Log::warn($message);
			return false;
		}
		$end = $seq['lastIndex'];
		$declaration = ppParsePhpCode::parseClassFieldDeclaration($seq['string']);
		//
		$field['name'] = $declaration['name'];
		$field['index'] = count($this->docu->data['classFields']);
		$field['classIndex'] = $this->curClassIndex;
		$field['fileIndex'] = $this->curFileIndex;
		$field['startLine'] = $this->curTA[$x][2];
		$field['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$field['declaringInstruction'] = $seq['string'];
		$field['defaultValue'] = $declaration['value'];
		$field['modifiers'] = $declaration['modifiers'];
		$field['visibility'] = ppParsePhpCode::getVisibility($field['modifiers']);
		$field['isStatic'] = ppParsePhpCode::isStatic($field['modifiers']);
		//
		$field['overrides'] = '';
		$field['overridenBy'] = array();
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$field['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		//
		$this->docu->data['classFields'][] = $field;
		//
		// Associate the field with current class
		if($field['isStatic']) $this->docu->data['classes'][$this->curClassIndex]['fields']['static'][$this->curClassName][] = $field['index'];
		else												$this->docu->data['classes'][$this->curClassIndex]['fields']['instance'][$this->curClassName][] = $field['index'];
//echo "<br/>=== classField = <pre>"; print_r($field); echo "</pre>";
		return $end;
	}// end loadClassField
	
	
	// *************************** loadClassConstant ***************************
	/**
		Computes a class constant from an index in curTA
		@param $x The index in curTA containing the T_CONST token
		@return index of curTA containing the last token of declaration (";")
									or false if unable to parse
	*/
	private function loadClassConstant($x){
		$before = array();
		$after = array('(', ')', ',', '=', '-',
								T_LNUMBER, T_DNUMBER,
								T_STRING, T_CONSTANT_ENCAPSED_STRING, T_ARRAY,
								T_FILE, T_LINE, T_CLASS_C, T_METHOD_C, T_FUNC_C,
								T_COMMENT, T_DOC_COMMENT,
								// arrays not possible for class constants
								T_DOUBLE_COLON // for self::constant1
								); 
		$stop = array(';');
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		if($seq['lastIndex'] === false){
			$message = "Unable to parse class constant in " . $this->curClassName;
			Log::warn($message);
			return false;
		}
		$end = $seq['lastIndex'];
		$declaration = ppParsePhpCode::parseClassConstantDeclaration($seq['string']);
		//
		$constant['name'] = $declaration['name'];
		$constant['index'] = count($this->docu->data['classConstants']);
		$constant['fileIndex'] = $this->curFileIndex;
		$constant['classIndex'] = $this->curClassIndex;
		$constant['value'] = $declaration['value'];
		$constant['startLine'] = $this->curTA[$x][2];
		$constant['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$constant['declaringInstruction'] = $seq['string'];
		//
		$field['overrides'] = '';
		$field['overridenBy'] = array();
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$constant['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		//
		// Associate the constant with current class
		$this->docu->data['classConstants'][] = $constant;
		$this->docu->data['classes'][$this->curClassIndex]['constants'][$this->curClassName][] = $constant['index'];
//echo "<br/>=== classConstant = <pre>"; print_r($constant); echo "</pre>";
		return $end;
	}// end loadClassConstant
	
	
	//========================================================================
	//															Loading non-object code
	//=================================== =====================================
	
	// *************************** loadFunction ***************************
	/**
		Computes a function from an index in curTA
		@param $x The index in curTA containing the T_FUNCTION token
		@return index of curTA containing the last token of declaration ("}")
									or false if unable to parse
	*/
	private function loadFunction($x){
		$before = array();
		$after = array('(', ')', ',', '=', '-', '&',
								 T_VARIABLE, 
								 T_LNUMBER, T_DNUMBER,
								 T_STRING, T_CONSTANT_ENCAPSED_STRING,
								 T_ARRAY, T_DOUBLE_ARROW,
								 T_DOUBLE_COLON, 
								 T_FILE, T_LINE, T_CLASS_C, T_METHOD_C, T_FUNC_C,
								 T_COMMENT, T_DOC_COMMENT,
								 );
		$stop = '{';
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		if($seq['lastIndex'] === false){
			$message = "Unable to parse function in " . $this->curFileRelativePath;
			Log::warn($message);
			return false;
		}
		$end = ppTokenArray::getBalancedCurlyBraces($this->curTA, $seq['lastIndex']);
		$declaration = ppParsePhpCode::parseFunctionDeclaration($seq['string']);
		//
		$function['name'] = $declaration['name'];
		$function['index'] = count($this->docu->data['functions']);
		$function['fileIndex'] = $this->curFileIndex;
		$function['packageIndex'] = ''; // filled by ldePackages
		$function['startLine'] = $this->curTA[$x][2];
		$function['endLine'] = ppTokenArray::getLine($this->curTA, $end);// useless ?
		$function['returnsRef'] = $declaration['returnsRef'];
		//
		$function['declaringInstruction'] = $seq['string'];
		$function['parameters'] = ppParsePhpCode::parseFunctionParameters(array_slice($this->curTA, $x, $seq['lastIndex']-$x));
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
		$function['commentObject'] = new FunctionComment($seq['comment'], $commentClient);
		//
		$this->docu->data['functions'][] = $function;
		$this->docu->data['files'][$this->curFileIndex]['functions'][] = $function['index'];
//echo "<br/>=== function = <pre>"; print_r($function); echo "</pre>";
		return $end;
	}// end loadFunction
	
	
	// *************************** loadConstant ***************************
	/**
		Computes a constant from an index in curTA
		@param $x The index in curTA containing the 'define' token (T_STRING)
		@return index of curTA containing the last token of declaration (";")
									or false if unable to parse
	*/
	private function loadConstant($x){
		$before = array('@');
		$after = array('(', ')', ',', '.', '-',
								T_LNUMBER, T_DNUMBER,
								T_CONSTANT_ENCAPSED_STRING, T_STRING,
								T_FILE, T_LINE, T_CLASS_C, T_METHOD_C, T_FUNC_C, // __FILE__ etc
								T_ARRAY, // ex : @define('toto7', array(1, 2, 3));
								T_DOUBLE_COLON, // for class::constant1
								T_COMMENT, T_DOC_COMMENT,
								);
		$stop = ';';
		$seq = ppTokenArray::getSequence($this->curTA, $x, $before, $after, $stop);
		if($seq['lastIndex'] === false){
			$message = "Unable to parse constant in " . $this->curFileRelativePath;
			Log::warn($message);
			return false;
		}
		$end = $seq['lastIndex'];
		$declaration = ppParsePhpCode::parseConstantDeclaration(array_slice($this->curTA, $x, $end-$x));
		// for class constants, the name is directly OK
		// for constants, the name comes with surrounding quotes
		$first = substr($declaration['name'], 0, 1);
		$last = substr($declaration['name'], -1);
		if($first == "'" || $first == '"') $declaration['name'] = substr($declaration['name'], 1, -1);
		//
		$constant['name'] = $declaration['name'];
		$constant['index'] = count($this->docu->data['constants']);
		$constant['fileIndex'] = $this->curFileIndex;
		$constant['packageIndex'] = ''; // filled by ldePackages
		$constant['value'] = $declaration['value'];
		$constant['caseInsensitive'] = $declaration['caseInsensitive'];
		$constant['startLine'] = $this->curTA[$x][2];
		//
		$constant['declaringInstruction'] = $seq['string'];
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath,  // in source dir
			'line' => $seq['commentLine'],
		);
		$constant['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		$this->docu->data['constants'][] = $constant;
		$this->docu->data['files'][$this->curFileIndex]['constants'][] = $constant['index'];
//echo "<br/>=== constant = <pre>"; print_r($constant); echo "</pre>";
		return $end;
	}// end loadConstant
	
	
	// *************************** loadVariable ***************************
	/**
		Computes a global variable from an index in curTA
		@param $x The index in curTA containing the T_VARIABLE token
		@return index of curTA containing the last token of declaration (";")
									or false if unable to parse
	*/
	private function loadVariable($x){
		// Here, particular case, as a variable can be defined or used
		// ex : for($i=0; $i < 10; $i++){ echo 'toto'; }
		// $i is declared once, then used twice.
		// ppTokenArray::getSequence2() will just return the instruction until the ;
		// ppParsePhpCode::parseVariableDeclaration will return false if not a declaration
		$stop = ';';
		$seq = ppTokenArray::getSequence2($this->curTA, $x, $stop);
		$end = $seq['lastIndex'];
		if($end === false){
			return false;
		}
		//
		$declaration = ppParsePhpCode::parseVariableDeclaration($seq['string']);
		if($declaration === false){
			// the variable is udes in a context that is not a declaration
			return $end;
		}
		//
		// look if the variable has already been identified
		// case like : $i = 3; $i = $i +1;
		if(in_array($declaration['name'], $this->knownVars)){
			$this->knownVars[] = $declaration['name'];
			return $end;
		}
		$this->knownVars[] = $declaration['name'];
		//
		$variable['name'] = $declaration['name'];
		$variable['value'] = $declaration['value'];
		$variable['index'] = count($this->docu->data['variables']);
		$variable['fileIndex'] = $this->curFileIndex;
		$variable['startLine'] = $this->curTA[$x][2];
		$variable['endLine'] = ppTokenArray::getLine($this->curTA, $end); // useless ?
		//
		$variable['declaringInstruction'] = $seq['string'];
		//
		$commentClient = array(
			'file' => $this->curFileRelativePath, // in source dir
			'line' => $seq['commentLine'],
		);
			$variable['commentObject'] = new CommonComment($seq['comment'], $commentClient);
		//
		$this->docu->data['variables'][] = $variable;
		//
		// Associate the variable with current file
		$this->docu->data['files'][$this->curFileIndex]['variables'][] = $variable['index'];
//echo "<br/>=== variable = <pre>"; print_r($variable); echo "</pre>";
		return $end;
	}// end loadVariable
	
	
	/**
		Computes the doc comment of current file
	*/
	private function computeFileDocComment(){
		for($i=0; $i < count($this->curTA); $i++){
			if(!is_array($this->curTA[$i])) continue;
			// test on T_COMMENT and T_DOC_COMMENT because of bug in token_get_all()
			if($this->curTA[$i][0] == T_COMMENT || $this->curTA[$i][0] == T_DOC_COMMENT){
				$comment = trim($this->curTA[$i][1]);
				// it's just a normal comment
				if(substr($comment, 0, 3) != '/**') continue;
				// it's a doc comment
				$commentClient = array(
					'file' => $this->curFileRelativePath,
					'line' => $this->curTA[$i][2]
				);
				$this->docu->data['files'][$this->curFileIndex]['commentObject'] = new CommonComment($comment, $commentClient);
				break;
			}
		}// end for
		if(!isset($this->docu->data['files'][$this->curFileIndex]['commentObject'])){
			$commentClient = array(
				'file' => $this->curFileRelativePath,
				'line' => -1
			);
			$this->docu->data['files'][$this->curFileIndex]['commentObject'] = new CommonComment('', $commentClient);
		}
	}// end computeFileDocComment
	
	
}//end class
?>
