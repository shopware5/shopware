<?php
class	Shopware_Components_File_Adapter_Ftp
{
	const DEFAULT_PORT = 21;
	const DEFAULT_TIMEOUT = 90;
	
	const MODE_ASCII = 1;
	const MODE_BINARY = 2;
	
	const WRAPPER_SSL = 'ssl';
	
	protected $stream;
	protected $lastError;
	
	public function __construct($host, $port=self::DEFAULT_PORT, $timeout=self::DEFAULT_TIMEOUT, $wrapper=null)
	{
		if (!extension_loaded('ftp')) {
            throw new Exception('The ftp extension are required');
        }
        
        $this->lastError = null;
		set_error_handler(array($this, 'handleError'), E_ALL);
        
		if($wrapper==self::WRAPPER_SSL) {
			$this->stream = ftp_ssl_connect($host, $port, $timeout);
		} else  {
			$this->stream = ftp_connect($host, $port, $timeout);
		}
		
		restore_error_handler();
		if(!$this->stream && $this->lastError!==null) {
			throw new ErrorException($this->lastError[1], 0, $this->lastError[0], $this->lastError[2], $this->lastError[3]);
		}
	}
	
    public function __destruct ()
	{
		if($this->stream!==null) {
			ftp_quit($this->stream);
		}
        $this->stream = null;
    }
    
	public function __call($name, $arguments)
	{
		return $this->execute($name, $arguments);
	}
	
	public function execute($command, $arguments=null, $throwErrors=true)
	{
		$this->lastError = null;
		$oldErrorHandler = set_error_handler(array($this, 'handleError'), E_ALL);
		
		$arguments = (array) $arguments;
		array_unshift($arguments, $this->stream);
		$name = 'ftp_'.$command;
		$result = call_user_func_array($name, $arguments);
		set_error_handler($oldErrorHandler);
		if($throwErrors && $this->lastError!==null) {
			throw new ErrorException($this->lastError[1], 0, $this->lastError[0], $this->lastError[2], $this->lastError[3]);
		}
		return $result;
	}
	
	public function handleError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $this->lastError = array($errno, $errstr, $errfile, $errline);
        return true;
    }
	
	public function fileExists($remoteFile)
	{
    	$remoteDir = dirname($remoteFile);
    	$list = $this->nlist($remoteDir);
        if (empty($list)) {
            return false;
        }
        $remoteFile = basename($remoteFile);  	
        foreach ($list as  $value) {
            if (basename($value) == $remoteFile) {
                return true;
            }
        }
        return false;
    }
    
    public function nlist($path)
	{
		if($path == '.') {
    		$path = '';
    	}
		return $this->execute('nlist', array($path), false);
	}
    
    public function isDir($path)
	{
		$origin = $this->pwd(); 
	    if ($this->execute('chdir', $path, false)) { 
	        $this->chdir($origin);    
	        return true; 
	    }
	    return false; 
	}
	
	public function getRealPath($path)
	{
		$origin = $this->ftp_pwd(); 
	    if ($this->execute('chdir', $path, false)) { 
	    	$result = $this->pwd(); 
	        $this->chdir($origin);    
	        return $result; 
	    }
	    return false; 
	}
	
	public function isFile($remoteFile)
	{
		return $this->size($remoteFile) !== -1;
	}
	
	public function putContents($remoteFile, $data, $mode=self::MODE_BINARY)
	{
		$tmpHandle = fopen('php://temp', 'r+');
		fwrite($tmpHandle, $data);
		rewind($tmpHandle);
		$result = ftp_fput($this->stream, $remoteFile, $tmpHandle, $mode);
		fclose($tmpHandle);
		return $result;
	}
	
	public function put($remoteFile, $handle, $mode=self::MODE_BINARY, $maxLength=-1, $offset=0)
	{
		if(is_string($handle)) {
			$handle = fopen($handle, 'rb');
		}
		if($maxLength==-1 && $offset==0) {
			return $this->fput($remoteFile, $handle, $mode);
		} else {
			$tmpHandle = fopen('php://temp', 'r+');
			stream_copy_tostream($handle, $tmpHandle, $maxLength, $offset);
			rewind($tmpHandle);
			$result = $this->fput($remoteFile, $tmpHandle, $mode);
			fclose($tmpHandle);
			return $result;
		}
	}
	
	public function changeMode($remoteFile, $mode)
	{
		if(is_numeric($remoteFile)) {
			$tmp = $remoteFile;
			$remoteFile = $mode;
			$mode = $tmp;
		}
		$this->execute('chmod', array($mode, $remoteFile));
		return $this;
	}
	
	public function makeDir($remoteFile, $mode=null, $recursive=false)
	{
		if($recursive && !$this->fileExists(dirname($remoteFile))) {
			$this->makeDir(dirname($remoteFile), $mode, $recursive);
		}
		if(!$this->fileExists($remoteFile)) {
			$this->execute('mkdir', array($remoteFile));
		}
		if($mode !== null) {
			$this->execute('chmod', array($mode, $remoteFile), false);
		}
		return $this;
	}
	
	public function removeDir($remoteFile)
	{
		return $this->rmdir($remoteFile);
	}
}