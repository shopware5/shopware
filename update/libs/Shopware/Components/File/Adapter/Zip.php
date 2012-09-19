<?php
class	Shopware_Components_File_Adapter_Zip extends Shopware_Components_File_Adapter
{
	protected $stream;
	
	public function __construct($fileName=null, $flags=null)
	{
		if (!extension_loaded('zip')) {
            throw new Exception('Die Zip-Erweiterung fehlt.');
        }
		$this->stream = new ZipArchive();
		if($fileName != null) {
			$res = @$this->stream->open($fileName, $flags);
			if($res !== true) {
				throw new Exception($this->stream->getStatusString());
			}
			$this->position = 0;
			$this->count = $this->stream->numFiles;
		}
	}
	
	public function current()
	{
		return new Shopware_Components_File_Entry_Zip($this, $this->position);
	}
	
	public function getStream($name)
	{
		return $this->stream->getStream($name);
	}
	
	public function getContents($name)
	{
		return $this->stream->getFromName($name);
	}
	
	public function getEntry($position)
	{
		return $this->stream->statIndex($position);
	}
}