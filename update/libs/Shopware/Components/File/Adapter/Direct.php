<?php
class	Shopware_Components_File_Adapter_Direct
{	
	public function putContents($remoteFile, $data)
	{
		return $this->file_put_contents($remoteFile, $data);
	}
	
	public function put($remoteFile, $handle)
	{
		if(is_string($handle)) {
			$handle = fopen($handle, 'rb');
		}
		return $this->file_put_contents($remoteFile, $handle);
	}
	
	public function changeMode($remoteFile, $mode)
	{
		$oldUmask = umask(0);
		$result = $this->chmod($remoteFile, $mode);
		umask($oldUmask);
		return $result;
	}
	
	public function makeDir($remoteFile, $mode=null, $recursive=false)
	{
		if(file_exists($remoteFile)) {
			return true;
		}
		if($mode !== null) {
			$oldUmask = umask(0);
			$result = $this->mkdir($remoteFile, $mode, $recursive);
			umask($oldUmask);
		} else {
			$result = $this->mkdir($remoteFile, 0, $recursive);
		}
		return $result;
	}
	
	public function fileExists($remoteFile)
	{
		return $this->file_exists($remoteFile);
	}
	
	public function delete($remoteFile)
	{
		return $this->unlink($remoteFile);
	}
	
	public function removeDir($remoteFile)
	{
		return $this->rmdir($remoteFile);
	}
	
	public function __call($name, $args = array())
	{
		$this->lastError = null;
		$oldErrorHandler = set_error_handler(array($this, 'handleError'), E_ALL);
		$result = call_user_func_array($name, (array) $args);
		set_error_handler($oldErrorHandler);
		if($this->lastError!==null) {
			throw new ErrorException($this->lastError[1], 0, $this->lastError[0], $this->lastError[2], $this->lastError[3]);
		}
		return $result;
	}
	
	public function handleError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $this->lastError = array($errno, $errstr, $errfile, $errline);
        return true;
    }
}