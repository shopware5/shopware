<?php
class	Shopware_Components_File_Entry_Zip
{
	protected $position;
	protected $stream;
	protected $entry;
	
	public function __construct($stream, $position)
	{
		$this->position = $position;
		$this->stream = $stream;
		$this->entry = $stream->getEntry($position);
	}
	
	public function getStream()
	{
		return $this->stream->getStream($this->entry['name']);
	}
	
	public function getContents()
	{
		return $this->stream->getContents($this->entry['name']);
	}
	
	public function isDir()
	{
		return substr($this->entry['name'], -1) === '/';
	}
	
	public function isFile()
	{
		return substr($this->entry['name'], -1) !== '/';
	}
	
	public function getName()
	{
		return $this->entry['name'];
	}
}