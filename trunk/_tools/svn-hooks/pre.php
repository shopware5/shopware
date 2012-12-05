#!/usr/bin/php
<?php
/**
 * Shopware AG SVN Pre-Hook Script
 * @link http://www.shopware.de
 * @package tools
 * @subpackage svn-hooks
 * @copyright (C) Shopware AG 2010-2011
 * @version Shopware 3.5.0
 */

define('AWK', '/usr/bin/awk');
define('GREP', '/bin/grep');
define('PHP', '/usr/bin/php');
define('SVNLOOK', '/usr/bin/svnlook');
define('DIVIDER','*****************************************');

include("comment/Comment.php");
include("comment/CommonComment.php");
include("comment/FunctionComment.php");
include("comment/PHPTokenizer.php");
include("comment/loader.php");

$arguments = $_SERVER["argv"];
$transaction = $arguments[3];
$repository = $arguments[1];

$Shopware_SVN = new Shopware_SVN_Actions();

$successfully = false;


// PHP-Lint
file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Running PHP-Lint-Check" . PHP_EOL);
$successfully = $Shopware_SVN->runPhpLint($transaction,$repository);
if ($successfully == false){
	exit(1);
}
// Comment-Style
file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Running Comment-Check" . PHP_EOL);
$successfully = $Shopware_SVN->runCommentCheck($transaction,$repository);
if ($successfully == false){
	exit(1);
}
// Basic Code-Style
file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Running Code-Style Check" . PHP_EOL);
$successfully = $Shopware_SVN->runBasicStyleCheck($transaction,$repository);

if ($successfully == false){
	exit(1);
}

exit(0);

/**
 * Shopware AG SVN Pre-Hook Script / Tools class
 * @link http://www.shopware.de
 * @package tools
 * @subpackage svn-hooks
 * @copyright (C) Shopware AG 2010-2011
 * @version Shopware 3.5.0
 */
class Shopware_SVN_Actions{
		
	/**
	 * Check if sourcecode is commented
	 * @param string $transaction svn transaction no
	 * @param string $repository path to svn repository
	 * @access public
	 * @return boolean true/false
	 */
	public function runBasicStyleCheck($transaction,$repository){
		$success = true;
		$changedOutput = array();
		// Get a list with changed php-files
		$changedCommand = SVNLOOK . ' changed -t "'
	        . $transaction . '" "'
	        . $repository . '" '
	        . '| ' . GREP . ' "^[UA]" '
	        . '| ' . GREP . ' "\\.php$" '
	        . '| ' . AWK . ' \'{print $2}\'';
	    exec($changedCommand, $changedOutput);
	   
	    // Loop through this files
	    foreach ($changedOutput as $file) {
	        
	        $temporaryFile = dirname(__FILE__)."/tmp/".md5($file);
	        
	        $lintCommand = SVNLOOK . ' cat -t "'
	            . $transaction . '" "'
	            . $repository . '" "' . $file . '" > '.escapeshellarg($temporaryFile);
	        
	        exec($lintCommand);
	     	
	        $temporaryCode = file_get_contents($temporaryFile);
	        
	        // Remove Dependencies
	       /* $temporaryCode = preg_replace("/((extends|implements) (.*)( |{|\n))/","\\4",$temporaryCode);
			$temporaryCode = str_replace(array("include ","include_once ","require ","require_once "),array("#include ","#include_once ","#require ","#require_once "),$temporaryCode);
			$temporaryCode = str_replace("parent::","//parent::",$temporaryCode);
			
			
	        file_put_contents($temporaryFile,$temporaryCode);
	        */
	        // Build temporary code
	        $class = "";
	        $classCount = 0;
	        $lint = explode("\n",$temporaryCode);
		
	        // Loop trough code and find class-name
	        foreach ($lint as $line){
	        	if (strpos($line,"class ")!==false){
	        		$tempClass = array();
	        		//preg_match("/class (.*)/",$line,$tempClass);
	        		 $matches=array();
                     if (preg_match('#^(\s*)((?:(?:abstract|final|static)\s+)*)class\s+([-a-zA-Z0-9_]+)(?:\s+extends\s+([-a-zA-Z0-9_]+))?(?:\s+implements\s+([-a-zA-Z0-9_,\s]+))?#',$line,$matches)) 
                     {
                        $class = $matches[3];
                        $classCount += 1;
                     }
	        	}
	        }
	        
	        // One class per file
	        if ($classCount == 1){
	        	file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Analyse comments in $file - Class: $class" . PHP_EOL);
	        	
	        	$ldeMainLoader = new ldeMainLoader();
				$ldeMainLoader->loadData(file_get_contents($temporaryFile));
				$documentation = $ldeMainLoader->docu;
				
				if (empty($documentation->data["classes"][0]["commentObject"]->description)){
					file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ No class comment for $class found!" . PHP_EOL);
	        		$success = false;
				}
	        	// Check Method-Comments
	        	$methods = $documentation->data["classMethods"];

	        	foreach ($methods as $method){
					if (!$method["commentObject"]->description){
						$name = $method["name"];
						file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ No comment for method $name in class $class! Aborting!" . PHP_EOL);
						$success = false;
					}else {
						$description = $method["commentObject"]->description;
						if (strlen($description) <= 15 || strpos($description,"Enter Description here")!==false){
							$name = $method["name"];
							file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Comment for method $name in class $class is not long enough!" . PHP_EOL);
							$success = false;
						}
					}
				}	        	
	        }elseif($classCount > 1){
	        	// More then one class?
	        	file_put_contents('php://stderr', PHP_EOL.DIVIDER.PHP_EOL . "|_ Code-Analyse for $file failed! More then one class per file is forbidden! $classCount classes found!" . PHP_EOL);
	        	$success = false;
	        }else {
	        	// File seems to not consider any classes
	        	$success = true;
	        }
	    }	
		return $success;
	}
	
	/**
	 * Check if svn comment matches requirements
	 * @param string $transaction svn transaction no
	 * @param string $repository path to svn repository
	 * @access public
	 * @return boolean true/false
	 */
	public function runCommentCheck($transaction,$repository){
		$command = SVNLOOK." log -t '". $transaction ."' '". $repository."'";
		$log = exec($command);
		
		$message = PHP_EOL . DIVIDER . PHP_EOL . PHP_EOL;
		$message .= "Comment `$log` did not match requirements (".$command.")";
		$successfully = true;
		
		if (strlen($log) <= 10){
			$successfully = false;
			$message .= PHP_EOL."|_Comment is too short, mininum 10 chars required!";
		}
		if (!preg_match("/\#[0-9][0-9][0-9][0-9]/",$log) && strpos($log,"#NTR")===false){
			$successfully = false;
			$message .= PHP_EOL."|_Comment must contain at least a corresponding ticket-id (Format: #0000) or the NO_TICKET_REQUIRED (#NTR) flag!";
		}
		if ($successfully != true){
			file_put_contents('php://stderr', $message . PHP_EOL);
		}
		return $successfully;
	}
	
	/**
	 * Run php lint against sourcefiles
	 * @param string $transaction svn transaction no
	 * @param string $repository path to svn repository
	 * @access public
	 * @return boolean true/false
	 */
	public function runPhpLint($transaction, $repository) {
	    $success = true;
	    $changedCommand = SVNLOOK . ' changed -t "'
	        . $transaction . '" "'
	        . $repository . '" '
	        . '| ' . GREP . ' "^[UA]" '
	        . '| ' . GREP . ' "\\.php$" '
	        . '| ' . AWK . ' \'{print $2}\'';
	    exec($changedCommand, $changedOutput);
	   
	    foreach ($changedOutput as $file) {
	        $lint = array();
	        $lintCommand = SVNLOOK . ' cat -t "'
	            . $transaction . '" "'
	            . $repository . '" "' . $file . '" '
	            . '| ' . PHP . ' -l';
	        exec($lintCommand, $lint);
	        if ('No syntax errors detected in -' == $lint[0]) {
	            // Lint returns text on good files,
	            // don't write that to standard
	            // error or consider it an error.
	            continue;
	        }
	        $message = PHP_EOL . DIVIDER . PHP_EOL . PHP_EOL
	            . $file . ' contains PHP error(s):' . PHP_EOL;
	        foreach ($lint as $line) {
	            // PHP lint includes some blank lines in its output.
	            if ('' == $line) {
	                continue;
	            }
	            // PHP lint tells us where the error is,
	            // which because we pass it in by piping it
	            // to standard in, doesn't tell us anything.
	            if ('Errors parsing -' == $line) {
	                continue;
	            }
	            $message .= "\t" . $line . PHP_EOL;
	        }
	        file_put_contents('php://stderr', $message . PHP_EOL);
	        $success = false;
	    }
	    return $success;
	}
}

?>