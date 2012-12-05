<?php
require_once(dirname(__FILE__)."/Shopware_Deployment_Colors.php");
/**
 * Shopware Deployment Script
 * @description Get diff between revisions and create various install and patch
 * packages for auto deployment
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Tools
 * @subpackage Deployment
 */
class/**/Shopware_Deployment {
	// Subversion credentials
	public $svnUser = "svn";
	public $svnPassword = "pmnoQpr12";
	public $tagsDirectory = "http://svn.shopware.in/svn/shopware/tags";
	public $trunkDirectory = "http://svn.shopware.in/svn/shopware/trunk";

	// We need an empty database for auto create db-variants
	public $databaseUser = "";
	public $databasePassword = "";
	public $databaseDb = "";
	public $databaseHost = "";

	protected $tag;
	protected $workspace;
	protected $project;

	public $ZendBin = "/usr/local/Zend/ZendGuard-5_0_1/bin/GuardEngine";
	public $ZendXML = "/root/scripts/shopware.xml";
	public $IoncubeBin = "/ioncube/ioncube_encoder5";
	
	protected $diffTag;

	public $config;

	/**
	 * Constructor to initiate this object
	 * Scheme of created file-/directory structures by this script
		 * Directory structure
		 * ROOT/_deploy/$tag_$date
		 * 	|_ patch_packages
		 * 	|__ ioncube
		 *  |__ zend
		 *  |__ plain
		 *  |_ install_packages
		 *  |__ ioncube_empty
		 *  |__ zend_empty
		 *  |__ plain_empty
		 *  |__ ioncube_demo
		 *  |__ zend_demo
		 *  |__ plain_demo
	 * @param  $tag
	 */
	public function __construct()
	{
		echo cmd_colors::bold("red", "Shopware Deployment-Script 1.0 - Start \n\n", "black");
		error_reporting(E_ALL);
		ini_set("display_errors",1);
	}

	/**
	 * Set config object 
	 * @param array $config
	 * @return void
	 */
	public function setConfig(array $config){
		$this->config = $config;
	}

	/**
	 * Set tag to exporr
	 * @param  $tag
	 * @return bool
	 */
	public function setTag($tag){
		$this->tag = $tag;
		return true;
	}

	/**
	 * Get tag that should be exported
	 * @return
	 */
	public function getTag(){
		return $this->tag;
	}

	/**
	 * Set Tag of previous release
	 * @param  $tag
	 * @return bool
	 */
	public function setDiffTag($tag){
		$this->diffTag = $tag;
		return true;
	}

	/**
	 * Get tag of previous release
	 * @return
	 */
	public function getDiffTag(){
		return $this->diffTag;
	}

	/**
	 * Init workspace directory
	 * @throws Exception
	 * @param  $directory
	 * @return void
	 */
	public function initWorkspace($directory){
 		mkdir($directory,0777,true);
		if (!is_dir($directory) || !is_writeable($directory)){
			throw new Exception("Directory $directory could not be created or is not writeable ;(");
		}
	}

	/**
	 * Set project (workspace + tag)
	 * @param  $project
	 * @return void
	 */
	public function setProject($project){
		$this->project = $project;
	}

	/**
	 * Get current active project
	 * @return
	 */
	public function getProject(){
		return $this->project;
	}

	/**
	 * Init project - create exports
	 * @throws Exception
	 * @return bool
	 */
	public function initProject(){
		if (!$this->getTag()){
			throw new Exception("Tag is needed in order to create project workspace");
		}

		$this->setProject($this->getWorkspace()."/".$this->getTag());
		
		if (is_dir($this->getProject())){
			$this->rmdir($this->getProject());
		}
		if (!mkdir($this->getProject())){
			throw new Exception("Project workspace ".$this->getProject()." could not created");
		}

		// Create subfolders
		if (!mkdir($this->getProject()."/checkout/clean",0777,true)){
			throw new Exception("Project workspace CLEAN could not created");
		}
		if (!mkdir($this->getProject()."/checkout/diff",0777,true)){
			throw new Exception("Project workspace DIFF could not created");
		}
		if (!mkdir($this->getProject()."/checkout/encode",0777,true)){
			throw new Exception("Project workspace ENCODE could not created");
		}
		if (!mkdir($this->getProject()."/checkout/zend",0777,true)){
			throw new Exception("Project workspace ENCODE could not created");
		}
		if (!mkdir($this->getProject()."/checkout/ioncube",0777,true)){
			throw new Exception("Project workspace ENCODE could not created");
		}
		if (!mkdir($this->getProject()."/checkout/sql",0777,true)){
			throw new Exception("Project workspace ENCODE could not created");
		}

		// Make clean checkout
		$this->initCheckoutClean();
		
		// Make a diff from previous tag
		if ($this->getDiffTag()){
			$this->initCheckoutDiff();
		}
		
		echo cmd_colors::bold("red", "... Ready to progress ...\n", "black");

		$this->initCheckoutEncode();
		$this->initSqlDeploy();
		$this->checkDeployment();
		
		return true;
	}

	/**
	 * Export sql-archive of shopware
	 * @return void
	 */
	public function initSqlDeploy(){
		echo "Do Sql-Export\n";
		// Do full svn export of given tag
		$path = $this->tagsDirectory."/".$this->getTag()."/_sql/";
		$command = "/usr/bin/svn export ".escapeshellarg($path)." --username ".$this->svnUser." --password ".$this->svnPassword." --non-interactive 2>&1 --force ".escapeshellarg($this->getProject()."/checkout/sql/");
		$diff = array();
		exec($command ,$diff);
		echo cmd_colors::bold("red", count($diff)." Positions in Tag\n", "black");
	}

	/**
	 * Check if all operations done well and creating directories for package
	 * variants
	 * @return void
	 */
	public function checkDeployment()
	{
		$base = $this->getProject()."/checkout/";

		$countZend = $this->countFilesInDirectory($base."zend");
		$countIoncube = $this->countFilesInDirectory($base."ioncube");
		$countEncode = $this->countFilesInDirectory($base."encode");
		$countDiff = $this->countFilesInDirectory($base."diff");
		$countClean = $this->countFilesInDirectory($base."clean");
		echo cmd_colors::bold("red", "Check operations...\n", "black");

		echo cmd_colors::bold("red", "Clean-Checkout results in $countClean files...\n", "black");
		echo cmd_colors::bold("red", "Diff-Checkout results in $countDiff files...\n", "black");
		echo cmd_colors::bold("red", "Encode-Preparement results in $countEncode files...\n", "black");
		echo cmd_colors::bold("red", "Zend-Checkout results in $countZend files...\n", "black");
		echo cmd_colors::bold("red", "Ioncube-Checkout results in $countIoncube files...\n", "black");

		if (empty($countClean) || empty($countZend) || empty($countEncode) || empty($countDiff) || empty($countClean)){
			throw new Exception("Oups. Something went wrong :(");
		}
	}
	
	public function saveEncoding($encodePath, $file)
	{
		$f = fopen($this->getProject() . '/' . $file, 'w');
		
		fwrite($f, '<?xml version="1.0"?>' . "\n");
		fwrite($f, '<shopware>' . "\n");
		fwrite($f, '<files>' . "\n");
		
		$check = '';
		$secret = base64_decode(str_rot13('AoKTV39CMyYdBOeK7T7uNodIOVl7j9+Z2gPIeUJsnq83Esbe'));
		
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				 $this->getProject() . '/' . $encodePath
			)
		);
		foreach ($iterator as $entry) {
			if(substr($entry->getFilename(), -4) != '.php') {
				continue;
			}
			$path = str_replace($this->getProject() . '/' . $encodePath, '', (string) $entry);
			$hash = sha1_file($entry->getPathname());
			fwrite($f, "\t<file><name>$path</name><hash>$hash</hash></file>\n");
			$check .= $path . $hash;
		}
		
		$m = sha1($this->getTag() . $check . $secret);
		fwrite($f, "\t<hash>$m</hash>\n");
		fwrite($f, "\t<version>{$this->getTag()}</version>\n");
		
		fwrite($f, '</files>' . "\n");
		fwrite($f, '</shopware>' . "\n");
	}
	
	public function checkEncoding($encode)
	{
		if (empty($this->config["encode"])){
			throw new Exception("Not to be encrypted file defined.");
		}
		
		$count = 0;
		$fail = 0;
		foreach ($this->config["encode"] as $encodePath) {
			$count++;
			$completePath = $this->getProject() . '/' . $encode . '/' . $encodePath;
			if(!file_exists($completePath)) {
				$fail++;
				var_dump('NF:' . $completePath);
			} elseif(substr($completePath, -1) == '/') {
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$completePath
					)
				);
				foreach ($iterator as $entry) {
					$count++;
					if(!$this->checkEncodeFile($entry->getPathname())) {
						$fail++;
						var_dump('FF:' . $entry->getPathname());
					}
				}
			} else {
				if(!$this->checkEncodeFile($completePath)) {
					$fail++;
					var_dump('DF:' . $completePath);
				}
			}
		}
		if($fail > 0) {
			throw new Exception('Some files (' . $fail . '/' . $count .') in "' . $encode . '" were not encrypted.');
		}
	}
	
	public function checkEncodeFile($file)
	{
		if(!is_file($file)) {
			return false;
		}
		if(substr($file, -4) != '.php' ) {
			return true;
		}
		$handle = @fopen($file, 'r');
		if ($handle) {
		    $line = fgets($handle, 4096);
		    fclose($handle);
		    if(strpos($line, '<?php @Zend;') === 0) {
		    	return true;
		    }
		    if(strpos($line, '<?php //0046a') === 0) {
		    	return true;
		    }
		}
		return false;
	}

	/**
	 * Count all files in a certain directory
	 * @param  $directory
	 * @return
	 */
	protected function countFilesInDirectory($directory){
		$dir_iterator = new RecursiveDirectoryIterator($directory);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		$countFiles = 0;
		foreach ($iterator as $entry) {
			if(!$entry->isFile()) {
				continue;
			}
			$countFiles++;
		}
		return $countFiles;
	}

	/**
	 * Recursive collect files that must be encoded
	 * @return void
	 */
	protected function initCheckoutEncode(){
		echo cmd_colors::bold("red", "Copying files that should be encoded...\n", "black");
		$origin = $this->getProject()."/checkout/clean";
		$target = $this->getProject()."/checkout/encode";
		foreach ($this->config["encode"] as $file){
			if (is_file($origin."/".$file)){
				@mkdir(dirname($target."/".$file),0777,true);
				copy($origin."/".$file,$target."/".$file);
			}elseif (is_dir($origin."/".$file)){
				@mkdir(dirname($target."/".$file),0777,true);
				$this->copyRecursive($origin."/".$file,$target."/".$file);
			}
		}

		// Make Zend Build
		$this->ZendBuild(
			"workspace/".$this->getTag()."/checkout/encode",
			"workspace/".$this->getTag()."/checkout/zend"
		);
		// Make Ioncube Build
		$this->IoncubeBuild($target,$this->getProject()."/checkout/ioncube", $this->getProject()."/checkout");
		
		$configFile = 'engine/Shopware/Plugins/Default/Core/License/CheckBase.xml';
		$this->saveEncoding('checkout/ioncube/', 'checkout/ioncube/' .$configFile);
		$this->saveEncoding('checkout/zend/', 'checkout/zend/' . $configFile);
	}

	/**
	 * Helper function to do recursive copy
	 * @param  $src
	 * @param  $dst
	 * @return void
	 */
	protected function copyRecursive($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->copyRecursive($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Do a clean checkout / export from a given svn path
	 * @return void
	 */
	protected function initCheckoutClean(){
		echo "Do full export of ".$this->getTag()."\n";
		// Do full svn export of given tag
		$path = $this->tagsDirectory."/".$this->getTag();
		$command = "/usr/bin/svn export ".escapeshellarg($path)." --username ".$this->svnUser." --password ".$this->svnPassword." --non-interactive 2>&1 --force ".escapeshellarg($this->getProject().'/checkout/clean/');
		$diff = array();
		exec($command ,$diff);
		echo cmd_colors::bold("red", count($diff)." Positions in Tag\n", "black");
		$this->cleanupDirectory($this->getProject()."/checkout/clean/");
	}

	/**
	 * Create patch between 2 tags
	 * @return void
	 */
	protected function initCheckoutDiff(){
		echo "Creating patch from ".$this->getDiffTag()." to ".$this->getTag()."\n";
		$from =  $this->tagsDirectory."/".$this->getDiffTag();
		$to = $this->tagsDirectory."/".$this->getTag();
		$command = "/usr/bin/svn diff  --old=$from --new=$to  --username ".$this->svnUser." --password ".$this->svnPassword." --non-interactive 2>&1 --force";
		$diff = array();
		exec($command ,$diff);
		$diff = implode("\n",$diff);
		$result = array();
		preg_match_all("#^Index: (.*)\n#Um",$diff,$result);
		$target = $this->getProject()."/checkout/diff";
		$filecounter = 0;
		foreach ($result[1] as $file){
			$file = str_replace(array("Index:"," "),"",$file);
			$folder = dirname($file);
			if (!is_dir($target."/".$folder)){
				mkdir($target."/".$folder,0777,true);
			}
			$targetFile = escapeshellarg($target."/".$folder."/".basename($file));
			$checkout = escapeshellarg($this->tagsDirectory."/".$this->getTag()."/".$file);
			$command = "/usr/bin/svn export $checkout $targetFile --username ".$this->svnUser." --password ".$this->svnPassword." --non-interactive 2>&1";
			exec($command ,$diff);
			$filecounter++;
		}
		$this->cleanupDirectoryDiff($this->getProject()."/checkout/diff/");
		echo cmd_colors::bold("red", "$filecounter changed files detected\n", "black");
	}

	/**
	 * Apply all commands / rules from deployment config
	 * @param  $directory
	 * @return void
	 */
	public function cleanupDirectory($directory)
	{
		foreach ($this->config["rmdir"] as $removeDirectory){
			if (is_dir($directory."/".$removeDirectory)){
				$this->rmdir($directory."/".$removeDirectory);
			}
		}
		foreach ($this->config["rmfile"] as $removeFile){
			if (is_file($directory."/".$removeFile)){
				unlink($directory."/".$removeFile);
			}
		}
		foreach ($this->config["mkdir"] as $makeDirectory){
			@mkdir($directory."/".$makeDirectory,0777,true);
		}
		foreach ($this->config["mvfile"] as $oldFile => $newFile){
			if (is_file($directory."/".$oldFile)) {
				rename($directory."/".$oldFile,$directory."/".$newFile);
			}
		}
		foreach ($this->config["touch"] as $newFile => $content){
			@mkdir($directory."/".$makeDirectory,0777,true);
			file_put_contents($directory."/".$newFile,$content);
		}
	}
	
	/**
	 * Apply all commands / rules from deployment config
	 * @param  $directory
	 * @return void
	 */
	public function cleanupDirectoryDiff($directory)
	{
		foreach ($this->config["rmdir"] as $removeDirectory){
			if (is_dir($directory."/".$removeDirectory)){
				$this->rmdir($directory."/".$removeDirectory);
			}
		}
		foreach ($this->config["rmfile"] as $removeFile){
			if (is_file($directory."/".$removeFile)){
				unlink($directory."/".$removeFile);
			}
		}
		foreach ($this->config["mvfile"] as $removeFile => $removeFile2){
			if (is_file($directory."/".$removeFile)){
				unlink($directory."/".$removeFile);
			}
		}
	}

	/**
	 * Set workspace directory
	 * @param  $directory
	 * @return bool
	 */
	public function setWorkspace($directory){
		$this->workspace = $directory;
		return true;
	}

	/**
	 * Get workspace directory
	 * @return
	 */
	public function getWorkspace(){
		return $this->workspace;
	}

	/**
	 * Recursive delete a directory
	 * @throws Exception
	 * @param  $dir
	 * @return void
	 */
	protected function rmdir($dir) {
	   if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
		   if ($object != "." && $object != "..") {
			 if (filetype($dir."/".$object) == "dir") $this->rmdir($dir."/".$object); else unlink($dir."/".$object);
		   }
		 }
		 reset($objects);
		 rmdir($dir);
	   }else {
		   throw new Exception("Directory $dir could not deleted");
	   }
	}

	/**
	 * Do Zend Encoding of a certain directory structure
	 * @param  $source
	 * @param  $target
	 * @param  $base
	 * @return void
	 */
	public function ZendBuild($source,$target){
		/*
		$xml = file_get_contents($this->ZendXML);

		// Replace placeholders Target & Source Path
		$xml = str_replace("%%TARGET%%",escapeshellarg($target),$xml);
		$xml = str_replace("%%SOURCE%%",escapeshellarg($source),$xml);
		file_put_contents($base."/shopware.xml",$xml);

		// Make build
		$output = array();
		$xmlDir = escapeshellarg($base."/shopware.xml");
		exec($this->ZendBin." --xml-file $xmlDir",$output);
		*/
		
		// /usr/local/Zend/ZendGuard-5_0_1/bin/zendenc5

		$output = array();
		$target = escapeshellarg($target);
		$source = escapeshellarg($source);
		exec("/usr/local/Zend/ZendGuard-5_0_1/bin/zendenc5 --silent --recursive $source $target", $output);
	}

	/**
	 * Do ioncube encoding of a certain directory structure
	 * @param  $source
	 * @param  $target
	 * @param  $base
	 * @return void
	 */
	public function IoncubeBuild($source,$target,$base){
		$output = array();
		$sourceArg = escapeshellarg($source);
		$targetArg = escapeshellarg($target);
		exec($this->IoncubeBin." --replace-target $sourceArg -o $targetArg",$output);
	}

}
